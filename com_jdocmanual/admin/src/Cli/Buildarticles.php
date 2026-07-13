<?php

/**
 * @package     Jdocmanual
 * @subpackage  Cli
 *
 * @copyright   Copyright (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Cli;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\ParameterType;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\Markdown2html;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\Responsive;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Build the jdm_articles table.
 *
 * @since  1.0.0
 */
class Buildarticles
{
    /**
     * Path to local source of markdown files.
     *
     * @var     string
     */
    protected $gfmfiles_path;

    /**
     * The pattern used to look for img links in markdown files.
     *
     * @var     string
     *
     * ![manual view](../../../en/images/jdocmanual/introduction-to-jdocmanual/00-jdocmanual.png)
     * $matches[0] will be the matched string to be replaced
     * $matches[1] will be the alt text, 'manual view'
     * $matches[2] will be the language, 'en'
     * $matches[3] will be the path segment, 'jdocmanual/introduction-to-jdocmanual/00-jdocmanual.png'
     * $matches[4] is a Title string or "Title string"
     */
    protected $imgPattern = '/\!\[(.*?)\]\(.*\/(.*?)\/images\/([^\s\)]+)(?:\s+(.*?))?\)/';

    /**
     * Regex pattern to select metadata from html comment string in markdown file.
     *
     * @var     string
     */
    protected $metadata = '/<!--.*?({.*?}).*-->/s';

    /**
     * Instance holder for the responsive image function called for every image.
     *
     * @var object
     */
    protected $responsive;

    /**
     * Saves typing $this->db everywhere
     *
     * @var object
     */
    protected $db;

    /**
     * The number of minutes since the last recorded update.
     *
     * @var integer
     */
    protected $minutes;

    // Remove this when function xxx is removed
    protected $toclevel = 0;

    /**
     * Array to store ll of the paths to articles in the manual being processed
     * 
     * @var array
     */
    protected $articlePaths = [];

    /**
     * Array used for building $articlePaths in recursive call
     * 
     * @var array
     */
    protected $parentPaths = [];

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->responsive = new Responsive();
        $this->db = Factory::getContainer()->get('DatabaseDriver');
    }

    /**
     * Entry point to convert md to html and save.
     *
     * @param   string  $manual     The manual to process.
     * @param   string  $language   The the language to process.
     * @param   integer $force      Age in minutes to be included in a rebuild, 0 for force rebuild all
     *
     * @return  string  A message reporting the outcome.
     *
     * @since   1.0
     */
    public function go($manual, $language, $force)
    {
        $time_start = microtime(true);

        // Check that the manual and article are installed
        $check = $this->preFlightCheck($manual, $language);
        if (empty($check[0])) {
            return $check[1];
        }

        // The memory limit needs to be quite large to build all of the articles.
        ini_set("memory_limit", "2048M");

        // Set the time limit to 10 minutes
        set_time_limit(600);

        // The menu.json file is always needed. Convert to am obkect.
        $menuObject = $this->getMenuObject($manual);
        if (empty($menuObject)) {
            // Return the error message.
            return 'The menu.json file is missing or invalid';
        }

        // The menu list needs to be turned into an array of filenames.
        // introduction
        // folder_name/anarticle

        // The array is stored in $this->articlePaths
        $this->getArticlePaths($menuObject);

        // Get a list of files that have changed or contain changed images.
        if (!empty($force)) {
            $articles_updated = $this->getArticlesUpdated($manual, $language, $force);
            $paths = array_intersect($this->articlePaths, $articles_updated);
        } else {
            $paths = $this->articlePaths;
        }

        $summary = '';
        $total = 0;
        foreach ($paths as $path) {
            list ($count, $note) = $this->setOneArticle($manual, $language, $path);
            $summary .= $note;
            $total += $count;
        }

        // Set the last update date/time for this manual and language
        $db = $this->db;

        $now = date('Y-m-d H:i:s');
        $query = $db->createQuery();
        $query->update($db->quoteName('#__jdm_git_updates'))
        ->set($db->quoteName('last_update') . ' = ' . $db->quote($now))
        ->where($db->quoteName('manual') . ' = :manual')
        ->where($db->quoteName('language') . ' = :language')
        ->bind(':manual', $manual, ParameterType::STRING)
        ->bind(':language', $language, ParameterType::STRING);
        $db->setQuery($query);
        $db->execute();

        $summary .= "\nFor {$manual}/{$language} {$total} articles were updated.\n";

        //$summary .= $this->setMenus($manual, $language);

        $time_end = microtime(true);
        $execution_time = $time_end - $time_start;

        $summary .= 'Total Execution Time: ' . number_format($execution_time, 2) . ' Seconds' . "\n\n";

        return $summary;
    }

    /**
     * Look for repo images to make into picture srcset.
     *
     * @param   $manual     The manual to process.
     * @param   $language   The language to be processed.
     * @param   $path       The path to the image in the source folder.
     * @param   $contents   A single page content in Markdown format.
     *
     * @return  $html       Content with Markdown img links converted to html picture tags.
     *
     * @since   1.0
     */
    private function fixImages($manual, $language, $path, $contents)
    {

        $test = preg_match_all($this->imgPattern, $contents, $matches, PREG_SET_ORDER);

        $srcdir = dirname($path);
        $dest_set = false;

        foreach ($matches as $match) {
            // Create a destination folder if it does not exist exist?
            if (empty($dest_set)) {
                $tmp = str_replace('.md', '', $path);

                $dest = JPATH_ROOT . "/jdmimages/{$manual}/{$match[2]}/{$tmp}";
                if (!is_dir($dest)) {
                    mkdir($dest, 0755, true);
                }
                $dest_set = true;
            }

            // $match[0] is the whole line to be replaced with a picture tag.
            // $match[1] is the alt text 
            // $match[2] is the language
            // $match[3] is the path, example: introduction-to-jdocmanual/00-jdocmanual.png
            // $match[4] is a Title string or "Title string"

            // Copy the image to the images folder.
            $origin = "{$this->gfmfiles_path}{$manual}/{$match[2]}/images/{$match[3]}";
            $destination = JPATH_ROOT . "/jdmimages/{$manual}/{$match[2]}/{$match[3]}";
            $link = "jdmimages/{$manual}/{$match[2]}/{$match[3]}";

            file_put_contents($destination, file_get_contents($origin));

            $title = $match[4] ?? '';
            // Create an img src set and set of images from an img tag.
            $img = '<img src="' . $link . '" alt="' . $match[1] . '" title="' . $title . '" class="screenshot">';
            $processed = $this->responsive->transformImage($img);

            if (!empty($processed)) {
                $contents = str_replace($match[0], $processed, $contents);
            }
        }
        return $contents;
    }

    protected function getArticlePaths($items)
    {
        // traverse the items in the menu list object
        foreach($items as $key => $item) {
            // Is this item an array?
            if (is_array($item)) {
                $this->parentPaths[] = $key;
                $this->getArticlePaths($item);
            } else {
                $path = array_merge($this->parentPaths, [$key]);
                $this->articlePaths[] = implode('/', $path);
            }
        }
        array_pop($this->parentPaths);
    }

    /**
     * Get a list of files updated since the last entry was made in the
     * #__jdm_git_updates table for this manual and language. Use a linux
     * command to find updated article and image files.
     *
     * @param string    $manual     The manual name.
     * @param string    $language   The language name.
     *
     * @return  $array  A list of articles to update.
     */
    protected function getArticlesUpdated($manual, $language, $timeout)
    {
        // Example command to find files changed less than 60 minutes ago:
        // find /Users/ceford/git/cefjdemos/manuals/help/en/articles -mmin -60

        // Tried git too but decided not to use it
        // git diff --name-only "@{2024-09-14 22:00:00}"

        $articlesDir = $this->gfmfiles_path . $manual . '/' . $language . '/articles';

        $threshold = time() - (($this->minutes + $timeout) * 60);

        $result = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($articlesDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                if ($file->getMTime() >= $threshold) {
                    $result[] = $file->getPathname();
                }
            }
        }

        $articles = [];
        foreach ($result as $line) {
            // Skip any extraneous lines.
            if (str_ends_with($line, '.md')) {
                $articles[] = $line;
            }
        }

        // In Version 2 images are in a folder with the same name as the article
        $imagesDir = $this->gfmfiles_path . $manual . '/' . $language . '/images';

        // If the images folder does not exist skip this section
        if (is_dir($imagesDir)) {
            $result = [];

            $imgiterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($imagesDir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($imgiterator as $img) {
                if ($img->isFile()) {
                    if ($img->getMTime() >= $threshold) {
                        $result[] = $img->getPathname();
                    }
                }
            }

            foreach ($result as $line) {
                // Skip any lines not containing an image file
                if ($is_picture = getimagesize($line) !== FALSE) {
                    $tmp = substr($line, 0, strrpos($line, '/')) . '.md';
                    $articles[] = $tmp;
                }
            }
        }

        // Need to remove path elements up to /articles
        // "/Users/ceford/git/cefjdemos/manuals/docs/en/articles/jdocmanual/jugl-2025-05-20.md"
        $updates = [];
        foreach($articles as $article) {
            $updates[] = preg_replace('/(.*?\/articles\/)/', '', $article);
        }

        // Eliminate duplicates.
        return array_unique($updates);
    }

    /**
     * Read the menu.json file in the default language and make an array of articles data.
     *
     * @param string    $manual     The manual name.
     * @param string    $language   The language name.
     *
     * @return object   
     */
    protected function getMenuObject($manual)
    {
        $params = ComponentHelper::getParams('com_jdocmanual');
        $default_language = $params->get('default_language');

        $menuObject = $this->gfmfiles_path . $manual . '/' . $default_language . '/menu.json';
        if (!file_exists($menuObject)) {
            return;
        }

        // Read in the menu.json file.
        $tmp = file_get_contents($menuObject);

        // Decode the json to give an object.
        return json_decode($tmp, true);
    }

    /**
     * Check that the required manual and language data are installed.
     *
     * @param string    $manual     The manual name.
     * @param string    $language   The language name.
     *
     * @return array    [true/false, message].
     */
    protected function preFlightCheck($manual, $language)
    {
        $params = ComponentHelper::getParams('com_jdocmanual');

        // Get the the 'manuals' path from the component parameters.
        $this->gfmfiles_path = $params->get('gfmfiles_path');

        // If not set return an error message.
        if (empty($this->gfmfiles_path)) {
            return [false,  "\nThe Markdown source could not be found: {$this->gfmfiles_path}. Set in Jdocmanual configuration.\n"];
        }
        // Get the git repo location of a manuel/language.
        $gitpath = $params->get('gfmfiles_path') . $manual . '/' . $language;

        $db = $this->db;

        // If there is an articles directory assume valid.
        if (is_dir($gitpath . '/articles')) {
            // Check for an entry in the #__jdm_git_updates table.
            $query = $db->createQuery();
            $query->select($db->quoteName('last_update'))
            ->from($db->quoteName('#__jdm_git_updates'))
            ->where($db->quoteName('manual') . ' = :manual')
            ->where($db->quoteName('language') . ' = :language')
            ->bind(':manual', $manual, ParameterType::STRING)
            ->bind(':language', $language, ParameterType::STRING);
            $db->setQuery($query);
            $last_update = $db->loadResult();

            // if the date is empty create a new record.
            if (empty($last_update)) {
                $last_update = '2020-01-01 00:00:00';
                // Make a new entry.
                $now = date('Y-m-d H:i:s');
                $query = $db->createQuery();
                $query->insert($db->quoteName('#__jdm_git_updates'))
                ->set($db->quoteName('manual') . ' = :manual')
                ->set($db->quoteName('language') . ' = :language')
                ->set($db->quoteName('last_update') . ' = ' . $db->quote($last_update))
                ->bind(':manual', $manual, ParameterType::STRING)
                ->bind(':language', $language, ParameterType::STRING);
                $db->setQuery($query);
                $db->execute();
            }
            // Convert $last_update to minutes in the past and save it
            $now = new \DateTime();
            $past = new \DateTime($last_update);
            $interval = $past->diff($now);
            $this->minutes = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;
            return [true, "\nGood to go"];
        }
        return [false, "The quoted manual and/or language are not installed: {$manual}/{$language}\n"];
    }

    /**
     * Populate the __jdm_menu_headings table. The ini file is used in building menus.
     *
     * @param string    $manual     The manual name.
     * @param string    $language   The language name.
     *
     * @return string    The number of headings processed.
     */
    protected function setMenus($manual, $language)
    {

        // The articles index is always needed.
        $menuObject = $this->getMenuObject($manual);
        if (empty($menuObject)) {
            // Return the error message.
            return 'The menu.json file is missing or invalid';
        }

        $db = $this->db;
        $count = 0;

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $contents) as $line) {
            if (empty($line)) {
                continue;
            }
            list ($heading, $translation) = explode('=', $line);
            // Check if there is an existing entry.
            $query = $db->createQuery();
            $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__jdm_menu_headings'))
            ->where($db->quoteName('manual') . ' = :manual')
            ->where($db->quoteName('language') . ' = :language')
            ->where($db->quoteName('heading') . ' = :heading')
            ->bind(':manual', $manual, ParameterType::STRING)
            ->bind(':language', $language, ParameterType::STRING)
            ->bind(':heading', $heading, ParameterType::STRING);
            $db->setQuery($query);
            $id = $db->loadResult();

            $query = $db->createQuery();
            if (empty($id)) {
                $query->insert($db->quoteName('#__jdm_menu_headings'))
                ->set($db->quoteName('manual') . ' = :manual')
                ->set($db->quoteName('language') . ' = :language')
                ->set($db->quoteName('heading') . ' = :heading')
                ->bind(':manual', $manual, ParameterType::STRING)
                ->bind(':language', $language, ParameterType::STRING)
                ->bind(':heading', $heading, ParameterType::STRING);
            } else {
                $query->update($db->quoteName('#__jdm_menu_headings'))
                ->where($db->quoteName('id') . ' = ' . $id);
            }
            $query->set($db->quoteName('title') . ' = :translation')
            ->bind(':translation', $translation, ParameterType::STRING);
            $db->setQuery($query);
            $db->execute();
            $count += 1;
        }
        return "Headings translated: {$count}\n";
    }

    /**
     * Build the HTML for one article and make an entry in the database.
     *
     * @param string    $manual     The manual name.
     * @param string    $language   The language name.
     * @param string    $path       The item path.
     *
     * @return array    [0|1, message].
     */
    protected function setOneArticle($manual, $language, $path)
    {
        $db = $this->db;
        $gfm_file = $this->gfmfiles_path . $manual . '/' . $language . '/articles/' . $path;
        if (!file_exists($gfm_file)) {
            // Many manuals in languages other than en will be missing articles.
            // Normal and no message needed.
            return [0, ''];
        }

        // Get the last modified timestamp
        $last_mod = date('Y-m-d H:i:s', @filemtime($gfm_file));

        // Remove .md from the end of the path.
        $dbpath = str_replace('.md', '', $path);

        // Check if there is an entry for this article.
        $query = $db->createQuery();
        $query->select($db->quotename('id'))
        ->select($db->quotename('modified'))
        ->from($db->quotename('#__jdm_articles'))
        ->where($db->quotename('manual') . ' = :manual')
        ->where($db->quotename('language') . ' = :language')
        ->where($db->quotename('path') . ' = :path')
        ->bind(':manual', $manual, ParameterType::STRING)
        ->bind(':language', $language, ParameterType::STRING)
        ->bind(':path', $dbpath, ParameterType::STRING);
        $db->setQuery($query);
        $row = $db->loadObject();

        $id = empty($row) ? 0 : $row->id;
        $contents = file_get_contents($gfm_file);

        // In Version 2 there is json structute inside <!-- --> at the top of the file
        $test = preg_match($this->metadata, $contents, $matches);

        if (empty($test)) {
            $summary = "Warning {$manual}/{$language}/{$path} does not contain metadata\n";
            $fn = substr($path, strrpos($path, '/'));
            $source_url = 'Unknown';
            $title = ucwords(str_replace('_', ' ', $fn));
        } else {
            $metadata = json_decode($matches[1]);
            $summary = '';
            $description = $metadata->description ?? ''; 
            $source_url = $metadata->source ?? 'Not specified in article!';
            $title = $metadata->title ?? 'Not specified in article!';
            $author = $metadata->author ?? '';
        }

        // Process the images for this article.
        $contents = $this->fixImages($manual, $language, $path, $contents);

        // Create the Markdown for this article.
        $html = Markdown2html::go($contents);

        $query = $db->createQuery();
        if (empty($id)) {
            // If id was empty do an insert.
            $query->insert($db->quotename('#__jdm_articles'));
        } else {
            // Otherwise do an update.
            $query->update($db->quotename('#__jdm_articles'));
            $query->where($db->quotename('id') . ' = ' . $id);
        }

        $query->set($db->quotename('source_url') . ' = :source_url')
        ->set($db->quotename('manual') . ' = :manual')
        ->set($db->quotename('language') . ' = :language')
        ->set($db->quotename('path') . ' = :path')
        ->set($db->quotename('title') . ' = :title')
        ->set($db->quotename('html') . ' = :html')
        ->set($db->quotename('modified') . ' = :last_mod')
        ->bind(':source_url', $source_url, ParameterType::STRING)
        ->bind(':manual', $manual, ParameterType::STRING)
        ->bind(':language', $language, ParameterType::STRING)
        ->bind(':path', $dbpath, ParameterType::STRING)
        ->bind(':title', $title, ParameterType::STRING)
        ->bind(':html', $html, ParameterType::STRING)
        ->bind(':last_mod', $last_mod, ParameterType::STRING);
        $db->setQuery($query);
        $db->execute();

        return [1, $summary];
    }
}

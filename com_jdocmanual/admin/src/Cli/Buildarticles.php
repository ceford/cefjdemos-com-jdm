<?php

/**
 * @package     Jdocmanual
 * @subpackage  Cli
 *
 * @copyright   Copyright (C) 2023 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Cli;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\ParameterType;
use Joomla\CMS\Plugin\PluginHelper;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\Markdown2html;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\Responsive;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\BuildHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined(constant_name: '_JEXEC') or die;
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
     * The pattern used to look for img links in markdown.md files.
     *
     * @var     string
     *
     * Example link: ![action logs module form](../../../images/en/admin-modules/modules-actionlogs-latest-screenshot.png "Action Logs Module Form")
     * In the pattern:
     * First bracket is alt text.
     * Second bracket is img url to be added to root/manual/
     * Third bracket is filename
     * Fourth bracket is title or empty
     */
    protected $pattern = '/\!\[(.*?)\]\(..\/..\/..(\/.*)?\/(.*?\.png|.*?\.jpg|.*?\.gif)(.*)\)/m';

    /**
     * Regex pattern to select Display title from GFM comment string.
     *
     * @var     string
     */
    protected $pattern2 = '/<!--.*Filename:(.*)?\/.*Display title:(.*)?-->/m';

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
     * @param   string  $force      Flag to force rebuild of all articles
     *
     * @return  string  A message reporting the outcome.
     *
     * @since   1.0.0
     */
    public function go($manual, $language, $force = false)
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

        // The articles index is always needed.
        $check = $this->getArticlesIndex($manual, $language);
        if (empty($check[0])) {
            // Return the error message.
            return $check[1];
        }
        $articles_indexed = $check[1];

        // Get a list of files that have changed or contain changed images.
        if (!$force) {
            $articles_updated = $this->getArticlesUpdated($manual, $language);
            $articles = array_intersect($articles_indexed, $articles_updated);
        } else {
            $articles = $articles_indexed;
        }
        $summary = '';
        $total = 0;
        foreach ($articles as $article) {
            list ($heading, $filename) = explode('/', $article);
            list ($count, $note) = $this->setOneArticle($manual, $language, $heading, $filename);
            $summary .= $note;
            $total += $count;
        }

        // Set the last update date/time for this manual and language
        $db = $this->db;

        $now = date('Y-m-d H:i:s');
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__jdm_git_updates'))
        ->set($db->quoteName('last_update') . ' = ' . $db->quote($now))
        ->where($db->quoteName('manual') . ' = :manual')
        ->where($db->quoteName('language') . ' = :language')
        ->bind(':manual', $manual, ParameterType::STRING)
        ->bind(':language', $language, ParameterType::STRING);
        $db->setQuery($query);
        $db->execute();

        $summary .= "\nFor {$manual}/{$language} {$total} articles were updated.\n";

        $summary .= $this->setMenuHeadings($manual, $language);

        $time_end = microtime(true);
        $execution_time = $time_end - $time_start;

        $summary .= 'Total Execution Time: ' . number_format($execution_time, 2) . ' Seconds' . "\n\n";

        return $summary;
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
            $query = $db->getQuery(true);
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
                $query = $db->getQuery(true);
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
     * Read the articles-index.txt file and make an array of articles data.
     *
     * @param string    $manual     The manual name.
     * @param string    $language   The language name.
     *
     * @return array    [true|false, message]
     */
    protected function getArticlesIndex($manual, $language)
    {
        // Read in articles-index.txt - changed to new format ini
        $articles_index = $this->gfmfiles_path . $manual . '/en/articles-index.txt';
        if (!file_exists($articles_index)) {
            $summary = "Skipping {$manual} - file does not exist: /en/{$articles_index}\n";
            return [false, $summary];
        }

        // Read in the articles_index : $heading, $filename, $source_url
        $tmp = file_get_contents($articles_index);

        // Create an array from the valid lines.
        $articles = [];
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $tmp) as $line) {
            if (empty($line)) {
                continue;
            }
            // The lines may contain more than two = symbols.
            $one_article = explode('=', $line, 3);
            array_pop($one_article);
            $articles[] = implode('/', $one_article);
        }
        return [true, $articles];
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
    protected function getArticlesUpdated($manual, $language)
    {
        // Example command to find files changed less than 60 minutes ago:
        // find /Users/ceford/git/cefjdemos/manuals/help/en/articles -mmin -60

        // Tried git too but decided not to use it
        // git diff --name-only "@{2024-09-14 22:00:00}"

        $articles = $this->gfmfiles_path . $manual . '/' . $language . '/articles';
        $images = $this->gfmfiles_path . $manual . '/' . $language . '/images';

        // Use the find command to locate files that have changed since the last update.
        $command1 = "find {$articles} {$images} -mmin -" . $this->minutes + 5;
        exec($command1, $result);

        $articles = [];
        foreach ($result as $line) {
            // Skip any extraneous lines.
            if (str_ends_with($line, '.md')) {
                $articles[] = $line;
            } elseif (str_ends_with($line, '.png') || str_ends_with($line, '.jpg')) {
                // For each image file - need to find the article that contains it.
                // This command returns the path/to/filename.md:line no:line containing search term
                //grep --include=\*.md -rnw '/Users/ceford/git/cefjdemos/manuals/help/en/articles/' -e "articles/articles-list.png"
                $segments = explode('/', $line);
                // The last two items are the parts of the image path to search for
                $img_name = implode('/', array_slice($segments, -2));
                // All but the last two are where to search.
                array_pop($segments);
                array_pop($segments);
                $path = implode('/', $segments);

                // Search in the articles for the image.
                $path = str_replace('/images', '/articles', $path);
                $command2 = "grep --include=\*.md -rnw '{$path}' -e '{$img_name}'";
                exec($command2, $mdfiles);

                // The output is filename:line no:line content. Could be more than 1?
                foreach ($mdfiles as $mdfile) {
                    $filename = explode(':', $mdfile);

                    // Sometimes an empty string to skip.
                    if (str_ends_with($filename[0], '.md')) {
                        $articles[] = $filename[0];
                    }
                }
            }
        }

        // Eliminate duplicates.
        $articles = array_unique($articles);

        // Create a new array containing just the heading and filename;
        $heading_filename = [];
        foreach ($articles as $article) {
            $segments = explode('/', $article);
            $heading_filename[] = implode('/', array_values(array_slice($segments, -2)));
        }

        // Return an array containing the headings and filenames of md files to update
        return $heading_filename;
    }

    /**
     * Build the HTML for one article and make an entry in the database.
     *
     * @param string    $manual     The manual name.
     * @param string    $language   The language name.
     * @param string    $heading    The item heading name.
     * @param string    $filename   The item file name.
     *
     * @return array    [0|1, message].
     */
    protected function setOneArticle($manual, $language, $heading, $filename)
    {
        $db = $this->db;
        $gfm_file = $this->gfmfiles_path . $manual . '/' . $language . '/articles/' . $heading . '/' . $filename;
        if (!file_exists($gfm_file)) {
            // Many manuals in languages other than en will be missing articles.
            // Normal and no message needed.
            return [0, ''];
        }

        // Get the last modified timestamp
        $last_mod = date('Y-m-d H:i:s', @filemtime($gfm_file));

        // Check if there is an entry for this article.
        $query = $db->getQuery(true);
        $query->select($db->quotename('id'))
        ->select($db->quotename('modified'))
        ->from($db->quotename('#__jdm_articles'))
        ->where($db->quotename('manual') . ' = :manual')
        ->where($db->quotename('language') . ' = :language')
        ->where($db->quotename('heading') . ' = :heading')
        ->where($db->quotename('filename') . ' = :filename')
        ->bind(':manual', $manual, ParameterType::STRING)
        ->bind(':language', $language, ParameterType::STRING)
        ->bind(':heading', $heading, ParameterType::STRING)
        ->bind(':filename', $filename, ParameterType::STRING);
        $db->setQuery($query);
        $row = $db->loadObject();

        $id = empty($row) ? 0 : $row->id;
        $contents = file_get_contents($gfm_file);

        // Get the title from the contents.
        // Look for Filename and Display Title
        // <!-- Filename: J4.x:Http_Header_Management / Display title: HTTP Header Verwaltung -->
        $test = preg_match($this->pattern2, $contents, $matches);
        if (empty($test)) {
            $summary = "Warning {$manual}/{$language}/{$heading}/{$filename} does not contain h1\n";
            $fn = substr($filename, 0, strpos($filename, '.md'));
            $source_url = 'Unknown';
            $display_title = ucwords(str_replace('_', ' ', $fn));
        } else {
            $summary = '';
            $source_url = trim($matches[1]);
            $display_title = trim($matches[2]);
        }

        // Process the images for this article.
        $contents = $this->fiximages($manual, $contents);

        // Create the Markdown for this article.
        $html = Markdown2html::go($contents);

        $query = $db->getQuery(true);
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
        ->set($db->quotename('heading') . ' = :heading')
        ->set($db->quotename('filename') . ' = :filename')
        ->set($db->quotename('display_title') . ' = :display_title')
        ->set($db->quotename('html') . ' = :html')
        ->set($db->quotename('modified') . ' = :last_mod')
        ->bind(':source_url', $source_url, ParameterType::STRING)
        ->bind(':manual', $manual, ParameterType::STRING)
        ->bind(':language', $language, ParameterType::STRING)
        ->bind(':heading', $heading, ParameterType::STRING)
        ->bind(':filename', $filename, ParameterType::STRING)
        ->bind(':display_title', $display_title, ParameterType::STRING)
        ->bind(':html', $html, ParameterType::STRING)
        ->bind(':last_mod', $last_mod, ParameterType::STRING);
        $db->setQuery($query);
        $db->execute();

        return [1, $summary];
    }

    /**
     * Populate the __jdm_menu_headings table. The ini file is used in building menus.
     *
     * @param string    $manual     The manual name.
     * @param string    $language   The language name.
     *
     * @return string    The number of headings processed.
     */
    protected function setMenuHeadings($manual, $language)
    {
        // Get the menu headings file
        $menu_headings_file = $this->gfmfiles_path . $manual . '/' . $language . '/articles/menu-headings.ini';
        if (!file_exists($menu_headings_file)) {
            return 'Menu headings missing: ' . $menu_headings_file . "\n";
        }

        $contents = file_get_contents($menu_headings_file);
        $db = $this->db;
        $count = 0;

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $contents) as $line) {
            if (empty($line)) {
                continue;
            }
            list ($heading, $translation) = explode('=', $line);
            // Check if there is an existing entry.
            $query = $db->getQuery(true);
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

            $query = $db->getQuery(true);
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
            $query->set($db->quoteName('display_title') . ' = :translation')
            ->bind(':translation', $translation, ParameterType::STRING);
            $db->setQuery($query);
            $db->execute();
            $count += 1;
        }
        return "Headings translated: {$count}\n";
    }

    /**
     * Look for repo images to make picture srcset.
     *
     * @param   $manual   The manual to process.
     * @param   $contents A single page content in Markdown format.
     *
     * @return  $html     Markdown img links converted for this site.
     *
     * @since   1.0.0
     */
    private function fiximages($manual, $contents)
    {
        // links are like this and must be on one line
        // ![action logs module form](../../../help/en/images/admin-modules/modules-actionlogs-latest-screenshot.png "Action Logs Module Form")
        $test = preg_match_all($this->pattern, $contents, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            // $match[0] is the whole line to be replaced with a picture tag.
            // $match[1] is the alt tag.
            // $match[2] is the path to the image in the repo source
            // $match[3] is the image filename
            // $match[4] is the title or empty.

            // Copy the image to the images folder.
            $destination_dir = JPATH_ROOT . '/jdmimages/manuals/' . $manual . $match[2];
            $destination =  $destination_dir . '/' . $match[3];

            // Does the destination folder exist?
            if (!is_dir($destination_dir)) {
                mkdir($destination_dir, 0755, true);
            }
            $origin = $this->gfmfiles_path . $manual . $match[2] . '/' . $match[3];

            file_put_contents($destination, file_get_contents($origin));

            $title = '';
            if (!empty($match[4])) {
                list ($gar, $title, $bage) = explode('"', $match[4]);
                $title = ' title="' . $title . '"';
            }
            // Create an img src set and set of images from an img tag.
            $img = '<img src="/jdmimages/manuals/' . $manual . $match[2] . '/' .
            $match[3] . '" alt="' . $match[1] . '"' .
            $title . ' class="screenshot">';
            $processed = $this->responsive->transformImage($img);
            if (!empty($processed)) {
                $contents = str_replace($match[0], $processed, $contents);
            }
        }
        return $contents;
    }
}

<?php

/**
 * @package     Jdocmanual
 * @subpackage  Cli
 *
 * @copyright   Copyright (C) 2003 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Cli;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\File;
use Joomla\Database\ParameterType;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\BuildHelper;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\InthispageHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined(constant_name: '_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Build the Joomla help html files used by the proxy server.
 *
 * @since  1.0.0
 */
class Buildproxy
{
    /**
     * Path to local source of markdown files.
     *
     * @var     string
     * @since   1.0.0
     */
    protected $gfmfiles_path;

    /**
     * Path to local source of markdown files.
     *
     * @var     string
     * @since   1.0.0
     */
    protected $installation_subfolder;

    protected $key_index;

    /**
     * Regex pattern to select first GFM H1 (#) string.
     *
     * @var     string;
     * @since   1.0.0
     */
    protected $pattern1 = '/\n[#]([\w| ].*)/m';

    /**
     * Regex pattern to select Display title from GFM comment string.
     *
     * @var     string
     * @since  1.0.0
     */
    protected $pattern2 = '/<!-- Filename:.*Display title:(.*)? -->/m';

    /**
     * Path fragment of manual to process.
     *
     * @var     string
     * @since  1.0.0
     */
    protected $manualtodo;

    /**
     * Path fragment of language to process.
     *
     * @var     string
     * @since  1.0.0
     */
    protected $languagetodo;

    /**
     * The top section of a Help page.
     *
     * @var     string
     * @since  1.0.0
     */
    protected $top;

    /**
     * The bottom section of a Help page.
     *
     * @var     string
     * @since  1.0.0
     */
    protected $bottom;

    /**
     * Content of menu index file.
     *
     * @var     string
     * @since  1.0.0
     */
    protected $tmp;

    /**
     * Accumulate a summary to return to caller.
     *
     * @var     string
     * @since  1.0.0
     */
    protected $summary = '';

    protected $db;

    /**
     * Entry point to convert md to html and save.
     *
     * @param   $manual     The path fragment of the manual to process.
     * @param   $language   The path fragment of the language to process.
     *
     * @return  $string     A message reporting the outcome.
     *
     * @since   1.0.0
     */
    public function go($manual, $language)
    {
        $time_start = microtime(true);

        $this->manualtodo = 'help';
        $this->languagetodo = $language;

        $memlimit = ini_get('memory_limit');
        ini_set("memory_limit", "2048M");
        $this->build();

        //ini_set("memory_limit", $memlimit);
        // Warning: Failed to set memory limit to 536870912 bytes
        //(Current memory usage is 1470103552 bytes) in [redacted]/Buildhtml.php on line 139

        $time_end = microtime(true);
        $execution_time = $time_end - $time_start;

        $this->summary .= 'Total Execution Time: ' . number_format($execution_time, 2) . ' Seconds' . "\n\n";
        return $this->summary;
    }

    /**
     * Cycle over manuals and languages to convert txt to html and save.
     * Skip all manuals except help.
     *
     * @return  $string     A message reporting the outcome.
     *
     * @since   1.0.0
     */
    protected function build()
    {
        // Get the data source path from the component parameters
        $this->gfmfiles_path = ComponentHelper::getComponent('com_jdocmanual')->getParams()->get('gfmfiles_path', ',');
        if (empty($this->gfmfiles_path)) {
            return "\nThe Markdown source could not be found: {$this->gfmfiles_path}. Set in Jdocmanual configuration.\n";
        }
        $this->installation_subfolder = ComponentHelper::getComponent('com_jdocmanual')->getParams()->get('installation_subfolder', '');
        $this->settop();
        $this->setbottom();

        $this->db = Factory::getContainer()->get('DatabaseDriver');

        // Get a list of active languages
        $active_languages = BuildHelper::getActiveLanguages($this->db);

        $counts = $this->html4lingo('help');

        foreach ($counts as $key => $count) {
            $this->summary .= 'Language: ' . $key . ' Count: ' . $count . "\n";
        }
        // Copy the proxy files from components to to root
        $src = JPATH_ROOT . '/components/com_jdocmanual/proxy/';
        $dst = JPATH_ROOT . '/proxy/';
        File::copy($src . 'help.css', $dst . 'help.css');
        File::copy($src . 'index.php', $dst . 'index.php');

        // Check for name changes in the source help files
        $this->check_sources();

        return;
    }

    /**
     * The help pages list in the admin Help section come from toc.json
     * There have been name changes so check that they
     */
    protected function check_sources() {
        // Read in the list of help pages
        $sources_file = JPATH_SITE . '/administrator/help/en-GB/toc.json';
        $jdoc_sources = file_get_contents($sources_file);
        $files = json_decode($jdoc_sources, true);
        $db = $this->db;

        // Check that is is in the database
        foreach ($files as $key => $value) {
            // The key is the part needed, example: Contacts:_Edit_Category
            $query = $db->getQuery(true);
            $newkey = "%{$key}";
            $query->select($db->quoteName('id'))
                ->from($db->quoteName('#__jdm_articles'))
                ->where($db->quoteName('source_url') . ' LIKE :key')
                ->where($db->quoteName('manual') . '=' . $db->quote('help'))
                ->bind(':key', $newkey, ParameterType::STRING);
            $db->setQuery($query);
            if (empty($db->loadResult())) {
                $this->summary .= "Article in toc.json is missing: {$key}\n";
            }
        }
    }

    /**
     * Convert help md files to html and save.
     *
     * @param   $manual     The path fragment of the manual to process.
     * @param   $language   The path fragment of the language to process.
     *
     * @return  $int        Count of the number of files.
     *
     * @since   1.0.0
     */
    protected function html4lingo($manual)
    {
        $db = $this->db;

        // Get the already converted html from the database.
        // Do English first.
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('source_url', 'language', 'heading', 'filename', 'display_title', 'html')))
        ->from('#__jdm_articles')
        ->where($db->quoteName('manual') . ' = ' . $db->quote('help'))
        ->where($db->quoteName('language') . ' = ' . $db->quote('en'))
        ->order($db->quoteName(array('language', 'heading', 'filename')));
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $counts = $this->do_rows($rows);
        $this->fix_urls($rows);

        // Do the other languages second.
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('source_url', 'language', 'heading', 'filename', 'display_title', 'html')))
            ->from('#__jdm_articles')
            ->where($db->quoteName('manual') . ' = ' . $db->quote('help'))
            ->where($db->quoteName('language') . ' != ' . $db->quote('en'))
            ->order($db->quoteName(array('language', 'heading', 'filename')));
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $counts = array_merge($counts, $this->do_rows($rows));
        $this->fix_urls($rows);

        return $counts;
    }

    protected function do_rows($rows) {
        $save_key_index = 0;
        $key_index = "<?php\n\$key_index = [\n";

        $counts = [];
        foreach ($rows as $row) {
            list($inthispage, $content) = InthispageHelper::doToc($row->html);

            $outfile = str_replace('.md', '.html', $row->filename);
            $html = $this->top;
            $html .= '<h1>' . $row->display_title . '</h1>';
            $html .= '<div id="toc" class="table-of-contents">';
            $html .= "{$inthispage}\n</div>\n{$content}";
            $html .= $this->bottom;
            if (!is_dir(JPATH_ROOT . '/proxy/' . $row->language . '/' . $row->heading)) {
                mkdir(JPATH_ROOT . '/proxy/' . $row->language . '/' . $row->heading, 0755, true);
            }
            // Need to replace links to jdocmanual to remove the leading /jdm3/proxy example
            // <a href="jdocmanual?manual=user&amp;heading=articles&amp;filename=adding-an-image-to-an-article.md">Adding an Image to an Article</a>
            $html = str_replace('href="jdocmanual?', 'href="' . $this->installation_subfolder . '/jdocmanual?', $html);
            File::write(JPATH_ROOT . '/proxy/' . $row->language . '/' . $row->heading . '/' . $outfile, $html);
            if (isset($counts[$row->language])) {
                $counts[$row->language] += 1;
            } else {
                $counts[$row->language] = 1;
            }
            if ($row->language == 'en') {
                // Extract key from full URL.
                // Help5.x:Admin_Modules:_Action_Logs_-_Latest
                // Needs to be Admin_Modules:_Action_Logs_-_Latest
                $parts = explode(':', $row->source_url, 2);
                if (empty($parts[1])) {
                    echo "Problem extracting key from {$row->source_url}\n";
                } else {
                    // A source_url may contain single quotes
                    $key = str_replace("'", "\'", $parts[1]);
                    $filename = str_replace('.md', '.html', $row->filename);
                    $key_index .= "'{$key}' => '{$row->heading}/{$filename}',\n";

                    // save the key index flipped for use later
                    $this->key_index[$row->heading . '/' . $filename] = $key;
                }
                $save_key_index = 1;
            }
            // for testing - do one file from each language
            // return $counts;
        }

        // Need a final dummy key_index entry without a comma.
        $key_index .= "    'dummy' => 'dummy'\n];\n";

        // Write a new key-index.php file.
        if ($save_key_index) {
            File::write(JPATH_ROOT . '/proxy/key-index.php', $key_index);
        }

        return $counts;
    }

    /**
     * Transfix docmanual internal URLs
     * html file: <li><a href="/jdm4/jdocmanual?article=help/common-elements/toolbars">Toolbars</a>.</li>
     * To become: <li><a href="/jdm4/proxy/index.php?keyref=Help53:Toolbars&lang=en">Toolbars</a>.</li>
     * source_url = "Help4.x:Articles:_Categories"
     * key_index = 'Toolbars' => 'common-elements/toolbars.html'
     */
    protected function fix_urls($rows) {
        $pattern = '/(href=".*?)(jdocmanual.*=help\/)(.*?)(">.*?<\/a>)/';
        $part2 = 'proxy/index.php?keyref=Help50:';
        foreach ($rows as $row) {
            $lang = '&lang=' . $row->language;
            $file = JPATH_ROOT . '/proxy/' . $row->language . '/' . $row->heading . '/' . str_replace('.md', '.html', $row->filename);
            $html = file_get_contents($file);
            $test = preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                // Get the original link from the link index
                $link = $this->key_index[$match[3] . '.html'];
                // keep $match[1] and $match[4] and replace $match[3]
                $stop = 1;
                $html = str_replace($match[0], $match[1] . $part2 . $link . $lang . $match[4], $html);
            }
            file_put_contents($file, $html);
        }
    }

    /**
     * Set the top part of a Help page.
     *
     * @return  $html   The required html code.
     *
     * @since   1.0.0
     */
    protected function settop()
    {
        $this->top = <<<EOF
<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Joomla! Help Screens</title>
        <link rel="stylesheet" href="https://help.joomla.org/media/css/help.css">
        <link href="{$this->installation_subfolder}/media/templates/administrator/atum/css/vendor/fontawesome-free/fontawesome.css" rel="stylesheet" data-asset-name="fontawesome" />
        <link rel="stylesheet" href="{$this->installation_subfolder}/media/com_jdocmanual/css/a11y-light.css">
        <link rel="stylesheet" href="{$this->installation_subfolder}/proxy/help.css">
    </head>
    <body>
        <main>
            <a name="Top" id="Top"></a>
EOF;
    }

    /**
     * Set the bottom part of a Help page.
     *
     * @return  $html   The required html code.
     *
     * @since   1.0.0
     */
    protected function setbottom()
    {
        $this->bottom = <<<EOF
            <div id="footer-wrapper">
                <div id="license">
                    License: <a href="https://docs.joomla.org/JEDL" target="_blank">
                        Joomla! Electronic Documentation License
                    </a>
                </div>
                <div id="source-page">
                    Source page:
                    <a href="https://docs.joomla.org/Help5.x:Articles/en" target="_blank">
                        https://docs.joomla.org/Help5.x:Articles/en
                    </a>
                </div>
                <div id="copyright">
                    Copyright &copy; 2023 <a href="https://www.opensourcematters.org" target="_blank">
                    Open Source Matters, Inc.</a> All rights reserved.
                </div>
            </div>
            <a href="#top" id="back-top" class="back-to-top-link" aria-label="Top">
                <span class="icon-arrow-up icon-fw" aria-hidden="true"></span>
            </a>
        </main>
    </body>
</html>
EOF;
    }
}

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
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\BuildHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Build the jdm_menus table.
 *
 * @since  1.0.0
 */
class Buildmenus
{
    /**
     * Path to local source of markdown files.
     *
     * @var     string
     * @since   1.0.0
     */
    protected $gfmfiles_path;

    /**
     * The content of the menu-index.txt files.
     *
     * @var     string
     * @since   1.0.0
     */
    protected $menu_index;

    /**
     * Accumulate a summary to return to caller.
     *
     * @var     string
     * @since  1.0.0
     */
    protected $summary = '';

    /**
     * Placeholder for database object
     *
     */
    protected $db;

    /**
     * Entry point to convert menu-index.txt to htmal and save.
     *
     * @param   string  $manual     The name of the manual to process.
     * @param   string  $language   The code of the language to process.
     *
     * @return  $string     A message reporting the outcome.
     */
    public function go($manual, $language)
    {
        $time_start = microtime(true);

        $this->db = Factory::getContainer()->get('DatabaseDriver');

        // The echo items appear in the CLI but not in Joomla.

        $memlimit = ini_get('memory_limit');
        ini_set("memory_limit", "2048M");

        $this->build($manual, $language);

        $time_end = \microtime(true);
        $execution_time = $time_end - $time_start;

        $this->summary .= 'Total Execution Time: ' . number_format($execution_time, 2) . ' Seconds' . "\n\n";

        return $this->summary;
    }

    /**
     * Cycle over manuals and languages to convert txt to html and save.
     *
     * @param   string  $manualtodo     The name of the manual to process.
     * @param   string  $languagetodo   The code of the language to process.
     *
     * @return void
     */
    protected function build($manualtodo, $languagetodo)
    {
        // Get the data source path from the component parameters
        $this->gfmfiles_path = ComponentHelper::getComponent('com_jdocmanual')->getParams()->get('gfmfiles_path', ',');
        if (empty($this->gfmfiles_path)) {
            $this->summary .= "\nThe Markdown source could not be found: {$this->gfmfiles_path}. Set in Jdocmanual configuration.\n";
        }

        // Get a list of active manuals.
        $manuals = BuildHelper::getActiveManuals($this->db);

        // Get a list of active languages
        $active_languages = BuildHelper::getActiveLanguages($this->db);

        foreach ($manuals as $manual) {
            // Skip of not all manuals are being updated
            if (!($manual === 'all' || $manual === $manualtodo)) {
                continue;
            }
            $count = 0;
            // Read in menu-index.txt
            $menu_index = $this->gfmfiles_path . $manual . '/en/menu-index.txt';
            if (!file_exists($menu_index)) {
                $this->summary .= "Skipping {$manual} - file does not exists: /en/{$menu_index}\n";
                continue;
            }
            // Read in the menu_index
            $this->menu_index = file_get_contents($menu_index);

            // Get a list of the language folders in a manual
            $languages = array_diff(scandir($this->gfmfiles_path . $manual), array('..', '.', '.DS_Store'));

            foreach ($languages as $language) {
                // Skip if not all languages are being updated
                if (!($language === 'all' || $languagetodo === $language)) {
                    continue;
                }
                // skip if this is not an active language.
                if (!in_array($language, $active_languages)) {
                    continue;
                }
                if (is_dir($this->gfmfiles_path . $manual . '/' . $language . '/articles/')) {
                    $count += $this->menu4lingo($manual, $language);
                }
                // For testing
                if ($count == 1) {
                    //break;
                }
            }
        }
    }

    /**
     * Convert a menu-headings file to html and save.
     *
     * @param   string  $manual     The name of the manual to process.
     * @param   string  $language   The code of the language to process.
     *
     * @return  int     Count of the number of files.
     */
    protected function menu4lingo($manual, $language)
    {
        $db = $this->db;

        // Read in the menu-headings.ini file for this language
        $menu_headings = $this->gfmfiles_path . $manual . '/' . $language . '/articles/menu-headings.ini';
        if (!file_exists($menu_headings)) {
            // If the language menu headings file is missing use the English menu headings
            $this->summary .=  "Warning {$manual}/{$language} - file does not exist: {$menu_headings}\n";
            $menu_headings = $this->gfmfiles_path . $manual . '/en/articles/menu-headings.ini';
        }

        // Read in the menu_index, format: advanced-administrator=Erweiterter Administrator
        $menu_headings = file_get_contents($menu_headings);
        $lines = preg_split("/((\r?\n)|(\r\n?))/", $menu_headings);
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            list($heading, $display_title) = explode('=', $line);
            $display_titles[$heading] = $display_title;
        }

        // Split the menu into lines.
        $lines = preg_split("/((\r?\n)|(\r\n?))/", $this->menu_index);
        $accordionid = 0;
        $order_id = [];
        $order_path = [];
        $order_display_title = [];
        $html = '<ul id="jdmmenu" class="jdm-metismenu">' . "\n";
        $total_translated = 0;
        $total_articles = 0;
        $previous_heading_level = 0;
        $new_heading_level = 0;

        foreach ($lines as $line) {
            // Skip empty lines or lines beginning with a semi-colon;
            if (empty(trim($line))) {
                continue;
            }
            if (strpos($line, ';') === 0) {
                continue;
            }

            // skip lines containing close-1-headings
            if (strpos($line, 'close-') === 0) {
                // get the number of headings to close
                $n = (int) substr($line, 6, 1);
                for ($i = 0; $i < $n; $i++) {
                    $html .= $this->accordionEnd();
                    $previous_heading_level--;
                }
                continue;
            }
            list($key, $heading, $filename) = explode('=', $line);

            // Does the key have a heading level? heading or heading-1 or heading-2
            if (strpos($key, 'heading') === 0) {
                if (
                    strpos($key, '-') &&
                    list($k, $l) = explode('-', $key)
                ) {
                    // This is a new subheading of level l
                    $new_heading_level = $l;
                    $key = $k;
                } else {
                    $new_heading_level = 0;
                }
            }

            if ($key == 'heading') {
                // If the line starts with 'heading=' start a new accordion
                if ($accordionid > 0) {
                    // End the previous accordion
                    while ($previous_heading_level >= $new_heading_level) {
                        $html .= $this->accordionEnd();
                        $previous_heading_level--;
                    }
                    $previous_heading_level++;
                }
                $accordionid += 1;
                // If the display_title is missing
                if (empty($display_titles[$heading])) {
                    $alt = ucwords(str_replace('-', ' ', $heading));
                    // Output a warning
                    $this->summary .=  "No translated heading for {$heading}. Using {$alt}\n";
                    // Output an accordion heading.
                    $html .= $this->accordionStart($accordionid, $new_heading_level, $alt);
                } else {
                    // Output an accordion heading.
                    $html .= $this->accordionStart($accordionid, $new_heading_level, $display_titles[$heading]);
                }
            } else {
                // Output an accordion item - get the id from the database.
                $query = $db->getQuery(true);
                $query->select($db->quoteName(array('id', 'display_title')))
                ->from($db->quoteName('#__jdm_articles'))
                ->where($db->quoteName('manual') . ' = :manual')
                ->where($db->quoteName('language') . ' = :language')
                ->where($db->quoteName('heading') . ' = :heading')
                ->where($db->quoteName('filename') . ' = :filename')
                ->bind(':manual', $manual, ParameterType::STRING)
                ->bind(':language', $language, ParameterType::STRING)
                ->bind(':heading', $heading, ParameterType::STRING)
                ->bind(':filename', $filename, ParameterType::STRING);
                $db->setQuery($query);
                $row = $db->loadObject();

                // But if the entry did not exist for a specific language get the English version
                if (empty($row)) {
                    $query = $db->getQuery(true);
                    $query->select($db->quoteName(array('id', 'display_title')))
                    ->from($db->quoteName('#__jdm_articles'))
                    ->where($db->quoteName('manual') . ' = :manual')
                    ->where($db->quoteName('language') . ' = ' . $db->quote('en'))
                    ->where($db->quoteName('heading') . ' = :heading')
                    ->where($db->quoteName('filename') . ' = :filename')
                    ->bind(':manual', $manual, ParameterType::STRING)
                    ->bind(':heading', $heading, ParameterType::STRING)
                    ->bind(':filename', $filename, ParameterType::STRING);
                    $db->setQuery($query);
                    $row = $db->loadObject();
                } else {
                    $total_translated += 1;
                }
                if (empty($row)) {
                    // There is a menu item but no article - issue a warning and skip?
                    $this->summary .=  "Skipping: there is no article for {$manual}/{$language}/{$heading}/{$filename}\n";
                    continue;
                }
                $total_articles += 1;

                // The manual part of the path is for search engines.
                //$path = "manual={$manual}&heading={$heading}&filename={$filename}";
                // Change to short form.
                $filename = str_replace('.md', '', $filename);
                $path = "article={$manual}/{$heading}/{$filename}";

                $html .= $this->accordionItem($row->id, $row->display_title, $path);

                // Store order for the Previous and Next buttons.
                $order_id[] = $row->id;
                $order_path[] = $path;
                $order_displaytitle[] = $row->display_title;
            }
        }
        // End the previous accordion
        while ($previous_heading_level >= $new_heading_level) {
            $html .= $this->accordionEnd();
            $previous_heading_level--;
        }

        $html .= "\n</ul>\n";

        $this->summary .=  "Summary: {$manual}/{$language} translated/total: {$total_translated}/{$total_articles}\n";
        $this->saveMenu($manual, $language, $html);
        $this->setOrder($order_id, $order_path, $order_display_title);
        return 1;
    }

    /**
     * Save the Previous and Next button code in the #__jdm_articles table.
     *
     * @param   integer     $orderid        The article order.
     * @param   string      $orderpath      The link for each item.
     *
     * @return  void
     */
    protected function setOrder($order_id, $order_path, $order_display_title)
    {
        $db = $this->db;

        $linkstart = '<a href="jdocmanual?';
        $previous = Text::_('JPREVIOUS');
        $next = Text::_('JNEXT');
        $linkend_next = '" class="btn btn-outline-secondary next">' . $next . ' <i class="fa-solid fa-hand-point-right"></i></a>';
        $linkend_previous = '" class="btn btn-outline-secondary previous"><i class="fa-solid fa-hand-point-left"></i> ' . $previous . '</a>';

        foreach ($order_id as $i => $value) {
            // The first item does not have a Previous link
            if ($i > 0) {
                // Save the previous link.
                $link = $linkstart . $order_path[$i - 1] . $linkend_previous;
                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__jdm_articles'))
                ->set($db->quoteName('order_previous') . ' = :op')
                ->where('id = ' . ($order_id[$i]))
                ->bind(':op', $link, ParameterType::STRING);
                $db->setQuery($query);
                $db->execute();
            }

            // The last item does not have a Next link.
            if ($i < count($order_id) - 1) {
                // Save the next link.
                $link = $linkstart . $order_path[$i + 1] . $linkend_next;
                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__jdm_articles'))
                ->set($db->quoteName('order_next') . ' = :op')
                ->where('id = ' . ($order_id[$i]))
                ->bind(':op', $link, ParameterType::STRING);
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    /**
     * Save a menu in html to the #__jdm_menus table.
     *
     * @param   string  $manual     The name of the manual to save.
     * @param   string  $language   The code of the language to save.
     * @param   string  $html       The html to save
     *
     * @return  $void
     */
    protected function saveMenu($manual, $language, $html)
    {
        $db = $this->db;

        // Check if there is a menu for this manual and language.
        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__jdm_menus'))
            ->where($db->quoteName('manual') . ' = :manual')
            ->where($db->quoteName('language') . ' = :language')
            ->bind(':manual', $manual, ParameterType::STRING)
            ->bind(':language', $language, ParameterType::STRING);
        $db->setQuery($query);
        $id = $db->loadResult();

        $query = $db->getQuery(true);
        if (empty($id)) {
            // Use an insert query.
            $query->insert($db->quoteName('#__jdm_menus'));
        } else {
            // Use an update query.
            $query->update($db->quoteName('#__jdm_menus'))
                ->where($db->quoteName('id') . ' = ' . $id);
        }

        $query->set($db->quoteName('manual') . ' = :manual')
            ->set($db->quoteName('language') . ' = :language')
            ->set($db->quoteName('menu') . ' = :menu')
            ->bind(':manual', $manual, ParameterType::STRING)
            ->bind(':language', $language, ParameterType::STRING)
            ->bind(':menu', $html, ParameterType::STRING);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Create an accordian start code.
     *
     * @param   integer $id     The sequence number of the accordion.
     * @param   string  $label  A summary label.
     *
     * @return  $html   The required html code.
     */
    protected function accordionStart($id, $depth, $label)
    {
        $html = "<li>\n";
        $html .= '<a class="has-arrow jdm-menu-link" href="#" aria-expanded="false">';
        $html .= "{$label}</a>\n<ul class=\"jdm-indent-1\">\n";
        return $html;
    }

    /**
     * Create an accordian end code.
     *
     * @return  string  The required html code.
     */
    protected function accordionEnd()
    {
        return "\n</ul>\n</li>\n";
    }

    /**
     * Create an accordian item code.
     *
     * @param   integer     $id             The sequence number of the accordion.
     * @param   string      $display_title  The display title.
     * @param   string      $path           The link path
     *
     * @return  string      The required html code.
     */
    protected function accordionItem($id, $display_title, $path)
    {
        // Escape any " character in the link.
        //'<li><span class="icon-file-alt icon-fw icon-jdocmanual" aria-hidden="true"></span>';
        $html = '<li id="article-' . $id . '">';
        $html .= '<a href="jdocmanual?' . $path . '" class="jdm-menu-link">' . $display_title . '</a></li>' . "\n";
        return $html;
    }

    /**
     * Create an accordian start code.
     *
     * @param   integer $id     The sequence number of the accordion.
     * @param   string  $label  A summary label.
     *
     * @return  $html   The required html code.
     */
    protected function oldaccordionStart($id, $depth, $label)
    {
        $html = "<details class=\"jdm\">\n<summary>{$label}</summary>\n<ul>\n";
        return $html;

        $html = <<<EOF
<div class="accordion-item">
<a href="#" class="accordion-header accordion-button jdocmenu-item" id="item_{$id}"
data-bs-toggle="collapse" data-bs-target="#collapse_{$id}" aria-expanded="false"
aria-controls="collapse_{$id}">
{$label}
</a>
<div id="collapse_{$id}" class="accordion-collapse collapse"
aria-labelledby="item_{$id}" data-bs-parent="#accordionJdoc">
<div class="jdocmanual-accordion-body">
<ul>
EOF;
        return $html;
    }

    /**
     * Create an accordian end code.
     *
     * @return  string  The required html code.
     */
    protected function oldaccordionEnd()
    {
        return "\n</ul>\n</details>\n";
    }

    /**
     * Create an accordian item code.
     *
     * @param   integer     $id             The sequence number of the accordion.
     * @param   string      $display_title  The display title.
     * @param   string      $path           The link path
     *
     * @return  string      The required html code.
     */
    protected function oldaccordionItem($id, $display_title, $path)
    {
        // Escape any " character in the link.
        //'<li><span class="icon-file-alt icon-fw icon-jdocmanual" aria-hidden="true"></span>';
        $html = '<li id="article-' . $id . '">';
        $html .= '<a href="jdocmanual?' . $path . '" class="content-link">' . $display_title . '</a></li>' . "\n";
        return $html;
    }
}

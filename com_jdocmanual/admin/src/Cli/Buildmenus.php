<?php

/**
 * @package     Jdocmanual
 * @subpackage  Cli
 *
 * @copyright   Copyright (C) 2003 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Cli;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

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
     * @since   1.0
     */
    protected $gfmfiles_path;

    /**
     * The content of the menu-index.txt files.
     *
     * @var     string
     * @since   1.0
     */
    protected $menu_index;

    /**
     * Accumulate a summary to return to caller.
     *
     * @var     string
     * @since   1.0
     */
    protected $summary = '';

    /**
     * Placeholder for database object
     *
     */
    protected $db;

    protected $menuObject;

    protected $menuFolders;
    
    protected $pathPrefix = [];

    // Store order for the Previous and Next buttons.
    protected $order = [];

    /**
     * The level of each submenu
     *
     * @var    integer
     */
    protected $toclevel = 0;

    protected $menuHTML = '';

    /**
     * Entry point to convert menu.json to htmal and save.
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

        $params = ComponentHelper::getParams('com_jdocmanual');

        // Get the the 'manuals' path from the component parameters.
        $this->gfmfiles_path = $params->get('gfmfiles_path');

        // The menu.json file is always needed. Convert to am obkect.
        $this->menuObject = $this->getMenuObject($manual, $language);
        if (empty($this->menuObject)) {
            // Return the error message.
            return 'The menu.json file is missing or invalid!';
        }

        if (!is_dir($this->gfmfiles_path . $manual . '/' . $language . '/articles/')) {
            return 'The articles folder is missing!';
        }        
        // Get the list of folder headings
        $menuFolders = file_get_contents($this->gfmfiles_path . $manual . '/' . $language . '/folders.json');
        $this->menuFolders = json_decode($menuFolders);
        
        $this->menuHTML = '<ul id="jdmmenu" class="jdm-metismenu metismenu mm-show">' . "\n";
        $this->renderSubmenu($this->menuObject, $manual, $language);
        $this->menuHTML .= '</ul>' . "\n";

        $this->saveMenu($manual, $language);

        // Set the data for the Previous and Next buttons in the Articles table
        $this->setOrder($manual, $language);
        
        // Summary of number of articles processed.
        $nArticles = count($this->order);
        return "Number of articles in the menu: {$nArticles}";

        $time_end = \microtime(true);
        $execution_time = $time_end - $time_start;

        $this->summary .= 'Total Execution Time: ' . number_format($execution_time, 2) . ' Seconds' . "\n\n";

        return $this->summary;
    }

    protected function getArticleId($manual, $language, $path)
    {
        $db = $this->db;

        $query = $db->createQuery();
        $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__jdm_articles'))
            ->where($db->quoteName('manual') . ' = :manual')
            ->where($db->quoteName('language') . ' = :language')
            ->where($db->quoteName('path') . ' = :path')
            ->bind(':manual', $manual, ParameterType::STRING)
            ->bind(':language', $language, ParameterType::STRING)
            ->bind(':path', $path, ParameterType::STRING);
        $db->setQuery($query);
        return $db->loadResult();
    }

    /**
     * Read the menu-index.txt file and make an array of articles data.
     *
     * @param string    $manual     The manual name.
     * @param string    $language   The language name.
     *
     * @return object   
     */
    protected function getMenuObject($manual, $language)
    {
        $menuObject = $this->gfmfiles_path . $manual . '/' . $language . '/menu.json';
        if (!file_exists($menuObject)) {
            return;
        }

        // Read in the menu.json file.
        $tmp = file_get_contents($menuObject);

        // Decode the json to give an object.
        return json_decode($tmp, true);
    }

    /**
     * Save the Previous and Next button code in the #__jdm_articles table.
     *
     * @param   integer     $orderid        The article order.
     * @param   string      $orderpath      The link for each item.
     *
     * @return  void
     */
    protected function setOrder($manual, $language)
    {
        $db = $this->db;

        $previous = Text::_('JPREVIOUS');
        $next = Text::_('JNEXT');
        $linkstart = '<a href="jdocmanual?article=' . $manual . '/';
        $linkend_next = '" class="btn btn-outline-secondary next">' . $next . ' <i class="fa-solid fa-hand-point-right"></i></a>';
        $linkend_previous = '" class="btn btn-outline-secondary previous"><i class="fa-solid fa-hand-point-left"></i> ' . $previous . '</a>';

        // The order data are stored in $this->order
        $nArticles = count($this->order);

        foreach ($this->order as $i => $item) {
            // $this->order[] = [$article_id, $path, $value, $link];

            // The first item does not have a Previous link
            if ($i > 0) {
                $order_previous = $linkstart . $this->order[$i - 1][1] . $linkend_previous;
            } else {
                $order_previous = '';
            }
            // The last link does not have a next item
            if ($i < $nArticles) {
                $order_next = $linkstart . $this->order[$i + 1][1] . $linkend_next;;
            } else {
                $order_next = '';
            }
            // Update this article order_next and order_previous fields
            $query = $db->createQuery();
            $query->update($db->quoteName('#__jdm_articles'))
                ->set($db->quoteName('order_previous') . ' = :order_previous')
                ->set($db->quoteName('order_next') . ' = :order_next')
                ->where($db->quoteName('manual') . ' = ' . $db->quote($manual))
                ->where($db->quoteName('language') . ' = ' . $db->quote($langauge))
                ->where($db->quoteName('path') . ' = ' . $db->quote($item->path))
                ->where($db->quoteName('id') . ' = ' . $i)
                ->bind(':order_previous', $order_previous, ParameterType::STRING)
                ->bind(':order_next', $order_next, ParameterType::STRING);
            $db->setQuery($query);
            $db->execute();
        }
    }

    /**
     * Save a menu in html to the #__jdm_menus table.
     *
     * @param   string  $manual     The name of the manual to save.
     * @param   string  $language   The code of the language to save.
     * @param   string  $html       The html to save
     *
     * @return  void
     */
    protected function saveMenu($manual, $language)
    {
        $db = $this->db;

        // Check if there is a menu for this manual and language.
        $query = $db->createQuery();
        $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__jdm_menus'))
            ->where($db->quoteName('manual') . ' = :manual')
            ->where($db->quoteName('language') . ' = :language')
            ->bind(':manual', $manual, ParameterType::STRING)
            ->bind(':language', $language, ParameterType::STRING);
        $db->setQuery($query);
        $id = $db->loadResult();

        $query = $db->createQuery();
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
            ->bind(':menu', $this->menuHTML, ParameterType::STRING);
        $db->setQuery($query);
        $db->execute();
    }

    protected function renderSubmenu($menu, $manual, $language) 
    {
        // Increase the toclevel on entry
        $this->toclevel += 1;
        if ($this->toclevel > 1) {
            $collapse = ' mm-collapse';
            $this->menuHTML .= "<ul class=\"jdm-indent-{$this->toclevel} {$collapse}\">\n";
        }
        foreach ($menu as $key => $value) {
            if (is_array($value)) {
                $icon = "<span class=\"icon-folder\" aria-hidden=\"true\"></span>";
                $wrap_label = "<span class=\"item-title\">{$this->menuFolders->$key}</span>";
                $this->menuHTML .= "<li class=\"item parent item-level-{$this->toclevel}\">";
                $this->menuHTML .= "<a href=\"#\" class=\"has-arrow\">";
                $this->menuHTML .= "{$wrap_label}</a>\n";
                if (!empty($value)) {
                    // Add a path prefix 
                    $this->pathPrefix[] = $key;

                    // Recursively build sublist.
                    $this->renderSubmenu($value, $manual, $language);
                } else {
                    // Remove the added path prefix
                    array_pop($this->pathPrefix);
                }
                $this->menuHTML .= "</li>\n";
            } else {
                // Create a path to be used to get the article ID
                if (empty($this->pathPrefix)) {
                    $path = $key;
                } else {
                    $path = implode('/', $this->pathPrefix) . "/$key";
                }
                // The path is taken from the menu.json file but the .md suffix is not stored in the database.
                $path = str_replace('.md', '', $path);

                // This is an article list item. Get the article id from the database
                $article_id = $this->getArticleId($manual, $language, $path);
                $icon = "<span class=\"icon-file-alt\" aria-hidden=\"true\"></span>";

                $link = "<a id=\"article-{$article_id}\" href=\"jdocmanual?article={$manual}/{$path}\">{$value}</a>";
                $this->menuHTML .= "<li class=\"item item-level-{$this->toclevel}\">{$link}</li>\n";

                // Store order for the Previous and Next buttons.
                $this->order[] = [$article_id, $path, $value, $link];
            }
        }
        $this->menuHTML .= "</ul>\n";

        // On return decrease the toclevel
        $this->toclevel -= 1;
    }
}

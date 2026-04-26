<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\ParameterType;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper to create a menu preview for the Menu Stash: Edit form.
 * This is an abbreiviated version of the code used in Cli/Buildmenus.php.
 *
 * @since  1.0
 */
class BuildmenusHelper
{
    /**
     * Build a menu table for a menu stash preview.
     *
     * @param   string  $manual     The path fragment for the manual.
     * @param   string  $menu       A string containing the menu-index.txt.
     * @param   string  $language   The language code for this menu.
     *
     * @return  string  The menu in html.
     *
     * @since   1.0
     */

    /**
     * Saves typing $this->db everywhere
     *
     * @var object
     */
    protected $db;

    /**
     * The level of each submenu
     *
     * @var    integer
     */
    protected $toclevel = 0;

    protected $menuFolders;

    protected $menuHTML = '';

    protected $menuObject;

    protected $pathPrefix = [];

    protected $gfmfiles_path;

    public function buildmenus($manual, $language, $menu)
    {
        // if any parameter is missing return false
        if (empty($manual) || empty($menu)) {
            return false;
        }
        $params = ComponentHelper::getParams('com_jdocmanual');

        // Get the the 'manuals' path from the component parameters.
        $this->gfmfiles_path = $params->get('gfmfiles_path');

        $this->db = Factory::getContainer()->get('DatabaseDriver');

        $this->menuObject = json_decode($menu, true);

        // Fetch a list of article headings in English.
        // $heading_titles = $this->setHeadings($manual);
        // Get the list of folder headings
        $menuFolders = file_get_contents($this->gfmfiles_path . $manual . '/' . $language . '/folders.json');
        $this->menuFolders = json_decode($menuFolders, true);
    
        $this->menuHTML = '<ul id="jdmmenu" class="jdm-metismenu metismenu mm-show">' . "\n";
        $this->renderSubmenu($this->menuObject, $manual, $language, true);
        $this->menuHTML .= '</ul>' . "\n";

        return $this->menuHTML;
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
                $wrap_label = "<span class=\"item-title\">{$this->menuFolders[$key]}</span>";
                $this->menuHTML .= "<li class=\"item parent item-level-{$this->toclevel}\">";
                $this->menuHTML .= "<a href=\"#\" class=\"has-arrow\">";
                $this->menuHTML .= "{$wrap_label}</a>\n";
                    // Add a path prefix 
                    $this->pathPrefix[] = $key;

                    // Recursively build sublist.
                    $this->renderSubmenu($value, $manual, $language);
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
                //$this->order[] = [$article_id, $path, $value, $link];
            }
        }
        $this->menuHTML .= "</ul>\n";

        // Remove the added path prefix
        array_pop($this->pathPrefix);

        // On return decrease the toclevel
        $this->toclevel -= 1;
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
     * Get article data for the menu.
     *
     * @param   string  $manual     The article path fragment.
     * @param   string  $language   The article language.
     * @param   string  $heading    The article filename.
     *
     * @return  string  The required html code.
     *
     * @since   1.0
     */
    protected function getArticleData($manual, $language, $heading)
    {
        // Try to get the article record from the database.
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->createQuery();
        $query->select($db->quoteName(array('id','title', 'source_url')))
        ->from($db->quoteName('#__jdm_articles'))
        ->where($db->quoteName('manual') . ' = :manual')
        ->where($db->quoteName('language') . ' = :language')
        ->where($db->quoteName('heading') . ' = :heading')
        ->bind(':manual', $manual, ParameterType::STRING)
        ->bind(':language', $language, ParameterType::STRING)
        ->bind(':heading', $heading, ParameterType::STRING);
        $db->setQuery($query);
        return $db->loadResult();
    }

    /**
     * Get the accordion headings for the menu.
     *
     * @param   manual     $manual      The article path fragment.
     * @param   language   $language    The article language.
     *
     * @return  array      An associative array of headings.
     *
     * @since   1.0
     */
    protected function setHeadings($manual, $language = 'en')
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->createQuery();
        $query->select($db->quoteName(array('heading','title')))
        ->from($db->quoteName('#__jdm_menu_headings'))
        ->where($db->quoteName('manual') . ' = :manual')
        ->where($db->quoteName('language') . ' = :language')
        ->bind(':manual', $manual, ParameterType::STRING)
        ->bind(':language', $language, ParameterType::STRING);
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        $headings = [];
        foreach ($rows as $row) {
            $headings[$row->heading] = $row->title;
        }
        return $headings;
    }
}

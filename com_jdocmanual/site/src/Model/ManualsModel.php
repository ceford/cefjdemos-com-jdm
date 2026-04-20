<?php

/**
 * @package     Jdocmanual
 * @subpackage  Site
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Site\Model;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\InthispageHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * First page start. Do some checks, get the Menu and Manual and Language lists.
 *
 * @since  1.0
 */
class ManualsModel extends ListModel
{
    /**
     * Get the first article on page load.
     *
     * @param string $manual        The name of the manual.
     * @param string $language      The name of the language.
     * @param string $data_path     The name of the data_path.
     *
     * @return  array  An array of display items.
     *
     * @since   1.0
     */
    public function getPage($manual, $language, $path)
    {
        $db = $this->getDatabase();
        $query = $db->createQuery();

        $query->select($db->quoteName(array('title','html','order_next','order_previous')))
        ->from($db->quoteName('#__jdm_articles'))
        ->where($db->quoteName('manual') . ' = :manual')
        ->where($db->quoteName('language') . ' = :language')
        ->where($db->quoteName('path') . ' = :path')
        ->bind(':manual', $manual, ParameterType::STRING)
        ->bind(':language', $language, ParameterType::STRING)
        ->bind(':path', $path, ParameterType::STRING);
        $db->setQuery($query);
        $row = $db->loadObject();

        if (empty($row) && $language != 'en') {
            // Try again with English
            $query = $db->createQuery();
            $language = 'en';

            $query->select($db->quoteName(array('title','html','order_next','order_previous')))
            ->from($db->quoteName('#__jdm_articles'))
            ->where($db->quoteName('manual') . ' = :manual')
            ->where($db->quoteName('language') . ' = :language')
            ->where($db->quoteName('path') . ' = :path')
            ->bind(':manual', $manual, ParameterType::STRING)
            ->bind(':language', $language, ParameterType::STRING)
            ->bind(':path', $path, ParameterType::STRING);
            $db->setQuery($query);
            $row = $db->loadObject();
        }
        if (empty($row)) {
            return array('placeholder', '', 'Please select a document');
        }

        if (empty($row->html)) {
            return array('placeholder', '', 'The html field has not been populated. Select the GFM Files menu.');
        }

        if ($manual === 'magazine') {
            $host = $_SERVER['HTTP_HOST'];  // e.g., "localhost" or "my.publicsite.org"
            if ($host !== 'localhost') {
                $row->html = InthispageHelper::trim2review($row->html);
            }
        }

        // First page load needs the ToC processed here.
        list ($in_this_page, $content) = InthispageHelper::doToc($row->html);

        // Add the next and previous links to $content
        $order = InthispageHelper::getPreviousNext($row->order_previous, $row->order_next);

        $content .= $order;

        return array($row->title, $in_this_page, $content);
    }

    /**
     * Populate the index of pages that appears in the left column
     *
     * @param string    $manual             The manual code.
     * @param string    $index_language     The menu language code.
     * @param string    $menu_page_id       The id of the currently open article.
     *
     * @return string   The menu html.
     */
    public function getMenu($manual, $language)
    {
        $db = $this->getDatabase();
        $query = $db->createQuery();

        $query->select($db->quoteName('menu'))
        ->from($db->quoteName('#__jdm_menus'))
        ->where($db->quoteName('state') . ' = 1')
        ->where($db->quoteName('manual') . ' = :manual')
        ->where($db->quoteName('language') . ' = :language')
        ->bind(':manual', $manual, ParameterType::STRING)
        ->bind(':language', $language, ParameterType::STRING)
        ->order($db->quoteName('id') . ' desc');
        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * Get a list of manuals.
     *
     * @return  array  An array of query result objects.
     *
     * @since   1.0
     */
    public function getManuals()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery();
        $query->select('*')
        ->from($db->quoteName('#__jdm_manuals'))
        ->where($db->quoteName('state') . ' = 1')
        ->order($db->quoteName('ordering'));
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get a list of language.
     *
     * @param   string  $indexorpage  Which list: index or page.
     *
     * @return  array  An array of query result objects.
     *
     * @since   1.0
     */
    public function getLanguages()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery();
        $query->select($db->quoteName(array('id', 'sef', 'lang_code', 'title')))
            ->from($db->quoteName('#__languages') . ' AS ' . $db->quoteName('a'))
            ->leftjoin($db->quoteName('#__jdm_languages') . ' AS ' . $db->quoteName('b') 
                . ' ON ' . $db->QuoteName('a.lang_id') . ' = ' . $db->QuoteName('b.lang_id'))
            ->where($db->quoteName('b.state') . ' = 1')
            ->order($db->quoteName('sef'));
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get a Manual data source.
     *
     * @param   int  $id  The id of the data source.
     *
     * @return  array  An array of query result objects.
     *
     * @since   1.0
     */
    public function getManualTitle($id)
    {
        $db = $this->getDatabase();
        $query = $db->createQuery();
        $query->select($db->quoteName('title'))
            ->from($db->quoteName('#__jdm_manuals'))
            ->where($db->quoteName('manual') . ' = :manual')
            ->bind(':manual', $id, ParameterType::STRING);
        $db->setQuery($query);
        return $db->loadResult();
    }
}

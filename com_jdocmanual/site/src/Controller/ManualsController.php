<?php

/**
 * @package     Jdocmanual
 * @subpackage  Site
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Database\ParameterType;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\InthispageHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller to load a single article in the Jdocmanual page
 * Called from jdocmanual.js line 193
 *
 * @since  1.0.0
 */
class ManualsController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since   1.0
     */
    protected $default_view = 'manuals';

    /**
     * Get the article from the database and return title and content.
     *
     * @return  $string     json encoded data
     *
     * @since   1.0
     */
    public function fillpanel()
    {
        $manual = $this->input->get('manual', '', 'string');
        $path = $this->input->get('path', '', 'string');

        $cookie = $this->input->cookie->get('jdm5cur', '', 'raw');
        list($man, $il, $language) = preg_split("'/'", $cookie);

        // array [0] 'Title', [1] 'In this Article', [2] 'Page Content'
        $result = $this->getModel()->getPage($manual, $language, $path);
        echo json_encode('{"document_title" : ' . $result[0] . '}, {"toc_panel" : ' . $result[1] . '}, {"document_panel" : ' . $result[2] . '}');
    
        exit();


        $db = Factory::getContainer()->get('DatabaseDriver');

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
            $content = array('Placeholder', 'Please select a document');
        } else {
            if ($manual === 'magazine') {
                $host = $_SERVER['HTTP_HOST'];  // e.g., "localhost" or "my.publicsite.org"
                if ($host !== 'localhost') {
                    $row->html = InthispageHelper::trim2review($row->html);
                }
            }
            // separate the Table of Contents - return array(toc, content);
            $content = InthispageHelper::doToc($row->html);

            // Add the next and previous links to $content
            $order = InthispageHelper::getPreviousNext($row->order_previous, $row->order_next);

            $content[1] .= $order;

            array_push($content, $row->title);
        }
        echo json_encode($content);
        jexit();
    }

    public function display($cachable = false, $urlparams = [])
    {
        return parent::display();
    }
}

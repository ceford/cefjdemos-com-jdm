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
        echo json_encode($result);
        
        exit();
    }

    public function display($cachable = false, $urlparams = [])
    {
        return parent::display();
    }
}

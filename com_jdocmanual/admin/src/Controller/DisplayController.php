<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller to display the default page - display of articles
 *
 * @since  1.0.0
 */
class DisplayController extends BaseController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var     string
     * @since   1.0
     */
    protected $text_prefix = 'COM_JDOCMANUAL_DISPLAY';

    /**
     * The default view.
     *
     * @var     string
     * @since   1.0
     */
    protected $default_view = 'manual';

   /**
     * Get the article from the database and return title and content.
     *
     * @return  $string     json encoded data
     *
     * @since   1.0
     */
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display();
    }
}

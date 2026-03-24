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

use Joomla\CMS\MVC\Controller\AdminController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for a list of article stashes
 *
 * @since  1.0.0
 */
class ArticlestashesController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var     string
     * @since   1.0
     */
    protected $text_prefix = 'COM_JDOCMANUAL_ARTICLESTASHES';
}

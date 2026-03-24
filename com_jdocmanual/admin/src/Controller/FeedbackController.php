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

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Cefjdemos\Component\Jdocmanual\Administrator\Cli\Buildarticles;
use Cefjdemos\Component\Jdocmanual\Administrator\Cli\Buildmenus;
use Cefjdemos\Component\Jdocmanual\Administrator\Cli\Buildproxy;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for a single source
 *
 * @since  1.6
 */
class FeedbackController extends AdminController
{
    protected $text_prefix = 'COM_JDOCMANUAL_FEEDBACK';
}

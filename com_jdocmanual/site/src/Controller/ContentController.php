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

use Cefjdemos\Component\Jdocmanual\Administrator\Controller\ContentController as AdmincontentController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Jdocmanual Content Controller
 * Used by the Site Javascript but uses the admin controller.
 *
 * @since  4.0.0
 */
class ContentController extends AdmincontentController
{
}

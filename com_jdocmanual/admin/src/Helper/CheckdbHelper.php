<?php

/**
 * @package     jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper to check database tables are populated.
 *
 * @since  4.0
 */
class CheckdbHelper
{
    /**
     * Check that the jdm_articles and jdm_menus tables have been populated.
     *
     * @return int  Flag for tables populated, value, 0 or 1.
     */
    public static function isPopulated()
    {
        $params = ComponentHelper::getParams('com_jdocmanual');
        if (empty($params->get('gfmfiles_path'))) {
            return 0;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->createQuery();
        $query->select('COUNT(id)')
        ->from('#__jdm_articles');
        $db->setQuery($query);
        $articles_total = $db->loadResult();
        if (empty($articles_total)) {
            // There are no articles installed
            return 1;
        }

        $query = $db->createQuery();
        $query->select('COUNT(id)')
        ->from('#__jdm_menus');
        $db->setQuery($query);
        $menus_total = $db->loadResult();
        if (empty($menus_total)) {
            // There are no menus installed
            return 2;
        }

        // Data installation seems good!
        return 3;
    }
}

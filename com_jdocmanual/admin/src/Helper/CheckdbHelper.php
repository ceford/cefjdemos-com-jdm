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
    public static function isGood()
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('COUNT(id)')
        ->from('#__jdm_articles');
        $db->setQuery($query);
        $articles_total = $db->loadResult();
        if (empty($articles_total)) {
            return 0;
        }

        $query = $db->getQuery(true);
        $query->select('COUNT(id)')
        ->from('#__jdm_menus');
        $db->setQuery($query);
        $menus_total = $db->loadResult();
        if (empty($menus_total)) {
            return 0;
        }

        return 1;
    }
}

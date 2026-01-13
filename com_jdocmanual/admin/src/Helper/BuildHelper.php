<?php

/**
 * @package     Jdocmanual
 * @subpackage  Cli
 *
 * @copyright   Copyright (C) 2023 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 namespace Cefjdemos\Component\Jdocmanual\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utilities for building jdm tables.
 */
class BuildHelper
{
    /**
     * Get a list of published manuals
     *
     * @param $db       A database connection.
     *
     * @return  $array  A list of published manuals.
     */
    public static function getActiveManuals($db)
    {
        $query = $db->getQuery(true);
        $query->select($db->quoteName('manual'))
        ->from($db->quoteName('#__jdm_manuals'))
        ->where($db->quoteName('state') . ' = 1');
        $db->setQuery($query);
        return $db->loadColumn();
    }

    /**
     * Get a list of published languages
     *
     * @param $db       A database connection.
     *
     * @return  $array  A list of published languages.
     */
    public static function getActivelanguages($db)
    {
        $query = $db->getQuery(true);
        $query->select($db->quoteName('code'))
        ->from($db->quoteName('#__jdm_languages'))
        ->where($db->quoteName('state') . ' = 1')
        ->order($db->quoteName('code') . ' ASC');
        $db->setQuery($query);
        return $db->loadColumn();
    }
}

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
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper to initialise data to avoid first page load problems.
 *
 * @since  1.0
 */
class SetupHelper
{
    /**
     * Set a cookie.
     *
     * @param   string  $name   The name of the cookie to set.
     * @param   string  $value  The cookie value.
     * @param   string  $days   The number of days the cookie should be valid.
     *
     * @return  void
     *
     * @since   1.0
     */
    protected function setcookie($name, $value, $days)
    {
        $app = Factory::getApplication();

        if (!empty($days)) {
            $offset = time() + $days * 24 * 60 * 60;
        } else {
            $offset = 0;
        }
        $cookie_domain = $app->get('cookie_domain', '');
        $cookie_path   = $app->get('cookie_path', '/');
        $cookie = session_get_cookie_params();
        $arr_cookie_options = array (
            'expires' => $offset,
            'path' => $cookie_path,
            'domain' => $cookie_domain, // leading dot for compatibility or use subdomain
            'secure' => false,     // or false
            'httponly' => false,    // or false
            'samesite' => 'Strict' // None || Lax  || Strict
            );
        setcookie($name, $value, $arr_cookie_options);
    }

    /**
     * Check for form parameters change.
     *
     * @return  array   Setup data for a page load.
     *
     * @since   1.0
     */
    public function setup()
    {
        $app = Factory::getApplication();

        $index_language_code = 'en';
        $page_language_code = 'en';

        // Have current manual and page and index languages been set in a cookie.
        $cookie = $app->input->cookie->get('jdmcur');
        if (!empty($cookie)) {
            // Current settings, example: user-en-en
            $cookie_items = preg_split("/-/", $cookie);
            if (!empty($cookie_items) && count($cookie_items) == 3) {
                $old_manual = $cookie_items[0];
                $index_language_code = $cookie_items[1];
                $page_language_code = $cookie_items[2];
            }
        }

        // Try a query string of the form jdocmanual?article=user/articles/some-article[.html]
        $qs =  $app->input->get('article', '', 'string');
        if (!empty($qs)) {
            $segments = explode('/', $qs);
            $manual = empty($segments[0]) ? '' : $segments[0];
            $heading = empty($segments[1]) ? '' : $segments[1];
            $filename = empty($segments[2]) ? '' : $segments[2];
            $filename = str_replace('.html', '', $filename) . '.md';
        } else {
            // Are there query parameters to work with.
            $manual = $app->input->get('manual', '', 'string');
            $heading = $app->input->get('heading', '', 'string');
            $filename = $app->input->get('filename', '', 'string');
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        // Checking for page language change in url bar for site map purposes
        $plc = $app->input->get('page_language_code', '', 'string');
        if (empty($plc)) {
            $lc = $app->input->get('language', '', 'string');
            // Get the current language code.
            $query = $db->getQuery(true);
            $query->select($db->quoteName('code'))
                ->from($db->quotename('#__jdm_languages'))
                ->where($db->quoteName('locale') . ' = ' . $db->quote($lc));
            $db->setQuery($query);
            $page_language_code = $db->loadResult();
        }

        // The case of a language change.
        if (empty($manual)) {
            $new_index_language_code = $app->input->get('index_language_code', '', 'string');
            $new_page_language_code = $app->input->get('page_language_code', '', 'string');
            if (!empty($new_index_language_code)) {
                $index_language_code = $new_index_language_code;
            }
            if (!empty($new_page_language_code)) {
                $page_language_code = $new_page_language_code;
            }
            // If there was a current manual cookie set.
            if (!empty($old_manual)) {
                $manual = $old_manual;
            }
        }

        // The case of a manual change.
        if (!empty($manual) && empty($heading)) {
            // Get the old manual cookie.
            $cookie = $app->input->cookie->get('jdm' . $manual);
            if (!empty($cookie)) {
                list ($heading, $filename) = preg_split("/--/", $cookie);
            } else {
                // Get the default for this manual.
                $query = $db->getQuery(true);
                $query->select($db->quoteName(array('heading_ini', 'filename_ini')))
                ->from($db->quoteName('#__jdm_manuals'))
                ->where($db->quoteName('manual') . ' = :manual')
                ->bind(':manual', $manual, ParameterType::STRING);
                $db->setQuery($query);
                $row = $db->loadObject();
                $heading = $row->heading_ini;
                $filename = $row->filename_ini;
            }
        } else {
            if (empty($manual) || empty($heading) || empty($filename)) {
                // Get the default manual.
                $query = $db->getQuery(true);
                $query->select($db->quoteName(array('manual', 'heading_ini', 'filename_ini')))
                ->from($db->quoteName('#__jdm_manuals'))
                ->where($db->quoteName('home') . ' = 1');
                $db->setQuery($query);
                list($manual, $heading, $filename) = $db->loadRow();
            }
        }
        $new_cookie = "{$manual}-{$index_language_code}-{$page_language_code}";
        $this->setCookie('jdmcur', $new_cookie, 10);

        $new_cookie = "{$heading}--{$filename}";
        // $name, $value, $days
        $this->setCookie('jdm' . $manual, $new_cookie, 10);

        return [$manual, $index_language_code, $page_language_code, $heading, $filename];
    }
}

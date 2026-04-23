<?php

/**
 * @package     jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Site\Helper;

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
        $arr_cookie_options = array (
            'expires' => $offset,
            'path' => $cookie_path,
            'domain' => $cookie_domain, // leading dot for compatibility or use subdomain
            'secure' => false,     // or false
            'httponly' => false,    // or false
            'samesite' => 'Strict' // None || Lax  || Strict
            );
        \setrawcookie($name, $value, $arr_cookie_options);
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
        /**
         * Priorities for setting manual, page_language, menu_language and path.
         * The manual and langiages are in jdm5cur.
         * The path is in a cookie with the same name as the manual: jdm5doc=introduction
         * 
         * 1. Change of page_language or menu_language or manual submits the form with only one item set.
         * 2. If url is set - use it.
         * 3. If cookies are set - use them.
         * 4. Use defaults.
         */
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Get defaults for the default manual
        $query = $db->createQuery();
        $query->select('*')
            ->from('#__jdm_manuals')
            ->where($db->quoteName('home') . ' = 1');
        $db->setQuery($query);
        $row = $db->loadObject();
        $manual = $row->manual;
        $page_language_code = $row->language;
        $menu_language_code = $page_language_code;
        $path = $row->path;

        // Get cookies to use if site has been visited - example docs/en/en
        $cookie = $app->getInput()->cookie->get('jdm5cur', '', 'raw');
        //$cookie = rawurldecode($rawCookie);
        if (!empty($cookie)) {
            $cookie_items = explode('/', $cookie);
            if (!empty($cookie_items) && count($cookie_items) == 3) {
                $manual = $cookie_items[0];
                $menu_language_code = $cookie_items[1];
                $page_language_code = $cookie_items[2];
            }

            $path = $app->getInput()->cookie->get('jdm5' . $manual, '', 'raw');
            //$path = rawurldecode($rawCookie);
        }

        // Look for a change of page settings 
        $plc = $app->getInput()->get('set_plc');
        $mlc = $app->getInput()->get('set_mlc');
        $new_man = $app->getInput()->get('set_manual');

        if (!empty($plc)) {
            $page_language_code = $plc;
        } else if (!empty($mlc)) {
            $menu_language_code = $mlc;
        } else if (!empty($new_man)) {
            $manual = $new_man;
        }

        // Try a query string of the form jdocmanual?article=user/articles/some-article[.html]
        $qs =  $app->getInput()->get('article', '', 'string');
        if (!empty($qs)) {
            $segments = explode('/', $qs, 2);
            $manual = empty($segments[0]) ? '' : $segments[0];
            $path = empty($segments[1]) ? '' : $segments[1];
        }
        
        // Current page: manual, menu_language, page_language Example: user-en-en
        $this->setCookie('jdm5cur', "{$manual}/{$menu_language_code}/{$page_language_code}", 10);

        // Settings for current manual: $name, $value, Example: jdm5user, user/getting-started, $days
        $this->setCookie('jdm5' . $manual, $path, 10);

        return [$manual, $menu_language_code, $page_language_code, $path];
    }
}

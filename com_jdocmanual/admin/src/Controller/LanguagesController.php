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
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for language selection
 *
 * @since  5.0
 */
class LanguagesController extends AdminController
{
    protected $text_prefix = 'COM_JDOCMANUAL_LANGUAGES';

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     *
     * @since   1.0
     */
    public function getModel($name = 'Languages', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function toggle()
    {
        $this->checkToken('post');

        $app = Factory::getApplication();
        $lang_id = $app->input->get('lang_id', 0, 'int');

        $db = Factory::getContainer()->get('DatabaseDriver');

        // Get the id value of the lang_id if it exists
        $query = $db->createQuery();
        $query->select($db->quoteName('state'))
            ->from($db->quoteName('#__jdm_languages'))
            ->where($db->quoteName('lang_id') . ' = :lang_id')
            ->bind(':lang_id', $lang_id, ParameterType::INTEGER);
        $db->setQuery($query);
        $state = $db->loadResult();

        $query = $db->createQuery();
        if (empty($state)) {
            if (is_numeric($state)) {
                $query->update($db->quoteName('#__jdm_languages'))
                ->where($db->quoteName('lang_id') . ' = :lang_id')
                ->bind(':lang_id', $lang_id, ParameterType::INTEGER);
            } else {
                $query->insert($db->quoteName('#__jdm_languages'));
                $query->set($db->quoteName('lang_id') . ' = :lang_id')
                    ->bind(':lang_id', $lang_id, ParameterType::INTEGER);
            }
            $query->set($db->quoteName('state') . ' = 1');
            $result = 'Yes';
        } else {
            $query->update($db->quoteName('#__jdm_languages'))
                ->set($db->quoteName('lang_id') . ' = 0')
                ->where($db->quoteName('lang_id') . ' = :lang_id')
                    ->bind(':lang_id', $lang_id, ParameterType::INTEGER);
            $result = 'No';
        }
        $db->setQuery($query);
        $db->execute();
        
        $json = json_encode('{"result": "' . $result . '"}');
        exit($json);
    }
}
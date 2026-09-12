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
use Joomla\Database\ParameterType;
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
class ManualsController extends AdminController
{
    protected $text_prefix = 'COM_JDOCMANUAL_MANUALS';

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
    public function getModel($name = 'Source', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to toggle the state of a source in the manuals table
     * 
     * @return A json object containing result of toggle.
     */
    public function toggle()
    {
        $this->checkToken('post');

        $app = Factory::getApplication();
        $manual_id = $app->input->get('manual_id', 0, 'int');

        // Returning json - should provide an error feedback.
        if (empty($manual_id)) {
            exit(0);
        }
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Get the id value of the lang_id if it exists
        $query = $db->createQuery();
        $query->select($db->quoteName('state'))
            ->from($db->quoteName('#__jdm_manuals'))
            ->where($db->quoteName('id') . ' = :manual_id')
            ->bind(':manual_id', $manual_id, ParameterType::INTEGER);
        $db->setQuery($query);
        $state = $db->loadResult();

        $query = $db->createQuery();
        $query->update($db->quoteName('#__jdm_manuals'))
            ->where($db->quoteName('id') . ' = :manual_id')
            ->bind(':manual_id', $manual_id, ParameterType::INTEGER);
        if (empty($state)) {
            $query->set($db->quoteName('state') . ' = 1');
            $result = 'Yes';
        } else {
            $query->set($db->quoteName('state') . ' = 0');
            $result = 'No';
        }
        $db->setQuery($query);
        $db->execute();
        
        $json = json_encode('{"result": "' . $result . '"}');
        exit($json);
    }

    /**
     * Method to toggle the state of a source in the manuals table
     * 
     * @return A json object containing result of toggle.
     */
    public function setdefault()
    {
        //$this->checkToken('get');

        $app = Factory::getApplication();
        $manual_id = $app->input->get('manual_id', 0, 'int');

        // Returning json - should provide an error feedback.
        if (empty($manual_id)) {
            exit(0);
        }
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Update the manuals table and set the home folder to 0 for all recoeds
        $query = $db->createQuery();
        $query->update($db->quoteName('#__jdm_manuals'))
            ->set($db->quoteName('home') . ' = 0');
        $db->setQuery($query);
        $db->execute();

        $query = $db->createQuery();
        $query->update($db->quoteName('#__jdm_manuals'))
            ->set($db->quoteName('home') . ' = 1')
            ->where($db->quoteName('id') . ' = :manual_id')
            ->bind(':manual_id', $manual_id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        $this->setRedirect(Route::_('index.php?option=com_jdocmanual&view=manuals', false));

    }

    /**
     * Update the article html for the selected manual and language.
     * This function updates all of the articles (ToDo: selected article).
     *
     * @return  void
     *
     * @since   1.0
     */
    public function buildhtml()
    {
        $manual = $this->input->get('manual', '', 'string');
        $language = $this->input->get('language', '', 'string');
        $force = $this->input->get('force', 0, 'int');

        if (!empty($manual)) {
            $ba = new Buildarticles();
            $summary = "Building Articles\n";
            $summary .= $ba->go($manual, $language, $force);
            $this->app->enqueueMessage(nl2br($summary, true));

            $this->buildmenus();

            if ($manual == 'help') {
                $this->buildproxy();
            }
        }
        $this->setRedirect(Route::_('index.php?option=com_jdocmanual&view=manuals', false));
    }

    /**
     * Update the menus html for the selected manual.
     *
     * @return  void
     *
     * @since   1.0
     */
    public function buildmenus()
    {
        $manual = $this->input->get('manual', '', 'string');
        $language = $this->input->get('language', '', 'string');

        if (!empty($manual)) {
            $bm = new Buildmenus();
            $summary = "Building Menus\n";
            $summary .= $bm->go($manual, $language);
            $this->app->enqueueMessage(nl2br($summary, true));
        }
        $this->setRedirect(Route::_('index.php?option=com_jdocmanual&view=manuals', false));
    }

    /**
     * Update the proxy html for the help manual.
     *
     * @return  void
     *
     * @since   1.0
     */
    public function buildproxy()
    {
        $manual = $this->input->get('manual', '', 'string');
        $language = $this->input->get('language', '', 'string');

        $summary = "Building Proxy\n";

        if (!empty($manual) && $manual == 'help') {
            $bp = new Buildproxy();
            $summary .= $bp->go($manual, $language);
        } else {
            $summary .= 'Select the <strong>help</strong> manual to build the proxy server';
        }
        $this->app->enqueueMessage(nl2br($summary, true));
        $this->setRedirect(Route::_('index.php?option=com_jdocmanual&view=manuals', false));
    }

    /**
     * Issue a git pull command for a specific manual and language
     *
     * @return  void
     *
     * @since   1.0
     */
    public function gitpull()
    {
        $manual = $this->input->get('manual', '', 'string');
        $language = $this->input->get('language', '', 'string');

        $summary = "Git Pull Request\n";

        if (empty($manual) || empty($language)) {
            $summary .= "\nMissing manual or language!\n";
        } else {
            $params = ComponentHelper::getParams('com_jdocmanual');
            // Get the the 'manuals' path from the component parameters.
            $path = $params->get('gfmfiles_path') . '/' . $manual . '/' . $language;
            $command = "cd $path; git pull";
            exec($command, $result);
            $summary .= implode("\n", $result);
        }
        $this->app->enqueueMessage(nl2br($summary, true));
        $this->setRedirect(Route::_('index.php?option=com_jdocmanual&view=manuals', false));
    }

    /**
     * Unpublish articles that have been deleted from the source files.
     *
     * @return  void
     *
     * @since   1.0
     */
    public function unpublishdeleted()
    {
        $ba = new Buildarticles();

        $summary = $ba->unpublishDeleted();

        $this->app->enqueueMessage(nl2br($summary, true));
        $this->setRedirect(Route::_('index.php?option=com_jdocmanual&view=manuals', false));
    }
}

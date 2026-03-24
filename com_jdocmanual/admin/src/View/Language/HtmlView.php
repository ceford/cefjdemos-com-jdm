<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\View\Language;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a language.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The \JForm object
     *
     * @var  \JForm
     */
    protected $form;

    /**
     * The active item
     *
     * @var  object
     */
    protected $item;

    /**
     * The model state
     *
     * @var  \JObject
     */
    protected $state;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        $model       = $this->getModel();
        $model->setUseExceptions(true);

        try {
            $this->item  = $model->getItem();
            $this->form  = $model->getForm();
            $this->state = $model->getState();
        } catch (\Exception $e) {
            throw new GenericDataException($e->getMessage(), 500, $e);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.0
     */
    protected function addToolbar()
    {
        $tmpl = Factory::getApplication()->input->getCmd('tmpl');

        Factory::getApplication()->input->set('hidemainmenu', true);

        $user  = $this->getCurrentUser();

        $userId     = $user->id;
        $isNew      = ($this->item->id == 0);

        ToolbarHelper::title(
            $isNew ? Text::_('COM_JDOCMANUAL_LANGUAGE_NEW') : Text::_('COM_JDOCMANUAL_LANGUAGE_EDIT'),
            'language jdocmanual'
        );

        ToolbarHelper::apply('language.apply');
        ToolbarHelper::save('language.save');

        if (empty($isNew)) {
            ToolbarHelper::cancel('language.cancel', 'JTOOLBAR_CLOSE');
        } else {
            ToolbarHelper::cancel('language.cancel');
        }

        ToolbarHelper::divider();

        if ($tmpl !== 'component') {
            ToolbarHelper::help('language', true);
        }
    }
}

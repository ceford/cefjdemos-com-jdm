<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\View\Articlestashes;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of jdocmanual locations.
 *
 * @since  4.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The active search filters
     *
     * @var    array
     * @since   1.0
     */
    public $activeFilters = [];

    /**
     * Category data
     *
     * @var    array
     * @since   1.0
     */
    protected $categories = [];

    /**
     * The search tools form
     *
     * @var    Form
     * @since   1.0
     */
    public $filterForm;

    /**
     * An array of items
     *
     * @var    array
     * @since   1.0
     */
    protected $items = [];

    /**
     * An array of my stashed items
     *
     * @var    array
     * @since   1.0
     */
    protected $mystashes = [];

    /**
     * An array of new page items
     *
     * @var    array
     * @since   1.0
     */
    protected $newpages = [];

    /**
     * The pagination object
     *
     * @var    Pagination
     * @since   1.0
     */
    protected $pagination;

    protected $pull_requests = null;

    /**
     * The model state
     *
     * @var    Registry
     * @since   1.0
     */
    protected $state;

    /**
     * The media tree
     *
     * @var     Array
     * @since   4.0
     */
    protected $tree;

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  void
     *
     * @since   1.0
     * @throws  Exception
     */
    public function display($tpl = null): void
    {
        $model               = $this->getModel();
        $model->setUseExceptions(true);

        try {
            $this->items         = $model->getItems();
            $this->pagination    = $model->getPagination();
            $this->state         = $model->getState();
            $this->filterForm    = $model->getFilterForm();
            $this->activeFilters = $model->getActiveFilters();
            $this->mystashes     = $model->getMystashes();
            $this->newpages      = $model->getNewpages();

            $user  = $this->getCurrentUser();

            // Change this to use custom group.
            if ($user->authorise('jdocmanual.publish', 'com_jdocmanual')) {
                $this->pull_requests = $model->getPullrequests();
            }
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
    protected function addToolbar(): void
    {
        $tmpl = Factory::getApplication()->input->getCmd('tmpl');

        $user  = $this->getCurrentUser();

        // Get the toolbar object instance
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_JDOCMANUAL_ARTICLES_STASHES'), 'code-branch');

        // Only show the New button if the selected language is English.
        if ($this->state->get('filter.language') == 'en'  && $this->state->get('filter.manual') != 'help') {
            $toolbar->addNew('articlestash.add');
        }

        if ($user->authorise('core.admin', 'com_jdocmanual') || $user->authorise('core.options', 'com_jdocmanual')) {
            $toolbar->preferences('com_jdocmanual');
        }

        if ($tmpl !== 'component') {
            ToolbarHelper::help('articlestashes', true);
        }
    }
}

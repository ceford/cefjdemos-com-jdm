<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\View\Manual;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\SetupHelper;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\CheckdbHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for jdocmanual.
 *
 * @since  4.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The search tools form
     *
     * @var    Form
     * @since  1.6
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var    array
     * @since  1.6
     */
    public $activeFilters = [];

    /**
     * Category data
     *
     * @var    array
     * @since  1.6
     */
    protected $categories = [];

    /**
     * An array for the list of index languages
     *
     * @var    array
     * @since  1.6
     */
    protected $index_languages = [];

    /**
     * An array of items
     *
     * @var    array
     * @since  1.6
     */
    protected $items = [];

    /**
     * An array for the list of manuals
     *
     * @var    array
     * @since  1.6
     */
    protected $manuals = [];

    /**
     * An array for the list of page languages
     *
     * @var    array
     * @since  1.6
     */
    protected $page_languages = [];

    /**
     * The pagination object
     *
     * @var    Pagination
     * @since  1.6
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var    Registry
     * @since  1.6
     */
    protected $state;

    protected $active_manual;

    protected $plugin_status;

    protected $manual;
    protected $index_language_code;
    protected $page_language_code;
    protected $heading;
    protected $filename;
    protected $display_title;
    protected $in_this_page;
    protected $page_content;
    protected $menu;
    protected $source;

    /**
     * Set to 1 if there are records in the the #__jdm_articles table.
     *
     * @var integer;
     * @since 4.0
     */
    protected $dbisgood;

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  void
     *
     * @since   1.6
     * @throws  Exception
     */
    public function display($tpl = null): void
    {
        /** @var JdocmanualModel $model */
        $model = $this->getModel();

        // Check the database has been populated.
        $this->dbisgood = CheckdbHelper::isGood();

        $this->plugin_status = $model->checkplugin();

        if (!empty($this->dbisgood)) {
            $this->manuals       = $model->getManuals();
            $this->index_languages     = $model->getLanguages('index');
            $this->page_languages     = $model->getLanguages('page');

            $setuphelper = new SetupHelper();
            list(
                $this->manual,
                $this->index_language_code,
                $this->page_language_code,
                $this->heading,
                $this->filename
            ) = $setuphelper->setup();

            list ($this->display_title, $this->in_this_page, $this->page_content) =
            $model->getPage(
                $this->manual,
                $this->page_language_code,
                $this->heading,
                $this->filename
            );

            $this->menu = $model->getMenu(
                $this->manual,
                $this->index_language_code,
                $this->heading,
                $this->filename
            );

            $this->source = $model->getSourceData($this->manual);

            // Check for errors.
            if (count($errors = $this->get('Errors'))) {
                throw new GenericDataException(implode("\n", $errors), 500);
            }
        }
        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar(): void
    {
        $app = Factory::getApplication();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        if (!empty($this->dbisgood)) {
            ToolbarHelper::title($this->source->title . ' (' . $this->page_language_code . ')', 'book');

            $dropdown = $toolbar->dropdownButton('select-manual')
            ->text('COM_JDOCMANUAL_MANUAL_MANUAL_SELECT')
            ->toggleSplit(false)
            ->icon('icon-code-branch')
            ->buttonClass('btn btn-action');

            $childBar = $dropdown->getChildToolbar();

            foreach ($this->manuals as $manual) {
                $icon = '';
                if ($this->manual == $manual->manual) {
                    $icon = 'icon-check';
                }
                $childBar->linkButton('manual-' . $manual->manual)
                ->text($manual->title)
                ->buttonClass('set-manual border-bottom')
                ->icon($icon)
                ->url('index.php?option=com_jdocmanual&view=manual&manual='  . $manual->manual);
            }

            $dropdown = $toolbar->dropdownButton('select-language')
            ->text('COM_JDOCMANUAL_MANUAL_INDEX_LANGUAGE')
            ->toggleSplit(false)
            ->icon('icon-language')
            ->buttonClass('btn btn-action');

            $childBar = $dropdown->getChildToolbar();

            foreach ($this->index_languages as $language) {
                $icon = '';
                if ($this->index_language_code == $language->code) {
                    $icon = 'icon-check';
                }
                $childBar->linkButton($language->code)
                ->text('<img src="media/mod_languages/images/' .
                str_replace('-', '_', strtolower($language->locale))  . '.gif" alt="">' . ' ' . $language->locale)
                ->buttonClass('set-language index')
                ->url('index.php?option=com_jdocmanual&view=manual&index_language_code='  . $language->code)
                ->icon($icon);
            }

            $dropdown = $toolbar->dropdownButton('select-language')
            ->text('COM_JDOCMANUAL_MANUAL_PAGE_LANGUAGE')
            ->toggleSplit(false)
            ->icon('icon-language')
            ->buttonClass('btn btn-action');

            $childBar = $dropdown->getChildToolbar();

            foreach ($this->page_languages as $language) {
                $icon = '';
                if ($this->page_language_code == $language->code) {
                    $icon = 'icon-check';
                }
                $childBar->linkButton($language->code)
                ->text('<img src="media/mod_languages/images/' . str_replace('-', '_', strtolower($language->locale))  . '.gif" alt="">' . ' ' . $language->title)
                ->buttonClass('set-language')
                ->url('index.php?option=com_jdocmanual&view=manual&page_language_code='  . $language->code)
                ->icon($icon);
            }

            $dropdown = $toolbar->dropdownButton('select-actions')
            ->text('COM_JDOCMANUAL_MANUAL_ACTIONS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action');

            $childBar = $dropdown->getChildToolbar();

            $layout = new FileLayout('toolbar.toggle-joomla-menu', JPATH_ADMINISTRATOR . '/components/com_jdocmanual/layouts');
            $childBar->appendButton('Custom', $layout->render([]), 'toggle-joomla-menu');

            $childBar->linkButton('notes')
            ->text('Installation notes')
            ->buttonClass('install-notes')
            ->url('index.php?option=com_jdocmanual&view=manual&&layout=notes')
            ->icon('icon-bookmark');
        } else {
            ToolbarHelper::title('Installation Notes', 'book');
        }

        $user  = $this->getCurrentUser();

        if ($user->authorise('core.admin', 'com_jdocmanual') || $user->authorise('core.options', 'com_jdocmanual')) {
            $toolbar->preferences('com_jdocmanual');
        }

        $tmpl = $app->input->getCmd('tmpl');
        if ($tmpl !== 'component') {
            ToolbarHelper::help('jdocmanual', true);
        }
    }
}

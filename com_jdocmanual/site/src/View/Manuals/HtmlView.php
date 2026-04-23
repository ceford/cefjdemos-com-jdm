<?php

/**
 * @package     Jdocmanual
 * @subpackage  Site
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Site\View\Manuals;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Cefjdemos\Component\Jdocmanual\Site\Helper\SetupHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for Manual.
 *
 * @since  4.0
 */
class HtmlView extends BaseHtmlView
{
    protected $languages;

    protected $heading;
    protected $filename;
    protected $title;
    protected $manual_title;
    protected $diff;
    protected $in_this_page;
    protected $page_content;
    protected $menu;
    protected $manualTitle;


    protected $manual;
    protected $manuals;
    protected $index_language_code;
    protected $page_language_code;
    protected $menu_page_id;

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
        /** @var ManualModel $model */
        $model                  = $this->getModel();
        $model->setUseExceptions(true);

        try {
            $this->manuals      = $model->getManuals();
            $this->languages    = $model->getLanguages();

            $setuphelper = new SetupHelper;

            list(
                $this->manual,
                $this->index_language_code,
                $this->page_language_code,
                $this->path
            ) = $setuphelper->setup();

            list ($this->title, $this->in_this_page, $this->page_content) =
            $model->getPage(
                $this->manual,
                $this->page_language_code,
                $this->path
            );

            $this->menu = $model->getMenu(
                $this->manual,
                $this->index_language_code
            );

            $this->manualTitle = $model->getManualTitle($this->manual);
        } catch (\Exception $e) {
            //throw new GenericDataException($e->getMessage(), 500, $e);
        }

        parent::display($tpl);
    }


    /**
     * Add the page language picker.
     *
     * @return  void
     *
     * @since   1.0
     */
    protected function addToolbarPageLanguage(): void
    {
        $pageToolbar = Factory::getContainer()
            ->get(ToolbarFactoryInterface::class)->createToolbar('pageLanguage');

        $dropdown = $pageToolbar->dropdownButton('select-language')
        ->text('COM_JDOCMANUAL_MANUAL_PAGE_LANGUAGE')
        ->toggleSplit(false)
        ->icon('icon-language')
        ->buttonClass('btn btn-sm btn-outline-primary ms-3');

        $childBar = $dropdown->getChildToolbar();

        foreach ($this->languages as $language) {
            $icon = '';
            if ($this->page_language_code == $language->sef) {
                $icon = 'icon-check';
            }
            $childBar->linkButton($language->sef)
            ->text('<img src="media/mod_languages/images/' . str_replace('-', '_', strtolower($language->lang_code))  . '.gif" alt="">' . ' ' . $language->title)
            ->buttonClass('set-language border-bottom')
            ->url($language->sef . '/jdocmanual?set_plc='  . $language->sef)
            ->icon($icon);
        }

        echo $pageToolbar->render();
    }

    /**
     * Add the menu language picker.
     *
     * @return  void
     *
     * @since   1.0
     */
    protected function addToolbarMenuLanguage(): void
    {
        //$menuToolbar = $this->getDocument()->getToolbar();
        $menuToolbar = Factory::getContainer()
            ->get(ToolbarFactoryInterface::class)->createToolbar('menuLanguage');

        $dropdown = $menuToolbar->dropdownButton('select-language')
        ->text('COM_JDOCMANUAL_MANUAL_INDEX_LANGUAGE')
        ->toggleSplit(false)
        ->icon('icon-language')
        ->buttonClass('btn btn-sm btn-outline-primary ms-3');

        $childBar = $dropdown->getChildToolbar();

        foreach ($this->languages as $language) {
            $icon = '';
            if ($this->index_language_code == $language->sef) {
                $icon = 'icon-check';
            }
            $childBar->linkButton($language->sef)
            ->text('<img src="media/mod_languages/images/' .
            str_replace('-', '_', strtolower($language->lang_code))  . '.gif" alt="">' . ' ' . $language->lang_code)
            ->buttonClass('set-language index border-bottom')
            ->url('jdocmanual?set_mlc='  . $language->sef)
            ->icon($icon);
        }
        echo $menuToolbar->render();
    }

    /**
     * Add the manual picker.
     *
     * @return  void
     *
     * @since   1.0
     */
    protected function addToolbarSelectManual(): void
    {
        $manualToolbar = Factory::getContainer()
            ->get(ToolbarFactoryInterface::class)->createToolbar('menuLanguage');

        $dropdown = $manualToolbar->dropdownButton('select-manual')
        ->text('COM_JDOCMANUAL_MANUAL_MANUAL_SELECT')
        ->toggleSplit(false)
        ->icon('icon-code-branch')
        ->buttonClass('btn btn-sm btn-outline-primary');

        $childBar = $dropdown->getChildToolbar();

        // ToDo: change to cycle through manuals from params
        foreach ($this->manuals as $manual) {
            $icon = '';
            if ($this->manual == $manual->manual) {
                $icon = 'icon-check';
            }
            $childBar->linkButton('manual-' . $manual->manual)
            ->text($manual->title)
            ->buttonClass('set-manual border-bottom')
            ->icon($icon)
            ->url('jdocmanual?set_manual='  . $manual->manual);
        }

        echo $manualToolbar->render();
    }
}

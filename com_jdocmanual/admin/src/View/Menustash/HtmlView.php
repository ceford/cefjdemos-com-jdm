<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\View\Menustash;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Button\BasicButton;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Cefjdemos\Component\Jdocmanual\Administrator\Cli\Buildmenus;
use Jfcherng\Diff\DiffHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a menustash.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{

    /**
     * The difference between old and new versions.
     *
     * @var string
     */
    protected $diff;

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
     * The html rendering of gfm
     *
     * @var
     */
    protected $preview;

    protected $stash;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        $model          = $this->getModel();
        $model->setUseExceptions(true);

        try {
            $this->item     = $model->getItem();
            $this->form     = $model->getForm();
            $this->state    = $model->getState();

            // Users/ceford/data/manuals/
            $params = ComponentHelper::getParams('com_jdocmanual');
            $basepath = $params->get('gfmfiles_path');
            $default_language = $this->gfmfiles_path = $params->get('default_language');

            // Check that the basepath is not empty - forgotten to enter it on installation.
            if (empty($basepath)) {
                Factory::getApplication()->enqueueMessage(Text::_('COM_JDOCMANUAL_ARTICLES_BASEPATH_MISSING'), 'error');
                return false;
            }

            $source = trim(file_get_contents($basepath . '/' . $this->item->manual . '/' . $default_language . '/menu.json'));

            $this->form->setValue('source', null, $source);
            require_once(JPATH_ADMINISTRATOR . '/components/com_jdocmanual/src/Helper/diffoptions.php');

            // if there is a stash record use the stash content.
            if (!empty($this->item->id)) {
                $stash = trim($this->item->menu_text);
                $old = $source;
            } else {
                // Get the source text.
                $stash = $source;
                $old = $stash;
                Factory::getApplication()->enqueueMessage(Text::_('COM_JDOCMANUAL_ARTICLES_ARTICLE_COPIED'), 'warning');
            }
            $new = $stash;
            $this->form->setValue('menu_text', null, $stash);

            // make the line endings consistent
            $new = preg_replace('~\R~u', "\n", $new);
            $old = preg_replace('~\R~u', "\n", $old);
            // The diff is the difference between the stash and the source.
            $this->diff = DiffHelper::calculate(
                $old,
                $new,
                'SideBySide',
                $diffOptions,
                $rendererOptions,
            );

            // Fill the preview field.
            $mh = new Buildmenus();
            $this->preview = $mh->buildstashmenu($this->item->manual, 'en', $new);

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

        $user  = $this->getCurrentUser();

        Factory::getApplication()->input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);

        ToolbarHelper::title(Text::_('COM_JDOCMANUAL_MENUSTASH_EDIT'), 'code-branch');

        ToolbarHelper::apply('menustash.apply');
        ToolbarHelper::save('menustash.save');

        if (empty($isNew)) {
            ToolbarHelper::cancel('menustash.cancel', 'JTOOLBAR_CLOSE');
        } else {
            ToolbarHelper::cancel('menustash.cancel');
        }

        $bar = $this->getDocument()->getToolbar();

        if (!$isNew) {
            $button = (new BasicButton('gfm-delete'))
            ->text('JTOOLBAR_DELETE')
            ->buttonClass('btn btn-danger')
            ->icon('icon-times icon-fw')
            ->task('menustash.delete');
            $bar->appendButton($button);
        }

        if (!empty($this->item->id)) {
            if (empty($this->item->pr)) {
                $button = (new BasicButton('gfm-pull-request'))
                ->text('COM_JDOCMANUAL_ARTICLES_PULL_REQUEST')
                ->buttonClass('btn btn-success')
                ->icon('icon icon-code-branch')
                ->task('menustash.pullrequest');
                $bar->appendButton($button);
            } else {
                $button = (new BasicButton('gfm-pull-request'))
                ->text('COM_JDOCMANUAL_ARTICLES_PULL_REQUEST_CANCEL')
                ->buttonClass('btn btn-success')
                ->icon('icon icon-code-branch')
                ->task('menustash.pullrequestcancel');
                $bar->appendButton($button);

                if ($user->authorise('jdocmanual.publish', 'com_jdocmanual')) {
                    $button = (new BasicButton('gfm-commit'))
                    ->text('COM_JDOCMANUAL_ARTICLES_COMMIT')
                    ->buttonClass('btn btn-success')
                    ->icon('icon icon-code-branch')
                    ->task('menustash.commit');
                    $bar->appendButton($button);
                }
            }
        }

        ToolbarHelper::divider();

        if ($tmpl !== 'component') {
            ToolbarHelper::help('menustash', true);
        }
    }
}

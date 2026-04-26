<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$params = ComponentHelper::getParams('com_jdocmanual');

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_jdocmanual.jdocmanual')
->useScript('com_jdocmanual.jdocmanual')
->useScript('com_jdocmanual.builders')
->useScript('com_jdocmanual.manuals');

?>

<?php if ($this->dbIspopulated === 0) : ?>

<h2><?php echo Text::_('COM_JDOCMANUAL_MANUALS_DATA_SOURCE_NOT_SET'); ?></h2>
<p><?php echo Text::_('COM_JDOCMANUAL_MANUALS_DATA_SOURCE_INSTRUCTIONS'); ?></p>

<?php endif; ?>

<?php if ($this->dbIspopulated === 1) : ?>

<h2><?php echo Text::_('COM_JDOCMANUAL_MANUALS_DATA_BUILD_REQUIRED'); ?></h2>
<p><?php echo Text::_('COM_JDOCMANUAL_MANUALS_DATA_BUILD_INSTRUCTIONS'); ?></p>

<?php endif; ?>

<?php if ($this->dbIspopulated === 2) : ?>

<h2><?php echo Text::_('COM_JDOCMANUAL_MANUALS_MENU_BUILD_REQUIRED'); ?></h2>
<p><?php echo Text::_('COM_JDOCMANUAL_MANUALS_MENU_BUILD_INSTRUTIONS'); ?></p>

<?php endif; ?>



<?php if ($this->dbIspopulated > 0) :

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_jdocmanual&task=manuals.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}

$source_edit_route = 'index.php?option=com_jdocmanual&task=manual.edit&id=';

$isGitpullEnabled = $this->isGitpullEnabled();

?>

<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details', 'recall' => true)); ?>
<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'manuals', Text::_('COM_JDOCMANUAL_MANUALS_TAB_MANUALS')); ?>

<form action="<?php echo Route::_('index.php?option=com_jdocmanual&view=manuals'); ?>"
    method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="fa fa-info-circle" aria-hidden="true"></span>
                        <span class="sr-only"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table id="jdocmanualList" class="table">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </th>
                                <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                                </th>
                                <th>
                                    <?php echo Text::_('JDEFAULT'); ?>
                                </th>
                                <th scope="col" class="text-center">
                                    <?php echo HTMLHelper::_(
                                        'searchtools.sort',
                                        'JPUBLISHED',
                                        'a.state',
                                        $listDirn,
                                        $listOrder
                                    ); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_(
                                        'searchtools.sort',
                                        'JGLOBAL_TITLE',
                                        'a.title',
                                        $listDirn,
                                        $listOrder
                                    ); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_(
                                        'searchtools.sort',
                                        'COM_JDOCMANUAL_MANUALS_FOLDER',
                                        'a.manual',
                                        $listDirn,
                                        $listOrder
                                    ); ?>
                                </th>
                                <th class="text-center">
                                    <label for="time-back">
                                    <?php echo Text::_('COM_JDOCMANUAL_MANUALS_TIME_BACK'); ?>
                                    <button type="button" class="help-icon btn btn-sm btn-outline-info" popovertarget="time-back-help" 
                                    aria-label="What is an API key?">?</button>
                                    </label>
                                    <div id="time-back-help" popover class="help-popover">
                                        <?php echo Text::_('COM_JDOCMANUAL_MANUALS_TIME_BACK_DESC'); ?>
                                    </div>
                                </th>
                                <th>
                                    <?php echo Text::_('COM_JDOCMANUAL_MANUALS_BUILD_ARTICLES'); ?>
                                </th>                                <th>
                                    <?php echo Text::_('COM_JDOCMANUAL_MANUALS_BUILD_MENU'); ?>
                                </th>
                                <?php if ($isGitpullEnabled) : ?>
                                <th>
                                    <?php echo Text::_('COM_JDOCMANUAL_MANUALS_PULL'); ?>
                                </th>
                                <?php endif; ?>
                                <th scope="col">
                                    <?php echo HTMLHelper::_(
                                        'searchtools.sort',
                                        'JGRID_HEADING_ID',
                                        'a.id',
                                        $listDirn,
                                        $listOrder
                                    ); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody <?php if ($saveOrder) :
                            ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php
                               endif; ?>>
                            <?php
                            $n = count($this->items);
                            foreach ($this->items as $i => $item) :
                                if (empty($item->state)) { $hide_selectors_css = ' d-none'; } else {$hide_selectors_css = ''; }
                            ?>
                            <tr class="row<?php echo $i % 2; ?> align-middle" data-draggable-group="0"
                                data-item-id="<?php echo $item->id; ?>" data-parents=""
                                data-level="0">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php
                                    $iconClass = '';
                                    if (!$saveOrder) {
                                        $iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
                                    }
                                    ?>
                                    <span class="sortable-handler<?php echo $iconClass ?>">
                                        <span class="icon-ellipsis-v" aria-hidden="true"></span>
                                    </span>
                                    <?php if ($saveOrder) : ?>
                                        <input type="text" name="order[]" size="5"
                                        value="<?php echo $item->id; ?>" class="width-20 text-area-order hidden">
                                    <?php endif; ?>
                                </td>
                                <td class="article-status text-center">
                                    <?php if (!empty($item->home)) : ?>
                                        <span id="jdm-default-<?php echo $item->id; ?>"
                                            data-manual-id="<?php echo $item->id; ?>"
                                            data-manual-name="<?php echo $item->manual; ?>"
                                            class="tbody-icon jgrid" 
                                            aria-labelledby="default-<?php echo $item->id; ?>-desc">
                                            <span class="icon-home" aria-hidden="true"></span>
                                        </span>
                                        <div role="tooltip" id="default-<?php echo $item->id; ?>-desc"><?php echo Text::_('JDEFAULT'); ?></div>
                                    <?php  else : ?>
                                        <span id="jdm-default-<?php echo $item->id; ?>" 
                                            data-manual-id="<?php echo $item->id; ?>" 
                                            data-manual-name="<?php echo $item->manual; ?>"
                                            class="tbody-icon jgrid" 
                                            aria-labelledby="default-<?php echo $item->id; ?>-desc">
                                            <span class="icon-unpublish" aria-hidden="true"></span>
                                        </span>
                                        <div role="tooltip" id="default-<?php echo $item->id; ?>-desc"><?php echo Text::_('JLIB_HTML_SETDEFAULT_ITEM'); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="article-status text-center">
                                    <?php if (!empty($item->state)) : ?>
                                        <span id="jdm-manual-<?php echo $item->id; ?>"
                                            data-manual-id="<?php echo $item->id; ?>"
                                            data-manual-name="<?php echo $item->manual; ?>"
                                            class="tbody-icon jgrid" 
                                            aria-labelledby="toggle-<?php echo $item->id; ?>-desc">
                                            <span class="icon-publish" aria-hidden="true"></span>
                                        </span>
                                        <div role="tooltip" id="toggle-<?php echo $item->id; ?>-desc"><?php echo Text::_('JGLOBAL_CLICK_TO_TOGGLE_STATE'); ?></div>
                                    <?php  else : ?>
                                        <span id="jdm-manual-<?php echo $item->id; ?>" 
                                            data-manual-id="<?php echo $item->id; ?>" 
                                            data-manual-name="<?php echo $item->manual; ?>"
                                            class="tbody-icon jgrid" 
                                            aria-labelledby="toggle-<?php echo $item->id; ?>-desc">
                                            <span class="icon-unpublish" aria-hidden="true"></span>
                                        </span>
                                        <div role="tooltip" id="toggle-<?php echo $item->id; ?>-desc"><?php echo Text::_('JGLOBAL_CLICK_TO_TOGGLE_STATE'); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td scope="row" class="has-context">
                                    <a href="<?php echo Route::_($source_edit_route . $item->id); ?>">
                                    <?php echo $this->escape($item->title); ?>
                                    </a>
                                </td>
                                <td class="d-md-table-cell">
                                    <?php echo $item->manual; ?>
                                </td>
                               <td class="text-center">
                                    <span class="data-force-name-<?php echo $item->manual . $hide_selectors_css; ?>">
                                    <input type="number" inputmode="numeric"  
                                    id="force-<?php echo $i; ?>" value="5" class="form-control valid form-control-success" 
                                    max="16383" step="1" min="0" aria-invalid="false">
                                    </span>
                                </td>
                                <td>
                                    <span class="data-build-name-<?php echo $item->manual . $hide_selectors_css; ?>">
                                    <?php echo $this->getLanguageFormHTML($item->manual, 'buildhtml', $i); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="data-build-menu-<?php echo $item->manual . $hide_selectors_css; ?>">
                                    <?php echo $this->getLanguageFormHTML($item->manual, 'buildmenu', $i); ?>
                                    </span>
                                </td>
                                <?php if ($isGitpullEnabled) : ?>
                                <td>
                                        <span class="data-fetch-name-<?php echo $item->manual . $hide_selectors_css; ?>">
                                        <?php echo $this->getLanguageFormHTML($item->manual, 'gitpull', $i); ?>
                                        </span>
                                </td>
                                <?php endif; ?>
                                <td class="d-none d-md-table-cell">
                                <?php echo $item->id; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php // load the pagination. ?>
                    <?php echo $this->pagination->getListFooter(); ?>

                <?php endif; ?>
                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>

</form>
<?php echo HTMLHelper::_('uitab.endTab'); ?>

<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'newpages', Text::_('COM_JDOCMANUAL_MANUALS_TAB_NOTES')); ?>
    <?php include __DIR__ . '../../manual/notes.php'; ?>
<?php echo HTMLHelper::_('uitab.endTab'); ?>

<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'newpages', Text::_('COM_JDOCMANUAL_MANUALS_MANAGEMENT_TAB')); ?>
    <?php // This is a complete html document: include __DIR__ . '/../../help/en-GB/source.html'; ?>
<?php echo HTMLHelper::_('uitab.endTab'); ?>

<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

<?php endif; ?>

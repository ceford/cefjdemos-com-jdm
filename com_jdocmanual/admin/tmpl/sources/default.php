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
use Joomla\CMS\Factory;
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
->useScript('com_jdocmanual.builders');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_jdocmanual&task=sources.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}

$states = array (
        '0' => Text::_('JUNPUBLISHED'),
        '1' => Text::_('JPUBLISHED'),
        '2' => Text::_('JARCHIVED'),
        '-2' => Text::_('JTRASHED')
);

$source_edit_route = 'index.php?option=com_jdocmanual&task=source.edit&id=';

$isGitpullEnabled = $this->isGitpullEnabled();

?>

<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details', 'recall' => true)); ?>
<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sources', Text::_('COM_JDOCMANUAL_SOURCES_TAB_SOURCES')); ?>

<form action="<?php echo Route::_('index.php?option=com_jdocmanual&view=sources'); ?>"
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
                                <th scope="col">
                                    <?php echo HTMLHelper::_(
                                        'searchtools.sort',
                                        'JSTATUS',
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
                                        'COM_JDOCMANUAL_SOURCES_FOLDER',
                                        'a.manual',
                                        $listDirn,
                                        $listOrder
                                    ); ?>
                                </th>
                                <th>
                                    Build
                                </th>
                                <?php if ($isGitpullEnabled) : ?>
                                <th>
                                    Pull
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
                                ?>
                            <tr class="row<?php echo $i % 2; ?>" data-draggable-group="0"
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
                                <td class="class="article-status"">
                                <?php echo $states[$item->state]; ?>
                                </td>
                                <td scope="row" class="has-context">
                                    <a href="<?php echo Route::_($source_edit_route . $item->id); ?>">
                                    <?php echo $this->escape($item->title); ?>
                                    </a>
                                </td>
                                <td class="d-none d-md-table-cell">
                                <?php echo $item->manual; ?>
                                </td>
                                <td>
                                <?php if (!empty($item->state)) : ?>
                                    <?php echo $this->getLanguageFormHTML($item->manual, 'buildhtml'); ?>
                                <?php endif; ?>
                                </td>
                                <?php if ($isGitpullEnabled) : ?>
                                <td>
                                    <?php if (!empty($item->state)) : ?>
                                        <?php echo $this->getLanguageFormHTML($item->manual, 'gitpull'); ?>
                                    <?php endif; ?>
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

<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'newpages', Text::_('COM_JDOCMANUAL_SOURCES_TAB_NOTES')); ?>
    <?php include __DIR__ . '../../manual/notes.php'; ?>
<?php echo HTMLHelper::_('uitab.endTab'); ?>

<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

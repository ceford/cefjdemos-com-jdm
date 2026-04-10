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

?>

<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details', 'recall' => true)); ?>
<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'manuals', Text::_('COM_JDOCMANUAL_MANUALS_TAB_MANUALS')); ?>

<form action="<?php echo Route::_('index.php?option=com_jdocmanual&view=feedback'); ?>"
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
                    <table class="table" id="jdocmanualList">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_(
                                        'searchtools.sort',
                                        'COM_JDOCMANUAL_ARTICLES_MANUAL',
                                        'a.manual',
                                        $listDirn,
                                        $listOrder
                                    ); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_(
                                        'searchtools.sort',
                                        'COM_JDOCMANUAL_ARTICLES_LANGUAGE',
                                        'a.langauge',
                                        $listDirn,
                                        $listOrder
                                    ); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_(
                                        'searchtools.sort',
                                        'COM_JDOCMANUAL_ARTICLES_HEADING',
                                        'a.heading, a.filename',
                                        $listDirn,
                                        $listOrder
                                    ); ?>
                                </th>
                                <th>
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_JDOCMANUAL_ARTICLES_FILENAME',
                                    'a.heading, a.filename',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                                </th>
                                <th>
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_JDOCMANUAL_FEADBACK_LIKEITORNOT',
                                    'a.likeitornot',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                                </th>
                                <th>
                                    <?php echo Text::_('COM_JDOCMANUAL_FEADBACK_COMMENT'); ?>
                                </th>
                                <th scope="col">
                                    <?php echo Text::_('JGLOBAL_CREATED_DATE'); ?>
                                </th>
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
                        <tbody>
                        <?php
                            $n = count($this->items);
                        foreach ($this->items as $i => $item) :
                            ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="text-center">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                </td>
                                <td scope="row" class="has-context">
                                <?php echo $item->manual; ?>
                                </td>
                                <td class="">
                            <?php echo $item->language; ?>
                                </td>
                                <td class="">
                            <?php echo $item->heading; ?>
                                </td>
                                <td class="">
                            <?php echo $item->filename; ?>
                                </td>
                                <td>
                            <?php
                            if ($item->likeitornot === 'like') {
                                echo Text::_('JYES');
                            } elseif ($item->likeitornot === 'dislike') {
                                echo Text::_('JNO');
                            }
                            ?>
                                </td>
                                <td class="">
                                <?php echo $item->comment; ?>
                                </td>
                                <td class="">
                                <?php echo $item->date_created; ?>
                                </td>
                                <td class="">
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

<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

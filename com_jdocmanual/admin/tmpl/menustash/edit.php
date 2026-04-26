<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_jdocmanual.jdocmanual')
    ->useScript('keepalive')
    ->useScript('com_jdocmanual.stash');

$wa->registerAndUseStyle('metismenujscss', 'media/com_jdocmanual/css/metismenujs.css', [], [], []);
$wa->registerAndUseStyle('mm-vertical', 'media/com_jdocmanual/css/mm-vertical.css', [], [], []);

$wa->useStyle('com_jdocmanual.diff-table');

$wa->registerAndUseStyle(
    'pp',
    'https://cdn.jsdelivr.net/npm/prismjs@1/themes/prism-okaidia.min.css',
    [],
    [],
    []
);
$wa->registerAndUseStyle(
    'pln',
    'https://cdn.jsdelivr.net/npm/prismjs@1/plugins/line-numbers/prism-line-numbers.min.css',
    [],
    [],
    []
);

$wa->registerAndUseScript('prism', 'https://cdn.jsdelivr.net/npm/prismjs@1/prism.min.js', [], ['defer' => true], []);
$wa->registerAndUseScript(
    'prism-diff',
    'https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-diff.min.js',
    [],
    ['defer' => true],
    []
);
$wa->registerAndUseScript(
    'prism-json',
    'https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-json.min.js',
    [],
    ['defer' => true],
    []
);
$wa->registerAndUseScript(
    'prism-ln',
    'https://cdn.jsdelivr.net/npm/prismjs@1/plugins/line-numbers/prism-line-numbers.min.js',
    [],
    ['defer' => true],
    []
);

$wa->useScript('metismenujs');

$wa->addInlineScript(
    'document.addEventListener("DOMContentLoaded", function(event) {
        new MetisMenu(\'#jdmmenu\', {
        toggle: false
      });
    });'
    );

?>

<form action="<?php echo Route::_('index.php?option=com_jdocmanual&view=menustash&layout=edit&id=' .
    $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="row">
        <div class="col">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details', 'recall' => true)); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_JDOCMANUAL_ARTICLE_TAB_DETAILS')); ?>
        <div class="row">
            <div class="col-md-9">
                <div class="row">
                    <div class="col-12 col-lg-9">
                        <?php echo $this->form->renderField('manual'); ?>
                        <?php echo $this->form->renderField('id'); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-light">
                    <div class="card-body">
                        <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'stash', Text::_('COM_JDOCMANUAL_ARTICLE_TAB_STASH')); ?>
            <div id="preview-area">
            <?php echo $this->form->getInput('menu_text'); ?>
            </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'source', Text::_('COM_JDOCMANUAL_ARTICLE_TAB_SOURCE')); ?>
        <div id="preview-area">
            <?php echo $this->form->getInput('source'); ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'diff', Text::_('COM_JDOCMANUAL_ARTICLE_TAB_DIFF')); ?>
            <?php if (!empty($this->diff)) : ?>
                <?php echo $this->diff; ?>
            <?php else : ?>
            <div class="alert alert-warning">
                <?php echo Text::_('COM_JDOCMANUAL_ARTICLE_TAB_DIFF_EMPTY'); ?>
            </div>
            <?php endif; ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_(
            'uitab.addTab',
            'myTab',
            'preview',
            Text::_('COM_JDOCMANUAL_ARTICLE_TAB_PREVIEW')
        ); ?>
            <div class="alert alert-info">
                The preview is for information only! It has no functionality.
                The item titles use placeholder text. The real titles are
                obtained from the individual articles. The Preview only works
                on saved content. <strong>Save you Stash to see the Preview!</strong>
            </div>
            <nav id="jdocmanual-wrapper" class="sidebar-nav p-0" style="max-width: 250px;">
                <?php echo $this->preview; ?>
            </nav>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_(
            'uitab.addTab',
            'myTab',
            'comments',
            Text::_('COM_JDOCMANUAL_ARTICLE_TAB_COMMENTS')
        ); ?>
            <?php echo $this->form->renderField('commit_message'); ?>
            <?php echo $this->form->renderField('comments'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        </div>
    </div>
    <input type="hidden" name="task" id="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php

/**
 * @package     Jdocmanual
 * @subpackage  Site
 *
 * @copyright   (C) 2023 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_jdocmanual.jdocmanual')
->useScript('com_jdocmanual.jdocmanual')
->useScript('metismenujs');

$wa->registerAndUseStyle('metismenujscss', 'media/com_jdocmanual/css/metismenujs.css', [], [], []);
$wa->registerAndUseStyle('mm-vertical', 'media/com_jdocmanual/css/mm-vertical.css', [], [], []);
$wa->registerAndUseScript('codehighlight', 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js', [], [], ['core']);
$wa->registerAndUseStyle('codehighlight-light', 'media/com_jdocmanual/css/a11y-light.min.css', [], [], []);

$wa->addInlineScript(
'document.addEventListener("DOMContentLoaded", function(event) {
    new MetisMenu(\'#jdmmenu\', {
    toggle: false
  });
});'
);

$proxy = false;

$app = Factory::getApplication();
$sitemenu = $app->getMenu();
$activeMenuitem = $sitemenu->getActive();

?>

<?php $this->addToolbar(); ?>

<?php if (empty($this->menu)) : ?>
    <p class="alert alert-warning">
        <?php echo Text::_('COM_JDOCMANUAL_MANUAL_MANUAL_SELECT_MISSING'); ?>
    </p>
<?php else : ?>
    <h1><?php echo $this->source->title; ?></h1>

    <?php include 'site-layout.php'; ?>
<?php endif; ?>

<?php include_once JPATH_SITE . '/components/com_jdocmanual/layouts/modalbox.php'; ?>

<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

use Joomla\CMS\Component\ComponentHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$gfmfiles_path = ComponentHelper::getComponent('com_jdocmanual')->getParams()->get('gfmfiles_path');
if (empty($gfmfiles_path)) {
    $data_path = '<span class="badge bg-warning">is empty. Select the Options button to set.</span>';
} else {
    // Does it end with /manuals/
    if (str_ends_with($gfmfiles_path, '/manuals/')) {
        $data_path = '<span class="badge bg-success">is set</span>';
    } else {
        $data_path = '<span class="badge bg-warning">is set but does not end with /manuals/</span>';
    }
}

$plugin_status = array(
    '<span class="badge bg-warning">is absent</span>',
    '<span class="badge bg-warning">is not enabled</span>',
    '<span class="badge bg-success">is present and enabled</span>'
);
?>

<h2>Configuration</h2>
<ul>
<li>Your path: <?php echo $data_path; ?>.</li>
<li>Your finder plugin: <?php echo $plugin_status[$this->plg_finder_status]; ?>.</li>
<li>Your cli plugin: <?php echo $plugin_status[$this->plg_system_status]; ?>.</li>
<?php if (!empty($gfmfiles_path) && !$this->dbIspopulated) : ?>
<li>Database has no content! Please publish and build content.</li>
<?php endif; ?>
</ul>

<h2>Installation</h2>
<ul>
    <li>Enable the Language Filter plugin, or the menus will not work.</li>
    <li>Create a Menu Item: Jdocmanual</li>
    <li>Go to the site Home page and use the newly created menu item.</li>
    <li>Continue with the <a href="https://jdocmanual/en/jdocmanual?article=jdm/installation/configure-languages">Installation Instructions</a> on the Jdocmanual demonstration site.</li>
</ul>

<p>Please visit the <a href="https://jdocmanual.org/jdocmanual?article=jdm/introduction">Jdocmanual</a> demonstration site for more instructions on data installation.</p>

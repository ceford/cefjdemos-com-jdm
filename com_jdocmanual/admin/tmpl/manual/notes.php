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
    '<span class="badge bg-warning">is is not enabled</span>',
    '<span class="badge bg-success">is present and enabled</span>'
);
// notes.html created with https://markdowntohtml.com/ or
// pandoc -f markdown -t html ~/git/cefjdemos-com-jdm/README.md > ~/git/cefjdemos-com-jdm/com_jdocmanual/admin/tmpl/manual/notes.html

?>

<h2>Configuration</h2>
<ul>
<li>Your path: <?php echo $data_path; ?>.</li>
<li>Your plugin: <?php echo $plugin_status[$this->plugin_status]; ?>.</li>
<?php if (!empty($gfmfiles_path) && !$this->dbisgood) : ?>
<li>Database has no content! Go to Sources to enable and build content (select the <strong>Sources</strong> tab).</li>
<?php endif; ?>

</ul>
<?php include 'notes.html' ?>

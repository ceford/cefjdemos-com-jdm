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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

// Including an action value in the form breaks either the site or admin views!

?>
<form action=""
    method="post" name="adminForm" id="adminForm">
    <input type="hidden" name="task" id="task" value="">
    <input type="hidden" name="jform[manual]" id="jform_manual" value="<?php echo $this->manual; ?>">
    <input type="hidden" name="jform[index_language_code]" id="jform_index_language_code"
        value="<?php echo $this->index_language_code; ?>">
    <input type="hidden" name="jform[page_language_code]" id="jform_page_language_code"
        value="<?php echo $this->page_language_code; ?>">
    <input type="hidden" name="jform[menu_page_id]" id="jform_menu_page_id"
        value="<?php echo $this->menu_page_id; ?>">
    <input type="hidden" name="jform[heading]" id="jform_heading"
        value="<?php echo $this->heading; ?>">
    <input type="hidden" name="jform[filename]" id="jform_filename"
        value="<?php echo $this->filename; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

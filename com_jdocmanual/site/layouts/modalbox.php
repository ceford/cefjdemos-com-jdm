<?php

/**
 * @package     Jdocmanual
 * @subpackage  Site
 *
 * @copyright   (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

HTMLHelper::_('bootstrap.modal', '#jdmFeedback', []);

?>
<!-- Modal -->
<div class="modal fade" id="jdmFeedback" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="jdmFeedbackLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title fs-5" id="jdmFeedbackLabel">Modal title</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><span id="comment_label"><?php echo Text::_('COM_JDOCMANUAL_FEEDBACK_INVITATION'); ?></span></p>
        <form id="feedback_form" action="#" method="post" name="feedback_form">
            <div class="input-group">
                <textarea id="comment" class="form-control" name="comment"
                rows="3" columns="60" minlength="10" maxlength="256" required
                placeholder="Text limited to 256 characters"></textarea>
                <label for="comment" class="visually-hidden">Comment</label>
            </div>
            <input type="hidden" name="manual" id="manual" value="" />
            <input type="hidden" name="language" id="language" value="" />
            <input type="hidden" name="heading" id="heading" value="" />
            <input type="hidden" name="filename" id="filename" value="" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo Text::_('JCLOSE'); ?></button>
        <button type="submit" id="modal-save" name="Submit" class="btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
      </div>
    </div>
  </div>
</div>

<!--
<div class="modal fade" id="jdmFeedback" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="jdmFeedbackLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title fs-5" id="jdmFeedbackLabel">Modal title</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><span id="comment_label"><?php echo Text::_('COM_JDOCMANUAL_FEEDBACK_INVITATION'); ?></span></p>
        <form id="feedback_form">
            <div class="input-group">
                <textarea id="comment" class="form-control" name="comment"
                rows="3" columns="60" minlength="10" maxlength="256" required
                placeholder="Text limited to 256 characters"></textarea>
                <label for="comment" class="visually-hidden">Comment</label>
            </div>
            <input type="hidden" name="manual" id="manual" value="" />
            <input type="hidden" name="language" id="language" value="" />
            <input type="hidden" name="heading" id="heading" value="" />
            <input type="hidden" name="filename" id="filename" value="" />

            <input type="hidden" name="id" id="like" value="" />
            <input type="hidden" name="task" id="task" value="content.feedback" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo Text::_('JCLOSE'); ?></button>
        <button type="submit" id="modal-save" name="Submit" class="btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
      </div>
    </div>
  </div>
</div>
-->

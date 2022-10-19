<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (count($ticketCategoryTemplates) > 0): ?>
<div class="row form-group">
    <div class="col-sm-6">
        <label class="control-label">Ticket Reason</label>
        <select name="ticket_category_template_id" id="ticket_category_template_id" class="form-control select2-input input-lg filter-set filter-value" style="width:100%;" required>
            <option value="">&nbsp;</option>
            <?php foreach ($ticketCategoryTemplates as $ticketCategoryTemplate): ?>
            <option value="<?php echo $ticketCategoryTemplate->id; ?>">
                <?php echo $ticketCategoryTemplate->subject; ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div><!-- column -->
    <div class="col-sm-6">
        <?php echo get_kb_field_explainer('ticket_category'); ?>
    </div><!-- column -->
</div>
<?php endif; ?>
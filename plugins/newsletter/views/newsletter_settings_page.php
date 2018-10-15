<div class="row">
    <div class="col-md-6">

        <div class="form-group">
            <label for="default_task_priority" class="control-label"><?php _nom('newsletter_email_queue');?></label>
            <select name="settings[newsletter_email_queue]" class="selectpicker" id="" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                <option value="0" <?php if(get_option('newsletter_email_queue') == 0){echo 'selected';} ?>><?php _nom('newsletter_no');?></option>
                <option value="1" <?php if(get_option('newsletter_email_queue') == 1){echo 'selected';} ?>><?php _nom('newsletter_yes');?></option>
            </select>
        </div>

        <hr />
        <div class="form-group">
            <label  class="control-label"><?php _nom('newsletter_default_sender_name')?></label>
            <input type="text"  class="form-control" name="settings[newsletter_sender_name]" value="<?php echo get_option('newsletter_sender_name')?>"/>
        </div>

        <hr />
        <div class="form-group">
            <label  class="control-label"><?php _nom('newsletter_default_sender_email')?></label>
            <input type="text"  class="form-control" name="settings[newsletter_sender_email]" value="<?php echo get_option('newsletter_sender_email')?>"/>
        </div>
    </div>
</div>

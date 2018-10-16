<?php if(isset($policy)){ ?>
<h4 class="customer-profile-group-heading"><?php echo _l('contracts_notes_tab'); ?></h4>
<div class="col-md-12">

 <a href="#" class="btn btn-success mtop15 mbot10" onclick="slideToggle('.usernote'); return false;"><?php echo _l('new_note'); ?></a>
    <a href="#" class="btn btn-success mtop15 mbot10" data-toggle="modal" data-target="#note_call_log"><?php echo _l('add_call_log'); ?></a>
 <div class="clearfix"></div>
<div class="row">
     <hr class="hr-panel-heading" />
</div>
 <div class="clearfix"></div>
 <div class="usernote hide">
    <?php echo form_open(admin_url( 'misc/add_note/'.$policy->id.'/policies')); ?>
    <?php echo render_textarea( 'description', 'note_description', '',array( 'rows'=>5)); ?>
    <button class="btn btn-info pull-right mbot15">
        <?php echo _l( 'submit'); ?>
    </button>
    <?php echo form_close(); ?>
</div>
<div id="note_call_log_data">
    <?php $this->load->view('admin/policies/modals/note_call_log');   ?>
</div>
<div class="clearfix"></div>
<div class="mtop15">

    <table class="table dt-table scroll-responsive" data-order-col="2" data-order-type="desc">
        <thead>
            <tr>
                <th width="50%">
                    <?php echo _l( 'clients_notes_table_description_heading'); ?>
                </th>
                <th>
                    <?php echo _l( 'clients_notes_table_addedfrom_heading'); ?>
                </th>
                <th>
                    <?php echo _l( 'clients_notes_table_dateadded_heading'); ?>
                </th>
                <th>
                    <?php echo _l( 'options'); ?>
                </th>

            </tr>
        </thead>
        <tbody>
            <?php foreach($user_notes as $note){ ?>
            <tr>
                <td width="50%">
                  <div data-note-description="<?php echo $note['id']; ?>">
                    <?php echo $note['description']; ?>
                </div>
                    <?php if($note['note_type'] == 'normal'){ ?>
                <div data-note-edit-textarea="<?php echo $note['id']; ?>" class="hide">
                    <textarea name="description" class="form-control" rows="4"><?php echo clear_textarea_breaks($note['description']); ?></textarea>
                    <div class="text-right mtop15">
                      <button type="button" class="btn btn-default" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><?php echo _l('cancel'); ?></button>
                      <button type="button" class="btn btn-info" onclick="edit_note(<?php echo $note['id']; ?>);"><?php echo _l('update_note'); ?></button>
                    </div>
                </div>
                    <?php } ?>
                </td>
            <td>
                <?php echo '<a href="'.admin_url( 'profile/'.$note[ 'addedfrom']). '">'.$note[ 'firstname'] . ' ' . $note[ 'lastname'] . '</a>' ?>
            </td>
        <td data-order="<?php echo $note['dateadded']; ?>">
         <?php if(!empty($note['date_contacted'])){ ?>
           <span data-toggle="tooltip" data-title="<?php echo _dt($note['date_contacted']); ?>">
              <i class="fa fa-phone-square text-success font-medium valign" aria-hidden="true"></i>
          </span>
          <?php } ?>
          <?php echo _dt($note[ 'dateadded']); ?>
        </td>
        <td>

            <?php if($note['addedfrom'] == get_staff_user_id() || is_admin()){ ?>
                <?php if($note['note_type'] == 'normal'){ ?>
                    <a href="#" class="btn btn-default btn-icon" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><i class="fa fa-pencil-square-o"></i></a>
                    <a href="<?php echo admin_url('misc/delete_note/'. $note['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                <?php } ?>
                <?php if($note['note_type'] == 'call_log'){ ?>
                    <a href="#" class="btn btn-default btn-icon" onclick="call_log(<?php echo $policy->id.','.$note['id']; ?>);return false;"><i class="fa fa-pencil-square-o"></i></a>
                    <a href="<?php echo admin_url('misc/delete_call_log/'. $note['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                <?php } ?>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
</tbody>
</table>
</div>
    <script>
        function call_log(policy_id,call_log_id) {

            if (typeof(call_log_id) == 'undefined') {
                call_log_id = '';
            }
            $.post(admin_url + 'policies/call_log/' + policy_id+'/'+call_log_id).done(function(response) {
                $('#note_call_log_data').html(response);
                $('#note_call_log').modal({
                    show: true,
                    backdrop: 'static'
                });
                $('body').off('shown.bs.modal','#note_call_log');
                $('body').on('shown.bs.modal', '#note_call_log', function() {
                    if (call_log_id == '') {
                        $('#note_call_log').find('input[name="subject"]').focus();
                    }
                });
                add_event_search_custom_field();
            }).fail(function(error) {
                var response = JSON.parse(error.responseText);
                alert_float('danger', response.message);
            });
        }

    </script>

<?php } ?>

<!-- Modal Contact -->
<div class="modal fade" id="note_call_log" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">

            <?php echo form_open('admin/client_families/call_log/'.$customer_id.'/'.$contactid.'/'.$call_log_id,array('id'=>'call-log-form','autocomplete'=>'off')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Call log<br /><small id=""></small></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo form_hidden('call_log_id',$call_log_id); ?>
                        <?php $value=( isset($call_log) ? $call_log->subject : ''); ?>
                        <?php echo render_input( 'subject', 'subject',$value); ?>

                        <?php $value=( isset($call_log) ? $call_log->phonenumber : ''); ?>
                        <?php echo render_input( 'phonenumber', 'phonenumber',$value); ?>

                        <?php $value=( isset($call_log) ? $call_log->call_type : 'inbound'); ?>
                        <?php echo render_option($value, 'Call type','call type','inbound','outbound','inbound','outbound'); ?>

                        <?php $value=( isset($call_log) ? $call_log->status : ''); ?>
                        <div class="form-group">
                            <label for="default_language" class="control-label"><?php echo _l('status'); ?>
                            </label>
                            <select name="status" id="status" class="form-control selectpicker " data-none-selected-text="" >
                                <option value=""></option>
                                <option value="answered" <?php if($value=="answered") echo "checked" ?> >Answered</option>
                                <option value="busy" <?php if($value=="busy") echo "checked" ?>>Busy</option>
                                <option value="failed" <?php if($value=="failed") echo "checked" ?>>Failed</option>
                                <option value="missed" <?php if($value=="missed") echo "checked" ?>>Missed</option>
                                <option value="voicemail" <?php if($value=="voicemail") echo "checked" ?>>Voicemail</option>
                                <option value="inquiry" <?php if($value=="inquiry") echo "checked" ?>>Inquiry</option>
                                <option value="interested" <?php if($value=="interested") echo "checked" ?>>Interested</option>
                                <option value="no_interested" <?php if($value=="no_interested") echo "checked" ?>>No Interested</option>
                                <option value="incorrect_referral" <?php if($value=="incorrect_referral") echo "checked" ?>>Incorrect referral</option>
                                <option value="meeting_scheduled" <?php if($value=="meeting_scheduled") echo "checked" ?>>Meeting scheduled</option>
                                <option value="new_opportunity" <?php if($value=="new_opportunity") echo "checked" ?>>New Opportunity</option>
                            </select>
                        </div>

                        <?php $value=( isset($call_log) ? $call_log->duration : '0');
                            $hour = round( ($value)/intval(60*60), 0, PHP_ROUND_HALF_DOWN);
                            $minute = round(($value - $hour*60*60)/intval(60), 0, PHP_ROUND_HALF_DOWN);
                            $second =  $value - $hour*60*60 - $minute*60;
                        ?>

                        <div class="row">
                            <div class="col-md-4">
                                <?php echo render_input( 'duration[hour]', 'hour',$hour,'number',array('autocomplete'=>'off')); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input( 'duration[minute]', 'minute',$minute,'number',array('autocomplete'=>'off')); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input( 'duration[second]', 'second',$second,'number',array('autocomplete'=>'off')); ?>
                            </div>
                        </div>

                        <?php $value=( isset($call_log) ? $call_log->description : ''); ?>
                        <?php echo render_textarea( 'description', 'description',$value); ?>

                    <?php $rel_id=( isset($call_log) ? $call_log->id : false); ?>
                    <?php echo render_custom_fields( 'call_logs',$rel_id); ?>

                </div>
                <hr />



            </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" data-form="#call-log-form"><?php echo _l('submit'); ?></button>
            </div>

        </div>
    <?php echo form_close(); ?>
</div>
</div>


<?php  if(!isset($contact)){ ?>
    <script>
        $(function(){
            // Guess auto email notifications based on the default contact permissios
            var permInputs = $('input[name="permissions[]"]');
            $.each(permInputs,function(i,input){
                input = $(input);
                if(input.prop('checked') === true){
                    $('#contact_email_notifications [data-perm-id="'+input.val()+'"]').prop('checked',true);
                }
            });
        });
    </script>
<?php }  ?>
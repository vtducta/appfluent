
<div class="modal fade _email" id="newEmailModal">
  <div class="modal-dialog" style="width: 1000px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo _l('new_email'); ?></h4>
      </div>
      <?php echo form_open('admin/client_families/send_email/'. $client->userid.'/'.$contact->id,array('id'=>'email-form')); ?>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="row">

                    <div class="col-md-12"><div class="form-group">
                            <label><?php echo _l('email_to'); ?></label>
                            <input required type="text" value="" name="to" class="form-control"/>
                        </div></div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="checkbox checkbox-inline mbot25">
                            <input type="checkbox" value="schedule" id="chkShowSchedule" name="schedule" onchange="checkSchedule();">
                            <label for="chkShowSchedule">Schedule</label>
                        </div>
                    </div>
                </div>
                <div class="row hide" id="div_schedule">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><?php echo _l('send_date'); ?></label>
                            <div class="input-group date"><input type="text" id="send_date" name="send_date" class="form-control datepicker"  aria-invalid="false"><div class="input-group-addon">
                                    <i class="fa fa-calendar calendar-icon"></i>
                                </div></div>
                        </div>
                    </div>
                    <div class="col-md-4"><div class="form-group">
                            <label>At</label>
                            <select class="form-control schedule_at" name="send_at" >
                                <option value="any_time">Any Time</option>
                                <option value="09:00">9:00 AM</option>
                                <option value="09:30">9:30 AM</option><option value="10:00">10:00 AM</option><option value="10:30">10:30 AM</option><option value="11:00">11:00 AM</option><option value="11:30">11:30 AM</option><option value="12:00">12:00 PM</option><option value="12:30">12:30 PM</option><option value="13:00">1:00 PM</option><option value="13:30">1:30 PM</option><option value="14:00">2:00 PM</option><option value="14:30">2:30 PM</option><option value="15:00">3:00 PM</option><option value="15:30">3:30 PM</option><option value="16:00">4:00 PM</option><option value="16:30">4:30 PM</option><option value="17:00">5:00 PM</option><option value="17:30">5:30 PM</option><option value="18:00">6:00 PM</option><option value="18:30">6:30 PM</option><option value="19:00">7:00 PM</option><option value="19:30">7:30 PM</option><option value="20:00">8:00 PM</option><option value="20:30">8:30 PM</option><option value="21:00">9:00 PM</option><option value="21:30">9:30 PM</option><option value="22:00">10:00 PM</option><option value="22:30">10:30 PM</option><option value="23:00">11:00 PM</option><option value="23:30">11:30 PM</option><option value="00:01">12:00 AM</option><option value="00:30">12:30 AM</option><option value="01:00">1:00 AM</option><option value="01:30">1:30 AM</option><option value="02:00">2:00 AM</option><option value="02:30">2:30 AM</option><option value="03:00">3:00 AM</option><option value="03:30">3:30 AM</option><option value="04:00">4:00 AM</option><option value="04:30">4:30 AM</option><option value="05:00">5:00 AM</option><option value="05:30">5:30 AM</option><option value="06:00">6:00 AM</option><option value="06:30">6:30 AM</option><option value="07:00">7:00 AM</option><option value="07:30">7:30 AM</option><option value="08:00">8:00 AM</option><option value="08:30">8:30 AM</option></select>
                        </div></div>
                </div>



            <div class="form-group" style="position: relative">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php echo _l('email_select_template');?> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu   ">
                    <?php foreach ($template_list as $template): ?>
                        <li>
                            <a style="display: block" href="" onclick="return newsletter_insert_template('<?php echo $template['id'] ?>')">

                                <img src="<?php echo base_url(($template['preview']) ? $template['preview'] : 'plugins/newsletter/icon.png') ?>" style="width: 50px;height: 50px"/> <?php echo $template['title'] ?>                  </a>
                        </li>
                    <?php endforeach ?>


                </ul>
            </div>
                <div class="form-group">
                    <label><?php echo _l('subject'); ?></label>
                    <input required type="text" value="" name="subject" class="form-control"/>
                </div>
            <div class="form-group">
                <label><?php echo _l('newsletter_campaign_content'); ?></label>
                <textarea id="campaign-text-content" class="form-control tinymce" rows="25" name="content"></textarea>
            </div>
            </div>
        </div>
    </div>

  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
    <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
  </div>
    </div>
  <?php echo form_close(); ?>
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
    function checkSchedule(){
       if($("#chkShowSchedule").is(":checked")){
           $("#div_schedule").removeClass("hide");
       }else{
           $("#div_schedule").addClass("hide");
       }
    };

</script>
<?php $CI = get_instance() ?>
<?php
$listAction = [
    '' => 'Event',
    'clicks' => 'Clicked',
    'opens' => 'Opened',
    'noopen' => 'No Open',
    'unsubscribed' => 'Unsubscribed'
];
$listWaiting = [
    'days' => 'Days',
    'hours' => 'Hours',
    'minutes' => 'Minutes'
];

$mail_list = json_decode($campaign['email_list']);
?>
<style>
    .next-campaigs .col-md-2, .next-campaigs .col-md-3, .next-campaigs .col-md-4{
        padding-left: 5px!important;
        padding-right: 5px!important;
    }
</style>
<form id="newsletter-campaign-form" action="" method="post" enctype="multipart/form-data">
    <div class="row">

        <div class="col-md-8">
            <div class="panel_s" style="min-height: 700px !important;">
                <div class="panel-body">

                    <div class="form-group">
                        <label><?php _nom('newsletter_subject') ?></label>
                        <input required type="text" value="<?php echo ($campaign) ? $campaign['subject'] : $CI->input->post('subject') ?>" name="subject" class="form-control"/>
                    </div>

                    <div class="form-group">
                        <label><?php _nom('newsletter_sender_name') ?></label>
                        <input required type="text" value="<?php echo ($campaign) ? $campaign['sender_name'] : get_option('newsletter_sender_name') ?>" name="sender_name" class="form-control"/>
                    </div>

                    <div class="form-group">
                        <label><?php _nom('newsletter_sender_email') ?></label>
                        <input required type="text" value="<?php echo ($campaign) ? $campaign['sender_email'] : get_option('newsletter_sender_email') ?>" name="sender_email" class="form-control"/>
                    </div>

                    <?php if (get_option('newsletter_email_queue')): ?>
                        <div class="form-group">
                            <label><?php _nom('newsletter_send_date') ?></label>
                            <div class="input-group date"><input type="text" id="start_date" name="send_date" class="form-control datepicker" value="<?php echo ($campaign) ? $campaign['send_date'] : $CI->input->post('send_date') ?>" aria-required="true" aria-invalid="false"><div class="input-group-addon">
                                    <i class="fa fa-calendar calendar-icon"></i>
                                </div></div>
                        </div>
                    <?php else: ?>

                    <?php endif ?>
                    <div class="form-group">
                        <label>On</label>
                        <select class="form-control schedule_on" name="send_on" required="required"> <option value="any_day">Any Day</option><option value="Mon-Fri">Mon-Fri</option><option value="Mon-Sat">Mon-Sat</option><option value="Sat-Sun">Sat-Sun</option><option value="Mon">Mon</option><option value="Tue">Tue</option><option value="Wed">Wed</option><option value="Thu">Thu</option><option value="Fri">Fri</option><option value="Sat">Sat</option><option value="Sun">Sun</option></select>
                    </div>
                    <div class="form-group">
                        <label>At</label>
                        <select class="form-control schedule_at" name="send_at" required="required"> <option value="any_time">Any Time</option><option value="09:00">9:00 AM</option><option value="09:30">9:30 AM</option><option value="10:00">10:00 AM</option><option value="10:30">10:30 AM</option><option value="11:00">11:00 AM</option><option value="11:30">11:30 AM</option><option value="12:00">12:00 PM</option><option value="12:30">12:30 PM</option><option value="13:00">1:00 PM</option><option value="13:30">1:30 PM</option><option value="14:00">2:00 PM</option><option value="14:30">2:30 PM</option><option value="15:00">3:00 PM</option><option value="15:30">3:30 PM</option><option value="16:00">4:00 PM</option><option value="16:30">4:30 PM</option><option value="17:00">5:00 PM</option><option value="17:30">5:30 PM</option><option value="18:00">6:00 PM</option><option value="18:30">6:30 PM</option><option value="19:00">7:00 PM</option><option value="19:30">7:30 PM</option><option value="20:00">8:00 PM</option><option value="20:30">8:30 PM</option><option value="21:00">9:00 PM</option><option value="21:30">9:30 PM</option><option value="22:00">10:00 PM</option><option value="22:30">10:30 PM</option><option value="23:00">11:00 PM</option><option value="23:30">11:30 PM</option><option value="00:01">12:00 AM</option><option value="00:30">12:30 AM</option><option value="01:00">1:00 AM</option><option value="01:30">1:30 AM</option><option value="02:00">2:00 AM</option><option value="02:30">2:30 AM</option><option value="03:00">3:00 AM</option><option value="03:30">3:30 AM</option><option value="04:00">4:00 AM</option><option value="04:30">4:30 AM</option><option value="05:00">5:00 AM</option><option value="05:30">5:30 AM</option><option value="06:00">6:00 AM</option><option value="06:30">6:30 AM</option><option value="07:00">7:00 AM</option><option value="07:30">7:30 AM</option><option value="08:00">8:00 AM</option><option value="08:30">8:30 AM</option></select>
                    </div>
                    <div class="form-group" style="position: relative">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php _nom('newsletter_select_template') ?> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu   ">
                            <?php foreach ($CI->Newsletter_model->getTemplateLists() as $template): ?>
                                <li>
                                    <a style="display: block" href="" onclick="return newsletter_insert_template('<?php echo $template['id'] ?>')">

                                        <img src="<?php echo base_url(($template['preview']) ? $template['preview'] : 'plugins/newsletter/icon.png') ?>" style="width: 50px;height: 50px"/> <?php echo $template['title'] ?>                  </a>
                                </li>
                            <?php endforeach ?>


                        </ul>


                    </div>

                    <div class="form-group">
                        <label><?php _nom('newsletter_campaign_content') ?></label>
                        <textarea id="campaign-text-content" class="form-control tinymce" rows="25" name="content"><?php echo ($campaign) ? $campaign['content'] : $CI->input->post('content') ?></textarea>
                    </div>

                    <?php if ($campaign): ?>
                        <button class="btn btn-info"><?php _nom('newsletter_save') ?></button>
                    <?php else: ?>
                        <input id="newsletter-status" type="hidden" name="status" value="1"/>
                        <button class="btn btn-info"><?php _nom('newsletter_send') ?></button>
                        <button type="button" onclick="return newsletter_save_campaign()" class="btn btn-default"><?php _nom('newsletter_save_as_draft') ?></button>
                    <?php endif ?>

                </div>
            </div>
        </div>
        <div class="col-md-4">
            <?php //if (!$campaign): ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <h5><?php _nom('newsletter_destination') ?></h5>


                        <label><?php _nom('newsletter_mail_list') ?></label>
                        <select   class="form-control selectpicker" multiple name="lists[]">
                            <?php foreach ($CI->Newsletter_model->getMailLists() as $value => $name): ?>
                            <?php
                                $selectedList = '';
                                if($mail_list->lists && in_array($value, $mail_list->lists)) {
                                    $selectedList = 'selected';
                                }
                            ?>
                            <option value="<?php echo $value ?>" <?php echo $selectedList?> ><?php echo $name ?></option>
                            <?php endforeach ?>
                        </select>

                        <hr/>
                        <label><?php _nom('newsletter_to_customer') ?></label>
                        <div class="alert alert-danger"><?php _nom('newsletter_to_customer_note') ?></div>
                        <select id="clientid" multiple name="customers[]" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">

                        </select>


                        <hr/>
                        <label><?php _nom('newsletter_to_staff') ?></label>
                        <select  class="form-control selectpicker" multiple name="staffs[]">
                            <?php foreach ($CI->Newsletter_model->getStaffs() as $staff): ?>
                            <?php
                                $selectedList = '';
                                if($mail_list->staffs && in_array($staff['staffid'], $mail_list->staffs)) {
                                    $selectedStaff = 'selected';
                                }
                            ?>
                                <option value="<?php echo $staff['staffid'] ?>" <?php echo $selectedStaff; ?>><?php echo $staff['firstname'] . ' ' . $staff['lastname'] ?></option>
                            <?php endforeach ?>
                        </select>
                        <!--Define Email send-->
                        <hr/>
                        <label><?php _nom('newsletter_email_to') ?></label>
                        <?php 
                            $email_to = $mail_list->email_to ? $mail_list->email_to : '{{email}}';
                        
                        ?>
                        <input class="form-control" name="email_to" value="<?php echo $email_to;?>">

                    </div>
                </div>
                <!--       Next Campaign-->
                <div class="panel_s next-campaigs" >
                    <div class="panel-body">
                        <h5><?php _nom('newsletter_next_action') ?></h5>
                        <!--                   <label>--><?php //_nom('newsletter_when_has_action')  ?><!--</label>-->
                        <div class="row">
                            <div class="col-md-3">
                                <label>Condition</label>
                            </div>
                            <div class="col-md-4">
                                <label>Campaign</label>
                            </div>
                            <div class="col-md-2">
                                <label>waiting</label>
                            </div>
                            <div class="col-md-3">
                                <label>type</label>
                            </div>
                        </div>
                        <?php for ($i = 1; $i < count($listAction); $i++): ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <select   class="form-control selectpicker" name="conditionals[]">
                                        <?php foreach ($listAction as $value => $text): ?>
                                            <option value="<?php echo $value ?>"><?php echo $text ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select  class="form-control selectpicker" name="next_campaigns[]">
                                        <option value="">Campaigns</option>
                                        <?php foreach ($CI->Newsletter_model->getCampaigns() as $camp): ?>
                                            <option value="<?php echo $camp['id'] ?>"><?php echo $camp['subject'] ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input class="form-control" type="number" min="1" name="waitings[]" value="1">
                                </div>
                                <div class="col-md-3">
                                    <select  class="form-control selectpicker" name="types[]">
                                        <?php foreach ($listWaiting as $value => $text): ?>
                                            <option value="<?php echo $value ?>"><?php echo $text ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                            <br>
                        <?php endfor ?>


                        <!--                   <label>--><?php //_nom('newsletter_select_campaign')  ?><!--</label>-->
                        <!--                   <select  class="form-control selectpicker" name="next_campaigns[]">-->
                        <!--                       --><?php //foreach($CI->Newsletter_model->getCampaigns() as $camp):  ?>
                        <!--                           <option value="--><?php //echo $camp['id']  ?><!--">--><?php //echo $camp['subject']  ?><!--</option>-->
                        <!--                       --><?php //endforeach  ?>
                        <!--                   </select>-->
                    </div>
                </div>
            <?php //else: ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <h5><?php _nom('newsletter_statistics') ?></h5>
                        <div class="row">
                            <div class="col-md-6"><?php _nom('newsletter_opens') ?></div>
                            <div class="col-md-6"><span class="pull-right label" style='color:#03a9f4;border:1px solid #03a9f4'><?php echo $campaign['opens'] ?></span></div>
                        </div>
                        <div class="row" style="padding-top: 4px !important;">
                            <div class="col-md-6"><?php _nom('newsletter_clicks') ?></div>
                            <div class="col-md-6"><span class="pull-right label" style='color:#03a9f4;border:1px solid #03a9f4'><?php echo $campaign['clicks'] ?></span></div>
                        </div>

                        <div style="margin-top: 20px"><?php echo $CI->Newsletter_model->getStatistics($campaign['id']) ?></div>
                    </div>
                </div>
            <?php //endif ?>

            <div class="panel_s">
                <div class="panel-body">
                    <h4 class="no-margin"><?php _nom('newsletter_available_merge') ?></h4>
                    <hr/>
                    <div class="alert alert-danger">
                        <?php _nom('newsletter_available_merge_note') ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Customer & Contact</h5>
                            <p><span class="pill">{contact_firstname}</span></p>
                            <p>{contact_lastname}</p>
                            <p>{contact_email}</p>
                            <p>{client_company}</p>
                            <p>{client_phonenumber}</p>
                            <p>{client_country}</p>
                            <p>{client_city}</p>
                            <p>{client_zip}</p>
                            <p>{client_address}</p>
                            <p>{client_state}</p>
                            <p>{client_vat_number}</p>
                            <p>{client_id}</p>

                            <h5>Staff</h5>
                            <p>{staff_firstname}</p>
                            <p>{staff_lastname}</p>
                            <p>{staff_email}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Others</h5>
                            <p{logo_url}</p>
                        <p>{logo_image_with_url}</p>
                        <p>{crm_url}</p>
                        <p>{admin_url}</p>
                        <p>{main_domain}</p>
                        <p>{companyname}</p>

                        <h5>Leads</h5>
                        <p>{lead_name}</p>
                        <p>{lead_email}</p>
                        <p>{lead_position}</p>
                        <p>{lead_website}</p>
                        <p>{lead_description}</p>
                        <p>{lead_phonenumber}</p>
                        <p>{lead_company}</p>
                        <p>{lead_country}</p>
                        <p>{lead_zip}</p>
                        <p>{lead_state}</p>
                        <p>{lead_city}</p>
                        <p>{lead_address}</p>
                        <p>{lead_assigned}</p>
                        <p>{lead_status}</p>
                        <p>{lead_source}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</form>
<script>
    var schedule_on = '<?php echo ($campaign) ? $campaign['send_on'] : $CI->input->post('schedule_on') ?>';
    var schedule_at = '<?php echo ($campaign) ? $campaign['send_at'] : $CI->input->post('schedule_at') ?>';

</script>
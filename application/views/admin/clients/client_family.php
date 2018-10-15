<?php init_head(); ?>
<div id="wrapper" class="customer_profile">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <?php if(isset($client) && $client->active == 0){ ?>
            <div class="alert alert-warning">
               <?php echo _l('customer_inactive_message'); ?>
               <br />
               <a href="<?php echo admin_url('clients/mark_as_active/'.$client->userid); ?>"><?php echo _l('mark_as_active'); ?></a>
            </div>
            <?php } ?>
            <?php if(isset($client) && $client->leadid != NULL){ ?>
            <div class="alert alert-info">
               <a href="#" onclick="init_lead(<?php echo $client->leadid; ?>); return false;"><?php echo _l('customer_from_lead',_l('lead')); ?></a>
            </div>
            <?php } ?>
            <?php if(isset($client) && (!has_permission('customers','','view') && is_customer_admin($client->userid))){?>
            <div class="alert alert-info">
               <?php echo _l('customer_admin_login_as_client_message',get_staff_full_name(get_staff_user_id())); ?>
            </div>
            <?php } ?>
         </div>
         <?php if($group == 'profile'|| $group =='profile_families'){ ?>
         <div class="btn-bottom-toolbar btn-toolbar-container-out text-right">
            <button class="btn btn-info only-save customer-form-submiter">
               <?php echo _l( 'submit'); ?>
            </button>
            <?php if(!isset($client)){ ?>
<!--            <button class="btn btn-info save-and-add-contact customer-form-submiter">-->
               <?php //echo _l( 'save_customer_and_add_contact'); ?>
<!--            </button>-->
            <?php } ?>
         </div>
         <?php } ?>
         <?php if(isset($client)){ ?>
             <div class="col-md-3">
                 <div class="panel_s">
                     <div class="panel-body customer-profile-tabs">
                         <h4 class="customer-heading-profile bold">

                             CONTACT #<?php echo $contact->id. ' ' . $contact->firstname .' '. $contact->lastname; ?></h4>
                         <?php $this->load->view('admin/clients/tabs_family'); ?>

                     </div>
                 </div>
             </div>
         <?php } ?>
         <div class="col-md-<?php if(isset($client)){echo 9;} else {echo 12;} ?>">
            <div class="panel_s">
               <div class="panel-body">
                   <?php if(isset($contact)){ ?>
                       <?php echo form_hidden( 'contact_id',$contact->id); ?>
                   <?php } ?>
                  <?php if(isset($client)){ ?>
                  <?php echo form_hidden( 'isedit'); ?>
                  <?php echo form_hidden( 'userid',$client->userid); ?>
                  <div class="clearfix"></div>
                  <?php } ?>
                  <div>
                     <div class="tab-content">
                        <?php
                          if(strpos($group,'section') === 0 ){
                              $this->load->view('admin/clients/groups/custom_section');
                          }else{
                              $this->load->view('admin/clients/groups/'.$group);
                          }
                        ?>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php if($group == 'profile'){ ?>
      <div class="btn-bottom-pusher"></div>
      <?php } ?>
   </div>
</div>
<?php init_tail(); ?>
<?php if(isset($contact)){ ?>
<script>
   init_rel_tasks_table(<?php echo $contact->id; ?>,'contacts');
</script>
<?php } ?>
<?php if(!empty($google_api_key) && !empty($client->latitude) && !empty($client->longitude)){ ?>
<script>
   var latitude = '<?php echo $client->latitude; ?>';
   var longitude = '<?php echo $client->longitude; ?>';
   var mapMarkerTitle = '<?php echo $client->company; ?>';
</script>
<?php echo app_script('assets/js','map.js'); ?>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_api_key; ?>&callback=initMap"></script>
<?php } ?>
<?php $this->load->view('admin/clients/clientfamily_js'); ?>


</body>
</html>

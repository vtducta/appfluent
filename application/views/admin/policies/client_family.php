<?php init_head(); ?>
<div id="wrapper" class="customer_profile">
   <div class="content">
      <div class="row">

         <?php if($group == 'profile'|| $group =='profile_families'){ ?>
         <div class="btn-bottom-toolbar btn-toolbar-container-out text-right">
            <button class="btn btn-info only-save customer-form-submiter">
               <?php echo _l( 'submit'); ?>
            </button>

         </div>
         <?php } ?>
         <?php if(isset($policy)){ ?>
             <div class="col-md-3">
                 <div class="panel_s">
                     <div class="panel-body customer-profile-tabs">
                         <h4 class="customer-heading-profile bold">

                             POLICY #<?php echo $policy->id. ' ' . $policy->firstname .' '. $policy->lastname; ?></h4>
                         <?php $this->load->view('admin/policies/tabs_family'); ?>

                     </div>
                 </div>
             </div>
         <?php } ?>
         <div class="col-md-<?php if(isset($policy)){echo 9;} else {echo 12;} ?>">
            <div class="panel_s">
               <div class="panel-body">
                   <?php if(isset($policy)){ ?>
                       <?php echo form_hidden( 'contact_id',$policy->id); ?>
                   <?php } ?>
                  <?php if(isset($policy)){ ?>
                  <?php echo form_hidden( 'isedit'); ?>
                  <?php echo form_hidden( 'userid',$policy->userid); ?>
                  <div class="clearfix"></div>
                  <?php } ?>
                  <div>
                     <div class="tab-content">
                        <?php
                          if(strpos($group,'section') === 0 ){
                              $this->load->view('admin/policies/groups/custom_section');
                          }else{
                              $this->load->view('admin/policies/groups/'.$group);
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
<?php if(isset($policy)){ ?>
<script>
   init_rel_tasks_table(<?php echo $policy->id; ?>,'policies');
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
<?php $this->load->view('admin/policies/clientfamily_js'); ?>


</body>
</html>

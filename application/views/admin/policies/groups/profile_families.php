<h4 class="customer-profile-group-heading"><?php echo _l('client_add_edit_profile'); ?></h4>
<div class="row">
   <?php echo form_open_multipart($this->uri->uri_string(),array('class'=>'client-form','autocomplete'=>'off')); ?>
   <div class="additional"></div>
   <div class="col-md-12">
      <ul class="nav nav-tabs profile-tabs row customer-profile-tabs" role="tablist">
         <li role="presentation" class="<?php if(!$this->input->get('tab')){echo 'active';}; ?>">
            <a href="#policy_info" aria-controls="policy_info" role="tab" data-toggle="tab">
               <?php echo _l( 'policy_profile_details'); ?>
            </a>
         </li>

          <?php do_action('after_custom_field_tab',isset($policy) ? $policy : false); ?>


          <?php  foreach ($list_custom_tab as $customtab) { ?>
              <li role="presentation" class="">
                  <a href="#<?=$customtab['slug']?>" aria-controls="<?=$customtab['slug']?>" role="tab" data-toggle="tab">
                      <?php echo $customtab['name']; ?>
                  </a>
              </li>
              <?php do_action('after_'.$customtab['slug'].'_tab',isset($policy) ? $policy : false); ?>
              <?php  }?>
      </ul>
      <div class="tab-content">
         <?php do_action('after_custom_profile_tab_content',isset($policy) ? $policy : false); ?>
         <div role="tabpanel" class="tab-pane<?php if(!$this->input->get('tab')){echo ' active';}; ?>" id="policy_info">
            <div class="row">
                <div class="col-md-12">
                    <?php if(isset($policy)){ ?>
                        <img src="<?php echo policy_profile_image_url($policy->id,'thumb'); ?>" id="policy-img" class="client-profile-image-small">
                        <?php if(!empty($policy->profile_image)){ ?>
                            <a href="#" onclick="delete_policy_profile_image(<?php echo $policy->id; ?>); return false;" class="text-danger pull-right" id="policy-remove-img"><i class="fa fa-remove"></i></a>
                        <?php } ?>
                        <hr />
                    <?php } ?>
                    <div id="policy-profile-image" class="form-group<?php if(isset($policy) && !empty($policy->profile_image)){echo ' hide';} ?>">
                        <label for="policy[profile_image]" class="profile-image"><?php echo _l('client_profile_image'); ?></label>
                        <input type="file" name="policy[profile_image]" class="form-control" id="policy[profile_image]">
                    </div>

                    <!-- // For email exist check -->
                    <?php echo form_hidden('policy[policyid]',$policyid); ?>
                    <?php $value=( isset($policy) ? $policy->firstname : ''); ?>
                    <?php $attrs = (isset($policy) ? array() : array('autofocus'=>true)); ?>
                    <?php echo render_input( 'policy[firstname]', 'client_firstname',$value,'text',$attrs); ?>
                    <?php $value=( isset($policy) ? $policy->lastname : ''); ?>
                    <?php echo render_input( 'policy[lastname]', 'client_lastname',$value); ?>
                    <?php $value=( isset($policy) ? $policy->title : ''); ?>
                    <?php echo render_input( 'policy[title]', 'policy_title',$value); ?>


                    <?php $value=( isset($policy) ? get_contact_full_name( $policy->contact_id) : '');
                    ?>
                    <?php echo form_hidden('policy[contact_id]',$policy->contact_id); ?>
                    <div class="form-group dropdown" app-field-wrapper="policy[contact]">
                        <label for="policy[contact]" class="control-label"><?php echo _l('policy_contact')?></label>
                        <input placeholder="Type to seach contact" data-toggle="dropdown" type="text" id="policy[contact]" name="policy[contact]" class="form-control " value="<?php echo $value ?>" aria-invalid="false">
                        <div id="contact_search_results" class="dropdown-menu"  style="position: relative">
                        </div>
                    </div>

                    <?php $value=( isset($policy) ? get_company_name( $policy->userid) : '');
                    ?>
                    <div class="form-group dropdown" app-field-wrapper="policy[company]">
                        <label for="policy[company]" class="control-label"><?php echo _l('client_company')?></label>
                        <input placeholder="Type to seach company" data-toggle="dropdown" type="text" id="policy[company]" name="policy[company]" class="form-control " value="<?php echo $value ?>" aria-invalid="false">
                        <div id="company_search_results" class="dropdown-menu"  style="position: relative">
                        </div>
                    </div>


                    <?php $value=( isset($policy) ? $policy->email : ''); ?>
                    <?php echo render_input( 'policy[email]', 'client_email',$value, 'email'); ?>
                    <?php $value=( isset($policy) ? $policy->phonenumber : ''); ?>
                    <?php echo render_input( 'policy[phonenumber]', 'client_phonenumber',$value,'text',array('autocomplete'=>'off')); ?>

                    <div class="form-group">

                        <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                        <?php $value=( isset($policy_tags) ? $policy_tags : ''); ?>
                        <input type="text" class="tagsinput" id="policy[tags]" name="policy[tags]" data-role="tagsinput" value="<?php echo $value; ?>">
                    </div>


                </div>




                <div class="col-md-12">
                    <hr/>
                </div>


         </div>
      </div>


          <?php
          foreach ($list_custom_tab as $customtab) { ?>
              <div role="tabpanel" class="tab-pane" id="<?=$customtab['slug']?>">
                  <div class="row">
                      <div class="col-md-12">
                          <?php $rel_id=( isset($policy) ? $policy->id : false); ?>
                          <?php echo render_custom_fields( 'policies',$rel_id,array('custom_tab_id'=>$customtab['id'])); ?>
                      </div>
                  </div>
              </div>
              <?php  }?>
      </div>
   </div>
   <?php echo form_close(); ?>
</div>
<div id="policy_data"></div>




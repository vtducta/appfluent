<h4 class="customer-profile-group-heading"><?php echo _l('client_add_edit_profile'); ?></h4>
<div class="row">
   <?php echo form_open($this->uri->uri_string(),array('class'=>'client-form','autocomplete'=>'off')); ?>
   <div class="additional"></div>
   <div class="col-md-12">
      <div class="horizontal-scrollable-tabs">
<div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
<div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
<div class="horizontal-tabs">
      <ul class="nav nav-tabs profile-tabs row customer-profile-tabs nav-tabs-horizontal" role="tablist">
         <li role="presentation" class="<?php if(!$this->input->get('tab')){echo 'active';}; ?>">
            <a href="#contact_info" aria-controls="contact_info" role="tab" data-toggle="tab">
            <?php echo _l( 'customer_profile_details'); ?>
            </a>
         </li>

         <li role="presentation">
            <a href="#billing_and_shipping" aria-controls="billing_and_shipping" role="tab" data-toggle="tab">
            <?php echo _l( 'billing_shipping'); ?>
            </a>
         </li>
         <?php do_action('after_customer_billing_and_shipping_tab',isset($client) ? $client : false); ?>
         <?php if(isset($client)){ ?>
             <li role="presentation">
                 <a href="#customer_admins" aria-controls="customer_admins" role="tab" data-toggle="tab">
                     <?php echo _l('customer_admins'); ?>
                 </a>
             </li>
             <?php do_action('after_customer_admins_tab', $client); ?>

             <li role="presentation">
                 <a href="#contact_tags" aria-controls="contact_tags" role="tab" data-toggle="tab">
                     <?php echo _l('contact_tags'); ?>
                 </a>
             </li>
             <?php do_action('after_contact_tags_tab', $client); ?>
         <?php } ?>

          <?php  foreach ($list_custom_tab as $customtab) { ?>
              <li role="presentation" class="">
                  <a href="#<?=$customtab['slug']?>" aria-controls="<?=$customtab['slug']?>" role="tab" data-toggle="tab">
                      <?php echo $customtab['name']; ?>
                  </a>
              </li>
              <?php do_action('after_'.$customtab['slug'].'_tab',isset($contact) ? $contact : false); ?>
          <?php  }?>
      </ul>
   </div>
</div>
      <div class="tab-content">
         <?php do_action('after_custom_profile_tab_content',isset($client) ? $client : false); ?>
         <div role="tabpanel" class="tab-pane<?php if(!$this->input->get('tab')){echo ' active';}; ?>" id="contact_info">
            <div class="row">
               <div class="col-md-12<?php if(isset($client) && (!is_empty_customer_company($client->userid) && total_rows('tblcontacts',array('userid'=>$client->userid,'is_primary'=>1)) > 0)) { echo ''; } else {echo ' hide';} ?>" id="client-show-primary-contact-wrapper">
                  <div class="checkbox checkbox-info mbot20 no-mtop">
                     <input type="checkbox" name="show_primary_contact"<?php if(isset($client) && $client->show_primary_contact == 1){echo ' checked';}?> value="1" id="show_primary_contact">
                     <label for="show_primary_contact"><?php echo _l('show_primary_contact',_l('invoices').', '._l('estimates').', '._l('payments').', '._l('credit_notes')); ?></label>
                  </div>
               </div>
                <div class="col-md-12">

                    <?php echo form_hidden('contact[id]',$contact->id); ?>
                    <div class="col-md-3">
                        <div class="form-group" >

                            <label for="salutation" class="control-label"><?php echo _l('contact_salutation') ?></label>
                            <?php
                            $value=( isset($contact) ? $contact->salutation : '');
                            ?>
                            <select name="contact[salutation]" id="contact[salutation]"   class="form-control selectpicker">
                                <option <?php if($value=="") echo 'selected' ?> value=""></option>
                                <option <?php if($value=="Mr.") echo 'selected' ?> value="Mr.">Mr.</option>
                                <option <?php if($value=="Mrs.") echo 'selected' ?> value="Mrs.">Mrs.</option>
                                <option <?php if($value=="Ms.") echo 'selected' ?> value="Ms.">Ms.</option>
                                <option <?php if($value=="Miss.") echo 'selected' ?> value="Miss.">Miss.</option>
                                <option <?php if($value=="Mx.") echo 'selected' ?> value="Mx.">Mx.</option>
                                <option <?php if($value=="Dr.") echo 'selected' ?> value="Dr.">Dr.</option>
                                <option <?php if($value=="Prof.") echo 'selected' ?> value="Prof.">Prof.</option>
                                <option <?php if($value=="Rev.") echo 'selected' ?> value="Rev.">Rev.</option>
                            </select>
                        </div>


                    </div>

                    <div class="col-md-3">
                        <?php $value=( isset($contact) ? $contact->firstname : ''); ?>
                        <?php $attrs = (isset($contact) ? array() : array('autofocus'=>true)); ?>
                        <?php echo render_input( 'contact[firstname]', 'client_first_name',$value,'text',$attrs); ?>

                    </div>
                    <div class="col-md-3">
                        <?php $value=( isset($contact) ? $contact->middle_name : ''); ?>
                        <?php $attrs = (isset($contact) ? array() : array('autofocus'=>true)); ?>
                        <?php echo render_input( 'contact[middle_name]', 'client_middle_name',$value,'text',$attrs); ?>

                    </div>
                    <div class="col-md-3">
                        <?php $value=( isset($contact) ? $contact->lastname : ''); ?>
                        <?php $attrs = (isset($contact) ? array() : array('autofocus'=>true)); ?>
                        <?php echo render_input( 'contact[lastname]', 'client_last_name',$value,'text',$attrs); ?>
                    </div>
                </div>
                <div class="col-md-12">
               <div class="col-md-6">

                  <?php $value=( isset($client) ? $client->company : ''); ?>
                  <?php $attrs = (isset($client) ? array() : array('autofocus'=>true)); ?>
                  <?php echo render_input( 'company', 'client_company',$value,'text',$attrs); ?>
                  <?php if(get_option('company_requires_vat_number_field') == 1){
                     $value=( isset($client) ? $client->vat : '');
                     //echo render_input( 'vat', 'client_vat_number',$value);
                     } ?>

                  <?php //$value=( isset($client) ? $client->phonenumber : ''); ?>
                  <?php //echo render_input( 'phonenumber', 'client_phonenumber',$value); ?>

                   <?php //$value=( isset($contact) ? $contact->phonenumber : ''); ?>
                   <?php //echo render_input( 'contact[phonenumber]', 'contact_phonenumber',$value,'text'); ?>

                   <!--begin phone -->
                   <div id="phone_row_1" class="row _contact_phone">
                       <div class="col-md-6">
                           <div class="form-group" app-field-wrapper="contact_info[phone][1][value]">
                               <div style="float:left; margin-top: -5px; margin-right: 5px">
                                   <a style="cursor: pointer;font-size: 17px" href="javascript:add_phone()">
                                       <i class="mdi mdi-plus-circle-outline"></i>
                                   </a>
                               </div>
                               <label for="contact_info[phone][1][value]" class="control-label">Phone</label>
                               <?php
                                 $value="";
                                 if($contact_phone){
                                   if(count($contact_phone)>0){
                                       $value = $contact_phone[0]['phone_number'];
                                   }
                               } ?>
                               <input type="text" id="contact_info[phone][1][value]" name="contact_info[phone][1][value]" class="form-control" value="<?php echo $value; ?>">
                           </div>
                       </div>
                       <div class="col-md-6">
                           <div class="select-placeholder form-group" app-field-wrapper="contact_info[phone][1][type]">

                               <label for="contact_info[phone][1][type]" class="control-label">Phone Type</label>
                               <?php
                               $value="";
                               if($contact_phone){
                                   if(count($contact_phone)>0){
                                       $value = $contact_phone[0]['phone_type'];
                                   }
                               } ?>
                               <select data-dropup-auto="false" name="contact_info[phone][1][type]" id="contact_info[phone][1][type]"   class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                   <option <?php if($value=="") echo 'selected' ?> value=""></option>
                                   <option <?php if($value=="Work") echo 'selected' ?> value="Work">Work</option>
                                   <option <?php if($value=="Home") echo 'selected' ?> value="Home">Home</option>
                                   <option <?php if($value=="Mobile") echo 'selected' ?> value="Mobile">Mobile</option>
                                   <option <?php if($value=="Main") echo 'selected' ?> value="Main">Main</option>
                                   <option <?php if($value=="Home_fax") echo 'selected' ?> value="Home_fax">Home fax</option>
                                   <option <?php if($value=="Work_fax") echo 'selected' ?> value="Work_fax">Work fax</option>
                                   <option <?php if($value=="Other") echo 'selected' ?> value="Other">Other</option>
                               </select>
                           </div>
                       </div>
                   </div>

                   <?php
                        if(count($contact_phone)>1){
                            $i=0;

                            foreach ($contact_phone as $ctp){
                                //var_dump($ctp);die;
                                $i++;
                                if($i==1) continue;
                                $numItems = $i;

                                $html = '<div class="row _contact_phone _ctp'.$numItems.'">' ;
                                $html .=    '                        <div class="col-md-6">' .
                                    '                            <div class="form-group" app-field-wrapper="contact_info[phone]['.$numItems.'][value]">' .
                                    '                                <input type="text" id="contact_info[phone]['.$numItems.'][value]" name="contact_info[phone]['.$numItems.'][value]" class="form-control" value="'.$ctp['phone_number'].'">' .
                                    '                            </div>' .
                                    '                        </div>' .
                                    '                        <div class="col-md-6">' .
                                    '                            <div style="float:left ;width: 80%" class="select-placeholder form-group" app-field-wrapper="contact_info[phone]['.$numItems.'][type]">' .
                                    '                                <select data-dropup-auto="false" name="contact_info[phone]['.$numItems.'][type]" id="contact_info[phone]['.$numItems.'][type]"   class="form-control selectpicker" >' .
                                    '                                    <option '. ($ctp['phone_type']==''?'selected':'') .'  value=""></option>' .
                                    '                                    <option '. ($ctp['phone_type']=='Work'?'selected':'') .' value="Work">Work</option>' .
                                    '                                    <option '. ($ctp['phone_type']=='Home'?'selected':'') .' value="Home">Home</option>' .
                                    '                                    <option '. ($ctp['phone_type']=='Mobile'?'selected':'') .' value="Mobile">Mobile</option>' .
                                    '                                    <option '. ($ctp['phone_type']=='Main'?'selected':'') .' value="Main">Main</option>' .
                                    '                                    <option '. ($ctp['phone_type']=='Home_fax'?'selected':'') .' value="Home_fax">Home fax</option>' .
                                    '                                    <option '. ($ctp['phone_type']=='Work_fax'?'selected':'') .' value="Work_fax">Work fax</option>' .
                                    '                                    <option '. ($ctp['phone_type']=='Other'?'selected':'') .' value="Other">Other</option>' .
                                    '                                </select>' .
                                    '                            </div>' .
                                    '                            <div style="float: left; font-size: 17px; margin-left: 5px">' .
                                    '                                <a style="cursor: pointer" href="javascript:remove_phone(\'_ctp'.$numItems.'\')">' .
                                    '                                    <i class="mdi mdi-close-circle-outline"></i>' .
                                    '                                </a>' .
                                    '                            </div>' .
                                    '                        </div>' .
                                    '                    </div>';
                                echo $html;

                            }
                        }
                   ?>

                   <!-- end phone -->

                   <!-- begin mail -->
                   <div id="mail_row_1" class="row _contact_mail">
                       <div class="col-md-6">
                           <div class="form-group" app-field-wrapper="contact_info[mail][1][value]">
                               <div style="float:left; margin-top: -5px; margin-right: 5px">
                                   <a style="cursor: pointer;font-size: 17px" href="javascript:add_mail()">
                                       <i class="mdi mdi-plus-circle-outline"></i>
                                   </a>
                               </div>
                               <label for="contact_info[mail][1][value]" class="control-label">Email</label>
                               <?php
                               $value="";
                               if($contact_mail){
                                   if(count($contact_mail)>0){
                                       $value = $contact_mail[0]['mail'];
                                   }
                               } ?>
                               <input type="text" id="contact_info[mail][1][value]" name="contact_info[mail][1][value]" class="form-control" value="<?php echo $value; ?>">
                           </div>
                       </div>
                       <div class="col-md-6">
                           <div class="form-group" app-field-wrapper="contact_info[mail][1][type]">

                               <label for="contact_info[mail][1][type]" class="control-label">Email Type</label>
                               <?php
                               $value="";
                               if($contact_mail){
                                   if(count($contact_mail)>0){
                                       $value = $contact_mail[0]['mail_type'];
                                   }
                               } ?>
                               <select data-dropup-auto="false" name="contact_info[mail][1][type]" id="contact_info[mail][1][type]"   class="form-control selectpicker">
                                   <option <?php if($value=="") echo 'selected' ?> value=""></option>
                                   <option <?php if($value=="work") echo 'selected' ?> value="work">Work</option>
                                   <option <?php if($value=="personal") echo 'selected' ?> value="personal">Personal</option>
                               </select>
                           </div>
                       </div>
                   </div>

                   <?php
                   if(count($contact_mail)>1){
                       $i=0;

                       foreach ($contact_mail as $ctm){
                           //var_dump($ctp);die;
                           $i++;
                           if($i==1) continue;
                           $numItems = $i;

                           $html = '<div class="row _contact_mail _ctm'.$numItems.'">' ;
                           $html .=    '                        <div class="col-md-6">' .
                               '                            <div class="form-group" app-field-wrapper="contact_info[mail]['.$numItems.'][value]">' .
                               '                                <input type="text" id="contact_info[mail]['.$numItems.'][value]" name="contact_info[mail]['.$numItems.'][value]" class="form-control" value="'.$ctm['mail'].'">' .
                               '                            </div>' .
                               '                        </div>' .
                               '                        <div class="col-md-6">' .
                               '                            <div style="float:left ;width: 80%" class="form-group" app-field-wrapper="contact_info[mail]['.$numItems.'][type]">' .
                               '                                <select data-dropup-auto="false" name="contact_info[mail]['.$numItems.'][type]" id="contact_info[mail]['.$numItems.'][type]"   class="form-control selectpicker" >' .
                               '                                    <option '. ($ctm['mail_type']==''?'selected':'') .'  value=""></option>' .
                               '                                    <option '. ($ctm['mail_type']=='work'?'selected':'') .' value="work">Work</option>' .
                               '                                    <option '. ($ctm['mail_type']=='personal'?'selected':'') .' value="personal">Personal</option>' .
                               '                                </select>' .
                               '                            </div>' .
                               '                            <div style="float: left; font-size: 17px; margin-left: 5px">' .
                               '                                <a style="cursor: pointer" href="javascript:remove_mail(\'_ctm'.$numItems.'\')">' .
                               '                                    <i class="mdi mdi-close-circle-outline"></i>' .
                               '                                </a>' .
                               '                            </div>' .
                               '                        </div>' .
                               '                    </div>';
                           echo $html;

                       }
                   }
                   ?>
                    <!-- end mail -->

                   <!-- begin website -->

                   <div id="website_row_1" class="row _contact_website">
                       <div class="col-md-6">
                           <div class="form-group" app-field-wrapper="contact_info[website][1][value]">
                               <div style="float:left; margin-top: -5px; margin-right: 5px">
                                   <a style="cursor: pointer;font-size: 17px" href="javascript:add_website()">
                                       <i class="mdi mdi-plus-circle-outline"></i>
                                   </a>
                               </div>
                               <label for="contact_info[website][1][value]" class="control-label">Website</label>
                               <?php
                               $value="";
                               if($contact_website){
                                   if(count($contact_website)>0){
                                       $value = $contact_website[0]['website'];
                                   }
                               } ?>
                               <input type="text" id="contact_info[website][1][value]" name="contact_info[website][1][value]" class="form-control" value="<?php echo $value; ?>">
                           </div>
                       </div>
                       <div class="col-md-6">
                           <div class="form-group" app-field-wrapper="contact_info[website][1][type]">

                               <label for="contact_info[website][1][type]" class="control-label">Website Type</label>
                               <?php
                               $value="";
                               if($contact_website){
                                   if(count($contact_website)>0){
                                       $value = $contact_website[0]['website_type'];
                                   }
                               } ?>
                               <select data-dropup-auto="false" name="contact_info[website][1][type]" id="contact_info[website][1][type]"   class="form-control selectpicker">
                                   <option <?php if($value=="") echo 'selected' ?> value=""></option>
                                   <option <?php if($value=="Website") echo 'selected' ?> value="Website">Website</option>
                                   <option <?php if($value=="Skype") echo 'selected' ?> value="Skype">Skype</option>
                                   <option <?php if($value=="Twitter") echo 'selected' ?> value="Twitter">Twitter</option>
                                   <option <?php if($value=="LinkedIn") echo 'selected' ?> value="LinkedIn">LinkedIn</option>
                                   <option <?php if($value=="Facebook") echo 'selected' ?> value="Facebook">Facebook</option>
                                   <option <?php if($value=="Xing") echo 'selected' ?> value="Xing">Xing</option>
                                   <option <?php if($value=="Blog") echo 'selected' ?> value="Blog">Blog</option>
                                   <option <?php if($value=="Google+") echo 'selected' ?> value="Google+">Google+</option>
                                   <option <?php if($value=="Flickr") echo 'selected' ?> value="Flickr">Flickr</option>
                                   <option <?php if($value=="GitHub") echo 'selected' ?> value="GitHub">GitHub</option>
                                   <option <?php if($value=="YouTube") echo 'selected' ?> value="YouTube">YouTube</option>
                               </select>
                           </div>
                       </div>
                   </div>

                   <?php
                   if(count($contact_website)>1){
                       $i=0;

                       foreach ($contact_website as $ctw){
                           //var_dump($ctp);die;
                           $i++;
                           if($i==1) continue;
                           $numItems = $i;

                           $html = '<div class="row _contact_website _ctw'.$numItems.'">' ;
                           $html .=    '                        <div class="col-md-6">' .
                               '                            <div class="form-group" app-field-wrapper="contact_info[website]['.$numItems.'][value]">' .
                               '                                <input type="text" id="contact_info[website]['.$numItems.'][value]" name="contact_info[website]['.$numItems.'][value]" class="form-control" value="'.$ctw['website'].'">' .
                               '                            </div>' .
                               '                        </div>' .
                               '                        <div class="col-md-6">' .
                               '                            <div style="float:left ;width: 80%" class="form-group" app-field-wrapper="contact_info[mail]['.$numItems.'][type]">' .
                               '                                <select data-dropup-auto="false" name="contact_info[website]['.$numItems.'][type]" id="contact_info[website]['.$numItems.'][type]"   class="form-control selectpicker" >' .
                               '                                    <option '. ($ctw['website_type']==''?'selected':'') .'  value=""></option>' .
                               '                                    <option '. ($ctw['website_type']=='Website'?'selected':'') .' value="Website">Website</option>' .
                               '                                    <option '. ($ctw['website_type']=='Skype'?'selected':'') .' value="Skype">Skype</option>' .
                               '                                    <option '. ($ctw['website_type']=='Twitter'?'selected':'') .' value="Twitter">Twitter</option>' .
                               '                                    <option '. ($ctw['website_type']=='LinkedIn'?'selected':'') .' value="LinkedIn">LinkedIn</option>' .
                               '                                    <option '. ($ctw['website_type']=='Facebook'?'selected':'') .' value="Facebook">Facebook</option>' .
                               '                                    <option '. ($ctw['website_type']=='Xing'?'selected':'') .' value="Xing">Xing</option>' .
                               '                                    <option '. ($ctw['website_type']=='Blog'?'selected':'') .' value="Blog">Blog</option>' .
                               '                                    <option '. ($ctw['website_type']=='Google+'?'selected':'') .' value="Google+">Google+</option>' .
                               '                                    <option '. ($ctw['website_type']=='Flickr'?'selected':'') .' value="Flickr">Flickr</option>' .
                               '                                    <option '. ($ctw['website_type']=='GitHub'?'selected':'') .' value="GitHub">GitHub</option>' .
                               '                                    <option '. ($ctw['website_type']=='YouTube'?'selected':'') .' value="YouTube">YouTube</option>' .
                               '                                </select>' .
                               '                            </div>' .
                               '                            <div style="float: left; font-size: 17px; margin-left: 5px">' .
                               '                                <a style="cursor: pointer" href="javascript:remove_website(\'_ctw'.$numItems.'\')">' .
                               '                                    <i class="mdi mdi-close-circle-outline"></i>' .
                               '                                </a>' .
                               '                            </div>' .
                               '                        </div>' .
                               '                    </div>';
                           echo $html;

                       }
                   }
                   ?>
                   <!-- end website -->




               </div>
               <div class="col-md-6">
                  <?php $value=( isset($client) ? $client->address : ''); ?>
                  <?php echo render_textarea( 'address', 'client_address',$value); ?>
                   <div class="row">
                      <div class="col-md-6">
                          <?php $value=( isset($client) ? $client->city : ''); ?>
                          <?php echo render_input( 'city', 'client_city',$value); ?>
                      </div>
                       <div class="col-md-6">
                           <?php $value=( isset($client) ? $client->state : ''); ?>
                           <?php echo render_input( 'state', 'client_state',$value); ?>
                       </div>
                   </div>

                   <div class="row">
                       <div class="col-md-6">
                           <?php $value=( isset($client) ? $client->zip : ''); ?>
                           <?php echo render_input( 'zip', 'client_postal_code',$value); ?>
                       </div>
                       <div class="col-md-6">
                           <?php $countries= get_all_countries();
                           $customer_default_country = get_option('customer_default_country');
                           $selected =( isset($client) ? $client->country : $customer_default_country);
                           echo render_select( 'country',$countries,array( 'country_id',array( 'short_name')), 'clients_country',$selected,array('data-none-selected-text'=>_l('dropdown_non_selected_tex')));
                           ?>
                       </div>
                   </div>

                   <div class="row">
                       <div class="col-md-6">
                           <?php
                           $selected = array();
                           if(isset($customer_groups)){
                               foreach($customer_groups as $group){
                                   array_push($selected,$group['groupid']);
                               }
                           }
                           if(is_admin() || get_option('staff_members_create_inline_customer_groups') == '1'){
                               echo render_select_with_input_group('groups_in[]',$groups,array('id','name'),'customer_groups',$selected,'<a href="#" data-toggle="modal" data-target="#customer_group_modal"><i class="fa fa-plus"></i></a>',array('multiple'=>true,'data-actions-box'=>true),array(),'','',false);
                           } else {
                               echo render_select('groups_in[]',$groups,array('id','name'),'customer_groups',$selected,array('multiple'=>true,'data-actions-box'=>true),array(),'','',false);
                           }
                           ?>
                       </div>
                       <div class="col-md-6">

                           <?php if(!isset($client)){ ?>
                               <!-- <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('customer_currency_change_notice'); ?>"></i> -->
                           <?php }
                           $s_attrs = array('data-none-selected-text'=>_l('system_default_string'));
                           $selected = '';
                           if(isset($client) && client_have_transactions($client->userid)){
                               $s_attrs['disabled'] = true;
                           }
                           foreach($currencies as $currency){
                               if(isset($client)){
                                   if($currency['id'] == $client->default_currency){
                                       $selected = $currency['id'];
                                   }
                               }
                           }
                           // Do not remove the currency field from the customer profile!
                           //echo render_select('default_currency',$currencies,array('id','name','symbol'),'invoice_add_edit_currency',$selected,$s_attrs); ?>
                           <?php if(get_option('disable_language') == 0){ ?>
                               <div class="form-group select-placeholder">
                                   <label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?>
                                   </label>
                                   <select data-dropup-auto="false" name="default_language" id="default_language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                       <option value=""><?php echo _l('system_default_string'); ?></option>
                                       <?php foreach(list_folders(APPPATH .'language') as $language){
                                           $selected = '';
                                           if(isset($client)){
                                               if($client->default_language == $language){
                                                   $selected = 'selected';
                                               }
                                           }
                                           ?>
                                           <option value="<?php echo $language; ?>" <?php echo $selected; ?>><?php echo ucfirst($language); ?></option>
                                       <?php } ?>
                                   </select>
                               </div>
                           <?php } ?>
                       </div>
                   </div>

               </div>
                </div>
            </div>
         </div>
         <?php if(isset($client)){ ?>
         <div role="tabpanel" class="tab-pane" id="customer_admins">
            <?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
            <a href="#" data-toggle="modal" data-target="#customer_admins_assign" class="btn btn-info mbot30"><?php echo _l('assign_admin'); ?></a>
            <?php } ?>
            <table class="table dt-table">
               <thead>
                  <tr>
                     <th><?php echo _l('staff_member'); ?></th>
                     <th><?php echo _l('customer_admin_date_assigned'); ?></th>
                     <?php if(has_permission('customers','','create') || has_permission('customers','','edit')){ ?>
                     <th><?php echo _l('options'); ?></th>
                     <?php } ?>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach($customer_admins as $c_admin){ ?>
                  <tr>
                     <td><a href="<?php echo admin_url('profile/'.$c_admin['staff_id']); ?>">
                        <?php echo staff_profile_image($c_admin['staff_id'], array(
                           'staff-profile-image-small',
                           'mright5'
                           ));
                           echo get_staff_full_name($c_admin['staff_id']); ?></a>
                     </td>
                     <td data-order="<?php echo $c_admin['date_assigned']; ?>"><?php echo _dt($c_admin['date_assigned']); ?></td>
                     <?php if(has_permission('customers','','create') || has_permission('customers','','edit')){ ?>
                     <td>
                        <a href="<?php echo admin_url('clients/delete_customer_admin/'.$client->userid.'/'.$c_admin['staff_id']); ?>" class="btn btn-danger _delete btn-icon"><i class="fa fa-remove"></i></a>
                     </td>
                     <?php } ?>
                  </tr>
                  <?php } ?>
               </tbody>
            </table>
         </div>
         <?php } ?>
         <div role="tabpanel" class="tab-pane" id="billing_and_shipping">
            <div class="row">
               <div class="col-md-12">
                  <div class="row">
                     <div class="col-md-6">
                        <h4 class="no-mtop"><?php echo _l('billing_address'); ?> <a href="#" class="pull-right billing-same-as-customer"><small class="font-medium-xs"><?php echo _l('customer_billing_same_as_profile'); ?></small></a></h4>
                        <hr />
                        <?php $value=( isset($client) ? $client->billing_street : ''); ?>
                        <?php echo render_textarea( 'billing_street', 'billing_street',$value); ?>
                        <?php $value=( isset($client) ? $client->billing_city : ''); ?>
                        <?php echo render_input( 'billing_city', 'billing_city',$value); ?>
                        <?php $value=( isset($client) ? $client->billing_state : ''); ?>
                        <?php echo render_input( 'billing_state', 'billing_state',$value); ?>
                        <?php $value=( isset($client) ? $client->billing_zip : ''); ?>
                        <?php echo render_input( 'billing_zip', 'billing_zip',$value); ?>
                        <?php $selected=( isset($client) ? $client->billing_country : '' ); ?>
                        <?php echo render_select( 'billing_country',$countries,array( 'country_id',array( 'short_name')), 'billing_country',$selected,array('data-none-selected-text'=>_l('dropdown_non_selected_tex'))); ?>
                     </div>
                     <div class="col-md-6">
                        <h4 class="no-mtop">
                           <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('customer_shipping_address_notice'); ?>"></i>
                           <?php echo _l('shipping_address'); ?> <a href="#" class="pull-right customer-copy-billing-address"><small class="font-medium-xs"><?php echo _l('customer_billing_copy'); ?></small></a>
                        </h4>
                        <hr />
                        <?php $value=( isset($client) ? $client->shipping_street : ''); ?>
                        <?php echo render_textarea( 'shipping_street', 'shipping_street',$value); ?>
                        <?php $value=( isset($client) ? $client->shipping_city : ''); ?>
                        <?php echo render_input( 'shipping_city', 'shipping_city',$value); ?>
                        <?php $value=( isset($client) ? $client->shipping_state : ''); ?>
                        <?php echo render_input( 'shipping_state', 'shipping_state',$value); ?>
                        <?php $value=( isset($client) ? $client->shipping_zip : ''); ?>
                        <?php echo render_input( 'shipping_zip', 'shipping_zip',$value); ?>
                        <?php $selected=( isset($client) ? $client->shipping_country : '' ); ?>
                        <?php echo render_select( 'shipping_country',$countries,array( 'country_id',array( 'short_name')), 'shipping_country',$selected,array('data-none-selected-text'=>_l('dropdown_non_selected_tex'))); ?>
                     </div>
                     <?php if(isset($client) &&
                        (total_rows('tblinvoices',array('clientid'=>$client->userid)) > 0 || total_rows('tblestimates',array('clientid'=>$client->userid)) > 0 || total_rows('tblcreditnotes',array('clientid'=>$client->userid)) > 0)){ ?>
                     <div class="col-md-12">
                        <div class="alert alert-warning">
                           <div class="checkbox checkbox-default">
                              <input type="checkbox" name="update_all_other_transactions" id="update_all_other_transactions">
                              <label for="update_all_other_transactions">
                              <?php echo _l('customer_update_address_info_on_invoices'); ?><br />
                              </label>
                           </div>
                           <b><?php echo _l('customer_update_address_info_on_invoices_help'); ?></b>
                           <div class="checkbox checkbox-default">
                              <input type="checkbox" name="update_credit_notes" id="update_credit_notes">
                              <label for="update_credit_notes">
                              <?php echo _l('customer_profile_update_credit_notes'); ?><br />
                              </label>
                           </div>
                        </div>
                     </div>
                     <?php } ?>
                  </div>
               </div>
            </div>
         </div>

          <?php
          foreach ($list_custom_tab as $customtab) { ?>
              <div role="tabpanel" class="tab-pane" id="<?= $customtab['slug'] ?>">
                  <div class="row">
                      <div class="col-md-12">
                          <?php $rel_id = (isset($contact) ? $contact->id : false); ?>
                          <?php echo render_custom_fields('contacts', $rel_id, array('custom_tab_id' => $customtab['id'])); ?>
                      </div>
                  </div>
              </div>
          <?php } ?>

          <div role="tabpanel" class="tab-pane" id="contact_tags">
              <div class="row">
                  <div class="col-md-12">
                      <div class="form-group">

                          <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                          <?php $value=( isset($contact_tags) ? $contact_tags : ''); ?>
                          <input type="text" class="tagsinput" id="contact[tags]" name="contact[tags]" data-role="tagsinput" value="<?php echo $value; ?>">
                      </div>
                  </div>
              </div>
          </div>
      </div>
   </div>
   <?php echo form_close(); ?>
</div>
<?php if(isset($client)){ ?>
<?php if (has_permission('customers', '', 'create') || has_permission('customers', '', 'edit')) { ?>
<div class="modal fade" id="customer_admins_assign" tabindex="-1" role="dialog">
   <div class="modal-dialog">
      <?php echo form_open(admin_url('clients/assign_admins/'.$client->userid)); ?>
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('assign_admin'); ?></h4>
         </div>
         <div class="modal-body">
            <?php
               $selected = array();
               foreach($customer_admins as $c_admin){
                  array_push($selected,$c_admin['staff_id']);
               }
               echo render_select('customer_admins[]',$staff,array('staffid',array('firstname','lastname')),'',$selected,array('multiple'=>true),array(),'','',false); ?>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
         </div>
      </div>
      <!-- /.modal-content -->
      <?php echo form_close(); ?>
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php } ?>
<?php } ?>
<?php $this->load->view('admin/clients/client_group'); ?>

<script>

</script>
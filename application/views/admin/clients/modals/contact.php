<!-- Modal Contact -->
<div class="modal fade" id="contact" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/clients/contact/' . $customer_id . '/' . $contactid, array('id' => 'contact-form', 'autocomplete' => 'off')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $title; ?><br/>
                    <small class="color-white" id=""><?php echo get_company_name($customer_id, true); ?></small>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="horizontal-scrollable-tabs">
                            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs profile-tabs row customer-profile-tabs nav-tabs-horizontal"
                                    style="margin-left: -15px; margin-right: -15px" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#contact_basic_info" aria-controls="contact_basic_info" role="tab"
                                           data-toggle="tab">
                                            <?php echo _l('contact_basic_info'); ?>
                                        </a>
                                    </li>
                                    <?php do_action('after_contact_basic_info_tab', false); ?>

                                    <li role="presentation">
                                        <a href="#contact_tags" aria-controls="contact_tags" role="tab" data-toggle="tab">
                                            <?php echo _l('contact_tags'); ?>
                                        </a>
                                    </li>
                                    <?php do_action('after_contact_tags_tab', $contact); ?>

                                    <?php  foreach ($list_custom_tab as $customtab) { ?>
                                        <li role="presentation" class="">
                                            <a href="#<?=$customtab['slug']?>" aria-controls="<?=$customtab['slug']?>" role="tab" data-toggle="tab">
                                                <?php echo $customtab['name']; ?>
                                            </a>
                                        </li>
                                        <?php do_action('after_'.$customtab['slug'].'_tab',isset($contact) ? $contact : false); ?>
                                    <?php  }?>
                                    <li role="presentation">
                                        <a href="#contact_other_info" aria-controls="contact_other_info" role="tab"
                                           data-toggle="tab">
                                            <?php echo _l('contact_other_info'); ?>
                                        </a>
                                    </li>
                                    <?php do_action('after_contact_other_info_tab', false); ?>
                                </ul>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="contact_basic_info">
                                <?php if (isset($contact)) { ?>
                                    <div class="alert alert-warning hide" role="alert" id="contact_proposal_warning">
                                        <?php echo _l('proposal_warning_email_change', array(_l('contact_lowercase'), _l('contact_lowercase'), _l('contact_lowercase'))); ?>
                                        <hr/>
                                        <a href="#" id="contact_update_proposals_emails" data-original-email=""
                                           onclick="update_all_proposal_emails_linked_to_contact(<?php echo $contact->id; ?>); return false;"><?php echo _l('update_proposal_email_yes'); ?></a>
                                        <br/>
                                        <a href="#"
                                           onclick="close_modal_manually('#contact'); return false;"><?php echo _l('update_proposal_email_no'); ?></a>
                                    </div>
                                <?php } ?>


                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">

                                            <label for="salutation"
                                                   class="control-label"><?php echo _l('contact_salutation') ?></label>
                                            <?php
                                            $value = (isset($contact) ? $contact->salutation : '');
                                            ?>
                                            <select data-dropup-auto="false" name="salutation" id="salutation" class="form-control selectpicker">
                                                <option <?php if ($value == "") echo 'selected' ?> value=""></option>
                                                <option <?php if ($value == "Mr.") echo 'selected' ?> value="Mr.">Mr.
                                                </option>
                                                <option <?php if ($value == "Mrs.") echo 'selected' ?> value="Mrs.">
                                                    Mrs.
                                                </option>
                                                <option <?php if ($value == "Ms.") echo 'selected' ?> value="Ms.">Ms.
                                                </option>
                                                <option <?php if ($value == "Miss.") echo 'selected' ?> value="Miss.">
                                                    Miss.
                                                </option>
                                                <option <?php if ($value == "Mx.") echo 'selected' ?> value="Mx.">Mx.
                                                </option>
                                                <option <?php if ($value == "Dr.") echo 'selected' ?> value="Dr.">Dr.
                                                </option>
                                                <option <?php if ($value == "Prof.") echo 'selected' ?> value="Prof.">
                                                    Prof.
                                                </option>
                                                <option <?php if ($value == "Rev.") echo 'selected' ?> value="Rev.">
                                                    Rev.
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <?php echo form_hidden('contactid', $contactid); ?>
                                        <?php $value = (isset($contact) ? $contact->firstname : ''); ?>
                                        <?php echo render_input('firstname', 'client_firstname', $value); ?>

                                    </div>
                                    <div class="col-md-2">
                                        <?php $value = (isset($contact) ? $contact->middle_name : ''); ?>
                                        <?php echo render_input('middle_name', 'client_middle_name', $value); ?>

                                    </div>
                                    <div class="col-md-2">
                                        <?php $value = (isset($contact) ? $contact->lastname : ''); ?>
                                        <?php echo render_input('lastname', 'client_lastname', $value); ?>

                                    </div>
                                    <div class="col-md-2">
                                        <?php $value = (isset($contact) ? $contact->title : ''); ?>
                                        <?php echo render_input('title', 'contact_position', $value); ?>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="salutation"
                                               class="control-label"><?php echo _l('contact_link_type') ?></label>
                                        <?php $value = (isset($contact) ? $contact->link_type : ''); ?>
                                        <select data-dropup-auto="false" name="link_type" id="link_type" class="form-control selectpicker">
                                            <option <?php if($value=='') echo 'selected';?> value=""></option>
                                            <optgroup  label="Person">
                                                <option <?php if($value=='Primary') echo 'selected';?> value="Primary">Primary</option>
                                                <option <?php if($value=='Spouse') echo 'selected';?> value="Spouse">Spouse</option>
                                                <option <?php if($value=='Husband') echo 'selected';?> value="Husband">Husband</option>
                                                <option <?php if($value=='Wife') echo 'selected';?> value="Wife">Wife</option>
                                                <option <?php if($value=='Common Law') echo 'selected';?> value="Common Law">Common Law</option>
                                                <option <?php if($value=='Child') echo 'selected';?> value="Child">Child</option>
                                                <option <?php if($value=='Son') echo 'selected';?> value="Son">Son</option>
                                                <option <?php if($value=='Daughter') echo 'selected';?> value="Daughter">Daughter</option>
                                                <option <?php if($value=='Sibling') echo 'selected';?> value="Sibling">Sibling</option>
                                                <option <?php if($value=='Brother') echo 'selected';?> value="Brother">Brother</option>
                                                <option <?php if($value=='Sister') echo 'selected';?> value="Sister">Sister</option>
                                                <option <?php if($value=='Grandchild') echo 'selected';?> value="Grandchild">Grandchild</option>
                                                <option <?php if($value=='Grandson') echo 'selected';?> value="Grandson">Grandson</option>
                                                <option <?php if($value=='Granddaughter') echo 'selected';?> value="Granddaughter">Granddaughter</option>
                                                <option <?php if($value=='Mother') echo 'selected';?> value="Mother">Mother</option>
                                                <option <?php if($value=='Father') echo 'selected';?> value="Father">Father</option>
                                                <option <?php if($value=='Uncle') echo 'selected';?> value="Uncle">Uncle</option>
                                                <option <?php if($value=='Aunt') echo 'selected';?> value="Aunt">Aunt</option>
                                                <option <?php if($value=='Nephew') echo 'selected';?> value="Nephew">Nephew</option>
                                                <option <?php if($value=='Niece') echo 'selected';?> value="Niece">Niece</option>
                                                <option <?php if($value=='Grandfather') echo 'selected';?> value="Grandfather">Grandfather</option>
                                                <option <?php if($value=='Grandmother') echo 'selected';?> value="Grandmother">Grandmother</option>
                                                <option <?php if($value=='Boyfriend') echo 'selected';?> value="Boyfriend">Boyfriend</option>
                                                <option <?php if($value=='Girlfriend') echo 'selected';?> value="Girlfriend">Girlfriend</option>
                                                <option <?php if($value=='Friend') echo 'selected';?> value="Friend">Friend</option>
                                                <option <?php if($value=='Missing') echo 'selected';?> value="Missing">Missing</option>

                                            </optgroup>
                                            <optgroup label="Company" >
                                                <option <?php if($value=='Principal') echo 'selected';?> value="Principal">Principal</option>
                                                <option <?php if($value=='Partner') echo 'selected';?> value="Partner">Partner</option>
                                                <option <?php if($value=='Key Person') echo 'selected';?> value="Key Person">Key Person</option>
                                                <option <?php if($value=='Executive') echo 'selected';?> value="Executive">Executive</option>
                                                <option <?php if($value=='Manager') echo 'selected';?> value="Manager">Manager</option>
                                                <option <?php if($value=='Shareholder') echo 'selected';?> value="Shareholder">Shareholder</option>
                                            </optgroup>
                                        </select>
                                    </div>

                                </div>
                                <!-- // For email exist check -->

                                <div class="row">
                                    <div class="col-md-4">
                                        <!--begin phone -->
                                        <div id="phone_row_1" class="row _contact_phone">
                                            <div class="col-md-6">
                                                <div class="form-group"
                                                     app-field-wrapper="contact_info[phone][1][value]">
                                                    <div style="float:left; margin-top: -5px; margin-right: 5px">
                                                        <a style="cursor: pointer;font-size: 17px"
                                                           href="javascript:add_phone()">
                                                            <i class="mdi mdi-plus-circle-outline"></i>
                                                        </a>
                                                    </div>
                                                    <label for="contact_info[phone][1][value]" class="control-label">Phone</label>
                                                    <?php
                                                    $value = "";
                                                    if ($contact_phone) {
                                                        if (count($contact_phone) > 0) {
                                                            $value = $contact_phone[0]['phone_number'];
                                                        }
                                                    } ?>
                                                    <input type="text" id="contact_info[phone][1][value]"
                                                           name="contact_info[phone][1][value]" class="form-control"
                                                           value="<?php echo $value; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group"
                                                     app-field-wrapper="contact_info[phone][1][type]">

                                                    <label for="contact_info[phone][1][type]" class="control-label">Phone
                                                        Type</label>
                                                    <?php
                                                    $value = "";
                                                    if ($contact_phone) {
                                                        if (count($contact_phone) > 0) {
                                                            $value = $contact_phone[0]['phone_type'];
                                                        }
                                                    } ?>
                                                    <select data-dropup-auto="false" name="contact_info[phone][1][type]"
                                                            id="contact_info[phone][1][type]"
                                                            class="form-control selectpicker">
                                                        <option <?php if ($value == "") echo 'selected' ?>
                                                                value=""></option>
                                                        <option <?php if ($value == "Work") echo 'selected' ?>
                                                                value="Work">Work
                                                        </option>
                                                        <option <?php if ($value == "Home") echo 'selected' ?>
                                                                value="Home">Home
                                                        </option>
                                                        <option <?php if ($value == "Mobile") echo 'selected' ?>
                                                                value="Mobile">Mobile
                                                        </option>
                                                        <option <?php if ($value == "Main") echo 'selected' ?>
                                                                value="Main">Main
                                                        </option>
                                                        <option <?php if ($value == "Home_fax") echo 'selected' ?>
                                                                value="Home_fax">Home fax
                                                        </option>
                                                        <option <?php if ($value == "Work_fax") echo 'selected' ?>
                                                                value="Work_fax">Work fax
                                                        </option>
                                                        <option <?php if ($value == "Other") echo 'selected' ?>
                                                                value="Other">Other
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        if (count($contact_phone) > 1) {
                                            $i = 0;

                                            foreach ($contact_phone as $ctp) {
                                                //var_dump($ctp);die;
                                                $i++;
                                                if ($i == 1) continue;
                                                $numItems = $i;

                                                $html = '<div class="row _contact_phone _ctp' . $numItems . '">';
                                                $html .= '                        <div class="col-md-6">' .
                                                    '                            <div class="form-group" app-field-wrapper="contact_info[phone][' . $numItems . '][value]">' .
                                                    '                                <input type="text" id="contact_info[phone][' . $numItems . '][value]" name="contact_info[phone][' . $numItems . '][value]" class="form-control" value="' . $ctp['phone_number'] . '">' .
                                                    '                            </div>' .
                                                    '                        </div>' .
                                                    '                        <div class="col-md-6">' .
                                                    '                            <div style="float:left ;width: 80%" class="form-group" app-field-wrapper="contact_info[phone][' . $numItems . '][type]">' .
                                                    '                                <select data-dropup-auto="false" name="contact_info[phone][' . $numItems . '][type]" id="contact_info[phone][' . $numItems . '][type]"   class="form-control selectpicker" >' .
                                                    '                                    <option ' . ($ctp['phone_type'] == '' ? 'selected' : '') . '  value=""></option>' .
                                                    '                                    <option ' . ($ctp['phone_type'] == 'Work' ? 'selected' : '') . ' value="Work">Work</option>' .
                                                    '                                    <option ' . ($ctp['phone_type'] == 'Home' ? 'selected' : '') . ' value="Home">Home</option>' .
                                                    '                                    <option ' . ($ctp['phone_type'] == 'Mobile' ? 'selected' : '') . ' value="Mobile">Mobile</option>' .
                                                    '                                    <option ' . ($ctp['phone_type'] == 'Main' ? 'selected' : '') . ' value="Main">Main</option>' .
                                                    '                                    <option ' . ($ctp['phone_type'] == 'Home_fax' ? 'selected' : '') . ' value="Home_fax">Home fax</option>' .
                                                    '                                    <option ' . ($ctp['phone_type'] == 'Work_fax' ? 'selected' : '') . ' value="Work_fax">Work fax</option>' .
                                                    '                                    <option ' . ($ctp['phone_type'] == 'Other' ? 'selected' : '') . ' value="Other">Other</option>' .
                                                    '                                </select>' .
                                                    '                            </div>' .
                                                    '                            <div style="float: left; font-size: 17px; margin-left: 5px">' .
                                                    '                                <a style="cursor: pointer" href="javascript:remove_phone(\'_ctp' . $numItems . '\')">' .
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
                                    </div>
                                    <div class="col-md-4">
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
                                    </div>
                                    <div class="col-md-4">
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
                            <div role="tabpanel" class="tab-pane" id="contact_other_info">

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="" style="text-align: center;">
                                            <?php if (isset($contact)) { ?>
                                                <img src="<?php echo contact_profile_image_url($contact->id, 'thumb'); ?>"
                                                     id="contact-img" class="client-profile-image-small" style="margin-top: 20px">
                                                <?php if (!empty($contact->profile_image)) { ?>
                                                    <a href="#"
                                                       onclick="delete_contact_profile_image(<?php echo $contact->id; ?>); return false;"
                                                       class="text-danger pull-right" id="contact-remove-img"><i
                                                                class="fa fa-remove"></i></a>
                                                <?php } ?>

                                            <?php } ?>



                                        </div>
                                        <div class="">
                                            <div id="contact-profile-image"
                                                 class="form-group<?php if (isset($contact) && !empty($contact->profile_image)) {
                                                     echo ' hide';
                                                 } ?>">
                                                <label for="profile_image"
                                                       class="profile-image"><?php echo _l('client_profile_image'); ?></label>
                                                <input type="file" name="profile_image" class="form-control" id="profile_image">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="row">


                                            <div class="col-md-6" >
                                                <div class="checkbox checkbox-primary">
                                                    <input type="checkbox" name="is_primary"
                                                           id="contact_primary" <?php if ((!isset($contact) && total_rows('tblcontacts', array('is_primary' => 1, 'userid' => $customer_id)) == 0) || (isset($contact) && $contact->is_primary == 1)) {
                                                        echo 'checked';
                                                    }; ?> <?php if ((isset($contact) && total_rows('tblcontacts', array('is_primary' => 1, 'userid' => $customer_id)) == 1 && $contact->is_primary == 1)) {
                                                        echo 'disabled';
                                                    } ?>>
                                                    <label for="contact_primary">
                                                        <?php echo _l('contact_primary'); ?>
                                                    </label>
                                                </div>
                                                <?php if (!isset($contact) && total_rows('tblemailtemplates', array('slug' => 'new-client-created', 'active' => 0)) == 0) { ?>
                                                    <div class="checkbox checkbox-primary">
                                                        <input type="checkbox" name="donotsendwelcomeemail" id="donotsendwelcomeemail">
                                                        <label for="donotsendwelcomeemail">
                                                            <?php echo _l('client_do_not_send_welcome_email'); ?>
                                                        </label>
                                                    </div>
                                                <?php } ?>
                                                <?php if (total_rows('tblemailtemplates', array('slug' => 'contact-set-password', 'active' => 0)) == 0) { ?>
                                                    <div class="checkbox checkbox-primary">
                                                        <input type="checkbox" name="send_set_password_email"
                                                               id="send_set_password_email">
                                                        <label for="send_set_password_email">
                                                            <?php echo _l('client_send_set_password_email'); ?>
                                                        </label>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="fake-autofill-field hide" name="fakeusernameremembered" value=''
                                                       tabindex="-1"/>
                                                <input type="password" class="fake-autofill-field hide" name="fakepasswordremembered"
                                                       value='' tabindex="-1"/>

                                                <div class="client_password_set_wrapper form-group">
                                                    <label for="password" class="control-label">
                                                        <?php echo _l('client_password'); ?>
                                                    </label>
                                                    <div class="input-group">

                                                        <input type="password" class="form-control password" name="password"
                                                               autocomplete="false">
                                                        <span class="input-group-addon">
                                                <a href="#password" class="show_password"
                                                   onclick="showPassword('password'); return false;"><i
                                                            class="fa fa-eye"></i></a>
                                                </span>
                                                        <span class="input-group-addon">
                                                    <a href="#" class="generate_password"
                                                       onclick="generatePassword(this);return false;"><i class="fa fa-refresh"></i></a>
                                                </span>
                                                    </div>
                                                    <?php if (isset($contact)) { ?>
                                                        <p class="text-muted">
                                                            <?php echo _l('client_password_change_populate_note'); ?>
                                                        </p>
                                                        <?php if ($contact->last_password_change != NULL) {
                                                            echo _l('client_password_last_changed');
                                                            echo '<span class="text-has-action" data-toggle="tooltip" data-title="' . _dt($contact->last_password_change) . '"> ' . time_ago($contact->last_password_change) . '</span>';
                                                        }
                                                    } ?>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-4" >
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group contact-direction-option">
                                                    <label for="direction"><?php echo _l('document_direction'); ?></label>
                                                    <select class="selectpicker"
                                                            data-none-selected-text="<?php echo _l('system_default_string'); ?>"
                                                            data-width="100%" name="direction" id="direction">
                                                        <option value="" <?php if (isset($contact) && empty($contact->direction)) {
                                                            echo 'selected';
                                                        } ?>></option>
                                                        <option value="ltr" <?php if (isset($contact) && $contact->direction == 'ltr') {
                                                            echo 'selected';
                                                        } ?>>LTR
                                                        </option>
                                                        <option value="rtl" <?php if (isset($contact) && $contact->direction == 'rtl') {
                                                            echo 'selected';
                                                        } ?>>RTL
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <hr/>
                                <div class="row">
                                    <div class="col-md-4">
                                            <p class="bold"><?php echo _l('customer_permissions'); ?></p>
                                            <p class="text-danger"><?php echo _l('contact_permissions_info'); ?></p>
                                            <?php
                                            $default_contact_permissions = array();
                                            if (!isset($contact)) {
                                                $default_contact_permissions = @unserialize(get_option('default_contact_permissions'));
                                            }
                                            ?>

                                            <?php foreach ($customer_permissions as $permission) { ?>
                                                <div class="col-md-12 row">
                                                    <div class="row">
                                                        <div class="col-md-6 mtop10 border-right">
                                                            <span><?php echo $permission['name']; ?></span>
                                                        </div>
                                                        <div class="col-md-6 mtop10">
                                                            <div class="onoffswitch">
                                                                <input type="checkbox" id="<?php echo $permission['id']; ?>"
                                                                       class="onoffswitch-checkbox" <?php if (isset($contact) && has_contact_permission($permission['short_name'], $contact->id) || is_array($default_contact_permissions) && in_array($permission['id'], $default_contact_permissions)) {
                                                                    echo 'checked';
                                                                } ?> value="<?php echo $permission['id']; ?>" name="permissions[]">
                                                                <label class="onoffswitch-label"
                                                                       for="<?php echo $permission['id']; ?>"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            <?php } ?>
                                    </div>

                                    <div class="col-md-4">
                                        <p class="bold"><?php echo _l('email_notifications'); ?><?php if (is_sms_trigger_active()) {
                                                echo '/SMS';
                                            } ?></p>
                                        <div id="contact_email_notifications">
                                            <div class="col-md-6 row">
                                                <div class="row">
                                                    <div class="col-md-6 mtop10 border-right">
                                                        <span><?php echo _l('invoice'); ?></span>
                                                    </div>
                                                    <div class="col-md-6 mtop10">
                                                        <div class="onoffswitch">
                                                            <input type="checkbox" id="invoice_emails" data-perm-id="1"
                                                                   class="onoffswitch-checkbox" <?php if (isset($contact) && $contact->invoice_emails == '1') {
                                                                echo 'checked';
                                                            } ?> value="invoice_emails" name="invoice_emails">
                                                            <label class="onoffswitch-label" for="invoice_emails"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 row">
                                                <div class="row">
                                                    <div class="col-md-6 mtop10 border-right">
                                                        <span><?php echo _l('estimate'); ?></span>
                                                    </div>
                                                    <div class="col-md-6 mtop10">
                                                        <div class="onoffswitch">
                                                            <input type="checkbox" id="estimate_emails" data-perm-id="2"
                                                                   class="onoffswitch-checkbox" <?php if (isset($contact) && $contact->estimate_emails == '1') {
                                                                echo 'checked';
                                                            } ?> value="estimate_emails" name="estimate_emails">
                                                            <label class="onoffswitch-label" for="estimate_emails"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 row">
                                                <div class="row">
                                                    <div class="col-md-6 mtop10 border-right">
                                                        <span><?php echo _l('credit_note'); ?></span>
                                                    </div>
                                                    <div class="col-md-6 mtop10">
                                                        <div class="onoffswitch">
                                                            <input type="checkbox" id="credit_note_emails" data-perm-id="1"
                                                                   class="onoffswitch-checkbox" <?php if (isset($contact) && $contact->credit_note_emails == '1') {
                                                                echo 'checked';
                                                            } ?> value="credit_note_emails" name="credit_note_emails">
                                                            <label class="onoffswitch-label" for="credit_note_emails"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 row">
                                                <div class="row">
                                                    <div class="col-md-6 mtop10 border-right">
                                                        <span><?php echo _l('project'); ?></span>
                                                    </div>
                                                    <div class="col-md-6 mtop10">
                                                        <div class="onoffswitch">
                                                            <input type="checkbox" id="project_emails" data-perm-id="6"
                                                                   class="onoffswitch-checkbox" <?php if (isset($contact) && $contact->project_emails == '1') {
                                                                echo 'checked';
                                                            } ?> value="project_emails" name="project_emails">
                                                            <label class="onoffswitch-label" for="project_emails"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 row">
                                                <div class="row">
                                                    <div class="col-md-6 mtop10 border-right">
                                                        <span><?php echo _l('tickets'); ?></span>
                                                    </div>
                                                    <div class="col-md-6 mtop10">
                                                        <div class="onoffswitch">
                                                            <input type="checkbox" id="ticket_emails" data-perm-id="5"
                                                                   class="onoffswitch-checkbox" <?php if (isset($contact) && $contact->ticket_emails == '1') {
                                                                echo 'checked';
                                                            } ?> value="ticket_emails" name="ticket_emails">
                                                            <label class="onoffswitch-label" for="ticket_emails"></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mtop10 border-right">
                                                <span><i class="fa fa-question-circle" data-toggle="tooltip"
                                                         data-title="<?php echo _l('only_project_tasks'); ?>"></i> <?php echo _l('task'); ?></span>
                                                    </div>
                                                    <div class="col-md-6 mtop10">
                                                        <div class="onoffswitch">
                                                            <input type="checkbox" id="task_emails" data-perm-id="6"
                                                                   class="onoffswitch-checkbox" <?php if (isset($contact) && $contact->task_emails == '1') {
                                                                echo 'checked';
                                                            } ?> value="task_emails" name="task_emails">
                                                            <label class="onoffswitch-label" for="task_emails"></label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-md-6 row">
                                                <div class="row">
                                                    <div class="col-md-6 mtop10 border-right">
                                                        <span><?php echo _l('contract'); ?></span>
                                                    </div>
                                                    <div class="col-md-6 mtop10">
                                                        <div class="onoffswitch">
                                                            <input type="checkbox" id="contract_emails" data-perm-id="3"
                                                                   class="onoffswitch-checkbox" <?php if (isset($contact) && $contact->contract_emails == '1') {
                                                                echo 'checked';
                                                            } ?> value="contract_emails" name="contract_emails">
                                                            <label class="onoffswitch-label" for="contract_emails"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" data-loading-text="<?php echo _l('wait_text'); ?>"
                        autocomplete="off" data-form="#contact-form"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php if (!isset($contact)) { ?>
    <script>
        $(function () {
            // Guess auto email notifications based on the default contact permissios
            var permInputs = $('input[name="permissions[]"]');
            $.each(permInputs, function (i, input) {
                input = $(input);
                if (input.prop('checked') === true) {
                    $('#contact_email_notifications [data-perm-id="' + input.val() + '"]').prop('checked', true);
                }
            });


        });
    </script>
<?php } ?>

<script>
    console.log('call contact modal');
    init_tags_inputs();
</script>

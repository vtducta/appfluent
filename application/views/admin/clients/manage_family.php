<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_filters _hidden_inputs hidden">
                    <?php
                    echo form_hidden('my_customers');
                    foreach($groups as $group){
                       echo form_hidden('customer_group_'.$group['id']);
                   }
                   foreach($contract_types as $type){
                       echo form_hidden('contract_type_'.$type['id']);
                   }
                   foreach($invoice_statuses as $status){
                       echo form_hidden('invoices_'.$status);
                   }
                   foreach($estimate_statuses as $status){
                       echo form_hidden('estimates_'.$status);
                   }
                   foreach($project_statuses as $status){
                    echo form_hidden('projects_'.$status['id']);
                }
                foreach($proposal_statuses as $status){
                    echo form_hidden('proposals_'.$status);
                }
                foreach($customer_admins as $cadmin){
                    echo form_hidden('responsible_admin_'.$cadmin['staff_id']);
                }


                ?>
            </div>
            <div class="panel_s">
                <div class="panel-body">

                    <div class="_buttons hide">
                        <?php if (has_permission('customers','','create')) { ?>
                        <a href="<?php echo admin_url('client_families/client'); ?>" class="btn btn-info mright5 test pull-left display-block">
                            <?php echo _l('new_client_family'); ?></a>
                            <a href="<?php echo admin_url('client_families/import'); ?>" class="btn btn-info pull-left display-block mright5 hidden-xs">
                                <?php echo _l('import_clientfamilies'); ?></a>
                                <?php } ?>
                                <a  href="<?php echo admin_url('client_families/all_contacts'); ?>" class="btn btn-info pull-left hide mright5">
                                    <?php echo _l('customer_contacts'); ?></a>
                                    <div class="visible-xs">
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="btn-group pull-right btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-filter" aria-hidden="true"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-left" style="width:300px;">
                                            <li class="active"><a href="#" data-cview="all" onclick="dt_custom_view('','.table-clients',''); return false;"><?php echo _l('customers_sort_all'); ?></a>
                                            </li>
                                             <li class="divider"></li>
                                             <li>
                                                  <a href="#" data-cview="my_customers" onclick="dt_custom_view('my_customers','.table-clients','my_customers'); return false;">
                                                           <?php echo _l('customers_assigned_to_me'); ?>
                                                        </a>
                                             </li>
                                            <li class="divider"></li>
                                            <?php if(count($groups) > 0){ ?>
                                            <li class="dropdown-submenu pull-left groups">
                                                <a href="#" tabindex="-1"><?php echo _l('customer_groups'); ?></a>
                                                <ul class="dropdown-menu dropdown-menu-left">
                                                    <?php foreach($groups as $group){ ?>
                                                    <li><a href="#" data-cview="customer_group_<?php echo $group['id']; ?>" onclick="dt_custom_view('customer_group_<?php echo $group['id']; ?>','.table-clients','customer_group_<?php echo $group['id']; ?>'); return false;"><?php echo $group['name']; ?></a></li>
                                                    <?php } ?>
                                                </ul>
                                            </li>
                                            <div class="clearfix"></div>
                                            <li class="divider"></li>
                                            <?php } ?>
                                            <li class="dropdown-submenu pull-left invoice">
                                                <a href="#" tabindex="-1"><?php echo _l('invoices'); ?></a>
                                                <ul class="dropdown-menu dropdown-menu-left">
                                                    <?php foreach($invoice_statuses as $status){ ?>
                                                    <li>
                                                        <a href="#" data-cview="invoices_<?php echo $status; ?>" data-cview="1" onclick="dt_custom_view('invoices_<?php echo $status; ?>','.table-clients','invoices_<?php echo $status; ?>'); return false;"><?php echo _l('customer_have_invoices_by',format_invoice_status($status,'',false)); ?></a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </li>
                                            <div class="clearfix"></div>
                                            <li class="divider"></li>
                                            <li class="dropdown-submenu pull-left estimate">
                                                <a href="#" tabindex="-1"><?php echo _l('estimates'); ?></a>
                                                <ul class="dropdown-menu dropdown-menu-left">
                                                    <?php foreach($estimate_statuses as $status){ ?>
                                                    <li>
                                                        <a href="#" data-cview="estimates_<?php echo $status; ?>" onclick="dt_custom_view('estimates_<?php echo $status; ?>','.table-clients','estimates_<?php echo $status; ?>'); return false;">
                                                            <?php echo _l('customer_have_estimates_by',format_estimate_status($status,'',false)); ?>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </li>
                                            <div class="clearfix"></div>
                                            <li class="divider"></li>
                                            <li class="dropdown-submenu pull-left project">
                                                <a href="#" tabindex="-1"><?php echo _l('projects'); ?></a>
                                                <ul class="dropdown-menu dropdown-menu-left">
                                                    <?php foreach($project_statuses as $status){ ?>
                                                    <li>
                                                        <a href="#" data-cview="projects_<?php echo $status['id']; ?>" onclick="dt_custom_view('projects_<?php echo $status['id']; ?>','.table-clients','projects_<?php echo $status['id']; ?>'); return false;">
                                                            <?php echo _l('customer_have_projects_by',$status['name']); ?>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </li>
                                            <div class="clearfix"></div>
                                            <li class="divider"></li>
                                            <li class="dropdown-submenu pull-left proposal">
                                                <a href="#" tabindex="-1"><?php echo _l('proposals'); ?></a>
                                                <ul class="dropdown-menu dropdown-menu-left">
                                                    <?php foreach($proposal_statuses as $status){ ?>
                                                    <li>
                                                        <a href="#" data-cview="proposals_<?php echo $status; ?>" onclick="dt_custom_view('proposals_<?php echo $status; ?>','.table-clients','proposals_<?php echo $status; ?>'); return false;">
                                                            <?php echo _l('customer_have_proposals_by',format_proposal_status($status,'',false)); ?>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </li>
                                            <div class="clearfix"></div>
                                            <?php if(count($contract_types) > 0) { ?>
                                            <li class="divider"></li>
                                            <li class="dropdown-submenu pull-left contract_types">
                                                <a href="#" tabindex="-1"><?php echo _l('contract_types'); ?></a>
                                                <ul class="dropdown-menu dropdown-menu-left">
                                                    <?php foreach($contract_types as $type){ ?>
                                                    <li>
                                                        <a href="#" data-cview="contract_type_<?php echo $type['id']; ?>" onclick="dt_custom_view('contract_type_<?php echo $type['id']; ?>','.table-clients','contract_type_<?php echo $type['id']; ?>'); return false;">
                                                            <?php echo _l('customer_have_contracts_by_type',$type['name']); ?>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </li>
                                            <?php } ?>
                                            <?php if(count($customer_admins) > 0 && (has_permission('customers','','create') || has_permission('customers','','edit'))){ ?>
                                            <div class="clearfix"></div>
                                            <li class="divider"></li>
                                            <li class="dropdown-submenu pull-left responsible_admin">
                                                <a href="#" tabindex="-1"><?php echo _l('responsible_admin'); ?></a>
                                                <ul class="dropdown-menu dropdown-menu-left">
                                                    <?php foreach($customer_admins as $cadmin){ ?>
                                                    <li>
                                                        <a href="#" data-cview="responsible_admin_<?php echo $cadmin['staff_id']; ?>" onclick="dt_custom_view('responsible_admin_<?php echo $cadmin['staff_id']; ?>','.table-clients','responsible_admin_<?php echo $cadmin['staff_id']; ?>'); return false;">
                                                            <?php echo get_staff_full_name($cadmin['staff_id']); ?>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                    <div class="row">
                        <div class="col-md-3" style="float:left">
                            <h3 class="text-success no-margin"><?php echo _l('customer_contacts'); ?></h3>
                        </div>

                        <div class="col-md-9 dt-buttons btn-group" >
                            <a class="btn btn-default buttons-collection btn-default-dt-options" style="float: right; margin-left: 10px" tabindex="0"  href="javascript:show_hide_filter();">
                                <span><?php echo _l('show_hide_filter'); ?></span>
                            </a>
                            <?php if (has_permission('customers','','create')) { ?>
                                <a class="btn btn-default buttons-collection btn-default-dt-options" style="float: right;" tabindex="0"  href="<?php echo admin_url('client_families/client'); ?>">
                                    <span><?php echo _l('new_client_family'); ?></span>
                                </a>
                            <?php }  ?>
                        </div>
                    </div>


                                <div class="clearfix"></div>
                                <?php if(has_permission('customers','','view') || have_assigned_customers()) {
                                    $where_summary = '';
                                    if(!has_permission('customers','','view')){
                                        $where_summary = ' AND userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id='.get_staff_user_id().')';
                                    }
                                    $where_contacts_client = ' AND userid IN (SELECT userid FROM tblclients WHERE is_client =1 )';
                                    ?>
                                    <hr class="hr-panel-heading hide" />
                                    <div class="row mbot15 hide">
                                        <div class="col-md-12">
                                            <h3 class="text-success no-margin"><?php echo _l('clientfamilies_summary'); ?></h3>
                                        </div>
                                        <div class="col-md-2 col-xs-6 border-right">
                                            <h3 class="bold"><?php echo total_rows('tblclients','is_client=1' . $where_summary);  ?></h3>
                                            <span class="text-dark"><?php echo _l('customers_summary_total'); ?></span>
                                        </div>
                                        <div class="col-md-2 col-xs-6 border-right">
                                            <h3 class="bold"><?php echo total_rows('tblclients','is_client=1 AND active=1'.$where_summary); ?></h3>
                                            <span class="text-success"><?php echo _l('active_customers'); ?></span>
                                        </div>
                                        <div class="col-md-2 col-xs-6 border-right">
                                            <h3 class="bold"><?php echo total_rows('tblclients','is_client=1 AND active=0'.$where_summary); ?></h3>
                                            <span class="text-danger"><?php echo _l('inactive_active_customers'); ?></span>
                                        </div>
                                        <div class="col-md-2 col-xs-6 border-right">
                                            <h3 class="bold"><?php echo total_rows('tblcontacts','active=1'.$where_summary.$where_contacts_client); ?></h3>
                                            <span class="text-info"><?php echo _l('customers_summary_active'); ?></span>
                                        </div>
                                        <div class="col-md-2  col-xs-6 border-right">
                                            <h3 class="bold"><?php echo total_rows('tblcontacts','active=0'.$where_summary.$where_contacts_client); ?></h3>
                                            <span class="text-danger"><?php echo _l('customers_summary_inactive'); ?></span>
                                        </div>
                                        <div class="col-md-2 col-xs-6">
                                            <h3 class="bold"><?php echo total_rows('tblcontacts','last_login LIKE "'.date('Y-m-d').'%"'.$where_summary.$where_contacts_client); ?></h3>
                                            <span class="text-muted">
                                                <?php if(count($contacts_logged_in_today)> 0){
                                                   $contactsTemplate = '';
                                                   foreach($contacts_logged_in_today as $contact){
                                                    $url = admin_url('client_families/client/'.$contact['userid'].'?contactid='.$contact['id']);
                                                    $fullName = $contact['firstname'] . ' ' . $contact['lastname'];
                                                    $dateLoggedIn = _dt($contact['last_login']);
                                                    $html = "<a href='$url' target='_blank'>$fullName</a><br /><small>$dateLoggedIn</small><br />";
                                                    $contactsTemplate .= htmlspecialchars('<p class="mbot5">'.$html.'</p>');
                                                }
                                                ?>
                                                <i class="fa fa-user pointer" data-toggle="popover" data-title="<?php echo _l('customers_summary_logged_in_today'); ?>" data-html="true" data-content="<?php echo $contactsTemplate; ?>">
                                                </i>
                                                <?php } ?>
                                                <?php echo _l('customers_summary_logged_in_today'); ?></span>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <hr class="hr-panel-heading" />
                                        <a href="#" data-toggle="modal" data-target="#customers_bulk_action" class="bulk-actions-btn table-btn hide " data-table=".table-clients"><?php echo _l('delete'); ?></a>
                                        <a href="#" data-toggle="modal" data-target="#customers_bulk_update" class="bulk-actions-btn2 table-btn hide" data-table=".table-clients"><?php echo _l('bulk_update'); ?></a>
                                        <?php if(count($list_campaign)) { ?>
                                        <a href="#" data-toggle="modal" data-target="#customers_campaign_action" class="campaign-actions-btn table-btn hide" data-table=".table-clients">
                                            <?php echo _l('campaign_actions'); ?>
                                        </a>
                                        <?php } ?>

                    <div class="modal fade bulk_update" id="customers_bulk_update" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title"><?php echo _l('bulk_update'); ?></h4>
                                </div>
                                <div class="modal-body">
                                    <?php if(has_permission('customers','','edit')){
                                        if(count($bulk_update_fields)> 0) {
                                            ?>
                                            <label for="default_language"
                                                   class="control-label"><?php echo _l('select_data_field'); ?>
                                            </label>
                                            <div class="form-group">
                                                <?php
                                                $select = '';
                                                $select = '<select name="select_data_field" id="select_data_field" class="form-control selectpicker _bulk_update_select_field" data-none-selected-text="">';
                                                $select = $select . '<option value=""></option>';
                                                foreach ($bulk_update_fields as $item) {
                                                        $select = $select . '<option value="'.$item['name'].'|'.$item['type'].'|'.$item['data_type'].'|'.$item['id'].'">'.$item['name'].'</option>';
                                                }
                                                $select = $select . '</select>';
                                                echo $select;

                                                ?>
                                            </div>
                                            <?php
                                            echo render_input('bulk_update_string_value', 'value', '','','','','_bulk_update_type_string _bulk_update_input hide','');
                                            echo render_input('bulk_update_number_value', '', '','number','','','_bulk_update_type_number _bulk_update_input hide','');
                                            echo render_date_input('bulk_update_date_value', 'value', '','','','_bulk_update_type_date _bulk_update_input hide','');
                                            echo render_datetime_input('bulk_update_datetime_value', 'value', '','','','_bulk_update_type_datetime _bulk_update_input hide','');

                                            ?>
                                        <?php }
                                    } ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                    <a href="#" class="btn btn-info" onclick="contact_bulk_update(this); return false;"><?php echo _l('confirm'); ?></a>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                                        <div class="modal fade bulk_actions" id="customers_bulk_action" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                             <div class="modal-content">
                                              <div class="modal-header">
                                               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                               <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                           </div>
                                                   <div class="modal-body">
                                                          <?php if(has_permission('customers','','delete')){ ?>
                                                          <div class="checkbox checkbox-danger">
                                                            <input type="checkbox" name="mass_delete" id="mass_delete">
                                                            <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                                        </div>
                                                        <?php } ?>
                                                   </div>
                                               <div class="modal-footer">
                                                   <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                                   <a href="#" class="btn btn-info" onclick="customers_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                               </div>
                                            </div><!-- /.modal-content -->
                                            </div><!-- /.modal-dialog -->
                                        </div><!-- /.modal -->

                    <div class="modal fade campaign_actions" id="customers_campaign_action" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title"><?php echo _l('campaign_actions'); ?></h4>
                                </div>
                                <div class="modal-body">
                                    <div id="bulk_change">
                                        <?php if(count($list_campaign)) echo render_select('add_campaign_contact_bulk[]',$list_campaign,array('id','subject'),'list_campaign','', array('multiple'=>true),array(),'','',false); ?>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                    <a href="#" class="btn btn-info" onclick="add_campaign_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <div class="checkbox hide" >
                                <input type="checkbox" checked id="exclude_inactive" name="exclude_inactive">
                                <label for="exclude_inactive"><?php echo _l('exclude_inactive'); ?> <?php echo _l('clientfamilies'); ?></label>
                            </div>
                           <div class="clearfix mtop20 hide"></div>
                            <div class="col-md-12 _div_list" >
                           <?php
                           $table_data = array();
                           $_table_data = array(
                            '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="clients"><label></label></div>',
                            //'#',

                            _l('contact_primary_family'),
                            _l('clientfamilies_list_company'),
                            _l('contact_tags_family'),
                            _l('company_primary_email_family'),
                            _l('clients_list_phone'),
                            //_l('customer_active'),
                            //_l('customer_groups'),
                            );

                           foreach($_table_data as $_t){
                            array_push($table_data,$_t);
                        }

                        $custom_fields = get_custom_fields('contacts',array('show_on_table'=>1));
                        foreach($custom_fields as $field){
                            array_push($table_data,$field['name']);
                        }

                        $table_data = do_action('customers_table_columns',$table_data);

                        //$_op = _l('options');

                        //array_push($table_data, $_op);
                        render_datatable($table_data,'clients');
                        ?>
                            </div>
                    <div class="_filter_special hide" style="height: 600px ; overflow: auto">
                      <p class="text-dark text-uppercase">
                            <?php echo _l( 'contact_filter'); ?>
                          <a href="#" onclick="clear_filter();" style="float: right;">clear</a>
                      </p>
                        <div class="form-group">
                            <label for="default_language" class="control-label"><?php echo _l('filter_tags'); ?>
                            </label>
                            <select name="filter_select_tags" id="filter_select_tags" class="form-control selectpicker _select_filter_contact _select_filter" data-none-selected-text="" >
                                <option value=""></option>
                                <option value="EQUALS">contain</option>
                                <option value="NOTEQUALS">not contain</option>
                            </select>
                        </div>
                            <?php
                                echo render_input('filter_values_tags', '', '','','','','','_input_filter_contact');
                            ?>

                        <?php if(count($contacts_structure)> 0) {
                            $filterTemplate = '';
                            foreach ($contacts_structure as $item) {
                        ?>
                                <div class="form-group">
                                <label for="default_language" class="control-label"><?php echo _l('filter_' . $item->name); ?>
                                </label>
                                <?php
                                $select ='';
                                if(strpos('varchar',strtolower($item->type))!==false){
                                   $select ='<select name="filter_select[' . $item->name;
                                   $select = $select . ']" id="filter_select[';
                                   $select = $select . $item->name . ']" class="form-control selectpicker _select_filter_contact _select_filter" data-none-selected-text="">';
                                   $select = $select . '<option value=""></option>';
                                   $select = $select . '<option value="EQUALS">is</option>';
                                   $select = $select . '<option value="NOTEQUALS">isn’t</option>';
                                   $select = $select . '<option value="LIKE">any</option>';
                                   $select = $select . '</select>';
                                }
                                echo $select;
                                ?>
                                </div>
                        <?php
                                echo render_input('filter_values['.$item->name.']', '', '','','','','','_input_filter_contact');
                            }
                        }
                        ?>

                        <?php if(count($contacts_custom_field_structure)> 0) {
                            foreach ($contacts_custom_field_structure as $item) {
                                ?>

                                    <?php
                                        $select ='';
                                        if(strpos(strtolower($item['type']),'input')!==false
                                            || strpos(strtolower($item['type']),'textarea')!==false
                                            || strpos(strtolower($item['type']),'multiselect')!==false
                                            || strpos(strtolower($item['type']),'select')!==false
                                            || strpos(strtolower($item['type']),'checkbox')!==false){
                                    ?>
                                            <div class="form-group">
                                            <label for="default_language" class="control-label"><?php echo $item['name']; ?>
                                            </label>
                                    <?php
                                            $select ='<select name="filter_custom_string_select[' . $item['id'];
                                            $select = $select . ']" id="filter_custom_string_select[';
                                            $select = $select . $item['id'] . ']" class="form-control selectpicker _select_filter_contact _select_filter" data-none-selected-text="">';
                                            $select = $select . '<option value=""></option>';
                                            $select = $select . '<option value="EQUALS">is</option>';
                                            $select = $select . '<option value="NOTEQUALS">isn’t</option>';
                                            $select = $select . '<option value="LIKE">any</option>';
                                            $select = $select . '</select>';
                                            echo $select;

                                    ?>
                                            </div>
                                    <?php
                                            echo render_input('filter_custom_string_values['.$item['id'].']', '', '','','','','','_input_filter_contact');
                                        }
                                if(strpos(strtolower($item['type']),'number')!==false){
                                ?>
                                    <div class="form-group">
                                    <label for="default_language" class="control-label"><?php echo $item['name']; ?>
                                    </label>

                                <?php
                                    $select ='<select name="filter_custom_number_select[' . $item['id'];
                                    $select = $select . ']" id="filter_custom_number_select[';
                                    $select = $select . $item['id'] . ']" class="form-control selectpicker _select_filter_number_contact _select_filter" data-none-selected-text="" onchange=onchageFilterNumber(this,'.$item['id'].')>';
                                    $select = $select . '<option value="" selected></option>';
                                    $select = $select . '<option value="IS_GREATER_THAN">greater than</option>';
                                    $select = $select . '<option value="IS_LESS_THAN">less than</option>';
                                    $select = $select . '<option value="BETWEEN">between</option>';
                                    $select = $select . '</select>';
                                    echo $select;
                                ?>
                                    </div>
                                <?php
                                    echo render_input('filter_custom_number_values['.$item['id'].']', '', '','number','','','','_input_filter_contact fcnc_'.$item['id']);
                                    echo render_input('filter_custom_number_min_values['.$item['id'].']', '', '','number',array('placeholder'=>'Min Value'),'','','hide _input_filter_contact fcnc_min_'.$item['id']);
                                    echo render_input('filter_custom_number_max_values['.$item['id'].']', '', '','number',array('placeholder'=>'Max Value'),'','','hide _input_filter_contact fcnc_max_'.$item['id']);
                                }
                                if(strpos(strtolower($item['type']),'date_picker_time')!==false ||
                                    strpos(strtolower($item['type']),'date_picker')!==false  ){
                                    ?>
                                    <div class="form-group">
                                        <label for="default_language" class="control-label"><?php echo $item['name']; ?>
                                        </label>

                                        <?php
                                        $select ='<select name="filter_custom_time_select[' . $item['id'];
                                        $select = $select . ']" id="filter_custom_time_select[';
                                        $select = $select . $item['id'] . ']" class="form-control selectpicker _select_filter_time_contact _select_filter" data-none-selected-text="" onchange=onchageFilterTime(this,'.$item['id'].')>';
                                        $select = $select . '<option value="" selected></option>';
                                        $select = $select . '<option value="ON">on</option>';
                                        $select = $select . '<option value="AFTER">after</option>';
                                        $select = $select . '<option value="BEFORE">before</option>';
                                        $select = $select . '<option value="BETWEEN">between</option>';
                                        $select = $select . '</select>';
                                        echo $select;
                                        ?>
                                    </div>
                                    <?php
                                    if(strpos(strtolower($item['type']),'date_picker_time')!==false){
                                        echo render_datetime_input('filter_custom_time_values['.$item['id'].']', '', '','','','_input_filter_contact fcdtc_'.$item['id'],'_dt_filter_contact');
                                        echo render_datetime_input('filter_custom_time_min_values['.$item['id'].']', '', '','','','hide _input_filter_contact fcdtc_min_'.$item['id'],'_dt_filter_contact');
                                        echo render_datetime_input('filter_custom_time_max_values['.$item['id'].']', '', '','','','hide _input_filter_contact fcdtc_max_'.$item['id'],'_dt_filter_contact');
                                    }else{
                                        echo render_date_input('filter_custom_time_values['.$item['id'].']', '', '','','','_input_filter_contact fcdtc_'.$item['id'],'_dt_filter_contact');
                                        echo render_date_input('filter_custom_time_min_values['.$item['id'].']', '', '','','','hide _input_filter_contact fcdtc_min_'.$item['id'],'_dt_filter_contact');
                                        echo render_date_input('filter_custom_time_max_values['.$item['id'].']', '', '','','','hide _input_filter_contact fcdtc_max_'.$item['id'],'_dt_filter_contact');
                                    }

                                }
                            }
                        }
                        ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var CustomersServerParams = {};
    $.each($('._hidden_inputs._filters input'),function(){
       CustomersServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
   });
    CustomersServerParams['exclude_inactive'] = '[name="exclude_inactive"]:checked';

    $.each($('._filter_special input'),function(){
        CustomersServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
    });

    $.each($('._filter_special select'),function(){
        CustomersServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
    });


    var headers_clients = $('.table-clients').find('th');
    var not_sortable_clients = (headers_clients.length - 1);
    var tAPI = initDataTable('.table-clients', admin_url+'client_families/table', [not_sortable_clients,0], [not_sortable_clients,0], CustomersServerParams,<?php echo do_action('customers_table_default_order',json_encode(array(2,'ASC'))); ?>);
    $('input[name="exclude_inactive"]').on('change',function(){
        tAPI.ajax.reload();
    });
    $('._bulk_update_select_field').on('change',function(){
        fieldInfo = ($('select[name="select_data_field"]').val());
        if(fieldInfo==""){
            $("._bulk_update_input").addClass('hide');
            return;
        }
        arrFieldInfo = fieldInfo.split("\|");
        fieldType = arrFieldInfo[1];
        dataType = arrFieldInfo[2];
        if(fieldType=="standard"|| fieldType=="related_to_tags"|| fieldType=="related_to_company" ){
            $("._bulk_update_input").addClass('hide');
            $("._bulk_update_type_string").removeClass('hide');
        }else if (fieldType=="custome_field_contacts" ){
            if(dataType=="input"){
                $("._bulk_update_input").addClass('hide');
                $("._bulk_update_type_string").removeClass('hide');
            } else if (dataType=="number"){
                $("._bulk_update_input").addClass('hide');
                $("._bulk_update_type_number").removeClass('hide');
            }else if (dataType=="date_picker"){
                $("._bulk_update_input").addClass('hide');
                $("._bulk_update_type_date").removeClass('hide');
            } else if (dataType=="date_picker_time"){
                $("._bulk_update_input").addClass('hide');
                $("._bulk_update_type_datetime").removeClass('hide');
            }else {
                $("._bulk_update_input").addClass('hide');
            }
        }  else {
            $("._bulk_update_input").addClass('hide');
        }
    });

    $('._select_filter_contact').on('change',function(){
        tAPI.ajax.reload();
    });
    function clear_filter() {
        location.reload();

    };
    function onchageFilterNumber(select,id){
        select_val = $(select).val();
        if(select_val == 'BETWEEN' ){
            $('.fcnc_'+id).addClass('hide');
            $('.fcnc_min_'+id).removeClass("hide");
            $('.fcnc_max_'+id).removeClass("hide");
        }else
        {
            $('.fcnc_'+id).removeClass('hide');
            $('.fcnc_min_'+id).addClass("hide");
            $('.fcnc_max_'+id).addClass("hide");
        }
       tAPI.ajax.reload();
    };

    function onchageFilterTime(select,id){
        select_val = $(select).val();
        if(select_val == 'BETWEEN' ){
            $('.fcdtc_'+id).addClass('hide');
            $('.fcdtc_min_'+id).removeClass("hide");
            $('.fcdtc_max_'+id).removeClass("hide");
        }else
        {
            $('.fcdtc_'+id).removeClass('hide');
            $('.fcdtc_min_'+id).addClass("hide");
            $('.fcdtc_max_'+id).addClass("hide");
        }
        tAPI.ajax.reload();
    };

    $('._input_filter_contact').on('keyup',function(){
        tAPI.ajax.reload();
    });
    $('._input_filter_contact').on('blur',function(){
        tAPI.ajax.reload();
    });
    $('._dt_filter_contact').on('blur',function(){
        tAPI.ajax.reload();
    });
    function customers_bulk_action(event) {
        var r = confirm(appLang.confirm_action_prompt);
        if (r == false) {
            return false;
        } else {
            var mass_delete = $('#mass_delete').prop('checked');
            var ids = [];
            var data = {};
            if(mass_delete == false || typeof(mass_delete) == 'undefined'){
                data.groups = $('select[name="move_to_groups_customers_bulk[]"]').selectpicker('val');
                if (data.groups.length == 0) {
                    data.groups = 'remove_all';
                }
            } else {
                data.mass_delete = true;
            }
            var rows = $('.table-clients').find('tbody tr');
            $.each(rows, function() {
                var checkbox = $($(this).find('td').eq(0)).find('input');
                if (checkbox.prop('checked') == true) {
                    ids.push(checkbox.val());
                }
            });
            data.ids = ids;
            $(event).addClass('disabled');
            setTimeout(function(){
              $.post(admin_url + 'client_families/bulk_action', data).done(function() {
               window.location.reload();
           });
          },50);
        }
    }
    function getBulkUpdateValue() {
        if(!$('._bulk_update_type_string').hasClass('hide')){
            return $('input[name="bulk_update_string_value"]').val();
        }
        if(!$('._bulk_update_type_number').hasClass('hide')){
            return $('input[name="bulk_update_number_value"]').val();
        }
        if(!$('._bulk_update_type_date').hasClass('hide')){
            return $('input[name="bulk_update_date_value"]').val();
        }
        if(!$('._bulk_update_type_datetime').hasClass('hide')){
            return $('input[name="bulk_update_datetime_value"]').val();
        }
        return "";
    }
    function contact_bulk_update(event) {

            var ids = [];
            var data = {};

            var rows = $('.table-clients').find('tbody tr');
            $.each(rows, function() {
                var checkbox = $($(this).find('td').eq(0)).find('input');
                if (checkbox.prop('checked') == true) {
                    ids.push(checkbox.val());
                }
            });
            data.ids = ids;
            data.field = $('select[name="select_data_field"]').val();
            data.value = getBulkUpdateValue();
            console.log(data);
            $(event).addClass('disabled');
            setTimeout(function(){
                $.post(admin_url + 'client_families/bulk_update', data).done(function() {
                    window.location.reload();
                });
            },50);

    }

    function add_campaign_bulk_action(event) {
        var r = confirm(appLang.confirm_action_prompt);
        if (r == false) {
            return false;
        } else {
            var ids = [];
            var data = {};
            data.campaigns = $('select[name="add_campaign_contact_bulk[]"]').selectpicker('val');
            var rows = $('.table-clients').find('tbody tr');
            $.each(rows, function() {
                var checkbox = $($(this).find('td').eq(0)).find('input');
                if (checkbox.prop('checked') == true) {
                    ids.push(checkbox.val());
                }
            });
            data.ids = ids;
            $(event).addClass('disabled');
            setTimeout(function(){
                $.post(admin_url + 'client_families/add_campaign_bulk_action', data).done(function() {
                    window.location.reload();
                });
            },50);
        }
    }

    function show_hide_filter() {
        if($('._filter_special').hasClass('col-md-3')){
            $('._filter_special').removeClass('col-md-3');
            $('._filter_special').addClass('hide');
            $('._div_list').removeClass('col-md-9');
            $('._div_list').addClass('col-md-12');
        }else{
            $('._filter_special').removeClass('hide');
            $('._filter_special').addClass('col-md-3');
            $('._div_list').removeClass('col-md-12');
            $('._div_list').addClass('col-md-9');
        }
    }
</script>
</body>
</html>

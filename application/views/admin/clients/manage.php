<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_filters _hidden_inputs hidden">
                    <?php
                    echo form_hidden('my_customers');
                    echo form_hidden('requires_registration_confirmation');
                    foreach ($groups as $group) {
                        echo form_hidden('customer_group_' . $group['id']);
                    }
                    foreach ($contract_types as $type) {
                        echo form_hidden('contract_type_' . $type['id']);
                    }
                    foreach ($invoice_statuses as $status) {
                        echo form_hidden('invoices_' . $status);
                    }
                    foreach ($estimate_statuses as $status) {
                        echo form_hidden('estimates_' . $status);
                    }
                    foreach ($project_statuses as $status) {
                        echo form_hidden('projects_' . $status['id']);
                    }
                    foreach ($proposal_statuses as $status) {
                        echo form_hidden('proposals_' . $status);
                    }
                    foreach ($customer_admins as $cadmin) {
                        echo form_hidden('responsible_admin_' . $cadmin['staff_id']);
                    }
                    foreach ($countries as $country) {
                        echo form_hidden('country_' . $country['country_id']);
                    }
                    ?>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if (has_permission('customers', '', 'create')) { ?>
                                <a href="<?php echo admin_url('clients/client'); ?>"
                                   class="btn btn-info mright5 test pull-left display-block">
                                    <?php echo _l('new_client'); ?></a>
                                <a href="<?php echo admin_url('clients/import'); ?>"
                                   class="btn btn-info pull-left display-block mright5 hidden-xs">
                                    <?php echo _l('import_customers'); ?></a>
                            <?php } ?>
                            <a href="<?php echo admin_url('client_families'); ?>"
                               class="btn btn-info pull-left display-block mright5">
                                <?php echo _l('customer_contacts'); ?></a>
                            <div class="visible-xs">
                                <div class="clearfix"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-5">
                                    <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip"
                                       data-title="<?php echo _l('clients_summary'); ?>" data-placement="bottom"
                                       onclick="slideToggle('.clients-overview'); return false;"><i
                                                class="fa fa-bar-chart"></i></a>
                                    <a href="javascript:show_hide_filter();" class="btn btn-default btn-with-tooltip mleft10" data-toggle="tooltip"
                                       data-title="<?php echo 'show/hide filter'; ?>" data-placement="bottom"
                                       ><i class="fa fa-filter"></i></a>

                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row  hide clients-overview">
                            <?php if (has_permission('customers', '', 'view') || have_assigned_customers()) {
                                $where_summary = '';
                                if (!has_permission('customers', '', 'view')) {
                                    $where_summary = ' AND is_client=0 AND userid IN (SELECT customer_id FROM tblcustomeradmins WHERE  staff_id=' . get_staff_user_id() . ')';
                                }
                                ?>
                                <hr class="hr-panel-heading"/>
                                <div class="row mbot15 mleft15">
                                    <div class="col-md-12">
                                        <h4 class="no-margin"><?php echo _l('customers_summary'); ?></h4>
                                    </div>
                                    <div class="col-md-2 col-xs-6 border-right">
                                        <h3 class="bold"><?php echo total_rows('tblclients', ($where_summary != '' ? substr($where_summary, 5) : ' is_client=0')); ?></h3>
                                        <span class="text-dark"><?php echo _l('customers_summary_total'); ?></span>
                                    </div>
                                    <div class="col-md-2 col-xs-6 border-right">
                                        <h3 class="bold"><?php echo total_rows('tblclients', 'active=1 and is_client=0' . $where_summary); ?></h3>
                                        <span class="text-success"><?php echo _l('active_customers'); ?></span>
                                    </div>
                                    <div class="col-md-2 col-xs-6 border-right">
                                        <h3 class="bold"><?php echo total_rows('tblclients', 'is_client=0 and active=0' . $where_summary); ?></h3>
                                        <span class="text-danger"><?php echo _l('inactive_active_customers'); ?></span>
                                    </div>
                                    <div class="col-md-2 col-xs-6 border-right">
                                        <h3 class="bold"><?php echo total_rows('tblcontacts', 'userid in (select userid from tblclients where is_client=0) and active=1' . $where_summary); ?></h3>
                                        <span class="text-info"><?php echo _l('customers_summary_active'); ?></span>
                                    </div>
                                    <div class="col-md-2  col-xs-6 border-right">
                                        <h3 class="bold"><?php echo total_rows('tblcontacts', 'userid in (select userid from tblclients where is_client=0) and active=0' . $where_summary); ?></h3>
                                        <span class="text-danger"><?php echo _l('customers_summary_inactive'); ?></span>
                                    </div>
                                    <div class="col-md-2 col-xs-6">
                                        <h3 class="bold"><?php echo total_rows('tblcontacts', 'userid in (select userid from tblclients where is_client=0) and last_login LIKE "' . date('Y-m-d') . '%"' . $where_summary); ?></h3>
                                        <span class="text-muted">
                                                <?php
                                                $contactsTemplate = '';
                                                if (count($contacts_logged_in_today) > 0) {
                                                    foreach ($contacts_logged_in_today as $contact) {
                                                        $url = admin_url('client_families/client/' . $contact['userid'] . '/' . $contact['id']);
                                                        $fullName = $contact['firstname'] . ' ' . $contact['lastname'];
                                                        $dateLoggedIn = _dt($contact['last_login']);
                                                        $html = "<a href='$url' target='_blank'>$fullName</a><br /><small>$dateLoggedIn</small><br />";
                                                        $contactsTemplate .= htmlspecialchars('<p class="mbot5">' . $html . '</p>');
                                                    }
                                                    ?>
                                                <?php } ?>
                                            <span<?php if ($contactsTemplate != '') { ?> class="pointer text-has-action" data-toggle="popover" data-title="<?php echo _l('customers_summary_logged_in_today'); ?>" data-html="true" data-content="<?php echo $contactsTemplate; ?>" data-placement="bottom" <?php } ?>><?php echo _l('customers_summary_logged_in_today'); ?></span>
                                            </span>
                                    </div>
                                </div>
                            <?php } ?>
                            <hr class="hr-panel-heading"/>
                        </div>

                        <a href="#" data-toggle="modal" data-target="#customers_bulk_action"
                           class="bulk-actions-btn table-btn hide"
                           data-table=".table-clients"><?php echo _l('bulk_actions'); ?></a>
                        <div class="modal fade bulk_actions" id="customers_bulk_action" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php if (has_permission('customers', '', 'delete')) { ?>
                                            <div class="checkbox checkbox-danger">
                                                <input type="checkbox" name="mass_delete" id="mass_delete">
                                                <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                            </div>
                                            <hr class="mass_delete_separator"/>
                                        <?php } ?>
                                        <div id="bulk_change">
                                            <?php echo render_select('move_to_groups_customers_bulk[]', $groups, array('id', 'name'), 'customer_groups', '', array('multiple' => true), array(), '', '', false); ?>
                                            <p class="text-danger"><?php echo _l('bulk_action_customers_groups_warning'); ?></p>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default"
                                                data-dismiss="modal"><?php echo _l('close'); ?></button>
                                        <a href="#" class="btn btn-info"
                                           onclick="customers_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                        <div class="checkbox">
                            <input type="checkbox" checked id="exclude_inactive" name="exclude_inactive">
                            <label for="exclude_inactive"><?php echo _l('exclude_inactive'); ?><?php echo _l('clients'); ?></label>
                        </div>
                        <div class="clearfix mtop20"></div>
                        <div class="col-md-12 _div_list" >
                        <?php
                        $table_data = array();
                        $_table_data = array(
                            '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="clients"><label></label></div>',
                            _l('the_number_sign'),
                            _l('contact_primary'),
                            _l('company_primary_email'),
                            _l('clients_list_company'),
                            _l('clients_list_phone'),
//                            _l('customer_active'),
//                            _l('customer_groups'),
                            _l('date_created'),
                        );

                        foreach ($_table_data as $_t) {
                            array_push($table_data, $_t);
                        }

                        $custom_fields = get_custom_fields('customers', array('show_on_table' => 1));
                        foreach ($custom_fields as $field) {
                            array_push($table_data, $field['name']);
                        }

                        $table_data = do_action('customers_table_columns', $table_data);

                        render_datatable($table_data, 'clients', [], [
                            'data-last-order-identifier' => 'customers',
                            'data-default-order' => get_table_last_order('customers'),
                        ]);
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
    $(function () {
        var CustomersServerParams = {};
        $.each($('._hidden_inputs._filters input'), function () {
            CustomersServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
        });
        CustomersServerParams['exclude_inactive'] = '[name="exclude_inactive"]:checked';

        var tAPI = initDataTable('.table-clients', admin_url + 'clients/table', [0], [0], CustomersServerParams,<?php echo do_action('customers_table_default_order', json_encode(array(2, 'asc'))); ?>);
        $('input[name="exclude_inactive"]').on('change', function () {
            tAPI.ajax.reload();
        });
    });

    function customers_bulk_action(event) {
        var r = confirm(appLang.confirm_action_prompt);
        if (r == false) {
            return false;
        } else {
            var mass_delete = $('#mass_delete').prop('checked');
            var ids = [];
            var data = {};
            if (mass_delete == false || typeof(mass_delete) == 'undefined') {
                data.groups = $('select[name="move_to_groups_customers_bulk[]"]').selectpicker('val');
                if (data.groups.length == 0) {
                    data.groups = 'remove_all';
                }
            } else {
                data.mass_delete = true;
            }
            var rows = $('.table-clients').find('tbody tr');
            $.each(rows, function () {
                var checkbox = $($(this).find('td').eq(0)).find('input');
                if (checkbox.prop('checked') == true) {
                    ids.push(checkbox.val());
                }
            });
            data.ids = ids;
            $(event).addClass('disabled');
            setTimeout(function () {
                $.post(admin_url + 'clients/bulk_action', data).done(function () {
                    window.location.reload();
                });
            }, 50);
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

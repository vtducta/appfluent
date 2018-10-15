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


                    <div class="row">
                        <div class="col-md-3" style="float:left">
                            <h3 class="text-success no-margin"><?php echo _l('Policies'); ?></h3>
                        </div>

                        <div class="col-md-9 dt-buttons btn-group" >
                            <a class="btn btn-default buttons-collection btn-default-dt-options" style="float: right; margin-left: 10px" tabindex="0"  href="javascript:show_hide_filter();">
                                <span><?php echo _l('show_hide_filter'); ?></span>
                            </a>
                            <?php if (has_permission('customers','','create')) { ?>
                                <a class="btn btn-default buttons-collection btn-default-dt-options" style="float: right;" tabindex="0"  href="<?php echo admin_url('policies/policy'); ?>">
                                    <span><?php echo _l('new_policy'); ?></span>
                                </a>
                            <?php }  ?>
                        </div>
                    </div>


                                <div class="clearfix"></div>

                                        <hr class="hr-panel-heading" />
                                        <a href="#" data-toggle="modal" data-target="#customers_bulk_action" class="bulk-actions-btn table-btn hide " data-table=".table-clients"><?php echo _l('delete'); ?></a>
                                        <a href="#" data-toggle="modal" data-target="#customers_bulk_update" class="bulk-actions-btn2 table-btn hide" data-table=".table-clients"><?php echo _l('bulk_update'); ?></a>


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
                                    <a href="#" class="btn btn-info" onclick="policy_bulk_update(this); return false;"><?php echo _l('confirm'); ?></a>
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



                    <div class="checkbox hide" >
                                <input type="checkbox" checked id="exclude_inactive" name="exclude_inactive">
                                <label for="exclude_inactive"><?php echo _l('exclude_inactive'); ?> <?php echo _l('clientfamilies'); ?></label>
                            </div>
                           <div class="clearfix mtop20 hide"></div>
                            <div class="col-md-12 _div_list" >
                           <?php
                           $table_data = array();
                           $_table_data = array(
                            '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="policies"><label></label></div>',
                            //'#',

                            _l('policy_name'),
                            _l('policy_contact'),
                            _l('policy_tags'),
                            _l('policy_email'),
                            _l('policy_phone'),
                            _l('policy_owner'),
                            //_l('customer_groups'),
                            );

                           foreach($_table_data as $_t){
                            array_push($table_data,$_t);
                        }

                        $custom_fields = get_custom_fields('polices',array('show_on_table'=>1));
                        foreach($custom_fields as $field){
                            array_push($table_data,$field['name']);
                        }

                        $table_data = do_action('customers_table_columns',$table_data);

                        //$_op = _l('options');

                        //array_push($table_data, $_op);
                        render_datatable($table_data,'policies');
                        ?>
                            </div>
                    <div class="_filter_special hide" style="height: 600px ; overflow: auto">
                      <p class="text-dark text-uppercase">
                            <?php echo _l( 'policy_filter'); ?>
                          <a href="#" onclick="clear_filter();" style="float: right;">clear</a>
                      </p>
                        <div class="form-group">
                            <label for="default_language" class="control-label"><?php echo _l('filter_tags'); ?>
                            </label>
                            <select name="filter_select_tags" id="filter_select_tags" class="form-control selectpicker _select_filter_policy _select_filter" data-none-selected-text="" >
                                <option value=""></option>
                                <option value="EQUALS">contain</option>
                                <option value="NOTEQUALS">not contain</option>
                            </select>
                        </div>
                            <?php
                                echo render_input('filter_values_tags', '', '','','','','','_input_filter_policy');
                            ?>

                        <?php if(count($policies_structure)> 0) {
                            $filterTemplate = '';
                            foreach ($policies_structure as $item) {
                        ?>
                                <div class="form-group">
                                <label for="default_language" class="control-label"><?php echo _l('filter_' . $item->name); ?>
                                </label>
                                <?php
                                $select ='';
                                if(strpos('varchar',strtolower($item->type))!==false){
                                   $select ='<select name="filter_select[' . $item->name;
                                   $select = $select . ']" id="filter_select[';
                                   $select = $select . $item->name . ']" class="form-control selectpicker _select_filter_policy _select_filter" data-none-selected-text="">';
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
                                echo render_input('filter_values['.$item->name.']', '', '','','','','','_input_filter_policy');
                            }
                        }
                        ?>

                        <?php if(count($policies_custom_field_structure)> 0) {
                            foreach ($policies_custom_field_structure as $item) {
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
                                            $select = $select . $item['id'] . ']" class="form-control selectpicker _select_filter_policy _select_filter" data-none-selected-text="">';
                                            $select = $select . '<option value=""></option>';
                                            $select = $select . '<option value="EQUALS">is</option>';
                                            $select = $select . '<option value="NOTEQUALS">isn’t</option>';
                                            $select = $select . '<option value="LIKE">any</option>';
                                            $select = $select . '</select>';
                                            echo $select;

                                    ?>
                                            </div>
                                    <?php
                                            echo render_input('filter_custom_string_values['.$item['id'].']', '', '','','','','','_input_filter_policy');
                                        }
                                if(strpos(strtolower($item['type']),'number')!==false){
                                ?>
                                    <div class="form-group">
                                    <label for="default_language" class="control-label"><?php echo $item['name']; ?>
                                    </label>

                                <?php
                                    $select ='<select name="filter_custom_number_select[' . $item['id'];
                                    $select = $select . ']" id="filter_custom_number_select[';
                                    $select = $select . $item['id'] . ']" class="form-control selectpicker _select_filter_number_policy _select_filter" data-none-selected-text="" onchange=onchageFilterNumber(this,'.$item['id'].')>';
                                    $select = $select . '<option value="" selected></option>';
                                    $select = $select . '<option value="IS_GREATER_THAN">greater than</option>';
                                    $select = $select . '<option value="IS_LESS_THAN">less than</option>';
                                    $select = $select . '<option value="BETWEEN">between</option>';
                                    $select = $select . '</select>';
                                    echo $select;
                                ?>
                                    </div>
                                <?php
                                    echo render_input('filter_custom_number_values['.$item['id'].']', '', '','number','','','','_input_filter_policy fcnc_'.$item['id']);
                                    echo render_input('filter_custom_number_min_values['.$item['id'].']', '', '','number',array('placeholder'=>'Min Value'),'','','hide _input_filter_policy fcnc_min_'.$item['id']);
                                    echo render_input('filter_custom_number_max_values['.$item['id'].']', '', '','number',array('placeholder'=>'Max Value'),'','','hide _input_filter_policy fcnc_max_'.$item['id']);
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
                                        $select = $select . $item['id'] . ']" class="form-control selectpicker _select_filter_time_policy _select_filter" data-none-selected-text="" onchange=onchageFilterTime(this,'.$item['id'].')>';
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
                                        echo render_datetime_input('filter_custom_time_values['.$item['id'].']', '', '','','','_input_filter_policy fcdtc_'.$item['id'],'_dt_filter_policy');
                                        echo render_datetime_input('filter_custom_time_min_values['.$item['id'].']', '', '','','','hide _input_filter_policy fcdtc_min_'.$item['id'],'_dt_filter_policy');
                                        echo render_datetime_input('filter_custom_time_max_values['.$item['id'].']', '', '','','','hide _input_filter_policy fcdtc_max_'.$item['id'],'_dt_filter_policy');
                                    }else{
                                        echo render_date_input('filter_custom_time_values['.$item['id'].']', '', '','','','_input_filter_policy fcdtc_'.$item['id'],'_dt_filter_policy');
                                        echo render_date_input('filter_custom_time_min_values['.$item['id'].']', '', '','','','hide _input_filter_policy fcdtc_min_'.$item['id'],'_dt_filter_policy');
                                        echo render_date_input('filter_custom_time_max_values['.$item['id'].']', '', '','','','hide _input_filter_policy fcdtc_max_'.$item['id'],'_dt_filter_policy');
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


    var headers_clients = $('.table-policies').find('th');
    var not_sortable_clients = (headers_clients.length - 1);
    var tAPI = initDataTable('.table-policies', admin_url+'policies/table', [not_sortable_clients,0], [not_sortable_clients,0], CustomersServerParams,<?php echo do_action('customers_table_default_order',json_encode(array(1,'ASC'))); ?>);
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
        }else if (fieldType=="custom_field_policies" ){
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

    $('._select_filter_policy').on('change',function(){
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

    $('._input_filter_policy').on('keyup',function(){
        tAPI.ajax.reload();
    });
    $('._input_filter_policy').on('blur',function(){
        tAPI.ajax.reload();
    });
    $('._dt_filter_policy').on('blur',function(){
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
              $.post(admin_url + 'policies/bulk_action', data).done(function() {
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
    function policy_bulk_update(event) {

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
                $.post(admin_url + 'policies/bulk_update', data).done(function() {
                    window.location.reload();
                });
            },50);

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

<?php
defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('customers', '', 'delete');

//$custom_fields = get_table_custom_fields('customers');
$custom_fields = get_table_custom_fields('policies');

$aColumns = array(
    'tblpolicies.id as policy_id',
    'ifnull(tblclients.company,\'\') as company',
    'CONCAT(tblpolicies.firstname, " ", tblpolicies.lastname) as policy_fullname',
    'tblpolicies.email as email',
    'tblpolicies.phonenumber as phonenumber',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM tbltags_in JOIN tbltags ON tbltags_in.tag_id = tbltags.id WHERE rel_id = tblpolicies.id and rel_type="policy" ORDER by tag_order ASC) as tags',
    'tblpolicies.contact_id as contact_id' ,
    'tblpolicies.userid as userid',
    'tblcontacts.userid as contact_userid',
    'tblpolicies.addedfrom as addedfrom',
    'CONCAT(tblcontacts.firstname, " ", tblcontacts.lastname) as contact_fullname'
    );

$sIndexColumn = "id";
$sTable       = 'tblpolicies';
$where   = array();
//array_push($where, 'AND tblclients.is_client=1');
// Add blank where all filter can be stored
$filter  = array();

$filter_contact = '';
if($this->_instance->input->post('firstname')){
    $filter_contact= $filter_contact . 'AND tblpolicies.firstname=\''.$this->_instance->input->post('firstname').'\'';
}
$join = array('LEFT JOIN tblcontacts ON tblcontacts.id=tblpolicies.contact_id ' ,
    'LEFT JOIN tblclients ON tblclients.userid=tblpolicies.userid ');


foreach ($custom_fields as $key => $field) {
    $selectAs = 'cvalue_'.$key;
    if(is_cf_date($field)){
        $selectAs = 'date_picker_cvalue_' . $key ;
    }else if (is_related_to_contact($field)){
        $selectAs = 'related_to_contact_cvalue_' . $key ;
    }else if (is_related_to_company($field)){
        $selectAs = 'related_to_company_cvalue_' . $key ;
    }
    //$selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_'.$key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_'.$key . ' ON tblpolicies.id = ctable_'.$key . '.relid AND ctable_'.$key . '.fieldto="'.$field['fieldto'].'" AND ctable_'.$key . '.fieldid='.$field['id']);
}

// Filter by custom groups


if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

//if (!has_permission('customers', '', 'view')) {
//    array_push($where, 'AND tblclients.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id=' . get_staff_user_id() . ')');
//}

if($this->_instance->input->post('exclude_inactive')){
    array_push($where,'AND tblpolicies.active=1');
}

if($this->_instance->input->post('filter_select_tags')){
    $compareType = $this->_instance->input->post('filter_select_tags');
    $compareValue = $this->_instance->input->post('filter_values_tags');
    if($compareValue) {
        $arrValue = explode(',', $compareValue);
        $compareValue = "('" . implode("','", $arrValue) . "')";

        if ($compareType == 'EQUALS') {
            array_push($where, 'AND tblpolicies.id in (select tbltags_in.rel_id from tbltags_in left join  tbltags on tbltags_in.tag_id = tbltags.id   where tbltags_in.rel_type=\'policy\'  and tbltags.name in ' . $compareValue . ' )');
        } elseif ($compareType == 'NOTEQUALS') {
            array_push($where, 'AND tblpolicies.id not in (select tbltags_in.rel_id from tbltags_in left join  tbltags on tbltags_in.tag_id = tbltags.id   where tbltags_in.rel_type=\'policy\'  and tbltags.name in ' . $compareValue . ' )');
        }
    }
}

if($this->_instance->input->post('filter_select[firstname]')){
    $compareType = $this->_instance->input->post('filter_select[firstname]');

    $compareValue = $this->_instance->input->post('filter_values[firstname]');
    if($compareType=='EQUALS'){
        array_push($where, 'AND tblpolicies.firstname=\''.$compareValue.'\'');
    }elseif ($compareType=='NOTEQUALS'){
        array_push($where, 'AND tblpolicies.firstname <> \''.$compareValue.'\'');
    }elseif ($compareType=='LIKE'){
        array_push($where, 'AND tblpolicies.firstname like \'%'.$compareValue.'%\'');
    }
}

if($this->_instance->input->post('filter_select[lastname]')){
    $compareType = $this->_instance->input->post('filter_select[lastname]');

    $compareValue = $this->_instance->input->post('filter_values[lastname]');
    if($compareType=='EQUALS'){
        array_push($where, 'AND tblpolicies.lastname=\''.$compareValue.'\'');
    }elseif ($compareType=='NOTEQUALS'){
        array_push($where, 'AND tblpolicies.lastname <> \''.$compareValue.'\'');
    }elseif ($compareType=='LIKE'){
        array_push($where, 'AND tblpolicies.lastname like \'%'.$compareValue.'%\'');
    }
}

if($this->_instance->input->post('filter_custom_string_select')){
    $arrSelectString = $this->_instance->input->post('filter_custom_string_select');
    foreach ($arrSelectString as $key => $value){
        $compareValue = $this->_instance->input->post('filter_custom_string_values['.$key.']');
        if($value=='EQUALS'){
            if($compareValue){
                array_push($where, 'AND tblpolicies.id in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid='.$key.' and value = \''.$compareValue.'\' )');
            }else{
                array_push($where, 'AND tblpolicies.id not in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid='.$key.' )');
            }
        }elseif ($value=='NOTEQUALS'){
            if($compareValue) {
                array_push($where, 'AND ( tblpolicies.id not in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid='.$key.' ) OR tblpolicies.id in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid=' . $key . ' and value <> \'' . $compareValue . '\' ))');
            }else{
                array_push($where, 'AND tblpolicies.id in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid='.$key.' )');
            }
        }elseif ($value=='LIKE'){
            if($compareValue) {
                array_push($where, 'AND tblpolicies.id in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid=' . $key . ' and value like \'%' . $compareValue . '%\' )');
            }
        }
    }
}

if($this->_instance->input->post('filter_custom_number_select')){
    $arrSelectString = $this->_instance->input->post('filter_custom_number_select');
    foreach ($arrSelectString as $key => $value){
        if($value=='IS_GREATER_THAN'){
            $compareValue = $this->_instance->input->post('filter_custom_number_values['.$key.']');
            if($compareValue){
                array_push($where, 'AND tblpolicies.id in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid='.$key.' and value >= '.$compareValue.' )');
            }
        }elseif ($value=='IS_LESS_THAN'){
            $compareValue = $this->_instance->input->post('filter_custom_number_values['.$key.']');
            if($compareValue) {
                array_push($where, 'AND tblpolicies.id in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid='.$key.' and value < '.$compareValue.' )');
            }
        }elseif ($value=='BETWEEN'){
            $compareMinValue = $this->_instance->input->post('filter_custom_number_min_values['.$key.']');
            $compareMaxValue = $this->_instance->input->post('filter_custom_number_max_values['.$key.']');

            if($compareMinValue&&$compareMaxValue) {
                array_push($where, 'AND tblpolicies.id in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid='.$key.' and value >= '.$compareMinValue.' and value <= '.$compareMaxValue.' )');
            }
        }
    }
}

if($this->_instance->input->post('filter_custom_time_select')){
    $arrSelectString = $this->_instance->input->post('filter_custom_time_select');
    foreach ($arrSelectString as $key => $value){
        if($value=='ON'){
            $compareValue = $this->_instance->input->post('filter_custom_time_values['.$key.']');
            if($compareValue){
                array_push($where, 'AND tblpolicies.id in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid='.$key.' and STR_TO_DATE(value,\'%Y-%m-%d %H:%i\') = STR_TO_DATE(\''.$compareValue.'\',\'%Y-%m-%d %H:%i\') )');
            }
        }elseif ($value=='AFTER'){
            $compareValue = $this->_instance->input->post('filter_custom_time_values['.$key.']');
            if($compareValue) {
                array_push($where, 'AND tblpolicies.id in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid='.$key.' and STR_TO_DATE(value,\'%Y-%m-%d %H:%i\') < STR_TO_DATE(\''.$compareValue.'\',\'%Y-%m-%d %H:%i\') )');
            }
        }elseif ($value=='BEFORE'){
            $compareValue = $this->_instance->input->post('filter_custom_time_values['.$key.']');
            if($compareValue) {
                array_push($where, 'AND tblpolicies.id in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid='.$key.' and STR_TO_DATE(value,\'%Y-%m-%d %H:%i\') > STR_TO_DATE(\''.$compareValue.'\',\'%Y-%m-%d %H:%i\') )');
            }
        }
        elseif ($value=='BETWEEN'){
            $compareMinValue = $this->_instance->input->post('filter_custom_time_min_values['.$key.']');
            $compareMaxValue = $this->_instance->input->post('filter_custom_time_max_values['.$key.']');

            if($compareMinValue&&$compareMaxValue) {
                array_push($where, 'AND tblpolicies.id in (select relid from tblcustomfieldsvalues where fieldto=\'policies\' and fieldid='.$key.' and STR_TO_DATE(value,\'%Y-%m-%d %H:%i\') >= STR_TO_DATE(\''.$compareMinValue.'\',\'%Y-%m-%d %H:%i\') and STR_TO_DATE(value,\'%Y-%m-%d %H:%i\') <= STR_TO_DATE(\''.$compareMaxValue.'\',\'%Y-%m-%d %H:%i\') )');
            }
        }
    }
}



// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->_instance->db->query('SET SQL_BIG_SELECTS=1');
}

$aColumns = do_action('customers_table_sql_columns', $aColumns);



$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'tblpolicies.id as policy_id'
));



$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {

    $row = array();

    // Bulk actions
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['policy_id'] . '"><label></label></div>';
    // User id
    //$row[] = $aRow['userid'];


    // Company
    $company = $aRow['company'];

//    if ($company == '') {
//        $company = _l('no_company_view_profile');
//    }

    $src = contact_profile_image_url($aRow['policy_id'],'thumb');
    $image ='<img src="' . $src . '" class="img img-responsive staff-profile-image-small" style="float:left; margin-right:10px">';
    // Primary contact

    $option_link = '<div class="row-options">';
    $option_link .= '<a href="' . 'policies/policy/' .$aRow['policy_id'] . '">' . _l('view') . '</a>';


    if ($hasPermissionDelete) {
        $option_link .= ' | <a href="' . admin_url('policies/delete/' . $aRow['policy_id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }

    $option_link .= '</div>';

    $row[] = ($aRow['policy_id'] ? '<a href="' . admin_url('policies/policy/' .$aRow['policy_id']).'">' .$image. $aRow['policy_fullname'] . '</a>'  . $option_link : '');

    $row[] = ($aRow['contact_fullname'] ? '<a href="client_families/client/'  . $aRow['contact_userid'] . '/'. $aRow['contact_id'] . '">' . $aRow['contact_fullname'] . '</a>' : '');
    //tags
    $row[] = render_tags($aRow['tags']);

    // Primary contact email
    $row[] = ($aRow['email'] ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');

    // Primary contact phone
    $row[] = ($aRow['phonenumber'] ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');

    $row[] = get_staff_full_name($aRow['addedfrom']);

    // Toggle active/inactive customer
    $toggleActive = '<div class="onoffswitch" data-toggle="tooltip" data-title="' . _l('customer_active_inactive_help') . '">
        <input type="checkbox" data-switch-url="' . admin_url().'policies/change_client_status" name="onoffswitch" class="onoffswitch-checkbox" id="' . $aRow['policy_id'] . '" data-id="' . $aRow['policy_id'] . '" ' . ($aRow['tblpolicies.active'] == 1 ? 'checked' : '') . '>
        <label class="onoffswitch-label" for="' . $aRow['policy_id'] . '"></label>
    </div>';

    // For exporting
    $toggleActive .= '<span class="hide">' . ($aRow['tblpolicies.active'] == 1 ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';

    //$row[] = $toggleActive;

    // Custom fields add values
    foreach($customFieldsColumns as $customFieldColumn){
        if(strpos($customFieldColumn, 'date_picker_') !== false){
            $row[] = _d($aRow[$customFieldColumn]);
        }elseif (strpos($customFieldColumn, 'related_to_contact_') !== false){
            $row[] = _contact_name($aRow[$customFieldColumn]);
        }elseif (strpos($customFieldColumn, 'related_to_company_') !== false){
            $row[] = _company_name($aRow[$customFieldColumn]);
        }else{
            $row[] = $aRow[$customFieldColumn];
        }
        //$row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook = do_action('customers_table_row_data', array(
        'output' => $row,
        'row' => $aRow
    ));

    $row = $hook['output'];

    // Table options
    $options = icon_btn('policies/policy/' .$aRow['policy_id'], 'pencil-square-o');

    // Show button delete if permission for delete exists
    if ($hasPermissionDelete) {
        $options .= icon_btn('policies/delete/' . $aRow['policy_id'], 'remove', 'btn-danger _delete', array(
        'data-toggle' => 'tooltip',
        'data-placement' => 'left',
        'title' => _l('policy_delete_tooltip')
        ));
    }

    //$row[] = $options;
    $output['aaData'][] = $row;
}

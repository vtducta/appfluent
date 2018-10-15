<?php
defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('customers', '', 'delete');

//$custom_fields = get_table_custom_fields('customers');
$custom_fields = get_table_custom_fields('contacts');

$aColumns = array(
    '1',
    'tblclients.userid as userid',
    'company',
    'CONCAT(firstname, " ", lastname) as contact_fullname',
    'email',
    'tblcontacts.phonenumber as phonenumber',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM tbltags_in JOIN tbltags ON tbltags_in.tag_id = tbltags.id WHERE rel_id = tblcontacts.id and rel_type="contact" ORDER by tag_order ASC) as tags',
    //'(SELECT GROUP_CONCAT(name ORDER BY name ASC) FROM tblcustomersgroups LEFT JOIN tblcustomergroups_in ON tblcustomergroups_in.groupid = tblcustomersgroups.id WHERE customer_id = tblclients.userid) as groups'
    'tblcontacts.id as contact_id',
    );

$sIndexColumn = "userid";
$sTable       = 'tblclients';
$where   = array();
//array_push($where, 'AND tblclients.is_client=1');
// Add blank where all filter can be stored
$filter  = array();

$filter_contact = '';
if($this->_instance->input->post('firstname')){
    $filter_contact= $filter_contact . 'AND tblcontacts.firstname=\''.$this->_instance->input->post('firstname').'\'';
}
$join = array('JOIN tblcontacts ON tblcontacts.userid=tblclients.userid ' );

//foreach ($custom_fields as $key => $field) {
//    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_'.$key);
//    array_push($customFieldsColumns,$selectAs);
//    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
//    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblclients.userid = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
//}

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
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_'.$key . ' ON tblcontacts.id = ctable_'.$key . '.relid AND ctable_'.$key . '.fieldto="'.$field['fieldto'].'" AND ctable_'.$key . '.fieldid='.$field['id']);
}

// Filter by custom groups
$groups  = $this->_instance->clients_model->get_groups();
$groupIds = array();
foreach ($groups as $group) {
    if ($this->_instance->input->post('customer_group_' . $group['id'])) {
        array_push($groupIds, $group['id']);
    }
}
if (count($groupIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT customer_id FROM tblcustomergroups_in WHERE groupid IN (' . implode(', ', $groupIds) . '))');
}

$this->_instance->load->model('invoices_model');
// Filter by invoices
$invoiceStatusIds = array();
foreach ($this->_instance->invoices_model->get_statuses() as $status) {
    if ($this->_instance->input->post('invoices_' . $status)) {
        array_push($invoiceStatusIds, $status);
    }
}
if (count($invoiceStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT clientid FROM tblinvoices WHERE status IN (' . implode(', ', $invoiceStatusIds) . '))');
}

// Filter by estimates
$estimateStatusIds = array();
$this->_instance->load->model('estimates_model');
foreach ($this->_instance->estimates_model->get_statuses() as $status) {
    if ($this->_instance->input->post('estimates_' . $status)) {
        array_push($estimateStatusIds, $status);
    }
}
if (count($estimateStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT clientid FROM tblestimates WHERE status IN (' . implode(', ', $estimateStatusIds) . '))');
}

// Filter by projects
$projectStatusIds = array();
$this->_instance->load->model('projects_model');
foreach ($this->_instance->projects_model->get_project_statuses() as $status) {
    if ($this->_instance->input->post('projects_' . $status['id'])) {
        array_push($projectStatusIds, $status['id']);
    }
}
if (count($projectStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT clientid FROM tblprojects WHERE status IN (' . implode(', ', $projectStatusIds) . '))');
}

// Filter by proposals
$proposalStatusIds = array();
$this->_instance->load->model('proposals_model');
foreach ($this->_instance->proposals_model->get_statuses() as $status) {
    if ($this->_instance->input->post('proposals_' . $status)) {
        array_push($proposalStatusIds, $status);
    }
}
if (count($proposalStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT rel_id FROM tblproposals WHERE status IN (' . implode(', ', $proposalStatusIds) . ') AND rel_type="customer")');
}

// Filter by having contracts by type
$this->_instance->load->model('contracts_model');
$contractTypesIds = array();
$contract_types  = $this->_instance->contracts_model->get_contract_types();

foreach ($contract_types as $type) {
    if ($this->_instance->input->post('contract_type_' . $type['id'])) {
        array_push($contractTypesIds, $type['id']);
    }
}
if (count($contractTypesIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT client FROM tblcontracts WHERE contract_type IN (' . implode(', ', $contractTypesIds) . '))');
}

// Filter by proposals
$customAdminIds = array();
foreach ($this->_instance->clients_model->get_customers_admin_unique_ids() as $cadmin) {
    if ($this->_instance->input->post('responsible_admin_' . $cadmin['staff_id'])) {
        array_push($customAdminIds, $cadmin['staff_id']);
    }
}

if (count($customAdminIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id IN (' . implode(', ', $customAdminIds) . '))');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if (!has_permission('customers', '', 'view')) {
    array_push($where, 'AND tblclients.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id=' . get_staff_user_id() . ')');
}

if($this->_instance->input->post('exclude_inactive')){
    array_push($where,'AND tblclients.active=1');
}

if($this->_instance->input->post('filter_select_tags')){
    $compareType = $this->_instance->input->post('filter_select_tags');
    $compareValue = $this->_instance->input->post('filter_values_tags');
    if($compareValue) {
        $arrValue = explode(',', $compareValue);
        $compareValue = "('" . implode("','", $arrValue) . "')";

        if ($compareType == 'EQUALS') {
            array_push($where, 'AND tblcontacts.id in (select tbltags_in.rel_id from tbltags_in left join  tbltags on tbltags_in.tag_id = tbltags.id   where tbltags_in.rel_type=\'contact\'  and tbltags.name in ' . $compareValue . ' )');
        } elseif ($compareType == 'NOTEQUALS') {
            array_push($where, 'AND tblcontacts.id not in (select tbltags_in.rel_id from tbltags_in left join  tbltags on tbltags_in.tag_id = tbltags.id   where tbltags_in.rel_type=\'contact\'  and tbltags.name in ' . $compareValue . ' )');
        }
    }
}

if($this->_instance->input->post('filter_select[firstname]')){
    $compareType = $this->_instance->input->post('filter_select[firstname]');

    $compareValue = $this->_instance->input->post('filter_values[firstname]');
    if($compareType=='EQUALS'){
        array_push($where, 'AND tblcontacts.firstname=\''.$compareValue.'\'');
    }elseif ($compareType=='NOTEQUALS'){
        array_push($where, 'AND tblcontacts.firstname <> \''.$compareValue.'\'');
    }elseif ($compareType=='LIKE'){
        array_push($where, 'AND tblcontacts.firstname like \'%'.$compareValue.'%\'');
    }
}

if($this->_instance->input->post('filter_select[lastname]')){
    $compareType = $this->_instance->input->post('filter_select[lastname]');

    $compareValue = $this->_instance->input->post('filter_values[lastname]');
    if($compareType=='EQUALS'){
        array_push($where, 'AND tblcontacts.lastname=\''.$compareValue.'\'');
    }elseif ($compareType=='NOTEQUALS'){
        array_push($where, 'AND tblcontacts.lastname <> \''.$compareValue.'\'');
    }elseif ($compareType=='LIKE'){
        array_push($where, 'AND tblcontacts.lastname like \'%'.$compareValue.'%\'');
    }
}

if($this->_instance->input->post('filter_custom_string_select')){
    $arrSelectString = $this->_instance->input->post('filter_custom_string_select');
    foreach ($arrSelectString as $key => $value){
        $compareValue = $this->_instance->input->post('filter_custom_string_values['.$key.']');
        if($value=='EQUALS'){
            if($compareValue){
                array_push($where, 'AND tblcontacts.id in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid='.$key.' and value = \''.$compareValue.'\' )');
            }else{
                array_push($where, 'AND tblcontacts.id not in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid='.$key.' )');
            }
        }elseif ($value=='NOTEQUALS'){
            if($compareValue) {
                array_push($where, 'AND ( tblcontacts.id not in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid='.$key.' ) OR tblcontacts.id in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid=' . $key . ' and value <> \'' . $compareValue . '\' ))');
            }else{
                array_push($where, 'AND tblcontacts.id in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid='.$key.' )');
            }
        }elseif ($value=='LIKE'){
            if($compareValue) {
                array_push($where, 'AND tblcontacts.id in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid=' . $key . ' and value like \'%' . $compareValue . '%\' )');
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
                array_push($where, 'AND tblcontacts.id in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid='.$key.' and value >= '.$compareValue.' )');
            }
        }elseif ($value=='IS_LESS_THAN'){
            $compareValue = $this->_instance->input->post('filter_custom_number_values['.$key.']');
            if($compareValue) {
                array_push($where, 'AND tblcontacts.id in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid='.$key.' and value < '.$compareValue.' )');
            }
        }elseif ($value=='BETWEEN'){
            $compareMinValue = $this->_instance->input->post('filter_custom_number_min_values['.$key.']');
            $compareMaxValue = $this->_instance->input->post('filter_custom_number_max_values['.$key.']');

            if($compareMinValue&&$compareMaxValue) {
                array_push($where, 'AND tblcontacts.id in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid='.$key.' and value >= '.$compareMinValue.' and value <= '.$compareMaxValue.' )');
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
                array_push($where, 'AND tblcontacts.id in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid='.$key.' and STR_TO_DATE(value,\'%Y-%m-%d %H:%i\') = STR_TO_DATE(\''.$compareValue.'\',\'%Y-%m-%d %H:%i\') )');
            }
        }elseif ($value=='AFTER'){
            $compareValue = $this->_instance->input->post('filter_custom_time_values['.$key.']');
            if($compareValue) {
                array_push($where, 'AND tblcontacts.id in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid='.$key.' and STR_TO_DATE(value,\'%Y-%m-%d %H:%i\') < STR_TO_DATE(\''.$compareValue.'\',\'%Y-%m-%d %H:%i\') )');
            }
        }elseif ($value=='BEFORE'){
            $compareValue = $this->_instance->input->post('filter_custom_time_values['.$key.']');
            if($compareValue) {
                array_push($where, 'AND tblcontacts.id in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid='.$key.' and STR_TO_DATE(value,\'%Y-%m-%d %H:%i\') > STR_TO_DATE(\''.$compareValue.'\',\'%Y-%m-%d %H:%i\') )');
            }
        }
        elseif ($value=='BETWEEN'){
            $compareMinValue = $this->_instance->input->post('filter_custom_time_min_values['.$key.']');
            $compareMaxValue = $this->_instance->input->post('filter_custom_time_max_values['.$key.']');

            if($compareMinValue&&$compareMaxValue) {
                array_push($where, 'AND tblcontacts.id in (select relid from tblcustomfieldsvalues where fieldto=\'contacts\' and fieldid='.$key.' and STR_TO_DATE(value,\'%Y-%m-%d %H:%i\') >= STR_TO_DATE(\''.$compareMinValue.'\',\'%Y-%m-%d %H:%i\') and STR_TO_DATE(value,\'%Y-%m-%d %H:%i\') <= STR_TO_DATE(\''.$compareMaxValue.'\',\'%Y-%m-%d %H:%i\') )');
            }
        }
    }
}

if($this->_instance->input->post('my_customers')){
    array_push($where,'AND tblclients.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id='.get_staff_user_id().')');
}

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->_instance->db->query('SET SQL_BIG_SELECTS=1');
}

$aColumns = do_action('customers_table_sql_columns', $aColumns);



$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'tblcontacts.id as contact_id',
    'tblclients.zip as zip'
));



$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {

    $row = array();

    // Bulk actions
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['contact_id'] . '"><label></label></div>';
    // User id
    //$row[] = $aRow['userid'];


    // Company
    $company = $aRow['company'];

//    if ($company == '') {
//        $company = _l('no_company_view_profile');
//    }

    $src = contact_profile_image_url($aRow['contact_id'],'thumb');
    $image ='<img src="' . $src . '" class="img img-responsive staff-profile-image-small" style="float:left; margin-right:10px">';
    // Primary contact

    $option_link = '<div class="row-options">';
    $option_link .= '<a href="' . 'client_families/client/' . $aRow['userid'].'/'.$aRow['contact_id'] . '">' . _l('view') . '</a>';


    if ($hasPermissionDelete) {
        $option_link .= ' | <a href="' . admin_url('client_families/delete/' . $aRow['contact_id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }

    $option_link .= '</div>';

    $row[] = ($aRow['contact_id'] ? '<a href="' . admin_url('client_families/client/' . $aRow['userid']) . '/'.$aRow['contact_id'].'">' .$image. $aRow['contact_fullname'] . '</a>'  .$option_link: '');

    $row[] = '<a href="">' . $company . '</a>';
    //tags
    $row[] = render_tags($aRow['tags']);

    // Primary contact email
    $row[] = ($aRow['email'] ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');

    // Primary contact phone
    $row[] = ($aRow['phonenumber'] ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');

    // Toggle active/inactive customer
    $toggleActive = '<div class="onoffswitch" data-toggle="tooltip" data-title="' . _l('customer_active_inactive_help') . '">
        <input type="checkbox" data-switch-url="' . admin_url().'client_families/change_client_status" name="onoffswitch" class="onoffswitch-checkbox" id="' . $aRow['userid'] . '" data-id="' . $aRow['userid'] . '" ' . ($aRow['tblclients.active'] == 1 ? 'checked' : '') . '>
        <label class="onoffswitch-label" for="' . $aRow['userid'] . '"></label>
    </div>';

    // For exporting
    $toggleActive .= '<span class="hide">' . ($aRow['tblclients.active'] == 1 ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';

    //$row[] = $toggleActive;

    // Customer groups parsing
//    $groupsRow  = '';
//    if ($aRow['groups']) {
//        $groups = explode(',', $aRow['groups']);
//        foreach ($groups as $group) {
//            $groupsRow .= '<span class="label label-default mleft5 inline-block customer-group-list pointer">' . $group . '</span>';
//        }
//    }

//    $row[] = $groupsRow;

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
    $options = icon_btn('client_families/client/' . $aRow['userid'].'/'.$aRow['contact_id'], 'pencil-square-o');

    // Show button delete if permission for delete exists
    if ($hasPermissionDelete) {
        $options .= icon_btn('client_families/delete/' . $aRow['contact_id'], 'remove', 'btn-danger _delete', array(
        'data-toggle' => 'tooltip',
        'data-placement' => 'left',
        'title' => _l('client_delete_tooltip')
        ));
    }

    //$row[] = $options;
    $output['aaData'][] = $row;
}

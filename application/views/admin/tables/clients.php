<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('customers', '', 'delete');

//$custom_fields = get_table_custom_fields('customers');
$custom_fields = get_table_custom_fields('contacts');
$this->ci->db->query("SET sql_mode = ''");

$aColumns = [
    '1',
    'tblclients.userid as userid',
    'company',
    'firstname',
    'email',
    'tblcontacts.phonenumber as phonenumber',
    'tblclients.active',
    'GROUP_CONCAT(DISTINCT(tblcustomersgroups.name)) as customerGroups',
    'tblclients.datecreated as datecreated',
];

$sIndexColumn = 'userid';
$sTable       = 'tblclients';
$where        = [];
array_push($where, 'AND tblclients.is_client=0');
// Add blank where all filter can be stored
$filter = [];

$join = [
    'LEFT JOIN tblcontacts ON tblcontacts.userid=tblclients.userid AND tblcontacts.is_primary=1',
    'LEFT JOIN tblcustomergroups_in ON tblcustomergroups_in.customer_id = tblclients.userid',
    'LEFT JOIN tblcustomersgroups ON tblcustomersgroups.id = tblcustomergroups_in.groupid',
];

//foreach ($custom_fields as $key => $field) {
//    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
//    array_push($customFieldsColumns, $selectAs);
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
$groups   = $this->ci->clients_model->get_groups();
$groupIds = [];
foreach ($groups as $group) {
    if ($this->ci->input->post('customer_group_' . $group['id'])) {
        array_push($groupIds, $group['id']);
    }
}
if (count($groupIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT customer_id FROM tblcustomergroups_in WHERE groupid IN (' . implode(', ', $groupIds) . '))');
}

$countries  = $this->ci->clients_model->get_clients_distinct_countries();
$countryIds = [];
foreach ($countries as $country) {
    if ($this->ci->input->post('country_' . $country['country_id'])) {
        array_push($countryIds, $country['country_id']);
    }
}
if (count($countryIds) > 0) {
    array_push($filter, 'AND country IN (' . implode(',', $countryIds) . ')');
}


$this->ci->load->model('invoices_model');
// Filter by invoices
$invoiceStatusIds = [];
foreach ($this->ci->invoices_model->get_statuses() as $status) {
    if ($this->ci->input->post('invoices_' . $status)) {
        array_push($invoiceStatusIds, $status);
    }
}
if (count($invoiceStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT clientid FROM tblinvoices WHERE status IN (' . implode(', ', $invoiceStatusIds) . '))');
}

// Filter by estimates
$estimateStatusIds = [];
$this->ci->load->model('estimates_model');
foreach ($this->ci->estimates_model->get_statuses() as $status) {
    if ($this->ci->input->post('estimates_' . $status)) {
        array_push($estimateStatusIds, $status);
    }
}
if (count($estimateStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT clientid FROM tblestimates WHERE status IN (' . implode(', ', $estimateStatusIds) . '))');
}

// Filter by projects
$projectStatusIds = [];
$this->ci->load->model('projects_model');
foreach ($this->ci->projects_model->get_project_statuses() as $status) {
    if ($this->ci->input->post('projects_' . $status['id'])) {
        array_push($projectStatusIds, $status['id']);
    }
}
if (count($projectStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT clientid FROM tblprojects WHERE status IN (' . implode(', ', $projectStatusIds) . '))');
}

// Filter by proposals
$proposalStatusIds = [];
$this->ci->load->model('proposals_model');
foreach ($this->ci->proposals_model->get_statuses() as $status) {
    if ($this->ci->input->post('proposals_' . $status)) {
        array_push($proposalStatusIds, $status);
    }
}
if (count($proposalStatusIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT rel_id FROM tblproposals WHERE status IN (' . implode(', ', $proposalStatusIds) . ') AND rel_type="customer")');
}

// Filter by having contracts by type
$this->ci->load->model('contracts_model');
$contractTypesIds = [];
$contract_types   = $this->ci->contracts_model->get_contract_types();

foreach ($contract_types as $type) {
    if ($this->ci->input->post('contract_type_' . $type['id'])) {
        array_push($contractTypesIds, $type['id']);
    }
}
if (count($contractTypesIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT client FROM tblcontracts WHERE contract_type IN (' . implode(', ', $contractTypesIds) . '))');
}

// Filter by proposals
$customAdminIds = [];
foreach ($this->ci->clients_model->get_customers_admin_unique_ids() as $cadmin) {
    if ($this->ci->input->post('responsible_admin_' . $cadmin['staff_id'])) {
        array_push($customAdminIds, $cadmin['staff_id']);
    }
}

if (count($customAdminIds) > 0) {
    array_push($filter, 'AND tblclients.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id IN (' . implode(', ', $customAdminIds) . '))');
}

if ($this->ci->input->post('requires_registration_confirmation')) {
    array_push($filter, 'AND tblclients.registration_confirmed=0');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if (!has_permission('customers', '', 'view')) {
    array_push($where, 'AND tblclients.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id=' . get_staff_user_id() . ')');
}

if ($this->ci->input->post('exclude_inactive')) {
    array_push($where, 'AND (tblclients.active = 1 OR tblclients.active=0 AND registration_confirmed = 0)');
}

if ($this->ci->input->post('my_customers')) {
    array_push($where, 'AND tblclients.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id=' . get_staff_user_id() . ')');
}

//begin for filter
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

if($this->_instance->input->post('filter_select[middle_name]')){
    $compareType = $this->_instance->input->post('filter_select[middle_name]');

    $compareValue = $this->_instance->input->post('filter_values[middle_name]');
    if($compareType=='EQUALS'){
        array_push($where, 'AND tblcontacts.middle_name=\''.$compareValue.'\'');
    }elseif ($compareType=='NOTEQUALS'){
        array_push($where, 'AND tblcontacts.middle_name <> \''.$compareValue.'\'');
    }elseif ($compareType=='LIKE'){
        array_push($where, 'AND tblcontacts.middle_name like \'%'.$compareValue.'%\'');
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

$aColumns = do_action('customers_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblcontacts.id as contact_id',
    'lastname',
    'middle_name',
    'tblclients.zip as zip',
    'registration_confirmed',
], 'group by tblclients.userid', [7 => 'name']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Bulk actions
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['userid'] . '"><label></label></div>';
    // User id
    $row[] = $aRow['userid'];

    // Company
    $company  = $aRow['company'];
    $isPerson = false;

    if ($company == '') {
        $company  = _l('no_company_view_profile');
        $isPerson = true;
    }

    $url = admin_url('clients/client/' . $aRow['userid']);

//    if ($isPerson && $aRow['contact_id']) {
//        $url .= '?contactid=' . $aRow['contact_id'];
//    }

//    $company = '<a href="' . $url . '">' . $company . '</a>';
//
//    $company .= '<div class="row-options">';
//    $company .= '<a href="' . $url . '">' . _l('view') . '</a>';
//
//    if ($aRow['registration_confirmed'] == 0 && is_admin()) {
//        $company .= ' | <a href="' . admin_url('clients/confirm_registration/' . $aRow['userid']) . '" class="text-success bold">' . _l('confirm_registration') . '</a>';
//    }
//    if (!$isPerson) {
//        $company .= ' | <a href="' . admin_url('clients/client/' . $aRow['userid'] . '?group=contacts') . '">' . _l('customer_contacts') . '</a>';
//    }
//    if ($hasPermissionDelete) {
//        $company .= ' | <a href="' . admin_url('clients/delete/' . $aRow['userid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
//    }
//
//    $company .= '</div>';
//
//    $row[] = $company;

    // Primary contact
    $row[] = ($aRow['contact_id'] ? '<a href="' .admin_url('clients/client/' . $aRow['userid']) . '">' . $aRow['firstname'] . ' ' . $aRow['middle_name'] . ' '. $aRow['lastname'] . '</a>' : '');

    // Primary contact email
    $row[] = ($aRow['email'] ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');


    $company = '<a href="' . $url . '">' . $company . '</a>';

    $company .= '<div class="row-options">';
    $company .= '<a href="' . $url . '">' . _l('view') . '</a>';

    if ($aRow['registration_confirmed'] == 0 && is_admin()) {
        $company .= ' | <a href="' . admin_url('clients/confirm_registration/' . $aRow['userid']) . '" class="text-success bold">' . _l('confirm_registration') . '</a>';
    }
    //if (!$isPerson) {
        $company .= ' | <a href="' . admin_url('clients/client/' . $aRow['userid'] . '?group=contacts') . '">' . _l('customer_contacts') . '</a>';
    //}
    if ($hasPermissionDelete) {
        $company .= ' | <a href="' . admin_url('clients/delete/' . $aRow['userid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }

    $company .= '</div>';

    $row[] = $company;

    // Primary contact phone
    $row[] = ($aRow['phonenumber'] ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');

    // Toggle active/inactive customer
    $toggleActive = '<div class="onoffswitch" data-toggle="tooltip" data-title="' . _l('customer_active_inactive_help') . '">
    <input type="checkbox"' . ($aRow['registration_confirmed'] == 0 ? ' disabled' : '') . ' data-switch-url="' . admin_url() . 'clients/change_client_status" name="onoffswitch" class="onoffswitch-checkbox" id="' . $aRow['userid'] . '" data-id="' . $aRow['userid'] . '" ' . ($aRow['tblclients.active'] == 1 ? 'checked' : '') . '>
    <label class="onoffswitch-label" for="' . $aRow['userid'] . '"></label>
    </div>';

    // For exporting
//    $toggleActive .= '<span class="hide">' . ($aRow['tblclients.active'] == 1 ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
//
//    $row[] = $toggleActive;
//
//    // Customer groups parsing
//    $groupsRow = '';
//    if ($aRow['customerGroups']) {
//        $groups = explode(',', $aRow['customerGroups']);
//        foreach ($groups as $group) {
//            $groupsRow .= '<span class="label label-default mleft5 inline-block customer-group-list pointer">' . $group . '</span>';
//        }
//    }
//
//    $row[] = $groupsRow;

    $row[] = _dt($aRow['datecreated']);

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook = do_action('customers_table_row_data', [
        'output' => $row,
        'row'    => $aRow,
    ]);

    $row = $hook['output'];

    $row['DT_RowClass'] = 'has-row-options';
    if ($aRow['registration_confirmed'] == 0) {
        $row['DT_RowClass'] .= ' alert-info requires-confirmation';
        $row['Data_Title']  = _l('customer_requires_registration_confirmation');
        $row['Data_Toggle'] = 'tooltip';
    }
    $output['aaData'][] = $row;
}

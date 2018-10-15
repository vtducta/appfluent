<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$aColumns     = array(
    'tblcustomtabs.id',
    'tblcustomtabs.name',
    'tblcustomsections.name as custom_section_name',
    'tblcustomtabs.slug',
    'tblcustomtabs.active',
    );
$sIndexColumn = "id";
$sTable       = 'tblcustomtabs';
$join = array('LEFT JOIN tblcustomsections ON tblcustomtabs.custom_section_id = tblcustomsections.id');

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable,$join);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if($i==2){
            $_data = $aRow['custom_section_name'];
        }else{
            $_data = $aRow[$aColumns[$i]];
        }
        if ($aColumns[$i] == 'name' || $aColumns[$i] == 'id') {
            $_data = '<a href="' . admin_url('custom_tabs/tab/' . $aRow['tblcustomtabs.id']) . '">' . $_data . '</a>';
        } else if ($aColumns[$i] == 'active') {
            $checked = '';
            if ($aRow['active'] == 1) {
                $checked = 'checked';
            }
            $_data = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="'.admin_url().'custom_tabs/change_custom_tab_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_'.$aRow['tblcustomtabs.id'].'" data-id="'.$aRow['tblcustomtabs.id'].'" ' . $checked . '>
                <label class="onoffswitch-label" for="c_'.$aRow['tblcustomtabs.id'].'"></label>
            </div>';
                        // For exporting
            $_data .=  '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) .'</span>';
        }

        $row[] = $_data;
    }
    $options = icon_btn('custom_tabs/tab/' . $aRow['tblcustomtabs.id'], 'pencil-square-o');
    $row[]   = $options .= icon_btn('custom_tabs/delete/' . $aRow['tblcustomtabs.id'], 'remove', 'btn-danger _delete');

    $output['aaData'][] = $row;


}

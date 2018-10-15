<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$aColumns     = array(
    'tblcustomfields.id',
    'tblcustomfields.name',
    'tblcustomtabs.name as custom_tab_name',
    'tblcustomfields.fieldto',
    'tblcustomfields.type',
    'tblcustomfields.slug',
    'tblcustomfields.active',

);
$sIndexColumn = "id";
$sTable       = 'tblcustomfields';
$join = array('LEFT JOIN tblcustomtabs ON tblcustomfields.custom_tab_id = tblcustomtabs.id');

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable,$join);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {

    $row = array();


    for ($i = 0; $i < count($aColumns); $i++) {
        if($i==2){
            $_data = $aRow['custom_tab_name'];
        }else{
            $_data = $aRow[$aColumns[$i]];
        }

        if ($aColumns[$i] == 'tblcustomfields.name' || $aColumns[$i] == 'tblcustomfields.id') {
            $_data = '<a href="' . admin_url('custom_fields/field/' . $aRow['tblcustomfields.id']) . '">' . $_data . '</a>';
        } else if ($aColumns[$i] == 'active') {
            $checked = '';
            if ($aRow['tblcustomfields.active'] == 1) {
                $checked = 'checked';
            }
            $_data = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="'.admin_url().'custom_fields/change_custom_field_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_'.$aRow['tblcustomfields.id'].'" data-id="'.$aRow['tblcustomfields.id'].'" ' . $checked . '>
                <label class="onoffswitch-label" for="c_'.$aRow['tblcustomfields.id'].'"></label>
            </div>';
                        // For exporting
            $_data .=  '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) .'</span>';
        }

        $row[] = $_data;

    }
    $options = icon_btn('custom_fields/field/' . $aRow['tblcustomfields.id'], 'pencil-square-o');
    $row[]   = $options .= icon_btn('custom_fields/delete/' . $aRow['tblcustomfields.id'], 'remove', 'btn-danger _delete');

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}

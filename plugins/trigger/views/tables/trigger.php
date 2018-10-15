<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$aColumns     = array(
    'trigger_name',
    'trigger_condition',
    'trigger_value',
    //'campaign_id',
    'camp.subject',
    'condition_name'
);
$sIndexColumn = "id";
$sTable       = 'tbltriggers';
$join[] = 'JOIN tblnewsletter_campaigns as camp ON tbltriggers.campaign_id = camp.id';
$join[] = 'JOIN tbltriggers_conditions as cond ON tbltriggers.trigger_condition = cond.id';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, array(), array(
    'tbltriggers.id'
));

$output       = $result['output'];
$rResult      = $result['rResult'];
$CI = get_instance();
foreach ($rResult as $aRow) {

    $row = array();
    $viewLink = base_url('trigger?type=trigger&id='.$aRow['id']);
    $options = '';
    $options .= icon_btn($viewLink, 'eye', 'btn-default');
    $options .= icon_btn(base_url('trigger/delete?id='.$aRow['id']), 'times', 'btn-danger');
    
    $row[] = "<a href='$viewLink'>".$aRow['trigger_name']."</a>";
    $row[] = $aRow['condition_name'];
    $row[] = $aRow['trigger_value'];
    $row[] = $aRow['subject'];
    $row[] = $options;

//    $viewLink = base_url('newsletter?type=campaign&id='.$aRow['id']);
//    $activeLink = base_url('newsletter?type=campaigns&active='.$aRow['id']. '&status='.$aRow['active']);
//    $row[] = "<a href='$viewLink'>".$aRow['subject']."</a>";
//    $row[] = getChildren($aRow);
//    $info = "<p>".nom('newsletter_name').': '.$aRow['sender_name'].'</p>';
//    $info .= "<p>".nom('newsletter_email').': '.$aRow['sender_email'].'</p>';
//    $row[] = $info;
//    $row[] = $CI->Newsletter_model->getStatistics($aRow['id']);
//    $row[] = "<span class='label inline-block ' style='color:#03a9f4;border:1px solid #03a9f4'>".$aRow['opens']."</span>"; //opens
//    $row[] = "<span class='label inline-block ' style='color:#03a9f4;border:1px solid #03a9f4'>".$aRow['clicks']."</span>"; //clicks
//    $status = ($aRow['status'] == 0) ? "<span class='badge'>".nom('newsletter_draft').'</span>' : "<span class='badge badge-success' style='background-color:#03A9F4'>".nom('newsletter_sent').'</span>';
//    if ($aRow['send_date'] and $aRow['send_date']) {
//        $status = "<span class='badge'>".nom('newsletter_scheduled').'</span>';
//    }
//    $row[] = $status;
//    $row[] = ($aRow['sent_date']) ? $aRow['sent_date'] : nom('newsletter_not_sent_yet');
//    $options = '';
//    if ($aRow['status'] == 0) {
//        $options .= icon_btn(base_url('newsletter?type=campaigns&send='.$aRow['id']), 'paper-plane', 'btn-primary', array('title' => nom('newsletter_send_campaign')));
//    }
//    $options .= icon_btn($viewLink, 'eye', 'btn-default');
//    $options .= icon_btn(base_url('newsletter?type=campaigns&delete='.$aRow['id']), 'times', 'btn-danger');
//    if ($aRow['active'] == 1) {
//        $options .= icon_btn($activeLink, 'check', 'btn-primary', ['title'=> 'Active']);
//    }else {
//        $options .= icon_btn($activeLink, 'ban', 'btn-danger', ['title'=> 'Inactive']);
//    }



    //$row[] = $options;
    /**$options = icon_btn('#' . $aRow['id'], 'pencil-square-o', 'btn-default', array(
        'data-toggle' => 'modal',
        'data-target' => '#currency_modal',
        'data-id' => $aRow['id']
    ));
    if ($aRow['isdefault'] == 0) {
        $options .= icon_btn('currencies/make_base_currency/' . $aRow['id'], 'star', 'btn-info', array(
            'data-toggle' => 'tooltip',
            'title' => _l('make_base_currency')
        ));
    }
    $row[]              = $options .= icon_btn('currencies/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');**/
    $output['aaData'][] = $row;
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$aColumns     = array(
    'subject',
    'sender_name',
    'sender_email',
    'opens',
    'clicks',
    'active',
    'parrent_id',
    'status',
    'sent_date',

);
$sIndexColumn = "id";
$sTable       = 'tblnewsletter_campaigns';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), array(), array(
    'id',
    'template',
    'creator',
    'send_date'
));

$output       = $result['output'];
$rResult      = $result['rResult'];
$CI = get_instance();
foreach ($rResult as $aRow) {
//    $camps = $CI->Newsletter_model->hasChildren($aRow['id']);
//    if(count($camps) <= 0) {
//        continue;
//    }
    $row = array();
    $row[] = $aRow['id'];

    $viewLink = base_url('newsletter?type=campaign&id='.$aRow['id']);
    $activeLink = base_url('newsletter?type=campaigns&active='.$aRow['id']. '&status='.$aRow['active']);
    $row[] = "<a href='$viewLink'>".$aRow['subject']."</a>";
    $row[] = getChildren($aRow);
    $info = "<p>".nom('newsletter_name').': '.$aRow['sender_name'].'</p>';
    $info .= "<p>".nom('newsletter_email').': '.$aRow['sender_email'].'</p>';
    $row[] = $info;
    $row[] = $CI->Newsletter_model->getStatistics($aRow['id']);
    $row[] = "<span class='label inline-block ' style='color:#03a9f4;border:1px solid #03a9f4'>".$aRow['opens']."</span>"; //opens
    $row[] = "<span class='label inline-block ' style='color:#03a9f4;border:1px solid #03a9f4'>".$aRow['clicks']."</span>"; //clicks
    $status = ($aRow['status'] == 0) ? "<span class='badge'>".nom('newsletter_draft').'</span>' : "<span class='badge badge-success' style='background-color:#03A9F4'>".nom('newsletter_sent').'</span>';
    if ($aRow['send_date'] and $aRow['send_date']) {
        $status = "<span class='badge'>".nom('newsletter_scheduled').'</span>';
    }
    $row[] = $status;
    $row[] = ($aRow['sent_date']) ? $aRow['sent_date'] : nom('newsletter_not_sent_yet');
    $options = '';
    if ($aRow['status'] == 0) {
        $options .= icon_btn(base_url('newsletter?type=campaigns&send='.$aRow['id']), 'paper-plane', 'btn-primary', array('title' => nom('newsletter_send_campaign')));
    }
    $options .= icon_btn($viewLink, 'eye', 'btn-default');
    $options .= icon_btn(base_url('newsletter?type=campaigns&delete='.$aRow['id']), 'times', 'btn-danger');
    if ($aRow['active'] == 1) {
        $options .= icon_btn($activeLink, 'check', 'btn-primary', ['title'=> 'Active']);
    }else {
        $options .= icon_btn($activeLink, 'ban', 'btn-danger', ['title'=> 'Inactive']);
    }



    $row[] = $options;
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
/**
 * get children campaigns
 */
function getChildren($aRow) {
    $children = '';
    $CI = get_instance();
    $camps = $CI->Newsletter_model->hasChildren($aRow['id']);
    $viewLink = base_url('newsletter?type=campaign&id=');
    if(count($camps) > 0) {
        foreach($camps as $camp) {
            $name = $CI->Newsletter_model->getCampaignSubject($camp['next_campaign']);
            $children .= '<p> '.$camp['conditional'].': <a href=\''.$viewLink. $camp['next_campaign'] . '\'>'.$name->subject .' </a></p>';
        }
        
    }else {
        $children = '';
    }

    return $children;
}
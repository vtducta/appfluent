<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$CI = get_instance();
$CI->load->add_package_path(trigger_get_base_path());
add_action("after_js_scripts_render", "trigger_add_script");
add_action("after_cron_run", "trigger_run_cron");

function trigger_get_base_path() {
    return str_replace("application" . DIRECTORY_SEPARATOR, "", APPPATH) . 'plugins/trigger';
}

function trigger_add_script() {
    echo '<script src="'.base_url('plugins/trigger/trigger.js').'"></script>';
    //echo '<script src="' . base_url('plugins/trigger/chart.min.js') . '"></script>';
    $CI = &get_instance();
    echo $CI->load->view("trigger_footer", "", true);
}

function trigger_run_cron() {
    $CI = &get_instance();
    $CI->load->model('Trigger_model');
    //if (get_option('trigger_email_queue')) {
        $CI->Trigger_model->runCron();
    //}
}

function _lang($lang) {   
    $CI = &get_instance();
    $language = get_option('active_language');
    if (is_staff_logged_in()) {
        $staff_language = get_staff_default_language();
        $language = $staff_language;
    } else {
        if (is_client_logged_in()) {
            $client_language = get_client_default_language();
            $language = $client_language;
        }
    }
    $CI->lang->load('trigger', $language);
    return $CI->lang->line($lang);
}

function lang($lang) {
    echo _lang($lang);
}

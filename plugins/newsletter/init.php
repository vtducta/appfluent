<?php
$CI = get_instance();
$CI->load->add_package_path(newsletter_get_base_path());
add_action("after_js_scripts_render", "newsletter_add_script");
//add_action("after_cron_run", "newsletter_run_cron");


function newsletter_get_base_path() {
    return str_replace("application".DIRECTORY_SEPARATOR, "", APPPATH).'plugins/newsletter';
}

function newsletter_add_script() {
    echo '<script src="' . base_url('plugins/newsletter/script.js') . '"></script>';
    //echo '<script src="'.base_url('plugins/newsletter/chart.min.js').'"></script>';
    $CI = &get_instance();
    echo $CI->load->view("newsletter_footer", "", true);
}

//function newsletter_run_cron() {
//    $CI = &get_instance();
//    $CI->load->model('Newsletter_model');
//    if (get_option('newsletter_email_queue')) {
//        $CI->Newsletter_model->runCron();
//        $CI->Newsletter_model->runCronTrigger();
//    }
//}

function nom($lang) {
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
    $CI->lang->load('newsletter_lang', $language);
    return $CI->lang->line($lang);
}
function _nom($lang) {
    echo  nom($lang);
}

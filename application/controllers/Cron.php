<?php

defined('BASEPATH') or exit('No direct script access allowed');
use IMAP\IMAPMailbox;
class Cron extends CRM_Controller
{
    public function __construct()
    {
        parent::__construct();
        update_option('cron_has_run_from_cli', 1);
    }

    public function index($key = '')
    {
        if (defined('APP_CRON_KEY') && (APP_CRON_KEY != $key)) {
            header('HTTP/1.0 401 Unauthorized');
            die('Passed cron job key is not correct. The cron job key should be the same like the one defined in APP_CRON_KEY constant.');
        }

        $last_cron_run = get_option('last_cron_run');

        if ($last_cron_run == '' || (time() > ($last_cron_run + do_action('cron_functions_execute_seconds', 300)))) {
            do_action('before_cron_run');

            $this->load->model('cron_model');
            $this->cron_model->run();

            do_action('after_cron_run');
        }
    }

    public function sync_system_email()
    {
        $host = '{imap.gmail.com:993/imap/ssl}';
        $user = 'test.appfluent@gmail.com';
        $pwd = 'ad123123';
        $mailbox = new IMAPMailbox($host, $user, $pwd);
        $emails = $mailbox->search('ALL');
        $this->load->model('emails_model');
        foreach ($emails as $email) {
            $headerinfo = $email->fetchHeaderinfo();
            $this->emails_model->insert_inbox($headerinfo, $email->getBody());
        }
        echo 'sync system email done';

    }
    public function sync_staff_email()
    {

        $this->load->model('staff_model');
        $staff_list= $this->staff_model->get();
        //var_dump($staff_list);die();
        $this->load->model('emails_model');
        foreach ($staff_list as $staff){
            $host = $staff['email_imap_host'].':'.$staff['email_imap_port'].'/imap/ssl';
            //    '{imap.gmail.com:993/imap/ssl}';
            //var_dump($staff['email_imap_host']);
            //var_dump($host);die();

            $user = $staff['email'];
            $pwd = $staff['email_password'];
            $mailbox = new IMAPMailbox($host, $user, $pwd);
            $emails = $mailbox->search('ALL');

            foreach ($emails as $email) {
                $headerinfo = $email->fetchHeaderinfo();
                $this->emails_model->insert_inbox_staff($staff['staffid'],$headerinfo, $email->getBody());
            }
        }


        echo 'sync staff email done';

    }
}

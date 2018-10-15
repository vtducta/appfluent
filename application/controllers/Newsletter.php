<?php

class Newsletter extends CRM_controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('authentication_model');
        $this->authentication_model->autologin();
        if (is_staff_logged_in()) {
            load_admin_language();
        } else {
            load_client_language();
        }

        $this->load->model('Newsletter_model');

        $this->load->model('payment_modes_model');
        $this->load->model('settings_model');

    }

    public function get_table_data($table, $params = array()) {
        $hook_data = do_action('before_render_table_data', array(
            'table' => $table,
            'params' => $params
        ));

        foreach ($hook_data['params'] as $key => $val) {
            $$key = $val;
        }
        $table = $hook_data['table'];

        $customFieldsColumns = array();

        include_once (newsletter_get_base_path() . '/views/tables/' . $table . '.php');

        echo json_encode($output);
        die;
    }

    public function settings() {
        $this->preInitAdmin();
        if (!has_permission('settings', '', 'view')) {
            access_denied('settings');
        }

        //     exit($this->load->view('message_settings_page', '', true));
        if ($this->input->post()) {
            if (!has_permission('settings', '', 'edit')) {
                access_denied('settings');
            }
            $logo_uploaded = (handle_company_logo_upload() ? true : false);
            $favicon_uploaded = (handle_favicon_upload() ? true : false);

            $post_data = $this->input->post(NULL, FALSE);
            $success = $this->settings_model->update($post_data);
            if ($success > 0) {
                set_alert('success', _l('settings_updated'));
            }

            if ($logo_uploaded || $favicon_uploaded) {
                set_debug_alert(_l('logo_favicon_changed_notice'));
            }

            // Do hard refresh on general for the logo
            if ($this->input->get('group') == 'general') {
                redirect(admin_url('settings?group=' . $this->input->get('group')), 'refresh');
            } else {
                redirect(base_url('newsletter/settings'));
            }
        }
        $this->load->model('taxes_model');
        $this->load->model('tickets_model');
        $this->load->model('leads_model');
        $data['taxes'] = $this->taxes_model->get();
        $data['ticket_priorities'] = $this->tickets_model->get_priority();
        $data['ticket_priorities']['callback_translate'] = 'ticket_priority_translate';
        $data['roles'] = $this->roles_model->get();
        $data['leads_sources'] = $this->leads_model->get_source();
        $data['leads_statuses'] = $this->leads_model->get_status();
        $data['title'] = _l('options');
        if (!$this->input->get('group') || ($this->input->get('group') == 'update' && !is_admin())) {
            $view = 'general';
        } else {
            $view = $this->input->get('group');
        }
        if ($view == 'update') {
            if (!extension_loaded('curl')) {
                $data['update_errors'][] = 'CURL Extension not enabled';
                $data['latest_version'] = 0;
                $data['update_info'] = json_decode("");
            } else {
                $data['update_info'] = $this->misc_model->get_update_info();
                if (strpos($data['update_info'], 'Curl Error -') !== FALSE) {
                    $data['update_errors'][] = $data['update_info'];
                    $data['latest_version'] = 0;
                    $data['update_info'] = json_decode("");
                } else {
                    $data['update_info'] = json_decode($data['update_info']);
                    $data['latest_version'] = $data['update_info']->latest_version;
                    $data['update_errors'] = array();
                }
            }

            if (!extension_loaded('zip')) {
                $data['update_errors'][] = 'ZIP Extension not enabled';
            }

            $data['current_version'] = $this->db->get('tblmigrations')->row()->version;
        }

        $data['contacts_permissions'] = $this->perfex_base->get_contact_permissions();
        $this->load->library('pdf');
        $data['payment_gateways'] = $this->payment_modes_model->get_online_payment_modes(true);
        $data['group'] = $this->input->get('group');
        $data['group_view'] = $this->load->view('newsletter_settings_page', $data, true);
        $this->load->view('admin/settings/all', $data);
    }

    public function redirect() {
        $campaignId = $this->input->get('id');
        $this->Newsletter_model->updateCampaign($campaignId, "clicks");

        redirect($this->input->get('url'));
    }

    public function gettemplate() {
        $id = $this->input->get('id');
        $template = $this->Newsletter_model->getTemplate($id);
        echo $template['content'];
    }

    public function track() {
        $campaignId = $this->input->get('id');
        $this->Newsletter_model->updateCampaign($campaignId, "opens");
        $graphic_http = base_url('plugins/newsletter/icon.png');

        //Get the filesize of the image for headers
        $filesize = filesize(newsletter_get_base_path() . '/icon.png');

        //Now actually output the image requested (intentionally disregarding if the database was affected)
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Disposition: attachment; filename="icon.png');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $filesize);
        readfile($graphic_http);
    }

    public function index() {
        $this->preInitAdmin();
        if (!is_admin()) {
            access_denied('Newsletter');
        }
        $type = $this->input->get('type');
        $type = ($type) ? $type : 'overview';
        $content = "";
        switch ($type) {

            case 'campaigns':
                $delete = $this->input->get('delete');

                if ($delete) {
                    $this->Newsletter_model->deleteCampaign($delete);
                    set_alert('success', nom('newsletter_campaign_deleted'));
                }
                $active = $this->input->get('active');
                if ($active) {
                    $status = $this->input->get('status');
                    $this->Newsletter_model->activeCampaign($active, $status);
                    set_alert('success', nom('newsletter_campaign_active'));
                }
                $send = $this->input->get('send');
                if ($send)
                    $this->Newsletter_model->sendCampaign($send);
                if ($this->input->is_ajax_request()) {
                    $this->get_table_data('campaigns');
                }
                $content = $this->load->view('newsletter_campaigns', '', true);
                break;
            case 'campaign':
                $type = "campaigns";
                $campaign = array();
                $message = null;
                $id = $this->input->get('id');
                if ($id) {
                    $campaign = $this->Newsletter_model->getCampaign($id);
                }
                if ($this->input->post()) {
                    $dataCampaign = [
                        'status' => $this->input->post('status'),
                        'subject' => $this->input->post('subject'),
                        'sender_name' => $this->input->post('sender_name'),
                        'sender_email' => $this->input->post('sender_email'),
                        'send_date' => $this->input->post('send_date'),
                        'lists' => $this->input->post('lists'),
                        'customers' => $this->input->post('customers'),
                        'staffs' => $this->input->post('staffs'),
                        'email_to' => $this->input->post('email_to'),
                        'content' => $this->input->post('content', false),
                        'send_on' => $this->input->post('send_on'),
                        'send_at' => $this->input->post('send_at'),
                        'conditionals' => $this->input->post('conditionals') && $this->input->post('conditionals') != 'null' ? $this->input->post('conditionals') : NULL,
                        'next_campaigns' => $this->input->post('next_campaigns') && $this->input->post('next_campaigns') != 'null' ? $this->input->post('next_campaigns') : NULL,
                        'waitings' => $this->input->post('waitings'),
                        'types' => $this->input->post('types'),
                    ];
                    if ($this->Newsletter_model->saveCampaign($dataCampaign, $id)) {
                        ($id) ? set_alert('success', nom('newsletter_campaign_updated')) : set_alert('success', nom('newsletter_campaign_created'));
                        header("Location: " . base_url("newsletter?type=campaigns"));
                    } else {
                        $message = nom('newsletter_failed_to_save_newsletter');
                    }
                }
                $content = $this->load->view('newsletter_campaign', array('campaign' => $campaign), true);
                break;
            case 'template':
                $type = 'templates';
                $id = $this->input->get('id');
                $template = array();
                if ($id) {
                    $template = $this->Newsletter_model->getTemplate($id);
                }
                if ($this->input->post()) {
                    $this->Newsletter_model->saveTemplate($this->input->post('title'), $this->input->post('content', false), $id);
                    ($id) ? set_alert('success', nom('newsletter_template_updated')) : set_alert('success', nom('newsletter_template_created'));
                    header("Location: " . base_url("newsletter?type=templates"));
                }
                $content = $this->load->view('newsletter_template', array('template' => $template), true);
                break;
            case 'templates':
                $delete = $this->input->get('delete');
                if ($delete) {
                    $this->Newsletter_model->deleteTemplate($delete);
                    set_alert('success', nom('newsletter_template_deleted'));
                }
                $content = $this->load->view('newsletter_templates', '', true);
                break;
            case 'triggers':
                $triggers = $this->Newsletter_model->getTriggers();
                $content = $this->load->view('newsletter_automation', $triggers, true);
                break;
            case 'automation':
                $triggers = $this->Newsletter_model->getTriggers();
                $content = $this->load->view('newsletter_automation', $triggers, true);
                break;
            case 'automation_digram':
                $id = $this->input->get('id');
                if ($id) {
                    
                }
                $content = $this->load->view('newsletter_automation_digram', '', true);
                break;
            default:
                $content = $this->load->view('newsletter_overview', '', true);
                break;
        }
        $this->load->view('newsletter_index', array('type' => $type, 'content' => $content));
    }

    public function install() {
        $basePath = newsletter_get_base_path();
        $installfile = $basePath . '/install.php';
        if (!file_exists($installfile))
            exit("PERMISSION DENIED");
        include($installfile);
        exit("Newsletter installation done");
    }

    public function update() {
        $basePath = newsletter_get_base_path();
        $updatefile = $basePath . '/update.php';
        if (!file_exists($updatefile))
            exit("PERMISSION DENIED");
        include($updatefile);
        exit("Newsletter update done!!!");
    }

    public function preInitAdmin() {
        $this->_current_version = $this->misc_model->get_current_db_version();


        //$language = load_admin_language();
        ///$this->load->model('authentication_model');
        //$this->authentication_model->autologin();

        if (!is_staff_logged_in()) {
            if (strpos(current_full_url(), 'authentication/admin') === false) {
                $this->session->set_userdata(array(
                    'red_url' => current_full_url()
                ));
            }
            redirect(site_url('authentication/admin'));
        }

        // In case staff have setup logged in as client - This is important don't change it
        $this->session->unset_userdata('client_user_id');
        $this->session->unset_userdata('client_logged_in');
        $this->session->unset_userdata('logged_in_as_client');

        $this->load->model('staff_model');

        // Do not check on ajax requests
        if (!$this->input->is_ajax_request()) {
            // Check for just updates message

            add_action('before_start_render_content', 'show_just_updated_message');

            if (ENVIRONMENT == 'production' && is_admin()) {
                if ($this->config->item('encryption_key') === '') {
                    die('<h1>Encryption key not sent in application/config/config.php</h1>For more info visit <a href="http://www.perfexcrm.com/knowledgebase/encryption-key/">Encryption key explained</a> FAQ3');
                } elseif (strlen($this->config->item('encryption_key')) != 32) {
                    die('<h1>Encryption key length should be 32 charachters</h1>For more info visit <a href="http://www.perfexcrm.com/knowledgebase/encryption-key/">Encryption key explained</a>');
                }
            }

            add_action('before_start_render_content', 'show_development_mode_message');
            // Check if cron is required to be setup for some features
            add_action('before_start_render_content', 'is_cron_setup_required');
            // Check if timezone is set
            add_action('before_start_render_content', '_maybe_timezone_not_set');
            // Notice for cloudflare rocket loader
            add_action('before_start_render_content', '_maybe_using_cloudflare_rocket_loader');

            $this->init_quick_actions_links();
        }

        if (is_mobile()) {
            $this->session->set_userdata(array(
                'is_mobile' => true
            ));
        } else {
            $this->session->unset_userdata('is_mobile');
        }

        $auto_loaded_vars = array(
            '_staff' => $this->staff_model->get(get_staff_user_id()),
            '_notifications' => $this->misc_model->get_user_notifications(false),
            '_quick_actions' => $this->perfex_base->get_quick_actions_links(),
            '_started_timers' => $this->misc_model->get_staff_started_timers(),
            'google_api_key' => get_option('google_api_key'),
            'total_pages_newsfeed' => total_rows('tblposts') / 10,
            'locale' => get_locale_key($language),
            'tinymce_lang' => get_tinymce_language(get_locale_key($language)),
            '_pinned_projects' => $this->get_pinned_projects(),
            'total_undismissed_announcements' => $this->get_total_undismissed_announcements(),
            'current_version' => $this->_current_version,
            'tasks_filter_assignees' => $this->get_tasks_distinct_assignees(),
            'task_statuses' => $this->tasks_model->get_statuses(),
            'unread_notifications' => total_rows('tblnotifications', array('touserid' => get_staff_user_id(), 'isread' => 0))
        );

        $auto_loaded_vars = do_action('before_set_auto_loaded_vars_admin_area', $auto_loaded_vars);
        $this->load->vars($auto_loaded_vars);
    }

    private function init_quick_actions_links() {
        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('qa_create_invoice'),
            'permission' => 'invoices',
            'url' => 'invoices/invoice'
        ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('qa_create_estimate'),
            'permission' => 'estimates',
            'url' => 'estimates/estimate'
        ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('qa_new_expense'),
            'permission' => 'expenses',
            'url' => 'expenses/expense'
        ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('new_proposal'),
            'permission' => 'proposals',
            'url' => 'proposals/proposal'
        ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('new_project'),
            'url' => 'projects/project',
            'permission' => 'projects'
        ));


        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('qa_create_task'),
            'url' => '#',
            'custom_url' => true,
            'href_attributes' => array(
                'onclick' => 'new_task();return false;'
            ),
            'permission' => 'tasks'
        ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('qa_create_client'),
            'permission' => 'customers',
            'url' => 'clients/client'
        ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('qa_create_contract'),
            'permission' => 'contracts',
            'url' => 'contracts/contract'
        ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('qa_create_lead'),
            'url' => '#',
            'custom_url' => true,
            'permission' => 'is_staff_member',
            'href_attributes' => array(
                'onclick' => 'init_lead(); return false;'
            )
        ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('qa_new_goal'),
            'url' => 'goals/goal',
            'permission' => 'goals'
        ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('qa_create_kba'),
            'permission' => 'knowledge_base',
            'url' => 'knowledge_base/article'
        ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('qa_create_survey'),
            'permission' => 'surveys',
            'url' => 'surveys/survey'
        ));

        $tickets = array(
            'name' => _l('qa_create_ticket'),
            'url' => 'tickets/add'
        );
        if (get_option('access_tickets_to_none_staff_members') == 0 && !is_staff_member()) {
            $tickets['permission'] = 'is_staff_member';
        }
        $this->perfex_base->add_quick_actions_link($tickets);

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('qa_create_staff'),
            'url' => 'staff/member',
            'permission' => 'staff'
        ));

        $this->perfex_base->add_quick_actions_link(array(
            'name' => _l('utility_calendar_new_event_title'),
            'url' => 'utilities/calendar?new_event=true&date=' . _d(date('Y-m-d')),
            'permission' => ''
        ));
    }

    public function get_tasks_distinct_assignees() {
        return $this->misc_model->get_tasks_distinct_assignees();
    }

    private function get_total_undismissed_announcements() {
        $this->load->model('announcements_model');

        return $this->announcements_model->get_total_undismissed_announcements();
    }

    private function get_pinned_projects() {
        $this->db->select('tblprojects.id,tblprojects.name');
        $this->db->join('tblprojects', 'tblprojects.id=tblpinnedprojects.project_id');
        $this->db->where('tblpinnedprojects.staff_id', get_staff_user_id());
        $projects = $this->db->get('tblpinnedprojects')->result_array();
        $i = 0;
        $this->load->model('projects_model');
        foreach ($projects as $project) {
            $projects[$i]['progress'] = $this->projects_model->calc_progress($project['id']);
            $i++;
        }

        return $projects;
    }

    public function testEmail() {
        $this->load->library('email');
        $template = new StdClass();
        $template->message = get_option('email_header') . 'This is test SMTP email. <br />If you received this message that means that your SMTP settings is set correctly.' . get_option('email_footer');
        $template->fromname = get_option('companyname');
        $template->subject = 'SMTP Setup Testing';

        $template = parse_email_template($template);

        do_action('before_send_test_smtp_email');
        $user = (get_option('smtp_username') == '') ? trim(get_option('smtp_email')) : trim(get_option('smtp_username'));
        $this->email = new CI_Email();
        $this->email->initialize(array(
            'protocol' => get_option('email_protocol'),
            'smtp_host' => trim(get_option('smtp_host')),
            'smtp_port' => trim(get_option('smtp_port')),
            'smtp_user' => $user,
            'smtp_pass' => $this->encryption->decrypt(get_option('smtp_password')),
            'smtp_crypto' => get_option('smtp_encryption'),
            'wordwrap' => TRUE,
            'mailtype' => 'html',
            'mailpath' => '/usr/bin/sendmail',
            'useragent' => 'CodeIgniter',
            'charset' => 'UTF-8',
            'newline' => "\r\n",
            'crlf' => "\r\n"
        ));
        $this->email->set_newline("\r\n");
        $this->email->from(get_option('smtp_email'), $template->fromname);
        $this->email->to('tiamiyuwaliu1212@gmail.com');
        $this->email->subject($template->subject);
        $this->email->message($template->message);
        $this->email->send();
    }

    /**
     * Automation
     */
    function automation() {
        return $this->load->view('newsletter_automation');
    }

    /**
     * Add trigger
     */
    function add_trigger() {
        $data = $this->input->post();
        $this->Newsletter_model->addNewTrigger($data);
        echo true;
        die;
    }

}

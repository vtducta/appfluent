<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Client_families extends Admin_controller
{
    private $not_importable_clients_fields = array('userid', 'id', 'is_primary', 'password', 'datecreated', 'last_ip', 'last_login', 'last_password_change', 'active', 'new_pass_key', 'new_pass_key_requested', 'leadid', 'default_currency', 'profile_image', 'default_language', 'direction', 'show_primary_contact', 'invoice_emails', 'estimate_emails', 'project_emails', 'task_emails', 'contract_emails', 'credit_note_emails');
    public $pdf_zip;

    public function __construct()
    {
        parent::__construct();
    }

    /* List all clients */
    public function index()
    {

        if (!has_permission('customers', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('customers', '', 'create')) {
                access_denied('customers');
            }
        }

        $this->load->model('contracts_model');
        $data['contract_types'] = $this->contracts_model->get_contract_types();
        $data['groups']         = $this->clients_model->get_groups();
        $data['title']          = _l('clientfamilies');

        $this->load->model('proposals_model');
        $data['proposal_statuses'] = $this->proposals_model->get_statuses();

        $this->load->model('invoices_model');
        $data['invoice_statuses'] = $this->invoices_model->get_statuses();

        $this->load->model('estimates_model');
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();

        $this->load->model('projects_model');
        $data['project_statuses'] = $this->projects_model->get_project_statuses();

        $data['customer_admins'] = $this->clients_model->get_customers_admin_unique_ids();


        $whereContactsLoggedIn = '';
        if (!has_permission('customers', '', 'view')) {
            $whereContactsLoggedIn = ' AND userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id='.get_staff_user_id().')';
        }
        $whereContactsLoggedIn = $whereContactsLoggedIn . ' AND userid in (Select userid from tblclients where is_client=1)';


        $data['contacts_logged_in_today'] = $this->clients_model->get_contacts('', 'last_login LIKE "'.date('Y-m-d').'%"'.$whereContactsLoggedIn);


        $arrContactField = $this->clients_model->get_structure_contact();


        $arrFieldStandard =  $this->unsetObject($arrContactField,'firstname,lastname,title');

        $arrBulkUpdateField = array();



        foreach ($arrFieldStandard as $item){
            $dataItem = array();
            $dataItem["name"] = $item->name;
            $dataItem["type"] = "standard";
            $arrBulkUpdateField[] = $dataItem;
        }

        $dataItem = array();
        $dataItem["name"] = "company";
        $dataItem["type"] = "related_to_company";
        $arrBulkUpdateField[] = $dataItem;

        $dataItem = array();
        $dataItem["name"] = "tags";
        $dataItem["type"] = "related_to_tags";
        $arrBulkUpdateField[] = $dataItem;

        $data['contacts_structure'] =  $this->unsetObject($arrContactField,'firstname,lastname');

        $arrContactCustomField = get_custom_fields('contacts');
        foreach ($arrContactCustomField as $item){
            $dataItem = array();
            $dataItem["name"] = $item['name'];
            $dataItem["type"] = "custome_field_contacts";
            $dataItem["id"] = $item['id'];
            $dataItem["data_type"] = $item['type'];
            $arrBulkUpdateField[] = $dataItem;
        }
        $data['bulk_update_fields']= $arrBulkUpdateField;


        $data['contacts_custom_field_structure'] =$arrContactCustomField;

//        $this->load->model('Newsletter_model');
//        $arrCampaign = $this->Newsletter_model->getCampaigns();
        $data['list_campaign'] = array();//$arrCampaign;

        $this->load->view('admin/clients/manage_family', $data);
    }

    public function  call_log($client_id = '', $contact_id='',$call_log_id = ''){
        if ($this->input->post() && !$this->input->is_ajax_request()) {
            if ($call_log_id == '') {
                $datapost = $this->input->post();
                $datapost['contact_id'] = $contact_id;
                $datapost['client_id'] = $client_id;
                $datapost['addedfrom'] = get_staff_user_id();
                //var_dump($datapost); die;
                $id = $this->clients_model->add_call_log($datapost);

                if($contact_id){
                    $this->load->model('clients_model');
                    $this->clients_model->log_contact_activity($contact_id, 'not_contact_activity_call_log_created', false, serialize(array(
                        get_staff_full_name(get_staff_user_id()),nl2br($this->input->post()['subject'])
                    )));
                }

                $message = '';
                $success = false;
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('call log'));
                }
                redirect($_SERVER['HTTP_REFERER']);
            }else{
                $datapost = $this->input->post();

                $this->clients_model->update_call_log($datapost,$call_log_id);
                if($contact_id){
                    $this->load->model('clients_model');
                    $this->clients_model->log_contact_activity($contact_id, 'not_contact_activity_call_log_updated', false, serialize(array(
                        get_staff_full_name(get_staff_user_id()),nl2br($this->input->post()['subject'])
                    )));
                }
                redirect($_SERVER['HTTP_REFERER']);
            }
        }else{
            $call_log = $this->clients_model->get_call_log($call_log_id);
            $data['call_log'] =$call_log;
            $data['customer_id'] =$client_id;
            $data['contactid'] =$contact_id;
            $data['call_log_id'] = $call_log_id;
            $this->load->view('admin/clients/modals/note_call_log',$data);
        }
    }
    public function delete_event($id)
    {

            $this->load->model('utilities_model');
            $event = $this->utilities_model->get_event_by_id($id);

            if ($event->userid != get_staff_user_id() && !is_admin()) {
                redirect($_SERVER['HTTP_REFERER']);
                die;
            }
            $success = $this->utilities_model->delete_event($id);
            $message = '';
            if ($success) {
                $message = _l('utility_calendar_event_deleted_successfully');
            }
            set_alert('success', $message);
            redirect($_SERVER['HTTP_REFERER']);
            die();
    }
    public function  event($client_id = '', $contact_id='',$event_id = ''){
        if ($this->input->post() && !$this->input->is_ajax_request()) {
            if ($event_id == '') {
                $datapost = $this->input->post();
                //var_dump($datapost); die();
                $this->load->model('utilities_model');
                $id = $this->utilities_model->event($datapost);

                $message = '';
                $success = false;
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('call log'));
                }
                redirect($_SERVER['HTTP_REFERER']);
            }else{
                $datapost = $this->input->post();
                $this->load->model('utilities_model');
                $this->utilities_model->event($datapost);
                redirect($_SERVER['HTTP_REFERER']);
            }
        }else{
            $this->load->model('utilities_model');
            $event = $this->utilities_model->get_event_by_id($event_id);
            $data['event'] =$event;
            $data['customer_id'] =$client_id;
            $data['contactid'] =$contact_id;
            $data['event_id'] = $event_id;
            $this->load->view('admin/clients/modals/event',$data);
        }
    }
    function unsetObject(array $array, $skipString='')
    {
        $result = array();
        foreach ($array as $item) {
            if(strpos ($skipString,$item -> name)!==false){
                $result[]=$item;
            }
        }
        return $result;
    }
    public function table()
    {
        if (!has_permission('customers', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('customers', '', 'create')) {
                ajax_access_denied();
            }
        }

        $this->app->get_table_data('client_families');
    }

    public function all_contacts()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('all_contacts');
        }
        $data['title'] = _l('customer_contacts');
        $this->load->view('admin/clients/all_contacts', $data);
    }

    public function send_email($client_id,$contact_id){
        if ($this->input->post() ){
            $this->load->model('emails_model');
//            $this->emails_model->add_attachment(array(
//                'attachment' => $this->input->post('file_path'),
//                'filename' => $this->input->post('file_name'),
//                'type' => $this->input->post('filetype'),
//                'read' => true
//            ));
            $message = $this->input->post('content');
            $message = nl2br($message);
            $success = $this->emails_model->send_simple_email($this->input->post('to'), $this->input->post('subject'), $message);
            if ($success) {
                $schedule_time="";
                if($this->input->post('schedule')){
                    $schedule_time=     $this->input->post('send_date') .'|'. $this->input->post('send_at');
                }
                $this->clients_model->log_email_activity($contact_id,$client_id,$this->input->post('to'),$this->input->post('schedule'),$schedule_time,$this->input->post('subject'),$message);

                if($contact_id){
                    $this->clients_model->log_contact_activity($contact_id, 'not_contact_activity_send_mail', false, serialize(array(
                        get_staff_full_name(get_staff_user_id()),nl2br($this->input->post()['subject'])
                    )));
                }
                set_alert('success', _l('mail_success_send', $this->input->post('to')));
            } else {
                set_alert('warning', _l('mail_fail_send'));
            }
        }
        redirect($_SERVER['HTTP_REFERER']);

    }

    /* Edit client or add new client*/
    public function client($id = '', $contact_id ='')
    {
        if (!has_permission('customers', '', 'view')) {
            if ($id != '' && !is_customer_admin($id)) {
                access_denied('customers');
            }
        }

        if ($this->input->post() && !$this->input->is_ajax_request()) {
            if ($id == '') {
                if (!has_permission('customers', '', 'create')) {
                    access_denied('customers');
                }

                $datapost = $this->input->post();
                //

                $data_contact = $datapost['contact'];
                $data_client  = $datapost['client'];
                unset($datapost['contact']);
                unset($datapost['client']);
                unset ($datapost['related_to_contact_custom_fields']);
                unset ($datapost['related_to_company_custom_fields']);
                $datamerge =  $datapost;
                //var_dump($datamerge); die;
                $save_and_add_contact = false;
                if (isset($datamerge['save_and_add_contact'])) {
                    unset($datamerge['save_and_add_contact']);
                    $save_and_add_contact = true;
                }

                $data = array_merge($datamerge,$data_client);

                $client_name = trim($data['company']);
                $search_client = $this->clients_model->get_by_name($client_name);

                $data_contact_insert = array_merge($datamerge,$data_contact);


                if(!$search_client){
                    if($client_name){
                        $data['is_client'] =0;
                    }
                    $id = $this->clients_model->add($data);
                }else{

                    $id = (int)($search_client->userid);
                    unset($data_contact_insert['is_primary']);

                }

                $tags = $data_contact_insert['tags'];
                unset($data_contact_insert['tags']);

                unset($data_contact_insert['contactid']);

                $contact_id      = $this->clients_model->add_contact($data_contact_insert, $id);


                handle_contact_profile_image_upload2($contact_id);

                handle_tags_save($tags, $contact_id, 'contact');


                if($contact_id>0){
                    $this->clients_model->log_contact_activity($contact_id, 'not_contact_activity_created', false, serialize(array(
                        get_staff_full_name(get_staff_user_id())
                    )));
                }

                if (!has_permission('customers', '', 'view')) {
                    $assign['customer_admins']   = array();
                    $assign['customer_admins'][] = get_staff_user_id();
                    $this->clients_model->assign_admins($assign, $id);
                }
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('client')));
                    if ($save_and_add_contact == false) {

                        redirect(admin_url('client_families/client/' . $id.'/'.$contact_id));
                    } else {
                        redirect(admin_url('client_families/client/' . $id . '/'.$contact_id.'?new_contact=true&tab=contacts'));
                    }
                }
            } else {
                if (!has_permission('customers', '', 'edit')) {
                    if (!is_customer_admin($id)) {
                        access_denied('customers');
                    }
                }

                $datapost = $this->input->post();

                //


                $data_contact = $datapost['contact'];
                $data_client  = $datapost['client'];
                unset($datapost['contact']);
                unset($datapost['client']);
                unset ($datapost['related_to_contact_custom_fields']);
                unset ($datapost['related_to_company_custom_fields']);
                $datamerge =  $datapost;

                $contact_id = $data_contact['contactid'];
                $success =false;
                if($contact_id){

                    $data = array_merge($datamerge,$data_contact);
                    unset($data['contactid']);

                    $tags = $data['tags'];
                    unset($data['tags']);
                    $original_contact = $this->clients_model->get_contact($contact_id);
                    $success          = $this->clients_model->update_contact($data, $contact_id);

                    handle_tags_save($tags, $contact_id, 'contact');


                    $message          = '';
                    $proposal_warning = false;
                    $original_email   = '';
                    $updated          = false;
                    if (is_array($success)) {
                        if (isset($success['set_password_email_sent'])) {
                            $message = _l('set_password_email_sent_to_client');
                        } elseif (isset($success['set_password_email_sent_and_profile_updated'])) {
                            $updated = true;
                            $message = _l('set_password_email_sent_to_client_and_profile_updated');
                        }
                    } else {
                        if ($success == true) {
                            $updated = true;
                            $message = _l('updated_successfully', _l('contact'));
                        }
                    }

//                    var_dump($_FILES['contact']);
//                    die;
                    if (handle_contact_profile_image_upload2($contact_id) && !$updated) {
                        $message = _l('updated_successfully', _l('contact'));
                        $success = true;
                    }


                    if ($updated == true) {
                        $contact = $this->clients_model->get_contact($contact_id);

                        if (total_rows('tblproposals', array(
                                'rel_type' => 'customer',
                                'rel_id' => $contact->userid,
                                'email' => $original_contact->email,
                            )) > 0 && ($original_contact->email != $contact->email)) {
                            $proposal_warning = true;
                            $original_email   = $original_contact->email;
                        }
                    }

                }

                $data = array_merge($datamerge,$data_client);
                unset($data['custom_fields']);

                $client_name = trim($data['company']);
                $search_client = $this->clients_model->get_by_name($client_name);

                $old_client_name =  $this->clients_model->get($id)->company;

                $new_id =$id;
                if(!$old_client_name){ //th ten cu rong
                    if($search_client){
                        $new_id = $search_client->userid;
                        $contact_primary = $this->clients_model->get_contact_primary($new_id);
                        $this->clients_model-> update_move_contact ($contact_id,$new_id,$contact_primary?0:1);
                    }else{ //khong tim thay
                        if($client_name){
                            $data['is_client'] =0;

                            $new_id = $this->clients_model->add($data);
                            $this->clients_model-> update_move_contact ($contact_id,$new_id,1);

                        }else{
                            $success_client = $this->clients_model->update($data, $id);
                        }
                    }
                }else { // ten cu khac rong
                    if($search_client){ //tim thay client
                        if($id!=$search_client->userid){ // khac client
                            $new_id = $search_client->userid;
                            $contact_primary = $this->clients_model->get_contact_primary($new_id);
                            $this->clients_model-> update_move_contact ($contact_id,$new_id,$contact_primary?0:1);
                        }else { // van la client cu
                            $success_client = $this->clients_model->update($data, $id);
                        }
                    }else{ //khong tim thay
                        if($client_name){
                            $data['is_client'] =0;
                        }else{
                            $data['is_client'] =1;
                        }
                        $new_id = $this->clients_model->add($data);
                        $this->clients_model-> update_move_contact ($contact_id,$new_id,1);
                    }
                }


                if ($success_client == true) {
                    set_alert('success', _l('updated_successfully', _l('client')));
                }
                if($success||$success_client){
                    $this->clients_model->log_contact_activity($contact_id, 'not_contact_activity_updated', false, serialize(array(
                        get_staff_full_name(get_staff_user_id())
                    )));
                }
                redirect(admin_url('client_families/client/' . $new_id.'/'.$contact_id));
            }
        }

        if (!$this->input->get('group')) {
            $group = 'profile_families';
        } else {
            $group = $this->input->get('group');
        }
        // View group
        $data['group']  = $group;
        // Customer groups
        $data['groups'] = $this->clients_model->get_groups();

        if ($id == '') {
            $title = _l('add_new', _l('client_lowercase'));
        } else {
            $client = $this->clients_model->get($id);


            if (!$client) {
                blank_page('Client Not Found');
            }

            try{
                $data['contact'] = $this->clients_model->get_contact($contact_id);

            }catch (Exception $ex){
                var_dump( $ex);
            }

            //$data['contacts']         = $this->clients_model->get_contacts($id);
            if ($data['contact']) {
                $data['contactid']=$data['contact']->id;
                $data['contact_tags'] = implode(',',get_tags_in($data['contactid'],'contact'));

            }
            // Fetch data based on groups
            if ($group == 'profile_families') {
                $data['customer_groups'] = $this->clients_model->get_customer_groups($id);
                $data['customer_admins'] = $this->clients_model->get_admins($id);

                $this->load->model('custom_tabs_model');
                $data['list_custom_tab'] = $this->custom_tabs_model->get_tab_show_in_profile('contact_profile');

            } elseif ($group == 'attachments_families') {
                $data['attachments']   = $this->clients_model->get_all_contacts_attachments($contact_id);
            } elseif ($group == 'vault') {
                $data['vault_entries'] = do_action('check_vault_entries_visibility', $this->clients_model->get_vault_entries($id));
            } elseif ($group == 'estimates') {
                $this->load->model('estimates_model');
                $data['estimate_statuses'] = $this->estimates_model->get_statuses();
            } elseif ($group == 'invoices') {
                $this->load->model('invoices_model');
                $data['invoice_statuses'] = $this->invoices_model->get_statuses();
            } elseif ($group == 'credit_notes') {
                $this->load->model('credit_notes_model');
                $data['credit_notes_statuses'] = $this->credit_notes_model->get_statuses();
                $data['credits_available'] = $this->credit_notes_model->total_remaining_credits_by_customer($id);
            } elseif ($group == 'payments') {
                $this->load->model('payment_modes_model');
                $data['payment_modes'] = $this->payment_modes_model->get();
            } elseif ($group == 'notes_families') {
                $data['customer_id']=$id;
                $data['user_notes'] = $this->misc_model->get_notes($data['contact']->id, 'contacts');
            }elseif ($group == 'events') {
                $data['events'] = $this->misc_model->get_events();
                $data['title']                = _l('events');
                // To load js files
                $data['customer_id']=$id;

            } elseif ($group == 'emails') {

                $data['emails'] = $this->misc_model->get_emails();

                $data['title']                = _l('emails');

                // To load js files
                $data['customer_id']=$id;

                $this->load->model('Newsletter_model');
                $data['template_list'] = $this->Newsletter_model->getTemplateLists();

            }  elseif ($group == 'policies') {
                $this->load->model('custom_tabs_model');
                $data['list_custom_tab'] = $this->custom_tabs_model->get();
                $data['title']                = _l('policies');

                // To load js files
                $data['customer_id']=$id;


            } elseif ($group == 'timeline') {
                $data['activity_log']  = $this->clients_model->get_contact_activity_log($data['contact']->id);
            } elseif ($group == 'projects') {
                $this->load->model('projects_model');
                $data['project_statuses'] = $this->projects_model->get_project_statuses();
            } elseif ($group == 'statement') {
                if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
                    set_alert('danger', _l('access_denied'));
                    redirect(admin_url('client_families/client/'.$id));
                }
                //$contact = $this->clients_model->get_contact(get_primary_contact_user_id($id));

                $email   = '';
                if ($data['contact']) {

                    $email = $data['contact']->email;
                }

                $template_name = 'client-statement';
                $data['template'] = get_email_template_for_sending($template_name, $email);

                $data['template_name']     = $template_name;
                $this->db->where('slug', $template_name);
                $this->db->where('language', 'english');
                $template_result = $this->db->get('tblemailtemplates')->row();

                $data['template_system_name'] = $template_result->name;
                $data['template_id'] = $template_result->emailtemplateid;

                $data['template_disabled'] = false;
                if (total_rows('tblemailtemplates', array('slug'=>$data['template_name'], 'active'=>0)) > 0) {
                    $data['template_disabled'] = true;
                }
            }elseif($group === 'campaign') {
                $this->load->model('Newsletter_model');
                $data['campaign_logs'] = $this->Newsletter_model->campaignLogs( $id , 'contact');
            } elseif ( strpos($group,'section_') === 0) {

                $slug = substr($group,8);
                $this->load->model('custom_tabs_model');

                $data['list_custom_tab'] = $this->custom_tabs_model->get_by_section_slug($slug);

                $this->load->model('custom_sections_model');
                $section = $this->custom_sections_model->get_by_section_slug($slug,'contacts');
                $data['title']                = $section["name"];

                // To load js files
                $data['customer_id']=$id;


            }

            $data['staff']           = $this->staff_model->get('', 1);

            $data['client']        = $client;
            $title                 = $client->company;

            // Get all active staff members (used to add reminder)
            $data['members'] = $data['staff'];


            if (!empty($data['client']->company)) {
                // Check if is realy empty client company so we can set this field to empty
                // The query where fetch the client auto populate firstname and lastname if company is empty
                if (is_empty_customer_company($data['client']->userid)) {
                    $data['client']->company = '';
                }
            }
        }

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        if($id != ''){
            $customer_currency = $data['client']->default_currency;

            foreach ($data['currencies'] as $currency) {
                if ($customer_currency != 0) {
                    if ($currency['id'] == $customer_currency) {
                        $customer_currency = $currency;
                        break;
                    }
                } else {
                    if ($currency['isdefault'] == 1) {
                        $customer_currency = $currency;
                        break;
                    }
                }
            }

            if(is_array($customer_currency)) {
                $customer_currency = (object) $customer_currency;
            }

            $data['customer_currency'] = $customer_currency;
        }

        $this->load->model('custom_sections_model');
        $arraySection = $this->custom_sections_model->get(false,'contacts');
        $arrayListGroup =  array();
        $i=20;

        //var_dump($arraySection[0]['name']); die;
        foreach ($arraySection as $section ){

            $obj = array();
            $obj['name'] = $section['name'];
            $obj['url'] = admin_url('client_families/client/'.$id.'/'.$contact_id.'?group=section_'.$section["slug"]);
            $obj['icon'] = 'fa fa-list-alt';
            $obj['lang'] = $section['name'];
            $obj['visible'] = true;
            $obj['order'] = $i++;
            $arrayListGroup[] = $obj;
        }

        $data['list_custom_section'] = $arrayListGroup;

        $data['bodyclass'] = 'customer-profile';
        $data['title'] = $title;

        $data['customer_permissions'] = $this->app->get_contact_permissions();

        $this->load->view('admin/clients/client_family', $data);
    }

    public function contact($customer_id, $contact_id = '')
    {
        if (!has_permission('customers', '', 'view')) {
            if (!is_customer_admin($customer_id)) {
                echo _l('access_denied');
                die;
            }
        }
        $data['customer_id'] = $customer_id;
        $data['contactid']   = $contact_id;
        if ($this->input->post()) {
            $data = $this->input->post();
            unset($data['contactid']);
            if ($contact_id == '') {
                if (!has_permission('customers', '', 'create')) {
                    if (!is_customer_admin($customer_id)) {
                        header('HTTP/1.0 400 Bad error');
                        echo json_encode(array(
                            'success' => false,
                            'message' => _l('access_denied'),
                        ));
                        die;
                    }
                }
                $id      = $this->clients_model->add_contact($data, $customer_id);
                $message = '';
                $success = false;
                if ($id) {
                    handle_contact_profile_image_upload($id);
                    $success = true;
                    $primary_contact_id = $this->clients_model->get_contact_primary($customer_id)->id;
                    $this->clients_model->log_contact_activity($primary_contact_id , 'not_contact_activity_created_related_contact', false, serialize(array(
                        get_staff_full_name(get_staff_user_id())
                    )));
                    $message = _l('added_successfully', _l('contact'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message,
                    'has_primary_contact'=>(total_rows('tblcontacts', array('userid'=>$customer_id, 'is_primary'=>1)) > 0 ? true : false),
                ));
                die;
            } else {
                if (!has_permission('customers', '', 'edit')) {
                    if (!is_customer_admin($customer_id)) {
                        header('HTTP/1.0 400 Bad error');
                        echo json_encode(array(
                            'success' => false,
                            'message' => _l('access_denied'),
                        ));
                        die;
                    }
                }
                $original_contact = $this->clients_model->get_contact($contact_id);
                $success          = $this->clients_model->update_contact($data, $contact_id);
                $message          = '';
                $proposal_warning = false;
                $original_email   = '';
                $updated          = false;
                if (is_array($success)) {
                    if (isset($success['set_password_email_sent'])) {
                        $message = _l('set_password_email_sent_to_client');
                    } elseif (isset($success['set_password_email_sent_and_profile_updated'])) {
                        $updated = true;
                        $message = _l('set_password_email_sent_to_client_and_profile_updated');
                    }
                } else {
                    if ($success == true) {
                        $updated = true;
                        $message = _l('updated_successfully', _l('contact'));
                    }
                }
                if (handle_contact_profile_image_upload($contact_id) && !$updated) {
                    $message = _l('updated_successfully', _l('contact'));
                    $success = true;
                }
                if ($updated == true) {
                    $contact = $this->clients_model->get_contact($contact_id);
                    if (total_rows('tblproposals', array(
                        'rel_type' => 'customer',
                        'rel_id' => $contact->userid,
                        'email' => $original_contact->email,
                    )) > 0 && ($original_contact->email != $contact->email)) {
                        $proposal_warning = true;
                        $original_email   = $original_contact->email;
                    }
                }
                if($success){
                    $primary_contact_id = $this->clients_model->get_contact_primary($customer_id)->id;
                    $this->clients_model->log_contact_activity($primary_contact_id , 'not_contact_activity_updated_related_contact', false, serialize(array(
                        get_staff_full_name(get_staff_user_id())
                    )));
                }
                echo json_encode(array(
                    'success' => $success,
                    'proposal_warning' => $proposal_warning,
                    'message' => $message,
                    'original_email' => $original_email,
                    'has_primary_contact'=>(total_rows('tblcontacts', array('userid'=>$customer_id, 'is_primary'=>1)) > 0 ? true : false),
                ));
                die;
            }
        }
        if ($contact_id == '') {
            $title = _l('add_new', _l('contact_lowercase'));
        } else {
            $data['contact'] = $this->clients_model->get_contact($contact_id);

            if (!$data['contact']) {
                header('HTTP/1.0 400 Bad error');
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Contact Not Found',
                ));
                die;
            }
            $title = $data['contact']->firstname . ' ' . $data['contact']->lastname;
        }

        $data['customer_permissions'] = $this->app->get_contact_permissions();
        $data['title']                = $title;
        $this->load->view('admin/clients/modals/contact_family', $data);
    }

    public function update_file_share_visibility()
    {
        if ($this->input->post()) {
            $file_id           = $this->input->post('file_id');
            $share_contacts_id = array();

            if ($this->input->post('share_contacts_id')) {
                $share_contacts_id = $this->input->post('share_contacts_id');
            }

            $this->db->where('file_id', $file_id);
            $this->db->delete('tblcustomerfiles_shares');

            foreach ($share_contacts_id as $share_contact_id) {
                $this->db->insert('tblcustomerfiles_shares', array(
                    'file_id' => $file_id,
                    'contact_id' => $share_contact_id,
                ));
            }
        }
    }

    public function delete_contact_profile_image($contact_id)
    {
        do_action('before_remove_contact_profile_image');
        if (file_exists(get_upload_path_by_type('contact_profile_images') . $contact_id)) {
            delete_dir(get_upload_path_by_type('contact_profile_images') . $contact_id);
        }
        $this->db->where('id', $contact_id);
        $this->db->update('tblcontacts', array(
            'profile_image' => null,
        ));
    }

    public function mark_as_active($id)
    {
        $this->db->where('userid', $id);
        $this->db->update('tblclients', array(
            'active' => 1,
        ));
        redirect(admin_url('client_families/client/' . $id));
    }

    public function update_all_proposal_emails_linked_to_customer($contact_id)
    {
        $success = false;
        $email   = '';
        if ($this->input->post('update')) {
            $this->load->model('proposals_model');

            $this->db->select('email,userid');
            $this->db->where('id', $contact_id);
            $contact = $this->db->get('tblcontacts')->row();

            $proposals     = $this->proposals_model->get('', array(
                'rel_type' => 'customer',
                'rel_id' => $contact->userid,
                'email' => $this->input->post('original_email'),
            ));
            $affected_rows = 0;

            foreach ($proposals as $proposal) {
                $this->db->where('id', $proposal['id']);
                $this->db->update('tblproposals', array(
                    'email' => $contact->email,
                ));
                if ($this->db->affected_rows() > 0) {
                    $affected_rows++;
                }
            }

            if ($affected_rows > 0) {
                $success = true;
            }
        }
        echo json_encode(array(
            'success' => $success,
            'message' => _l('proposals_emails_updated', array(
                _l('contact_lowercase'),
                $contact->email,
            )),
        ));
    }

    public function assign_admins($id)
    {
        if (!has_permission('customers', '', 'create') && !has_permission('customers', '', 'edit')) {
            access_denied('customers');
        }
        $success = $this->clients_model->assign_admins($this->input->post(), $id);
        if ($success == true) {
            $primary_contact_id = $this->clients_model->get_contact_primary($id)->id;
            $this->clients_model->log_contact_activity($primary_contact_id , 'not_contact_activity_assign_admin', false, serialize(array(
                get_staff_full_name(get_staff_user_id())
            )));
            set_alert('success', _l('updated_successfully', _l('client')));
        }

        redirect(admin_url('client_families/client/' . $id . '?tab=customer_admins'));
    }

    public function delete_customer_admin($customer_id, $staff_id)
    {
        if (!has_permission('customers', '', 'create') && !has_permission('customers', '', 'edit')) {
            access_denied('customers');
        }

        $this->db->where('customer_id', $customer_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->delete('tblcustomeradmins');
        redirect(admin_url('client_families/client/'.$customer_id).'?tab=customer_admins');
    }

    public function delete_contact($customer_id, $id)
    {
        if (!has_permission('customers', '', 'delete')) {
            if (!is_customer_admin($customer_id)) {
                access_denied('customers');
            }
        }

        $this->clients_model->delete_contact($id);
        redirect(admin_url('client_families/client/' . $customer_id . '?tab=contacts'));
    }

    public function contacts($client_id)
    {
        $this->app->get_table_data('contacts', array(
            'client_id' => $client_id,
        ));

//        if (!has_permission('customers', '', 'view')) {
//            if (!have_assigned_customers() && !has_permission('customers', '', 'create')) {
//                ajax_access_denied();
//            }
//        }
//
//        $this->perfex_base->get_table_data('client_families');
    }

    public function upload_attachment($id)
    {
        handle_contact_attachments_upload($id);
    }

    public function add_external_attachment()
    {
        if ($this->input->post()) {
            $this->misc_model->add_attachment_to_database($this->input->post('clientid'), 'customer', $this->input->post('files'), $this->input->post('external'));
        }
    }

    public function delete_attachment($contact_id, $id)
    {
        if (has_permission('customers', '', 'delete')) {
            $this->clients_model->delete_attachment($id);
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    /* Delete client */
    public function delete($contact_id)
    {
        if (!has_permission('customers', '', 'delete')) {
            access_denied('customers');
        }
        if (!$contact_id) {
            redirect(admin_url('clients'));
        }
        $response = $this->clients_model->delete_contact($contact_id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('client_delete_invoices_warning'));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('client')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('client_lowercase')));
        }
        redirect(admin_url('client_families'));
    }

    /* Staff can login as client */
    public function login_as_client($id)
    {
        if (is_admin()) {
            $this->clients_model->login_as_client($id);
        }
        do_action('after_contact_login');
        redirect(site_url());
    }

    public function get_customer_billing_and_shipping_details($id)
    {
        echo json_encode($this->clients_model->get_customer_billing_and_shipping_details($id));
    }

    /* Change client status / active / inactive */
    public function change_contact_status($id, $status)
    {
        if (has_permission('customers', '', 'edit') || is_customer_admin(get_user_id_by_contact_id($id))) {
            if ($this->input->is_ajax_request()) {
                $this->clients_model->change_contact_status($id, $status);
            }
        }
    }

    /* Change client status / active / inactive */
    public function change_client_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->clients_model->change_client_status($id, $status);
        }
    }

    /* Zip function for credit notes */
    public function zip_credit_notes($id)
    {
        $has_permission_view = has_permission('credit_notes', '', 'view');

        if (!$has_permission_view && !has_permission('credit_notes', '', 'view_own')) {
            access_denied('Zip Customer Credit Notes');
        }

        if ($this->input->post()) {
            $status        = $this->input->post('credit_note_zip_status');
            $zip_file_name = $this->input->post('file_name');
            if ($this->input->post('zip-to') && $this->input->post('zip-from')) {
                $from_date = to_sql_date($this->input->post('zip-from'));
                $to_date   = to_sql_date($this->input->post('zip-to'));
                if ($from_date == $to_date) {
                    $this->db->where('date', $from_date);
                } else {
                    $this->db->where('date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                }
            }
            $this->db->select('id');
            $this->db->from('tblcreditnotes');
            if ($status != 'all') {
                $this->db->where('status', $status);
            }
            $this->db->where('clientid', $id);
            $this->db->order_by('number', 'desc');

            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }
            $credit_notes = $this->db->get()->result_array();

            $this->load->model('credit_notes_model');

            $this->load->helper('file');
            if (!is_really_writable(TEMP_FOLDER)) {
                show_error('/temp folder is not writable. You need to change the permissions to 755');
            }

            $dir = TEMP_FOLDER . $zip_file_name;

            if (is_dir($dir)) {
                delete_dir($dir);
            }

            if (count($credit_notes) == 0) {
                set_alert('warning', _l('client_zip_no_data_found', _l('credit_notes')));
                redirect(admin_url('client_families/client/' . $id . '?group=credit_notes'));
            }

            mkdir($dir, 0777);

            foreach ($credit_notes as $credit_note) {
                $credit_note    = $this->credit_notes_model->get($credit_note['id']);
                $this->pdf_zip   = credit_note_pdf($credit_note);
                $_temp_file_name = slug_it(format_credit_note_number($credit_note->id));
                $file_name       = $dir . '/' . strtoupper($_temp_file_name);
                $this->pdf_zip->Output($file_name . '.pdf', 'F');
            }

            $this->load->library('zip');
            // Read the credit notes
            $this->zip->read_dir($dir, false);
            // Delete the temp directory for the client
            delete_dir($dir);
            $this->zip->download(slug_it(get_option('companyname')) . '-credit-notes-' . $zip_file_name . '.zip');
            $this->zip->clear_data();
        }
    }

    public function zip_invoices($id)
    {
        $has_permission_view = has_permission('invoices', '', 'view');
        if (!$has_permission_view && !has_permission('invoices', '', 'view_own')) {
            access_denied('Zip Customer Invoices');
        }
        if ($this->input->post()) {
            $status        = $this->input->post('invoice_zip_status');
            $zip_file_name = $this->input->post('file_name');
            if ($this->input->post('zip-to') && $this->input->post('zip-from')) {
                $from_date = to_sql_date($this->input->post('zip-from'));
                $to_date   = to_sql_date($this->input->post('zip-to'));
                if ($from_date == $to_date) {
                    $this->db->where('date', $from_date);
                } else {
                    $this->db->where('date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                }
            }
            $this->db->select('id');
            $this->db->from('tblinvoices');
            if ($status != 'all') {
                $this->db->where('status', $status);
            }
            $this->db->where('clientid', $id);
            $this->db->order_by('number,YEAR(date)', 'desc');

            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }

            $invoices = $this->db->get()->result_array();
            $this->load->model('invoices_model');
            $this->load->helper('file');
            if (!is_really_writable(TEMP_FOLDER)) {
                show_error('/temp folder is not writable. You need to change the permissions to 755');
            }
            $dir = TEMP_FOLDER . $zip_file_name;
            if (is_dir($dir)) {
                delete_dir($dir);
            }
            if (count($invoices) == 0) {
                set_alert('warning', _l('client_zip_no_data_found', _l('invoices')));
                redirect(admin_url('client_families/client/' . $id . '?group=invoices'));
            }
            mkdir($dir, 0777);
            foreach ($invoices as $invoice) {
                $invoice_data    = $this->invoices_model->get($invoice['id']);
                $this->pdf_zip   = invoice_pdf($invoice_data);
                $_temp_file_name = slug_it(format_invoice_number($invoice_data->id));
                $file_name       = $dir . '/' . strtoupper($_temp_file_name);
                $this->pdf_zip->Output($file_name . '.pdf', 'F');
            }
            $this->load->library('zip');
            // Read the invoices
            $this->zip->read_dir($dir, false);
            // Delete the temp directory for the client
            delete_dir($dir);
            $this->zip->download(slug_it(get_option('companyname')) . '-invoices-' . $zip_file_name . '.zip');
            $this->zip->clear_data();
        }
    }

    /* Since version 1.0.2 zip client invoices */
    public function zip_estimates($id)
    {
        $has_permission_view = has_permission('estimates', '', 'view');
        if (!$has_permission_view && !has_permission('estimates', '', 'view_own')) {
            access_denied('Zip Customer Estimates');
        }


        if ($this->input->post()) {
            $status        = $this->input->post('estimate_zip_status');
            $zip_file_name = $this->input->post('file_name');
            if ($this->input->post('zip-to') && $this->input->post('zip-from')) {
                $from_date = to_sql_date($this->input->post('zip-from'));
                $to_date   = to_sql_date($this->input->post('zip-to'));
                if ($from_date == $to_date) {
                    $this->db->where('date', $from_date);
                } else {
                    $this->db->where('date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                }
            }
            $this->db->select('id');
            $this->db->from('tblestimates');
            if ($status != 'all') {
                $this->db->where('status', $status);
            }
            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }
            $this->db->where('clientid', $id);
            $this->db->order_by('number,YEAR(date)', 'desc');
            $estimates = $this->db->get()->result_array();
            $this->load->helper('file');
            if (!is_really_writable(TEMP_FOLDER)) {
                show_error('/temp folder is not writable. You need to change the permissions to 777');
            }
            $this->load->model('estimates_model');
            $dir = TEMP_FOLDER . $zip_file_name;
            if (is_dir($dir)) {
                delete_dir($dir);
            }
            if (count($estimates) == 0) {
                set_alert('warning', _l('client_zip_no_data_found', _l('estimates')));
                redirect(admin_url('client_families/client/' . $id . '?group=estimates'));
            }
            mkdir($dir, 0777);
            foreach ($estimates as $estimate) {
                $estimate_data   = $this->estimates_model->get($estimate['id']);
                $this->pdf_zip   = estimate_pdf($estimate_data);
                $_temp_file_name = slug_it(format_estimate_number($estimate_data->id));
                $file_name       = $dir . '/' . strtoupper($_temp_file_name);
                $this->pdf_zip->Output($file_name . '.pdf', 'F');
            }
            $this->load->library('zip');
            // Read the invoices
            $this->zip->read_dir($dir, false);
            // Delete the temp directory for the client
            delete_dir($dir);
            $this->zip->download(slug_it(get_option('companyname')) . '-estimates-' . $zip_file_name . '.zip');
            $this->zip->clear_data();
        }
    }

    public function zip_payments($id)
    {
        if (!$id) {
            die('No user id');
        }

        $has_permission_view = has_permission('payments', '', 'view');
        if (!$has_permission_view && !has_permission('invoices', '', 'view_own')) {
            access_denied('Zip Customer Payments');
        }

        if ($this->input->post('zip-to') && $this->input->post('zip-from')) {
            $from_date = to_sql_date($this->input->post('zip-from'));
            $to_date   = to_sql_date($this->input->post('zip-to'));
            if ($from_date == $to_date) {
                $this->db->where('tblinvoicepaymentrecords.date', $from_date);
            } else {
                $this->db->where('tblinvoicepaymentrecords.date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
            }
        }
        $this->db->select('tblinvoicepaymentrecords.id as paymentid');
        $this->db->from('tblinvoicepaymentrecords');
        $this->db->where('tblclients.userid', $id);
        if (!$has_permission_view) {
            $this->db->where('invoiceid IN (SELECT id FROM tblinvoices WHERE addedfrom=' . get_staff_user_id() . ')');
        }
        $this->db->join('tblinvoices', 'tblinvoices.id = tblinvoicepaymentrecords.invoiceid', 'left');
        $this->db->join('tblclients', 'tblclients.userid = tblinvoices.clientid', 'left');
        if ($this->input->post('paymentmode')) {
            $this->db->where('paymentmode', $this->input->post('paymentmode'));
        }
        $payments      = $this->db->get()->result_array();
        $zip_file_name = $this->input->post('file_name');
        $this->load->helper('file');
        if (!is_really_writable(TEMP_FOLDER)) {
            show_error('/temp folder is not writable. You need to change the permissions to 777');
        }
        $dir = TEMP_FOLDER . $zip_file_name;
        if (is_dir($dir)) {
            delete_dir($dir);
        }
        if (count($payments) == 0) {
            set_alert('warning', _l('client_zip_no_data_found', _l('payments')));
            redirect(admin_url('client_families/client/' . $id . '?group=payments'));
        }
        mkdir($dir, 0777);
        $this->load->model('payments_model');
        $this->load->model('invoices_model');
        foreach ($payments as $payment) {
            $payment_data               = $this->payments_model->get($payment['paymentid']);
            $payment_data->invoice_data = $this->invoices_model->get($payment_data->invoiceid);
            $this->pdf_zip              = payment_pdf($payment_data);
            $file_name                  = $dir;
            $file_name .= '/' . strtoupper(_l('payment'));
            $file_name .= '-' . strtoupper($payment_data->paymentid) . '.pdf';
            $this->pdf_zip->Output($file_name, 'F');
        }
        $this->load->library('zip');
        // Read the invoices
        $this->zip->read_dir($dir, false);
        // Delete the temp directory for the client
        delete_dir($dir);
        $this->zip->download(slug_it(get_option('companyname')) . '-payments-' . $zip_file_name . '.zip');
        $this->zip->clear_data();
    }

    public function import()
    {
        if (!has_permission('customers', '', 'create')) {
            access_denied('customers');
        }
        $country_fields = array('country', 'billing_country', 'shipping_country');

        $simulate_data  = array();
        $total_imported = 0;
        if ($this->input->post()) {

            // Used when checking existing company to merge contact
            $contactFields = $this->db->list_fields('tblcontacts');

            if (isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
                // Get the temp file path
                $tmpFilePath = $_FILES['file_csv']['tmp_name'];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    // Setup our new file path
                    $newFilePath = TEMP_FOLDER . $_FILES['file_csv']['name'];
                    if (!file_exists(TEMP_FOLDER)) {
                        mkdir(TEMP_FOLDER, 777);
                    }
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $import_result = true;
                        $fd            = fopen($newFilePath, 'r');
                        $rows          = array();
                        while ($row = fgetcsv($fd)) {
                            $rows[] = $row;
                        }

                        $data['total_rows_post'] = count($rows);
                        fclose($fd);
                        if (count($rows) <= 1) {
                            set_alert('warning', 'Not enought rows for importing');
                            redirect(admin_url('client_families/import'));
                        }
                        unset($rows[0]);
                        if ($this->input->post('simulate')) {
                            if (count($rows) > 500) {
                                set_alert('warning', 'Recommended splitting the CSV file into smaller files. Our recomendation is 500 row, your CSV file has ' . count($rows));
                            }
                        }
                        $client_contacts_fields = $this->db->list_fields('tblcontacts');
                        $i                      = 0;
                        foreach ($client_contacts_fields as $cf) {
                            if ($cf == 'phonenumber') {
                                $client_contacts_fields[$i] = 'contact_phonenumber';
                            }
                            $i++;
                        }
                        $db_temp_fields = $this->db->list_fields('tblclients');
                        $db_temp_fields = array_merge($client_contacts_fields, $db_temp_fields);
                        $db_fields      = array();
                        foreach ($db_temp_fields as $field) {
                            if (in_array($field, $this->not_importable_clients_fields)) {
                                continue;
                            }
                            $db_fields[] = $field;
                        }
                        $custom_fields = get_custom_fields('customers');
                        $_row_simulate = 0;

                        $required = array(
                            'firstname',
                            'lastname',
                            'email',
                        );

                        if (get_option('company_is_required') == 1) {
                            array_push($required, 'company');
                        }

                        foreach ($rows as $row) {
                            // do for db fields
                            $insert    = array();
                            $duplicate = false;
                            for ($i = 0; $i < count($db_fields); $i++) {
                                if (!isset($row[$i])) {
                                    continue;
                                }
                                if ($db_fields[$i] == 'email') {
                                    $email_exists = total_rows('tblcontacts', array(
                                        'email' => $row[$i],
                                    ));
                                    // don't insert duplicate emails
                                    if ($email_exists > 0) {
                                        $duplicate = true;
                                    }
                                }
                                // Avoid errors on required fields;
                                if (in_array($db_fields[$i], $required) && $row[$i] == '' && $db_fields[$i] != 'company') {
                                    $row[$i] = '/';
                                } elseif (in_array($db_fields[$i], $country_fields)) {
                                    if ($row[$i] != '') {
                                        if (!is_numeric($row[$i])) {
                                            $this->db->where('iso2', $row[$i]);
                                            $this->db->or_where('short_name', $row[$i]);
                                            $this->db->or_where('long_name', $row[$i]);
                                            $country = $this->db->get('tblcountries')->row();
                                            if ($country) {
                                                $row[$i] = $country->country_id;
                                            } else {
                                                $row[$i] = 0;
                                            }
                                        }
                                    } else {
                                        $row[$i] = 0;
                                    }
                                }
                                $insert[$db_fields[$i]] = $row[$i];
                            }

                            if ($duplicate == true) {
                                continue;
                            }
                            if (count($insert) > 0) {
                                $total_imported++;
                                $insert['datecreated'] = date('Y-m-d H:i:s');
                                if ($this->input->post('default_pass_all')) {
                                    $insert['password'] = $this->input->post('default_pass_all');
                                }
                                if (!$this->input->post('simulate')) {
                                    $insert['donotsendwelcomeemail'] = true;
                                    foreach ($insert as $key =>$val) {
                                        $insert[$key] = trim($val);
                                    }

                                    if (isset($insert['company']) && $insert['company'] != '' && $insert['company'] != '/') {
                                        if (total_rows('tblclients', array('company'=>$insert['company'])) === 1) {
                                            $this->db->where('company', $insert['company']);
                                            $existingCompany = $this->db->get('tblclients')->row();
                                            $tmpInsert = array();

                                            foreach ($insert as $key=>$val) {
                                                foreach ($contactFields as $tmpContactField) {
                                                    if (isset($insert[$tmpContactField])) {
                                                        $tmpInsert[$tmpContactField] = $insert[$tmpContactField];
                                                    }
                                                }
                                            }
                                            $tmpInsert['donotsendwelcomeemail'] = true;
                                            if (isset($insert['contact_phonenumber'])) {
                                                $tmpInsert['phonenumber'] = $insert['contact_phonenumber'];
                                            }

                                            $contactid = $this->clients_model->add_contact($tmpInsert, $existingCompany->userid, true);

                                            continue;
                                        }
                                    }
                                    $insert['is_primary'] = 1;

                                    $clientid                        = $this->clients_model->add($insert, true);
                                    if ($clientid) {
                                        if ($this->input->post('groups_in[]')) {
                                            $groups_in = $this->input->post('groups_in[]');
                                            foreach ($groups_in as $group) {
                                                $this->db->insert('tblcustomergroups_in', array(
                                                    'customer_id' => $clientid,
                                                    'groupid' => $group,
                                                ));
                                            }
                                        }
                                        if (!has_permission('customers', '', 'view')) {
                                            $assign['customer_admins']   = array();
                                            $assign['customer_admins'][] = get_staff_user_id();
                                            $this->clients_model->assign_admins($assign, $clientid);
                                        }
                                    }
                                } else {
                                    foreach ($country_fields as $country_field) {
                                        if (array_key_exists($country_field, $insert)) {
                                            if ($insert[$country_field] != 0) {
                                                $c = get_country($insert[$country_field]);
                                                if ($c) {
                                                    $insert[$country_field] = $c->short_name;
                                                }
                                            } elseif ($insert[$country_field] == 0) {
                                                $insert[$country_field] = '';
                                            }
                                        }
                                    }
                                    $simulate_data[$_row_simulate] = $insert;
                                    $clientid                      = true;
                                }
                                if ($clientid) {
                                    $insert = array();
                                    foreach ($custom_fields as $field) {
                                        if (!$this->input->post('simulate')) {
                                            if ($row[$i] != '') {
                                                $this->db->insert('tblcustomfieldsvalues', array(
                                                    'relid' => $clientid,
                                                    'fieldid' => $field['id'],
                                                    'value' => $row[$i],
                                                    'fieldto' => 'customers',
                                                ));
                                            }
                                        } else {
                                            $simulate_data[$_row_simulate][$field['name']] = $row[$i];
                                        }
                                        $i++;
                                    }
                                }
                            }
                            $_row_simulate++;
                            if ($this->input->post('simulate') && $_row_simulate >= 100) {
                                break;
                            }
                        }
                        unlink($newFilePath);
                    }
                } else {
                    set_alert('warning', _l('import_upload_failed'));
                }
            }
        }
        if (count($simulate_data) > 0) {
            $data['simulate'] = $simulate_data;
        }
        if (isset($import_result)) {
            set_alert('success', _l('import_total_imported', $total_imported));
        }
        $data['groups']         = $this->clients_model->get_groups();
        $data['not_importable'] = $this->not_importable_clients_fields;
        $data['title']          = _l('import');
        $this->load->view('admin/clients/import', $data);
    }

    public function groups()
    {
        if (!is_admin()) {
            access_denied('Customer Groups');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('customers_groups');
        }
        $data['title'] = _l('customer_groups');
        $this->load->view('admin/client_families/groups_manage', $data);
    }

    public function group()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $success = $this->clients_model->add_group($data);
                $message = '';
                if ($success == true) {
                    $message = _l('added_successfully', _l('customer_group'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message,
                ));
            } else {
                $success = $this->clients_model->edit_group($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('customer_group'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message,
                ));
            }
        }
    }

    public function delete_group($id)
    {
        if (!is_admin()) {
            access_denied('Delete Customer Group');
        }
        if (!$id) {
            redirect(admin_url('client_families/groups'));
        }
        $response = $this->clients_model->delete_group($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('customer_group')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('customer_group_lowercase')));
        }
        redirect(admin_url('client_families/groups'));
    }

    public function bulk_action()
    {
        do_action('before_do_bulk_action_for_customers');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids    = $this->input->post('ids');
            $groups = $this->input->post('groups');

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($this->clients_model->delete_contact($id)) {
                            $total_deleted++;
                        }
                    } else {
                        if (!is_array($groups)) {
                            $groups = false;
                        }
                        $this->clients_model->handle_update_groups($id, $groups);
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_clients_deleted', $total_deleted));
        }
    }

    public function bulk_update()
    {
        do_action('before_do_bulk_update_for_customers');
        $total_updated = 0;
        if ($this->input->post()) {
            $ids    = $this->input->post('ids');
            $field = $this->input->post('field');
            $value = $this->input->post('value');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $arrFieldInfo =  explode("|",$field);
                    $fieldName = $arrFieldInfo[0];
                    $fieldType = $arrFieldInfo[1];
                    $dataType = $arrFieldInfo[2];
                    $customFieldId =  $arrFieldInfo[3];
                    if($fieldType=="standard"){
                        $data = array();
                        $data[$fieldName] = $value;
                        $this->clients_model->update_contact($data,$id);
                    }

                    if($fieldType=="related_to_tags"){
                        handle_tags_save($value, $id, 'contact');
                    }

                    if($fieldType =="custome_field_contacts"){
                        if($dataType=="input"||$dataType=="number"||$dataType=="date_picker"||$dataType=="date_picker_time"){
                            $data = array();
                            $custom_fields= array();
                            $contacts = array();
                            $contacts[$customFieldId] = $value;
                            $custom_fields["contacts"] = $contacts;
                            $data[custom_fields] = $custom_fields;
                            $this->clients_model->update_contact($data,$id);
                        }
                    }

                    if($fieldType=="related_to_company"){
                        $client_name = trim($value);
                        if($client_name=="") break;
                        $search_client = $this->clients_model->get_by_name($client_name);
                        if($search_client){

                            $contact_primary = $this->clients_model->get_contact_primary($search_client->userid);
                            $this->clients_model-> update_move_contact ($id,$search_client->userid,$contact_primary?0:1);
                        }else{
                            $data=array();
                            $data["company"] = $client_name;
                            $client_id = $this->clients_model->add($data);
                            $contact_primary = $this->clients_model->get_contact_primary($client_id);
                            $this->clients_model-> update_move_contact ($id,$client_id,$contact_primary?0:1);
                        }
                    }
                    $total_updated++;
                }
            }
        }
        set_alert('success', _l('total_contacts_updated', $total_updated));
    }

    public function add_campaign_bulk_action()
    {
        do_action('before_do_add_campaign_bulk_action_for_customers');
        $this->load->model('Trigger_model');
        $count_record =0;
        if ($this->input->post()) {
            $ids    = $this->input->post('ids');
            $count_record= count($ids);
            $campaigns = $this->input->post('campaigns');
            if(is_array($campaigns)) {
                foreach ($campaigns as $campaign) {
                    if (is_array($ids)) {
                        $this->Trigger_model->updateCampiagnKinds($ids,$campaign,'contact');
                    }
                }

            }
        }

        set_alert('success', _l('total_clients_add_campaign', $count_record));
    }

    public function vault_entry_create($customer_id)
    {
        $data = $this->input->post();

        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }
        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }

        unset($data['id']);
        $data['creator'] = get_staff_user_id();
        $data['creator_name'] = get_staff_full_name($data['creator']);
        $data['description'] = nl2br($data['description']);
        $data['password'] = $this->encryption->encrypt($data['password']);

        if (empty($data['port'])) {
            unset($data['port']);
        }

        $this->clients_model->vault_entry_create($data, $customer_id);
        set_alert('success', _l('added_successfully', _l('vault_entry')));
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_entry_update($entry_id)
    {
        $entry = $this->clients_model->get_vault_entry($entry_id);

        if ($entry->creator == get_staff_user_id() || is_admin()) {
            $data = $this->input->post();

            if (isset($data['fakeusernameremembered'])) {
                unset($data['fakeusernameremembered']);
            }
            if (isset($data['fakepasswordremembered'])) {
                unset($data['fakepasswordremembered']);
            }

            $data['last_updated_from'] = get_staff_full_name(get_staff_user_id());
            $data['description'] = nl2br($data['description']);

            if (!empty($data['password'])) {
                $data['password'] = $this->encryption->encrypt($data['password']);
            } else {
                unset($data['password']);
            }

            if (empty($data['port'])) {
                unset($data['port']);
            }

            $this->clients_model->vault_entry_update($entry_id, $data);
            set_alert('success', _l('updated_successfully', _l('vault_entry')));
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_entry_delete($id)
    {
        $entry = $this->clients_model->get_vault_entry($id);
        if ($entry->creator == get_staff_user_id() || is_admin()) {
            $this->clients_model->vault_entry_delete($id);
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_encrypt_password()
    {
        $id = $this->input->post('id');
        $user_password = $this->input->post('user_password');
        $user = $this->staff_model->get(get_staff_user_id());

        $this->load->helper('phpass');

        $hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
        if (!$hasher->CheckPassword($user_password, $user->password)) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(array('error_msg'=>_l('vault_password_user_not_correct')));
            die;
        }

        $vault = $this->clients_model->get_vault_entry($id);
        $password = $this->encryption->decrypt($vault->password);

        // Failed to decrypt
        if (!$password) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode(array('error_msg'=>_l('failed_to_decrypt_password')));
            die;
        }

        echo json_encode(array('password'=>$password));
    }

    public function get_vault_entry($id)
    {
        $entry = $this->clients_model->get_vault_entry($id);
        unset($entry->password);
        $entry->description = clear_textarea_breaks($entry->description);
        echo json_encode($entry);
    }

    public function statement_pdf()
    {
        $customer_id = $this->input->get('customer_id');

        if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('client_families/client/'.$customer_id));
        }

        $from = $this->input->get('from');
        $to = $this->input->get('to');

        $data['statement'] = $this->clients_model->get_statement($customer_id, to_sql_date($from), to_sql_date($to));

        try {
            $pdf            = statement_pdf($data['statement']);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type           = 'D';
        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(slug_it(_l('customer_statement').'-'.$data['statement']['client']->company) . '.pdf', $type);
    }

    public function send_statement()
    {
        $customer_id = $this->input->get('customer_id');

        if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('client_families/client/'.$customer_id));
        }

        $from = $this->input->get('from');
        $to = $this->input->get('to');

        $send_to = $this->input->post('send_to');
        $cc = $this->input->post('cc');

        $success = $this->clients_model->send_statement_to_email($customer_id, $send_to, $from, $to, $cc);
        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('statement_sent_to_client_success'));
        } else {
            set_alert('danger', _l('statement_sent_to_client_fail'));
        }

        redirect(admin_url('client_families/client/' . $customer_id.'?group=statement'));
    }

    public function statement()
    {
        if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }

        $customer_id = $this->input->get('customer_id');
        $from = $this->input->get('from');
        $to = $this->input->get('to');

        $data['statement'] = $this->clients_model->get_statement($customer_id, to_sql_date($from), to_sql_date($to));

        $data['from'] = $from;
        $data['to'] = $to;

        $viewData['html'] = $this->load->view('admin/client_families/groups/_statement', $data, true);

        echo json_encode($viewData);
    }

    public function add_activity()
    {
        $contactid = $this->input->post('contactid');
        $customer_id = $this->input->post('customer_id');
        if (!has_permission('customers', '', 'edit')) {
            if (!is_customer_admin($customer_id)) {
                access_denied('customers');
            }
        }

        if ($this->input->post()) {
            $message = $this->input->post('activity');
            $this->clients_model->log_contact_activity($contactid, $message,false,'',1);

            echo json_encode(array('message'=>"done"));
        }
    }
}

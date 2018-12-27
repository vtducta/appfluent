<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Clients_model extends CRM_Model
{
    private $contact_columns;

    public function __construct()
    {
        parent::__construct();

        $this->contact_columns = do_action('contact_columns', ['firstname', 'lastname', 'email', 'phonenumber', 'title', 'password', 'send_set_password_email', 'donotsendwelcomeemail', 'permissions', 'direction', 'invoice_emails', 'estimate_emails', 'credit_note_emails', 'contract_emails', 'task_emails', 'project_emails', 'ticket_emails', 'is_primary']);

        $this->load->model(['client_vault_entries_model', 'client_groups_model', 'statement_model']);
    }

    public function get_structure_contact(){
        return $this->db->field_data('tblcontacts');
    }
    /**
     * Get client object based on passed clientid if not passed clientid return array of all clients
     * @param  mixed $id    client id
     * @param  array  $where
     * @return mixed
     */
    public function get($id = '', $where = array('tblclients.active' => 1))
    {
        $this->db->select(implode(',', prefixed_table_fields_array('tblclients')) . ',' . get_sql_select_client_company());

        //$this->db->join('tblcountries', 'tblcountries.country_id = tblclients.country', 'left');
        //$this->db->join('tblcontacts', 'tblcontacts.userid = tblclients.userid AND is_primary = 1', 'left');

        if (is_numeric($id)) {
            $this->db->where('tblclients.userid', $id);
            $client = $this->db->get('tblclients')->row();

            if (get_option('company_requires_vat_number_field') == 0) {
                $client->vat = null;
            }

            return $client;
        }

        $this->db->where($where);
        $this->db->order_by('company', 'asc');

        return $this->db->get('tblclients')->result_array();
    }

    public function get_call_log($id = '')
    {
        $this->db->select(implode(',', prefixed_table_fields_array('tblcall_logs')) );

        //$this->db->join('tblcountries', 'tblcountries.country_id = tblclients.country', 'left');
        //$this->db->join('tblcontacts', 'tblcontacts.userid = tblclients.userid AND is_primary = 1', 'left');

        if (is_numeric($id)) {
            $this->db->where('tblcall_logs.id', $id);
            $call_log = $this->db->get('tblcall_logs')->row();
            return $call_log;
        }

        return false;
    }
    public function get_by_name($name = '')
    {


        if($name){
            $this->db->select(implode(',', prefixed_table_fields_array('tblclients')) . ',CASE company WHEN "" THEN (SELECT CONCAT(firstname, " ", lastname) FROM tblcontacts WHERE userid = tblclients.userid and is_primary = 1) ELSE company END as company');

            $this->db->join('tblcountries', 'tblcountries.country_id = tblclients.country', 'left');
            $this->db->join('tblcontacts', 'tblcontacts.userid = tblclients.userid AND is_primary = 1', 'left');
            $this->db->where('tblclients.company', $name);
            $client = $this->db->get('tblclients')->row();

            if (get_option('company_requires_vat_number_field') == 0) {
                $client->vat = null;
            }

            return $client;
        }
        return false;

    }
    /**
     * Get customers contacts
     * @param  mixed $customer_id
     * @param  array  $where       perform where in query
     * @return array
     */
    public function get_contacts($customer_id = '', $where = ['active' => 1])
    {
        $this->db->where($where);
        if ($customer_id != '') {
            $this->db->where('userid', $customer_id);
        }
        $this->db->order_by('is_primary', 'DESC');

        return $this->db->get('tblcontacts')->result_array();
    }

    /**
     * Get single contacts
     * @param  mixed $id contact id
     * @return object
     */
    public function get_contact($id)
    {
        $this->db->where('id', $id);

        return $this->db->get('tblcontacts')->row();
    }

    public function get_contact_primary($customer_id){
        $this->db->where('userid',$customer_id);
        $this->db->where('is_primary',1);
        return $this->db->get('tblcontacts')->row();
    }


    /**
     * @param  array $_POST data
     * @param  integer ID
     * @return boolean
     * Update client informations
     */
    public function update($data, $id, $client_request = false)
    {
        if (isset($data['update_all_other_transactions'])) {
            $update_all_other_transactions = true;
            unset($data['update_all_other_transactions']);
        }

        if (isset($data['update_credit_notes'])) {
            $update_credit_notes = true;
            unset($data['update_credit_notes']);
        }

        $affectedRows = 0;
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if (isset($data['groups_in'])) {
            $groups_in = $data['groups_in'];
            unset($data['groups_in']);
        }

        $data = $this->check_zero_columns($data);

        $_data = do_action('before_client_updated', [
            'userid' => $id,
            'data'   => $data,
        ]);

        $data = $_data['data'];
        $this->db->where('userid', $id);
        $this->db->update('tblclients', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if (isset($update_all_other_transactions) || isset($update_credit_notes)) {
            $transactions_update = [
                    'billing_street'   => $data['billing_street'],
                    'billing_city'     => $data['billing_city'],
                    'billing_state'    => $data['billing_state'],
                    'billing_zip'      => $data['billing_zip'],
                    'billing_country'  => $data['billing_country'],
                    'shipping_street'  => $data['shipping_street'],
                    'shipping_city'    => $data['shipping_city'],
                    'shipping_state'   => $data['shipping_state'],
                    'shipping_zip'     => $data['shipping_zip'],
                    'shipping_country' => $data['shipping_country'],
                ];
            if (isset($update_all_other_transactions)) {

                // Update all invoices except paid ones.
                $this->db->where('clientid', $id);
                $this->db->where('status !=', 2);
                $this->db->update('tblinvoices', $transactions_update);
                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }

                // Update all estimates
                $this->db->where('clientid', $id);
                $this->db->update('tblestimates', $transactions_update);
                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }
            if (isset($update_credit_notes)) {
                $this->db->where('clientid', $id);
                $this->db->where('status !=', 2);
                $this->db->update('tblcreditnotes', $transactions_update);
                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }
        }

        if (!isset($groups_in)) {
            $groups_in = false;
        }

        if ($this->client_groups_model->sync_customer_groups($id, $groups_in)) {
            $affectedRows++;
        }

        if ($affectedRows > 0) {
            do_action('after_client_updated', $id);
            logActivity('Customer Info Updated [ID: ' . $id . ']');

            return true;
        }

        return false;
    }
    public function update_move_contact($contact_id,$client_id,$is_primary=0){
        $data = array();
        $data['userid']= $client_id;
        $data['is_primary'] =$is_primary;
        $this->db->where('id', $contact_id);
        $this->db->update('tblcontacts', $data);
    }
    /**
     * Update contact data
     * @param  array  $data           $_POST data
     * @param  mixed  $id             contact id
     * @param  boolean $client_request is request from customers area
     * @return mixed
     */
    public function update_contact($data, $id, $client_request = false)
    {
        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }

        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }

        if (isset($data['related_to_contact_custom_fields'])) {
            unset($data['related_to_contact_custom_fields']);
        }

        if (isset($data['related_to_company_custom_fields'])) {
            unset($data['related_to_company_custom_fields']);
        }

        foreach (array('fakeusernameremembered', 'fakepasswordremembered', 'DataTables_Table_0_length', 'DataTables_Table_1_length', 'onoffswitch') as $not_used) {
            if (isset($data[$not_used])) {
                unset($data[$not_used]);
            }
        }

        $hook_data['data'] = $data;
        $hook_data['id']   = $id;
        $hook_data         = do_action('before_update_contact', $hook_data);
        $data              = $hook_data['data'];
        $id                = $hook_data['id'];

        $affectedRows = 0;
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $this->load->helper('phpass');
            $hasher                       = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
            $data['password']             = $hasher->HashPassword($data['password']);
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }
        $permissions = array();
        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
            unset($data['permissions']);
        }
        if (isset($data['send_set_password_email'])) {
            $send_set_password_email = true;
            unset($data['send_set_password_email']);
        }
        $contact = $this->get_contact($id);
        if (isset($data['is_primary'])) {
            $data['is_primary'] = 1;
        } else {
            $data['is_primary'] = 0;
        }
        // Contact cant change if is primary or not
        if ($client_request == true) {
            unset($data['is_primary']);
            if (isset($data['email'])) {
                unset($data['email']);
            }
        }
        if (isset($send_set_password_email)) {
            $success = $this->authentication_model->set_password_email($data['email'], 0);
            if ($success) {
                $set_password_email_sent = true;
            }
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if ($client_request == false) {
            $data['invoice_emails']     = isset($data['invoice_emails']) ? 1 :0;
            $data['estimate_emails']    = isset($data['estimate_emails']) ? 1 :0;
            $data['credit_note_emails'] = isset($data['credit_note_emails']) ? 1 :0;
            $data['contract_emails']    = isset($data['contract_emails']) ? 1 :0;
            $data['task_emails']        = isset($data['task_emails']) ? 1 :0;
            $data['project_emails']     = isset($data['project_emails']) ? 1 :0;
            $data['ticket_emails']      = isset($data['ticket_emails']) ? 1 :0;
        }

        $hook_data = do_action('before_update_contact', ['data' => $data, 'id' => $id]);
        $data      = $hook_data['data'];

        $this->db->where('id', $id);
        $this->db->update('tblcontacts', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if (isset($data['is_primary']) && $data['is_primary'] == 1) {
                $this->db->where('userid', $contact->userid);
                $this->db->where('id !=', $id);
                $this->db->update('tblcontacts', [
                    'is_primary' => 0,
                ]);
            }
        }

        if ($client_request == false) {
            $customer_permissions = $this->roles_model->get_contact_permissions($id);
            if (sizeof($customer_permissions) > 0) {
                foreach ($customer_permissions as $customer_permission) {
                    if (!in_array($customer_permission['permission_id'], $permissions)) {
                        $this->db->where('userid', $id);
                        $this->db->where('permission_id', $customer_permission['permission_id']);
                        $this->db->delete('tblcontactpermissions');
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
                foreach ($permissions as $permission) {
                    $this->db->where('userid', $id);
                    $this->db->where('permission_id', $permission);
                    $_exists = $this->db->get('tblcontactpermissions')->row();
                    if (!$_exists) {
                        $this->db->insert('tblcontactpermissions', [
                            'userid'        => $id,
                            'permission_id' => $permission,
                        ]);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
            } else {
                foreach ($permissions as $permission) {
                    $this->db->insert('tblcontactpermissions', [
                        'userid'        => $id,
                        'permission_id' => $permission,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            if ($send_set_password_email) {
                $set_password_email_sent = $this->authentication_model->set_password_email($data['email'], 0);
            }
        }
        if ($affectedRows > 0 && !$set_password_email_sent) {
            logActivity('Contact Updated [ID: ' . $id . ']');

            return true;
        } elseif ($affectedRows > 0 && $set_password_email_sent) {
            return [
                'set_password_email_sent_and_profile_updated' => true,
            ];
        } elseif ($affectedRows == 0 && $set_password_email_sent) {
            return [
                'set_password_email_sent' => true,
            ];
        }

        return false;
    }

    /**
     * Add new contact
     * @param array  $data               $_POST data
     * @param mixed  $customer_id        customer id
     * @param boolean $not_manual_request is manual from admin area customer profile or register, convert to lead
     */
    public function add_contact($data, $customer_id, $not_manual_request = false)
    {
        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }
        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }

        if (isset($data['related_to_contact_custom_fields'])) {
            unset($data['related_to_contact_custom_fields']);
        }

        if (isset($data['related_to_company_custom_fields'])) {
            unset($data['related_to_company_custom_fields']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }



        if (isset($data['send_set_password_email'])) {
            $send_set_password_email = true;
            unset($data['send_set_password_email']);
        }
        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
            unset($data['permissions']);
        }

        $send_welcome_email = true;
        if (isset($data['donotsendwelcomeemail'])) {
            $send_welcome_email = false;
            unset($data['donotsendwelcomeemail']);
        } elseif (strpos($_SERVER['HTTP_REFERER'], 'register') !== false) {
            $send_welcome_email = true;

            // Do not send welcome email if confirmation for registration is enabled
            if (get_option('customers_register_require_confirmation') == '1') {
                $send_welcome_email = false;
            }
            // If client register set this auto contact as primary
            $data['is_primary'] = 1;
        }

        if (isset($data['is_primary'])) {
            $data['is_primary'] = 1;
            $this->db->where('userid', $customer_id);
            $this->db->update('tblcontacts', [
                'is_primary' => 0,
            ]);
        } else {
            $data['is_primary'] = 0;
        }

        $password_before_hash = '';
        $data['userid']       = $customer_id;
        if (isset($data['password'])) {
            $password_before_hash = $data['password'];
            $this->load->helper('phpass');
            $hasher           = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
            $data['password'] = $hasher->HashPassword($data['password']);
        }

        $data['datecreated'] = date('Y-m-d H:i:s');

        $_data = array(
            'data' => $data,
            'not_manual_request' => $not_manual_request,
        );

        $_data = do_action('before_create_contact', $_data);
        $data  = $_data['data'];

        $data['email'] = trim($data['email']);


        if (!$not_manual_request) {
            $data['invoice_emails']     = isset($data['invoice_emails']) ? 1 :0;
            $data['estimate_emails']    = isset($data['estimate_emails']) ? 1 :0;
            $data['credit_note_emails'] = isset($data['credit_note_emails']) ? 1 :0;
            $data['contract_emails']    = isset($data['contract_emails']) ? 1 :0;
            $data['task_emails']        = isset($data['task_emails']) ? 1 :0;
            $data['project_emails']     = isset($data['project_emails']) ? 1 :0;
            $data['ticket_emails']      = isset($data['ticket_emails']) ? 1 :0;
        }

        $hook_data = [
            'data'               => $data,
            'not_manual_request' => $not_manual_request,
        ];

        $hook_data = do_action('before_create_contact', $hook_data);
        $data      = $hook_data['data'];

        $data['email'] = trim($data['email']);

        $this->db->insert('tblcontacts', $data);
        $contact_id = $this->db->insert_id();
        if ($contact_id) {
            if (isset($custom_fields)) {
                handle_custom_fields_post($contact_id, $custom_fields);
            }
            // request from admin area
            if (!isset($permissions) && $not_manual_request == false) {
                $permissions = [];
            } elseif ($not_manual_request == true) {
                $permissions         = [];
                $_permissions        = get_contact_permissions();
                $default_permissions = @unserialize(get_option('default_contact_permissions'));
                if (is_array($default_permissions)) {
                    foreach ($_permissions as $permission) {
                        if (in_array($permission['id'], $default_permissions)) {
                            array_push($permissions, $permission['id']);
                        }
                    }
                }
            }

            if ($not_manual_request == true) {
                // update all email notifications to 0
                $this->db->where('id', $contact_id);
                $this->db->update('tblcontacts', [
                    'invoice_emails'     => 0,
                    'estimate_emails'    => 0,
                    'credit_note_emails' => 0,
                    'contract_emails'    => 0,
                    'task_emails'        => 0,
                    'project_emails'     => 0,
                    'ticket_emails'      => 0,
                ]);
            }
            foreach ($permissions as $permission) {
                $this->db->insert('tblcontactpermissions', [
                    'userid'        => $contact_id,
                    'permission_id' => $permission,
                ]);

                // Auto set email notifications based on permissions
                if ($not_manual_request == true) {
                    if ($permission == 6) {
                        $this->db->where('id', $contact_id);
                        $this->db->update('tblcontacts', ['project_emails' => 1, 'task_emails' => 1]);
                    } elseif ($permission == 3) {
                        $this->db->where('id', $contact_id);
                        $this->db->update('tblcontacts', ['contract_emails' => 1]);
                    } elseif ($permission == 2) {
                        $this->db->where('id', $contact_id);
                        $this->db->update('tblcontacts', ['estimate_emails' => 1]);
                    } elseif ($permission == 1) {
                        $this->db->where('id', $contact_id);
                        $this->db->update('tblcontacts', ['invoice_emails' => 1, 'credit_note_emails' => 1]);
                    } elseif ($permission == 5) {
                        $this->db->where('id', $contact_id);
                        $this->db->update('tblcontacts', ['ticket_emails' => 1]);
                    }
                }
            }

            $lastAnnouncement = $this->db->query('SELECT announcementid FROM tblannouncements WHERE showtousers = 1 AND announcementid = (SELECT MAX(announcementid) FROM tblannouncements)')->row();
            if ($lastAnnouncement) {
                // Get all announcements and set it to read.
                $this->db->select('announcementid')
                ->from('tblannouncements')
                ->where('showtousers', 1)
                ->where('announcementid !=', $lastAnnouncement->announcementid);

                $announcements = $this->db->get()->result_array();
                foreach ($announcements as $announcement) {
                    $this->db->insert('tbldismissedannouncements', [
                        'announcementid' => $announcement['announcementid'],
                        'staff'          => 0,
                        'userid'         => $contact_id,
                    ]);
                }
            }


            if ($send_welcome_email == true) {
                $this->load->model('emails_model');
                $merge_fields = [];
                $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($data['userid'], $contact_id, $password_before_hash));
                $this->emails_model->send_email_template('new-client-created', $data['email'], $merge_fields);
            }

            if ($send_set_password_email) {
                $this->authentication_model->set_password_email($data['email'], 0);
            }

            logActivity('Contact Created [ID: ' . $contact_id . ']');
            do_action('contact_created', $contact_id);

            return $contact_id;
        }

        return false;
    }
    public function update_call_log($data,$call_log_id)
    {
        foreach (array('fakeusernameremembered', 'fakepasswordremembered','DataTables_Table_0_length',
                     'DataTables_Table_1_length', 'onoffswitch',
                     'related_to_contact_custom_fields','call_log_id') as $not_used) {
            if (isset($data[$not_used])) {
                unset($data[$not_used]);
            }
        }


        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        $data['call_type'] ='inbound';
        if(isset($data['settings'])){
            if($data['settings']['inbound']!='inbound'){
                $data['call_type'] ='outbound';
            }
            unset($data['settings']);
        }
        $duration = 0;
        if (isset($data['duration'])){
            $duration = $data['duration']['hour']*60*60 +  $data['duration']['minute']*60 +  $data['duration']['second'];
            unset($data['duration']);
        }

        $data['duration'] = $duration;


        //$data['created_date'] = date('Y-m-d H:i:s');

        $data                = do_action('before_call_log_updated', $data);

        //var_dump($data); die;
        $this->db->where('id', $call_log_id);
        $this->db->update('tblcall_logs', $data);
        if (isset($custom_fields)) {
            handle_custom_fields_post($call_log_id, $custom_fields);
        }

        logActivity('New call log created', get_staff_user_id());

        return true;
    }
    public function add_call_log($data)
    {
        foreach (array('fakeusernameremembered', 'fakepasswordremembered','DataTables_Table_0_length',
                     'DataTables_Table_1_length', 'onoffswitch',
                     'related_to_contact_custom_fields','call_log_id') as $not_used) {
            if (isset($data[$not_used])) {
                unset($data[$not_used]);
            }
        }


        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        $data['call_type'] ='inbound';
        if(isset($data['settings'])){
            if($data['settings']['inbound']!='inbound'){
                $data['call_type'] ='outbound';
            }
            unset($data['settings']);
        }
        $duration = 0;
        if (isset($data['duration'])){
            $duration = $data['duration']['hour']*60*60 +  $data['duration']['minute']*60 +  $data['duration']['second'];
            unset($data['duration']);
        }

        $data['duration'] = $duration;


        $data['created_date'] = date('Y-m-d H:i:s');

        $data                = do_action('before_call_log_added', $data);

        //var_dump($data); die;

        $this->db->insert('tblcall_logs', $data);


        $call_log_id = $this->db->insert_id();
        if ($call_log_id) {
            if (isset($custom_fields)) {
                handle_custom_fields_post($call_log_id, $custom_fields);
            }

            logActivity('New call log created', get_staff_user_id());

        }
        return $call_log_id;
    }
    /**
     * @param array $_POST data
     * @param client_request is this request from the customer area
     * @return integer Insert ID
     * Add new client to database
     */
    public function add($data, $client_or_lead_convert_request = false)
    {
        foreach (array('fakeusernameremembered', 'fakepasswordremembered','DataTables_Table_0_length', 'DataTables_Table_1_length', 'onoffswitch') as $not_used) {
            if (isset($data[$not_used])) {
                unset($data[$not_used]);
            }
        }

        $contact_data = array();
        foreach ($this->contact_data as $field) {
            if (isset($data[$field])) {
                $contact_data[$field] = $data[$field];
                // Phonenumber is also used for the company profile
                if ($field != 'phonenumber') {
                    unset($data[$field]);
                }
            }
        }
        // From customer profile register
        if (isset($data['contact_phonenumber'])) {
            $contact_data['phonenumber'] = $data['contact_phonenumber'];
            unset($data['contact_phonenumber']);
        }

        if (isset($data['passwordr'])) {
            unset($data['passwordr']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        if (isset($data['groups_in'])) {
            $groups_in = $data['groups_in'];
            unset($data['groups_in']);
        }

        $data = $this->check_zero_columns($data);

        // From v.1.9.4 these fields are textareas
        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);
        if (isset($data['billing_street'])) {
            $data['billing_street'] = trim($data['billing_street']);
            $data['billing_street'] = nl2br($data['billing_street']);
        }
        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }
        $data['datecreated'] = date('Y-m-d H:i:s');

        if(is_staff_logged_in()) {
            $data['addedfrom'] = get_staff_user_id();
        }

        $data                = do_action('before_client_added', $data);
        $this->db->insert('tblclients', $data);
        $userid = $this->db->insert_id();
        if ($userid) {
            if (isset($custom_fields)) {
                $_custom_fields = $custom_fields;
                // Possible request from the register area with 2 types of custom fields for contact and for comapny/customer
                if (count($custom_fields) == 2) {
                    unset($custom_fields);
                    $custom_fields['customers']                = $_custom_fields['customers'];
                    $contact_data['custom_fields']['contacts'] = $_custom_fields['contacts'];
                } elseif (count($custom_fields) == 1) {
                    if (isset($_custom_fields['contacts'])) {
                        $contact_data['custom_fields']['contacts'] = $_custom_fields['contacts'];
                        unset($custom_fields);
                    }
                }
                handle_custom_fields_post($userid, $custom_fields);
            }


            /**
             * Used in Import, Lead Convert, Register
             */
            if ($client_or_lead_convert_request == true) {
                $contact_id = $this->add_contact($contact_data, $userid, $client_or_lead_convert_request);
            }
            if (isset($groups_in)) {
                foreach ($groups_in as $group) {
                    $this->db->insert('tblcustomergroups_in', array(
                        'customer_id' => $userid,
                        'groupid' => $group,
                    ));
                }
            }
            do_action('after_client_added', $userid);
            $_new_client_log = $data['company'];
            if ($_new_client_log == '' && isset($contact_id)) {
                $_new_client_log = get_contact_full_name($contact_id);
            }

            $_is_staff = null;
            if (!is_client_logged_in() && is_staff_logged_in()) {
                $_new_client_log .= ' From Staff: ' . get_staff_user_id();
                $_is_staff = get_staff_user_id();
            }
            logActivity('New Client Created [' . $_new_client_log . ']', $_is_staff);

        }

        return $userid;
    }

    /**
     * Used to update company details from customers area
     * @param  array $data $_POST data
     * @param  mixed $id
     * @return boolean
     */
    public function update_company_details($data, $id)
    {
        $affectedRows = 0;
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        if (isset($data['country']) && $data['country'] == '' || !isset($data['country'])) {
            $data['country'] = 0;
        }
        if (isset($data['billing_country']) && $data['billing_country'] == '') {
            $data['billing_country'] = 0;
        }
        if (isset($data['shipping_country']) && $data['shipping_country'] == '') {
            $data['shipping_country'] = 0;
        }

        // From v.1.9.4 these fields are textareas
        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);
        if (isset($data['billing_street'])) {
            $data['billing_street'] = trim($data['billing_street']);
            $data['billing_street'] = nl2br($data['billing_street']);
        }
        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }

        $this->db->where('userid', $id);
        $this->db->update('tblclients', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            do_action('customer_updated_company_info', $id);
            logActivity('Customer Info Updated From Clients Area [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Get customer staff members that are added as customer admins
     * @param  mixed $id customer id
     * @return array
     */
    public function get_admins($id)
    {
        $this->db->where('customer_id', $id);

        return $this->db->get('tblcustomeradmins')->result_array();
    }

    /**
     * Get unique staff id's of customer admins
     * @return array
     */
    public function get_customers_admin_unique_ids()
    {
        return $this->db->query('SELECT DISTINCT(staff_id) FROM tblcustomeradmins')->result_array();
    }

    /**
     * Assign staff members as admin to customers
     * @param  array $data $_POST data
     * @param  mixed $id   customer id
     * @return boolean
     */
    public function assign_admins($data, $id)
    {
        $affectedRows = 0;

        if (count($data) == 0) {
            $this->db->where('customer_id', $id);
            $this->db->delete('tblcustomeradmins');
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        } else {
            $current_admins     = $this->get_admins($id);
            $current_admins_ids = [];
            foreach ($current_admins as $c_admin) {
                array_push($current_admins_ids, $c_admin['staff_id']);
            }
            foreach ($current_admins_ids as $c_admin_id) {
                if (!in_array($c_admin_id, $data['customer_admins'])) {
                    $this->db->where('staff_id', $c_admin_id);
                    $this->db->where('customer_id', $id);
                    $this->db->delete('tblcustomeradmins');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            foreach ($data['customer_admins'] as $n_admin_id) {
                if (total_rows('tblcustomeradmins', [
                    'customer_id' => $id,
                    'staff_id' => $n_admin_id,
                ]) == 0) {
                    $this->db->insert('tblcustomeradmins', [
                        'customer_id'   => $id,
                        'staff_id'      => $n_admin_id,
                        'date_assigned' => date('Y-m-d H:i:s'),
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param  integer ID
     * @return boolean
     * Delete client, also deleting rows from, dismissed client announcements, ticket replies, tickets, autologin, user notes
     */
    public function delete($id)
    {
        $affectedRows = 0;

        if (!is_gdpr() && is_reference_in_table('clientid', 'tblinvoices', $id)) {
            return [
                'referenced' => true,
            ];
        }

        if (!is_gdpr() && is_reference_in_table('clientid', 'tblestimates', $id)) {
            return [
                'referenced' => true,
            ];
        }

        if (!is_gdpr() && is_reference_in_table('clientid', 'tblcreditnotes', $id)) {
            return [
                'referenced' => true,
            ];
        }

        do_action('before_client_deleted', $id);

        $last_activity = get_last_system_activity_id();
        $company       = get_company_name($id);

        $this->db->where('userid', $id);
        $this->db->delete('tblclients');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            // Delete all user contacts
            $this->db->where('userid', $id);
            $contacts = $this->db->get('tblcontacts')->result_array();
            foreach ($contacts as $contact) {
                $this->delete_contact($contact['id']);
            }

            // Delete all tickets start here
            $this->db->where('userid', $id);
            $tickets = $this->db->get('tbltickets')->result_array();
            $this->load->model('tickets_model');
            foreach ($tickets as $ticket) {
                $this->tickets_model->delete($ticket['ticketid']);
            }

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'customer');
            $this->db->delete('tblnotes');

            if (is_gdpr() && get_option('gdpr_on_forgotten_remove_invoices_credit_notes') == '1') {
                $this->load->model('invoices_model');
                $this->db->where('clientid', $id);
                $invoices = $this->db->get('tblinvoices')->result_array();
                foreach ($invoices as $invoice) {
                    $this->invoices_model->delete($invoice['id'], true);
                }

                $this->load->model('credit_notes_model');
                $this->db->where('clientid', $id);
                $credit_notes = $this->db->get('tblcreditnotes')->result_array();
                foreach ($credit_notes as $credit_note) {
                    $this->credit_notes_model->delete($credit_note['id'], true);
                }
            } elseif (is_gdpr()) {
                $this->db->where('clientid', $id);
                $this->db->update('tblinvoices', ['deleted_customer_name' => $company]);

                $this->db->where('clientid', $id);
                $this->db->update('tblcreditnotes', ['deleted_customer_name' => $company]);
            }

            $this->db->where('clientid', $id);
            $this->db->update('tblcreditnotes', [
                'clientid'   => 0,
                'project_id' => 0,
            ]);

            $this->db->where('clientid', $id);
            $this->db->update('tblinvoices', [
                'clientid'                 => 0,
                'recurring'                => 0,
                'recurring_type'           => null,
                'custom_recurring'         => 0,
                'cycles'                   => 0,
                'last_recurring_date'      => null,
                'project_id'               => 0,
                'subscription_id'          => 0,
                'cancel_overdue_reminders' => 1,
                'last_overdue_reminder'    => null,
            ]);

            if (is_gdpr() && get_option('gdpr_on_forgotten_remove_estimates') == '1') {
                $this->load->model('estimates_model');
                $this->db->where('clientid', $id);
                $estimates = $this->db->get('tblestimates')->result_array();
                foreach ($estimates as $estimate) {
                    $this->estimates_model->delete($estimate['id'], true);
                }
            } elseif (is_gdpr()) {
                $this->db->where('clientid', $id);
                $this->db->update('tblestimates', ['deleted_customer_name' => $company]);
            }

            $this->db->where('clientid', $id);
            $this->db->update('tblestimates', [
                'clientid'           => 0,
                'project_id'         => 0,
                'is_expiry_notified' => 1,
            ]);

            $this->load->model('subscriptions_model');
            $this->db->where('clientid', $id);
            $subscriptions = $this->db->get('tblsubscriptions')->result_array();
            foreach ($subscriptions as $subscription) {
                $this->subscriptions_model->delete($subscription['id'], true);
            }
            // Get all client contracts
            $this->load->model('contracts_model');
            $this->db->where('client', $id);
            $contracts = $this->db->get('tblcontracts')->result_array();
            foreach ($contracts as $contract) {
                $this->contracts_model->delete($contract['id']);
            }
            // Delete the custom field values
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'customers');
            $this->db->delete('tblcustomfieldsvalues');

            // Get customer related tasks
            $this->db->where('rel_type', 'customer');
            $this->db->where('rel_id', $id);
            $tasks = $this->db->get('tblstafftasks')->result_array();

            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id'], false);
            }

            $this->db->where('rel_type', 'customer');
            $this->db->where('rel_id', $id);
            $this->db->delete('tblreminders');

            $this->db->where('customer_id', $id);
            $this->db->delete('tblcustomeradmins');

            $this->db->where('customer_id', $id);
            $this->db->delete('tblvault');

            $this->db->where('customer_id', $id);
            $this->db->delete('tblcustomergroups_in');

            $this->load->model('proposals_model');
            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'customer');
            $proposals = $this->db->get('tblproposals')->result_array();
            foreach ($proposals as $proposal) {
                $this->proposals_model->delete($proposal['id']);
            }
            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'customer');
            $attachments = $this->db->get('tblfiles')->result_array();
            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            $this->db->where('clientid', $id);
            $expenses = $this->db->get('tblexpenses')->result_array();

            $this->load->model('expenses_model');
            foreach ($expenses as $expense) {
                $this->expenses_model->delete($expense['id'], true);
            }

            $this->db->where('client_id', $id);
            $this->db->delete('tblusermeta');

            $this->db->where('client_id', $id);
            $this->db->update('tblleads', ['client_id' => 0]);

            // Delete all projects
            $this->load->model('projects_model');
            $this->db->where('clientid', $id);
            $projects = $this->db->get('tblprojects')->result_array();
            foreach ($projects as $project) {
                $this->projects_model->delete($project['id']);
            }
        }
        if ($affectedRows > 0) {
            do_action('after_client_deleted', $id);

            // Delete activity log caused by delete customer function
            if ($last_activity) {
                $this->db->where('id >', $last_activity->id);
                $this->db->delete('tblactivitylog');
            }

            logActivity('Client Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete customer contact
     * @param  mixed $id contact id
     * @return boolean
     */
    public function delete_contact($id)
    {
        $this->db->where('id', $id);
        $result      = $this->db->get('tblcontacts')->row();
        $customer_id = $result->userid;

        do_action('before_delete_contact', $id);

        $last_activity = get_last_system_activity_id();

        $this->db->where('id', $id);
        $this->db->delete('tblcontacts');

        if ($this->db->affected_rows() > 0) {
            if (is_dir(get_upload_path_by_type('contact_profile_images') . $id)) {
                delete_dir(get_upload_path_by_type('contact_profile_images') . $id);
            }

            $this->db->where('contact_id', $id);
            $this->db->delete('tblconsents');

            $this->db->where('contact_id', $id);
            $this->db->delete('tblcustomerfiles_shares');

            $this->db->where('userid', $id);
            $this->db->where('staff', 0);
            $this->db->delete('tbldismissedannouncements');

            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'contacts');
            $this->db->delete('tblcustomfieldsvalues');

            $this->db->where('userid', $id);
            $this->db->delete('tblcontactpermissions');

            $this->db->where('user_id', $id);
            $this->db->where('staff', 0);
            $this->db->delete('tbluserautologin');

            $this->db->select('ticketid');
            $this->db->where('contactid', $id);
            $this->db->where('userid', $customer_id);
            $tickets = $this->db->get('tbltickets')->result_array();

            $this->load->model('tickets_model');
            foreach ($tickets as $ticket) {
                $this->tickets_model->delete($ticket['ticketid']);
            }

            $this->load->model('tasks_model');

            $this->db->where('addedfrom', $id);
            $this->db->where('is_added_from_contact', 1);
            $tasks = $this->db->get('tblstafftasks')->result_array();

            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id'], false);
            }

            // Added from contact in customer profile
            $this->db->where('contact_id', $id);
            $this->db->where('rel_type', 'customer');
            $attachments = $this->db->get('tblfiles')->result_array();

            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            // Remove contact files uploaded to tasks
            $this->db->where('rel_type', 'task');
            $this->db->where('contact_id', $id);
            $filesUploadedFromContactToTasks = $this->db->get('tblfiles')->result_array();

            foreach ($filesUploadedFromContactToTasks as $file) {
                $this->tasks_model->remove_task_attachment($file['id']);
            }

            $this->db->where('contact_id', $id);
            $tasksComments = $this->db->get('tblstafftaskcomments')->result_array();
            foreach ($tasksComments as $comment) {
                $this->tasks_model->remove_comment($comment['id'], true);
            }

            $this->load->model('projects_model');

            $this->db->where('contact_id', $id);
            $files = $this->db->get('tblprojectfiles')->result_array();
            foreach ($files as $file) {
                $this->projects_model->remove_file($file['id'], false);
            }

            $this->db->where('contact_id', $id);
            $discussions = $this->db->get('tblprojectdiscussions')->result_array();
            foreach ($discussions as $discussion) {
                $this->projects_model->delete_discussion($discussion['id'], false);
            }

            $this->db->where('contact_id', $id);
            $discussionsComments = $this->db->get('tblprojectdiscussioncomments')->result_array();
            foreach ($discussionsComments as $comment) {
                $this->projects_model->delete_discussion_comment($comment['id'], false);
            }

            $this->db->where('contact_id', $id);
            $this->db->delete('tblusermeta');

            $this->db->where('(email="' . $result->email . '" OR bcc LIKE "%' . $result->email . '%" OR cc LIKE "%' . $result->email . '%")');
            $this->db->delete('tblemailqueue');

            $this->db->where('email', $result->email);
            $this->db->delete('tblsurveysemailsendcron');

            if (is_gdpr()) {
                $this->db->where('email', $result->email);
                $this->db->delete('tbllistemails');

                if (!empty($result->last_ip)) {
                    $this->db->where('ip', $result->last_ip);
                    $this->db->delete('tblknowledgebasearticleanswers');

                    $this->db->where('ip', $result->last_ip);
                    $this->db->delete('tblsurveyresultsets');
                }

                $this->db->where('email', $result->email);
                $this->db->delete('tblticketpipelog');

                $this->db->where('email', $result->email);
                $this->db->delete('tblemailstracking');

                $this->db->where('contact_id', $id);
                $this->db->delete('tblprojectactivity');

                $this->db->where('(additional_data LIKE "%' . $result->email . '%" OR full_name LIKE "%' . $result->firstname . ' ' . $result->lastname . '%")');
                $this->db->where('additional_data != "" AND additional_data IS NOT NULL');
                $this->db->delete('tblsalesactivity');

                $whereActivityLog = '(description LIKE "%' . $result->email . '%" OR description LIKE "%' . $result->firstname . ' ' . $result->lastname . '%" OR description LIKE "%' . $result->firstname . '%" OR description LIKE "%' . $result->lastname . '%" OR description LIKE "%' . $result->phonenumber . '%"';
                if (!empty($result->last_ip)) {
                    $whereActivityLog .= ' OR description LIKE "%' . $result->last_ip . '%"';
                }
                $whereActivityLog .= ')';
                $this->db->where($whereActivityLog);
                $this->db->delete('tblactivitylog');
            }



            // Delete activity log caused by delete contact function
            if ($last_activity) {
                $this->db->where('id >', $last_activity->id);
                $this->db->delete('tblactivitylog');
            }

            return true;
        }

        return false;
    }

    /**
     * Get customer default currency
     * @param  mixed $id customer id
     * @return mixed
     */
    public function get_customer_default_currency($id)
    {
        $this->db->select('default_currency');
        $this->db->where('userid', $id);
        $result = $this->db->get('tblclients')->row();
        if ($result) {
            return $result->default_currency;
        }

        return false;
    }

    /**
     *  Get customer billing details
     * @param   mixed $id   customer id
     * @return  array
     */
    public function get_customer_billing_and_shipping_details($id)
    {
        $this->db->select('billing_street,billing_city,billing_state,billing_zip,billing_country,shipping_street,shipping_city,shipping_state,shipping_zip,shipping_country');
        $this->db->from('tblclients');
        $this->db->where('userid', $id);

        $result = $this->db->get()->result_array();
        if (count($result) > 0) {
            $result[0]['billing_street']  = clear_textarea_breaks($result[0]['billing_street']);
            $result[0]['shipping_street'] = clear_textarea_breaks($result[0]['shipping_street']);
        }

        return $result;
    }

    /**
     * Get customer files uploaded in the customer profile
     * @param  mixed $id    customer id
     * @param  array  $where perform where
     * @return array
     */
    public function get_customer_files($id, $where = [])
    {
        $this->db->where($where);
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'customer');
        $this->db->order_by('dateadded', 'desc');

        return $this->db->get('tblfiles')->result_array();
    }

    /**
     *  Get customer attachment
     * @param   mixed $id   customer id
     * @return  array
     */
    public function get_all_customer_attachments($id)
    {
        $attachments             = array();
        $attachments['invoice']  = array();
        $attachments['estimate'] = array();
        $attachments['credit_note'] = array();
        $attachments['proposal'] = array();
        $attachments['contract'] = array();
        $attachments['lead']     = array();
        $attachments['task']     = array();
        $attachments['customer'] = array();
        $attachments['ticket']   = array();
        $attachments['expense']  = array();

        $has_permission_expenses_view = has_permission('expenses', '', 'view');
        $has_permission_expenses_own  = has_permission('expenses', '', 'view_own');
        if ($has_permission_expenses_view || $has_permission_expenses_own) {
            // Expenses
            $this->db->select('clientid,id');
            $this->db->where('clientid', $id);
            if (!$has_permission_expenses_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }
            $this->db->from('tblexpenses');
            $expenses = $this->db->get()->result_array();
            foreach ($expenses as $expense) {
                $this->db->where('rel_id', $expense['id']);
                $this->db->where('rel_type', 'expense');
                $_attachments = $this->db->get('tblfiles')->result_array();
                if (count($_attachments) > 0) {
                    foreach ($_attachments as $_att) {
                        array_push($attachments['expense'], $_att);
                    }
                }
            }
        }


        $has_permission_invoices_view = has_permission('invoices', '', 'view');
        $has_permission_invoices_own  = has_permission('invoices', '', 'view_own');
        if ($has_permission_invoices_view || $has_permission_invoices_own) {
            // Invoices
            $this->db->select('clientid,id');
            $this->db->where('clientid', $id);

            if (!$has_permission_invoices_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }

            $this->db->from('tblinvoices');
            $invoices = $this->db->get()->result_array();
            foreach ($invoices as $invoice) {
                $this->db->where('rel_id', $invoice['id']);
                $this->db->where('rel_type', 'invoice');
                $_attachments = $this->db->get('tblfiles')->result_array();
                if (count($_attachments) > 0) {
                    foreach ($_attachments as $_att) {
                        array_push($attachments['invoice'], $_att);
                    }
                }
            }
        }

        $has_permission_credit_notes_view = has_permission('credit_notes', '', 'view');
        $has_permission_credit_notes_own  = has_permission('credit_notes', '', 'view_own');
        if ($has_permission_credit_notes_view || $has_permission_credit_notes_own) {
            // credit_notes
            $this->db->select('clientid,id');
            $this->db->where('clientid', $id);

            if (!$has_permission_credit_notes_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }

            $this->db->from('tblcreditnotes');
            $credit_notes = $this->db->get()->result_array();
            foreach ($credit_notes as $credit_note) {
                $this->db->where('rel_id', $credit_note['id']);
                $this->db->where('rel_type', 'credit_note');
                $_attachments = $this->db->get('tblfiles')->result_array();
                if (count($_attachments) > 0) {
                    foreach ($_attachments as $_att) {
                        array_push($attachments['credit_note'], $_att);
                    }
                }
            }
        }

        $permission_estimates_view = has_permission('estimates', '', 'view');
        $permission_estimates_own  = has_permission('estimates', '', 'view_own');

        if ($permission_estimates_view || $permission_estimates_own) {
            // Estimates
            $this->db->select('clientid,id');
            $this->db->where('clientid', $id);
            if (!$permission_estimates_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }
            $this->db->from('tblestimates');
            $estimates = $this->db->get()->result_array();
            foreach ($estimates as $estimate) {
                $this->db->where('rel_id', $estimate['id']);
                $this->db->where('rel_type', 'estimate');
                $_attachments = $this->db->get('tblfiles')->result_array();
                if (count($_attachments) > 0) {
                    foreach ($_attachments as $_att) {
                        array_push($attachments['estimate'], $_att);
                    }
                }
            }
        }

        $has_permission_proposals_view = has_permission('proposals', '', 'view');
        $has_permission_proposals_own  = has_permission('proposals', '', 'view_own');

        if ($has_permission_proposals_view || $has_permission_proposals_own) {
            // Proposals
            $this->db->select('rel_id,id');
            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'customer');
            if (!$has_permission_proposals_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }
            $this->db->from('tblproposals');
            $proposals = $this->db->get()->result_array();
            foreach ($proposals as $proposal) {
                $this->db->where('rel_id', $proposal['id']);
                $this->db->where('rel_type', 'proposal');
                $_attachments = $this->db->get('tblfiles')->result_array();
                if (count($_attachments) > 0) {
                    foreach ($_attachments as $_att) {
                        array_push($attachments['proposal'], $_att);
                    }
                }
            }
        }

        $permission_contracts_view = has_permission('contracts', '', 'view');
        $permission_contracts_own  = has_permission('contracts', '', 'view_own');
        if ($permission_contracts_view || $permission_contracts_own) {
            // Contracts
            $this->db->select('client,id');
            $this->db->where('client', $id);
            if (!$permission_contracts_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }
            $this->db->from('tblcontracts');
            $contracts = $this->db->get()->result_array();
            foreach ($contracts as $contract) {
                $this->db->where('rel_id', $contract['id']);
                $this->db->where('rel_type', 'contract');
                $_attachments = $this->db->get('tblfiles')->result_array();
                if (count($_attachments) > 0) {
                    foreach ($_attachments as $_att) {
                        array_push($attachments['contract'], $_att);
                    }
                }
            }
        }

        $customer = $this->get($id);
        if ($customer->leadid != null) {
            $this->db->where('rel_id', $customer->leadid);
            $this->db->where('rel_type', 'lead');
            $_attachments = $this->db->get('tblfiles')->result_array();
            if (count($_attachments) > 0) {
                foreach ($_attachments as $_att) {
                    array_push($attachments['lead'], $_att);
                }
            }
        }
        $this->db->select('ticketid,userid');
        $this->db->where('userid', $id);
        $this->db->from('tbltickets');
        $tickets = $this->db->get()->result_array();
        foreach ($tickets as $ticket) {
            $this->db->where('ticketid', $ticket['ticketid']);
            $_attachments = $this->db->get('tblticketattachments')->result_array();
            if (count($_attachments) > 0) {
                foreach ($_attachments as $_att) {
                    array_push($attachments['ticket'], $_att);
                }
            }
        }

        $has_permission_tasks_view = has_permission('tasks', '', 'view');
        $this->db->select('rel_id,id');
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'customer');

        if (!$has_permission_tasks_view) {
            $this->db->where(get_tasks_where_string(false));
        }

        $this->db->from('tblstafftasks');
        $tasks = $this->db->get()->result_array();
        foreach ($tasks as $task) {
            $this->db->where('rel_type', 'task');
            $this->db->where('rel_id', $task['id']);
            $_attachments = $this->db->get('tblfiles')->result_array();
            if (count($_attachments) > 0) {
                foreach ($_attachments as $_att) {
                    array_push($attachments['task'], $_att);
                }
            }
        }

        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'customer');
        $client_main_attachments = $this->db->get('tblfiles')->result_array();

        $attachments['customer'] = $client_main_attachments;

        return $attachments;
    }

    public function get_all_contacts_attachments($id)
    {
        $attachments             = array();
        $attachments['invoice']  = array();
        $attachments['estimate'] = array();
        $attachments['credit_note'] = array();
        $attachments['proposal'] = array();
        $attachments['contract'] = array();
        $attachments['lead']     = array();
        $attachments['task']     = array();
        $attachments['customer'] = array();
        $attachments['ticket']   = array();
        $attachments['expense']  = array();

//        $has_permission_expenses_view = has_permission('expenses', '', 'view');
//        $has_permission_expenses_own  = has_permission('expenses', '', 'view_own');
//        if ($has_permission_expenses_view || $has_permission_expenses_own) {
//            // Expenses
//            $this->db->select('clientid,id');
//            $this->db->where('clientid', $id);
//            if (!$has_permission_expenses_view) {
//                $this->db->where('addedfrom', get_staff_user_id());
//            }
//            $this->db->from('tblexpenses');
//            $expenses = $this->db->get()->result_array();
//            foreach ($expenses as $expense) {
//                $this->db->where('rel_id', $expense['id']);
//                $this->db->where('rel_type', 'expense');
//                $_attachments = $this->db->get('tblfiles')->result_array();
//                if (count($_attachments) > 0) {
//                    foreach ($_attachments as $_att) {
//                        array_push($attachments['expense'], $_att);
//                    }
//                }
//            }
//        }


//        $has_permission_invoices_view = has_permission('invoices', '', 'view');
//        $has_permission_invoices_own  = has_permission('invoices', '', 'view_own');
//        if ($has_permission_invoices_view || $has_permission_invoices_own) {
//            // Invoices
//            $this->db->select('clientid,id');
//            $this->db->where('clientid', $id);
//
//            if (!$has_permission_invoices_view) {
//                $this->db->where('addedfrom', get_staff_user_id());
//            }
//
//            $this->db->from('tblinvoices');
//            $invoices = $this->db->get()->result_array();
//            foreach ($invoices as $invoice) {
//                $this->db->where('rel_id', $invoice['id']);
//                $this->db->where('rel_type', 'invoice');
//                $_attachments = $this->db->get('tblfiles')->result_array();
//                if (count($_attachments) > 0) {
//                    foreach ($_attachments as $_att) {
//                        array_push($attachments['invoice'], $_att);
//                    }
//                }
//            }
//        }

//        $has_permission_credit_notes_view = has_permission('credit_notes', '', 'view');
//        $has_permission_credit_notes_own  = has_permission('credit_notes', '', 'view_own');
//        if ($has_permission_credit_notes_view || $has_permission_credit_notes_own) {
//            // credit_notes
//            $this->db->select('clientid,id');
//            $this->db->where('clientid', $id);
//
//            if (!$has_permission_credit_notes_view) {
//                $this->db->where('addedfrom', get_staff_user_id());
//            }
//
//            $this->db->from('tblcreditnotes');
//            $credit_notes = $this->db->get()->result_array();
//            foreach ($credit_notes as $credit_note) {
//                $this->db->where('rel_id', $credit_note['id']);
//                $this->db->where('rel_type', 'credit_note');
//                $_attachments = $this->db->get('tblfiles')->result_array();
//                if (count($_attachments) > 0) {
//                    foreach ($_attachments as $_att) {
//                        array_push($attachments['credit_note'], $_att);
//                    }
//                }
//            }
//        }

//        $permission_estimates_view = has_permission('estimates', '', 'view');
//        $permission_estimates_own  = has_permission('estimates', '', 'view_own');
//
//        if ($permission_estimates_view || $permission_estimates_own) {
//            // Estimates
//            $this->db->select('clientid,id');
//            $this->db->where('clientid', $id);
//            if (!$permission_estimates_view) {
//                $this->db->where('addedfrom', get_staff_user_id());
//            }
//            $this->db->from('tblestimates');
//            $estimates = $this->db->get()->result_array();
//            foreach ($estimates as $estimate) {
//                $this->db->where('rel_id', $estimate['id']);
//                $this->db->where('rel_type', 'estimate');
//                $_attachments = $this->db->get('tblfiles')->result_array();
//                if (count($_attachments) > 0) {
//                    foreach ($_attachments as $_att) {
//                        array_push($attachments['estimate'], $_att);
//                    }
//                }
//            }
//        }

//        $has_permission_proposals_view = has_permission('proposals', '', 'view');
//        $has_permission_proposals_own  = has_permission('proposals', '', 'view_own');
//
//        if ($has_permission_proposals_view || $has_permission_proposals_own) {
//            // Proposals
//            $this->db->select('rel_id,id');
//            $this->db->where('rel_id', $id);
//            $this->db->where('rel_type', 'customer');
//            if (!$has_permission_proposals_view) {
//                $this->db->where('addedfrom', get_staff_user_id());
//            }
//            $this->db->from('tblproposals');
//            $proposals = $this->db->get()->result_array();
//            foreach ($proposals as $proposal) {
//                $this->db->where('rel_id', $proposal['id']);
//                $this->db->where('rel_type', 'proposal');
//                $_attachments = $this->db->get('tblfiles')->result_array();
//                if (count($_attachments) > 0) {
//                    foreach ($_attachments as $_att) {
//                        array_push($attachments['proposal'], $_att);
//                    }
//                }
//            }
//        }

//        $permission_contracts_view = has_permission('contracts', '', 'view');
//        $permission_contracts_own  = has_permission('contracts', '', 'view_own');
//        if ($permission_contracts_view || $permission_contracts_own) {
//            // Contracts
//            $this->db->select('client,id');
//            $this->db->where('client', $id);
//            if (!$permission_contracts_view) {
//                $this->db->where('addedfrom', get_staff_user_id());
//            }
//            $this->db->from('tblcontracts');
//            $contracts = $this->db->get()->result_array();
//            foreach ($contracts as $contract) {
//                $this->db->where('rel_id', $contract['id']);
//                $this->db->where('rel_type', 'contract');
//                $_attachments = $this->db->get('tblfiles')->result_array();
//                if (count($_attachments) > 0) {
//                    foreach ($_attachments as $_att) {
//                        array_push($attachments['contract'], $_att);
//                    }
//                }
//            }
//        }

//        $customer = $this->get($id);
//        if ($customer->leadid != null) {
//            $this->db->where('rel_id', $customer->leadid);
//            $this->db->where('rel_type', 'lead');
//            $_attachments = $this->db->get('tblfiles')->result_array();
//            if (count($_attachments) > 0) {
//                foreach ($_attachments as $_att) {
//                    array_push($attachments['lead'], $_att);
//                }
//            }
//        }
//        $this->db->select('ticketid,userid');
//        $this->db->where('userid', $id);
//        $this->db->from('tbltickets');
//        $tickets = $this->db->get()->result_array();
//        foreach ($tickets as $ticket) {
//            $this->db->where('ticketid', $ticket['ticketid']);
//            $_attachments = $this->db->get('tblticketattachments')->result_array();
//            if (count($_attachments) > 0) {
//                foreach ($_attachments as $_att) {
//                    array_push($attachments['ticket'], $_att);
//                }
//            }
//        }
//
//        $has_permission_tasks_view = has_permission('tasks', '', 'view');
//        $this->db->select('rel_id,id');
//        $this->db->where('rel_id', $id);
//        $this->db->where('rel_type', 'customer');
//
//        if (!$has_permission_tasks_view) {
//            $this->db->where(get_tasks_where_string(false));
//        }
//
//        $this->db->from('tblstafftasks');
//        $tasks = $this->db->get()->result_array();
//        foreach ($tasks as $task) {
//            $this->db->where('rel_type', 'task');
//            $this->db->where('rel_id', $task['id']);
//            $_attachments = $this->db->get('tblfiles')->result_array();
//            if (count($_attachments) > 0) {
//                foreach ($_attachments as $_att) {
//                    array_push($attachments['task'], $_att);
//                }
//            }
//        }

        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'contacts');
        $client_main_attachments = $this->db->get('tblfiles')->result_array();

        $attachments['contact'] = $client_main_attachments;

        return $attachments;
    }

    /**
     * Delete customer attachment uploaded from the customer profile
     * @param  mixed $id attachment id
     * @return boolean
     */
    public function delete_attachment($id)
    {
        $this->db->where('id', $id);
        $attachment = $this->db->get('tblfiles')->row();
        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                $relPath  = get_upload_path_by_type('customer') . $attachment->rel_id . '/';
                $fullPath = $relPath . $attachment->file_name;
                unlink($fullPath);
                $fname     = pathinfo($fullPath, PATHINFO_FILENAME);
                $fext      = pathinfo($fullPath, PATHINFO_EXTENSION);
                $thumbPath = $relPath . $fname . '_thumb.' . $fext;
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }

            $this->db->where('id', $id);
            $this->db->delete('tblfiles');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                $this->db->where('file_id', $id);
                $this->db->delete('tblcustomerfiles_shares');
                logActivity('Customer Attachment Deleted [ID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('customer') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('customer') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    delete_dir(get_upload_path_by_type('customer') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update contact status Active/Inactive
     */
    public function change_contact_status($id, $status)
    {
        $hook_data['id']     = $id;
        $hook_data['status'] = $status;
        $hook_data           = do_action('change_contact_status', $hook_data);
        $status              = $hook_data['status'];
        $id                  = $hook_data['id'];
        $this->db->where('id', $id);
        $this->db->update('tblcontacts', [
            'active' => $status,
        ]);
        if ($this->db->affected_rows() > 0) {
            logActivity('Contact Status Changed [ContactID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update client status Active/Inactive
     */
    public function change_client_status($id, $status)
    {
        $this->db->where('userid', $id);
        $this->db->update('tblclients', [
            'active' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            logActivity('Customer Status Changed [ID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  mixed $_POST data
     * @return mixed
     * Change contact password, used from client area
     */
    public function change_contact_password($data)
    {
        $hook_data['data'] = $data;
        $hook_data         = do_action('before_contact_change_password', $hook_data);
        $data              = $hook_data['data'];

        // Get current password
        $this->db->where('id', get_contact_user_id());
        $client = $this->db->get('tblcontacts')->row();
        $this->load->helper('phpass');
        $hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
        if (!$hasher->CheckPassword($data['oldpassword'], $client->password)) {
            return [
                'old_password_not_match' => true,
            ];
        }
        $update_data['password']             = $hasher->HashPassword($data['newpasswordr']);
        $update_data['last_password_change'] = date('Y-m-d H:i:s');
        $this->db->where('id', get_contact_user_id());
        $this->db->update('tblcontacts', $update_data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Contact Password Changed [ContactID: ' . get_contact_user_id() . ']');

            return true;
        }

        return false;
    }

    /**
     * Get customer groups where customer belongs
     * @param  mixed $id customer id
     * @return array
     */
    public function get_customer_groups($id)
    {
        return $this->client_groups_model->get_customer_groups($id);
    }

    /**
     * Get all customer groups
     * @param  string $id
     * @return mixed
     */
    public function get_groups($id = '')
    {
        return $this->client_groups_model->get_groups($id);
    }

    /**
     * Delete customer groups
     * @param  mixed $id group id
     * @return boolean
     */
    public function delete_group($id)
    {
        return $this->client_groups_model->delete($id);
    }

    /**
     * Add new customer groups
     * @param array $data $_POST data
     */
    public function add_group($data)
    {
        return $this->client_groups_model->add($data);
    }

    /**
     * Edit customer group
     * @param  array $data $_POST data
     * @return boolean
     */
    public function edit_group($data)
    {
        return $this->client_groups_model->edit($data);
    }

    /**
    * Create new vault entry
    * @param  array $data        $_POST data
    * @param  mixed $customer_id customer id
    * @return boolean
    */
    public function vault_entry_create($data, $customer_id)
    {
        return $this->client_vault_entries_model->create($data, $customer_id);
    }

    /**
     * Update vault entry
     * @param  mixed $id   vault entry id
     * @param  array $data $_POST data
     * @return boolean
     */
    public function vault_entry_update($id, $data)
    {
        return $this->client_vault_entries_model->update($id, $data);
    }

    /**
     * Delete vault entry
     * @param  mixed $id entry id
     * @return boolean
     */
    public function vault_entry_delete($id)
    {
        return $this->client_vault_entries_model->delete($id);
    }

    /**
     * Get customer vault entries
     * @param  mixed $customer_id
     * @param  array  $where       additional wher
     * @return array
     */
    public function get_vault_entries($customer_id, $where = [])
    {
        return $this->client_vault_entries_model->get_by_customer_id($customer_id, $where);
    }

    /**
     * Get single vault entry
     * @param  mixed $id vault entry id
     * @return object
     */
    public function get_vault_entry($id)
    {
        return $this->client_vault_entries_model->get($id);
    }

    /**
    * Get customer statement formatted
    * @param  mixed $customer_id customer id
    * @param  string $from        date from
    * @param  string $to          date to
    * @return array
    */
    public function get_statement($customer_id, $from, $to)
    {
        return $this->statement_model->get_statement($customer_id, $from, $to);
    }

    /**
    * Send customer statement to email
    * @param  mixed $customer_id customer id
    * @param  array $send_to     array of contact emails to send
    * @param  string $from        date from
    * @param  string $to          date to
    * @param  string $cc          email CC
    * @return boolean
    */
    public function send_statement_to_email($customer_id, $send_to, $from, $to, $cc = '')
    {
        return $this->statement_model->send_statement_to_email($customer_id, $send_to, $from, $to, $cc);
    }

    /**
     * When customer register, mark the contact and the customer as inactive and set the registration_confirmed field to 0
     * @param  mixed $client_id  the customer id
     * @return boolean
     */
    public function require_confirmation($client_id)
    {
        $contact_id = get_primary_contact_user_id($client_id);
        $this->db->where('userid', $client_id);
        $this->db->update('tblclients', ['active' => 0, 'registration_confirmed' => 0]);

        $this->db->where('id', $contact_id);
        $this->db->update('tblcontacts', ['active' => 0]);

        return true;
    }

    public function confirm_registration($client_id)
    {
        $contact_id = get_primary_contact_user_id($client_id);
        $this->db->where('userid', $client_id);
        $this->db->update('tblclients', ['active' => 1, 'registration_confirmed' => 1]);

        $this->db->where('id', $contact_id);
        $this->db->update('tblcontacts', ['active' => 1]);

        $this->db->where('id', $contact_id);
        $contact = $this->db->get('tblcontacts')->row();

        if ($contact) {
            $this->load->model('emails_model');
            $merge_fields = [];
            $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($client_id, $contact_id));
            $this->emails_model->send_email_template('client-registration-confirmed', $contact->email, $merge_fields);

            return true;
        }

        return false;
    }

    public function get_clients_distinct_countries()
    {
        return $this->db->query('SELECT DISTINCT(country_id), short_name FROM tblclients JOIN tblcountries ON tblcountries.country_id=tblclients.country')->result_array();
    }

    private function check_zero_columns($data)
    {
        if (!isset($data['show_primary_contact'])) {
            $data['show_primary_contact'] = 0;
        }

        if (isset($data['default_currency']) && $data['default_currency'] == '' || !isset($data['default_currency'])) {
            $data['default_currency'] = 0;
        }

        if (isset($data['country']) && $data['country'] == '' || !isset($data['country'])) {
            $data['country'] = 0;
        }

        if (isset($data['billing_country']) && $data['billing_country'] == '' || !isset($data['billing_country'])) {
            $data['billing_country'] = 0;
        }

        if (isset($data['shipping_country']) && $data['shipping_country'] == '' || !isset($data['shipping_country'])) {
            $data['shipping_country'] = 0;
        }

        return $data;
    }

    public function get_contact_activity_log($id)
    {
        $sorting = do_action('lead_contact_log_default_sort','DESC');

        $this->db->where('contactid', $id);
        $this->db->order_by('date', $sorting);

        return $this->db->get('tblcontactactivitylog')->result_array();
    }

    public function log_contact_activity($contactid, $description, $integration = false, $additional_data = '', $custom_activity =0)
    {
        $log = array(
            'date' => date('Y-m-d H:i:s'),
            'description' => $description,
            'contactid' => $contactid,
            'staffid' => get_staff_user_id(),
            'additional_data' => $additional_data,
            'full_name' => get_staff_full_name(get_staff_user_id()),
            'custom_activity' => $custom_activity
        );
        if ($integration == true) {
            $log['staffid']   = 0;
            $log['full_name'] = '[CRON]';
        }

        $this->db->insert('tblcontactactivitylog', $log);

        return $this->db->insert_id();
    }
    public function log_email_activity($contactid,$clientid,$to,$schedule,$schedule_time,$subject,$content)
    {
        $data = array(
            'created_date' => date('Y-m-d H:i:s'),
            'subject' => $subject,
            'content' => $content,
            'added_from' => get_staff_user_id(),
            'to' => $to,
            'contact_id' => $contactid,
            'client_id' => $clientid,
            'schedule_option' => $schedule,
            'sendtime' => $schedule_time,
        );

        $this->db->insert('tblemails', $data);

        return $this->db->insert_id();
    }

    public function get_contact_phone($contact_id){
        $this->db->where('contact_id',$contact_id);
        return $this->db->get('tblcontact_phonenumbers')->result_array();
    }

    public function insert_contact_phone($contact_id,$data_phone){
        $this->db->where('contact_id',$contact_id);
        $this->db->delete('tblcontact_phonenumbers');

        foreach ($data_phone as $phone){
            if($phone['value']){
                $data = array(
                    'contact_id' => $contact_id,
                    'phone_number' => $phone['value'],
                    'phone_type' => $phone['type']
                );
                $this->db->insert('tblcontact_phonenumbers', $data);
            }

        }

    }
}

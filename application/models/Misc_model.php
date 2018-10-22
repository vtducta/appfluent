<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Misc_model extends CRM_Model
{
    public $notifications_limit;

    public function __construct()
    {
        parent::__construct();
        $this->notifications_limit = 15;
    }

    public function get_notifications_limit()
    {
        return do_action('notifications_limit', $this->notifications_limit);
    }

    public function get_taxes_dropdown_template($name, $taxname, $type = '', $item_id = '', $is_edit = false, $manual = false)
    {
        // if passed manually - like in proposal convert items or project
        if ($manual == true) {
            // + is no longer used and is here for backward compatibilities
            if (is_array($taxname) || strpos($taxname, '+') !== false) {
                if (!is_array($taxname)) {
                    $__tax = explode('+', $taxname);
                } else {
                    $__tax = $taxname;
                }
                // Multiple taxes found // possible option from default settings when invoicing project
                $taxname = [];
                foreach ($__tax as $t) {
                    $tax_array = explode('|', $t);
                    if (isset($tax_array[0]) && isset($tax_array[1])) {
                        array_push($taxname, $tax_array[0] . '|' . $tax_array[1]);
                    }
                }
            } else {
                $tax_array = explode('|', $taxname);
                // isset tax rate
                if (isset($tax_array[0]) && isset($tax_array[1])) {
                    $tax = get_tax_by_name($tax_array[0]);
                    if ($tax) {
                        $taxname = $tax->name . '|' . $tax->taxrate;
                    }
                }
            }
        }
        // First get all system taxes
        $this->load->model('taxes_model');
        $taxes = $this->taxes_model->get();
        $i     = 0;
        foreach ($taxes as $tax) {
            unset($taxes[$i]['id']);
            $taxes[$i]['name'] = $tax['name'] . '|' . $tax['taxrate'];
            $i++;
        }
        if ($is_edit == true) {

            // Lets check the items taxes in case of changes.
            // Separate functions exists to get item taxes for Invoice, Estimate, Proposal, Credit Note
            $func_taxes = 'get_' . $type . '_item_taxes';
            if (function_exists($func_taxes)) {
                $item_taxes = call_user_func($func_taxes, $item_id);
            }

            foreach ($item_taxes as $item_tax) {
                $new_tax            = [];
                $new_tax['name']    = $item_tax['taxname'];
                $new_tax['taxrate'] = $item_tax['taxrate'];
                $taxes[]            = $new_tax;
            }
        }

        // In case tax is changed and the old tax is still linked to estimate/proposal when converting
        // This will allow the tax that don't exists to be shown on the dropdowns too.
        if (is_array($taxname)) {
            foreach ($taxname as $tax) {
                // Check if tax empty
                if ((!is_array($tax) && $tax == '') || is_array($tax) && $tax['taxname'] == '') {
                    continue;
                };
                // Check if really the taxname NAME|RATE don't exists in all taxes
                if (!value_exists_in_array_by_key($taxes, 'name', $tax)) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ($tmp_taxname == '') {
                            continue;
                        }
                    }
                    $taxes[] = ['name' => $tmp_taxname, 'taxrate' => $tax_array[1]];
                }
            }
        }

        // Clear the duplicates
        $taxes = array_map('unserialize', array_unique(array_map('serialize', $taxes)));

        $select = '<select class="selectpicker display-block tax" data-width="100%" name="' . $name . '" multiple data-none-selected-text="' . _l('no_tax') . '">';

        foreach ($taxes as $tax) {
            $selected = '';
            if (is_array($taxname)) {
                foreach ($taxname as $_tax) {
                    if (is_array($_tax)) {
                        if ($_tax['taxname'] == $tax['name']) {
                            $selected = 'selected';
                        }
                    } else {
                        if ($_tax == $tax['name']) {
                            $selected = 'selected';
                        }
                    }
                }
            } else {
                if ($taxname == $tax['name']) {
                    $selected = 'selected';
                }
            }

            $select .= '<option value="' . $tax['name'] . '" ' . $selected . ' data-taxrate="' . $tax['taxrate'] . '" data-taxname="' . $tax['name'] . '" data-subtext="' . $tax['name'] . '">' . $tax['taxrate'] . '%</option>';
        }
        $select .= '</select>';

        return $select;
    }

    public function add_attachment_to_database($rel_id, $rel_type, $attachment, $external = false)
    {
        $data['dateadded'] = date('Y-m-d H:i:s');
        $data['rel_id']    = $rel_id;
        if (!isset($attachment[0]['staffid'])) {
            $data['staffid'] = get_staff_user_id();
        } else {
            $data['staffid'] = $attachment[0]['staffid'];
        }

        if (isset($attachment[0]['task_comment_id'])) {
            $data['task_comment_id'] = $attachment[0]['task_comment_id'];
        }

        $data['rel_type'] = $rel_type;
        if (isset($attachment[0]['contact_id'])) {
            $data['contact_id']          = $attachment[0]['contact_id'];
            $data['visible_to_customer'] = 1;
            if (isset($data['staffid'])) {
                unset($data['staffid']);
            }
        }

        $data['attachment_key'] = app_generate_hash();

        if ($external == false) {
            $data['file_name'] = $attachment[0]['file_name'];
            $data['filetype']  = $attachment[0]['filetype'];
        } else {
            $path_parts            = pathinfo($attachment[0]['name']);
            $data['file_name']     = $attachment[0]['name'];
            $data['external_link'] = $attachment[0]['link'];
            $data['filetype']      = get_mime_by_extension('.' . $path_parts['extension']);
            $data['external']      = $external;
            if (isset($attachment[0]['thumbnailLink'])) {
                $data['thumbnail_link'] = $attachment[0]['thumbnailLink'];
            }
        }

        $this->db->insert('tblfiles', $data);
        $insert_id = $this->db->insert_id();

        if ($data['rel_type'] == 'customer' && isset($data['contact_id'])) {
            if (get_option('only_own_files_contacts') == 1) {
                $this->db->insert('tblcustomerfiles_shares', [
                    'file_id'    => $insert_id,
                    'contact_id' => $data['contact_id'],
                ]);
            } else {
                $this->db->select('id');
                $this->db->where('userid', $data['rel_id']);
                $contacts = $this->db->get('tblcontacts')->result_array();
                foreach ($contacts as $contact) {
                    $this->db->insert('tblcustomerfiles_shares', [
                        'file_id'    => $insert_id,
                        'contact_id' => $contact['id'],
                    ]);
                }
            }
        }

        if (isset($data['rel_type']) && $data['rel_type'] == 'customer') {
            $this->load->model('clients_model');
            $primary_contact_id = $this->clients_model->get_contact_primary($data['rel_id'])->id;
            $this->clients_model->log_contact_activity($primary_contact_id , 'not_contact_activity_add_files', false, serialize(array(
                get_staff_full_name(get_staff_user_id())
            )));

        }

        if (isset($data['rel_type']) && $data['rel_type'] == 'policies') {
            $this->load->model('policies_model');
            $this->policies_model->log_policy_activity($rel_id , 'not_policy_activity_add_files', false, serialize(array(
                get_staff_full_name(get_staff_user_id())
            )));

        }
        return $insert_id;
    }

    public function get_file($id)
    {
        $this->db->where('id', $id);

        return $this->db->get('tblfiles')->row();
    }

    public function get_staff_started_timers()
    {
        $this->db->select('tbltaskstimers.*,tblstafftasks.name as task_subject');
        $this->db->join('tblstaff', 'tblstaff.staffid=tbltaskstimers.staff_id');
        $this->db->join('tblstafftasks', 'tblstafftasks.id=tbltaskstimers.task_id', 'left');
        $this->db->where('staff_id', get_staff_user_id());
        $this->db->where('end_time IS NULL');

        return $this->db->get('tbltaskstimers')->result_array();
    }

    /**
     * Add reminder
     * @since  Version 1.0.2
     * @param mixed $data All $_POST data for the reminder
     * @param mixed $id   relid id
     * @return boolean
     */
    public function add_reminder($data, $id)
    {
        if (isset($data['notify_by_email'])) {
            $data['notify_by_email'] = 1;
        } //isset($data['notify_by_email'])
        else {
            $data['notify_by_email'] = 0;
        }
        $data['date']        = to_sql_date($data['date'], true);
        $data['description'] = nl2br($data['description']);
        $data['creator']     = get_staff_user_id();
        $this->db->insert('tblreminders', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if ($data['rel_type'] == 'lead') {
                $this->load->model('leads_model');
                $this->leads_model->log_lead_activity($data['rel_id'], 'not_activity_new_reminder_created', false, serialize([
                    get_staff_full_name($data['staff']),
                    _dt($data['date']),
                    ]));
            }
            logActivity('New Reminder Added [' . ucfirst($data['rel_type']) . 'ID: ' . $data['rel_id'] . ' Description: ' . $data['description'] . ']');

            return true;
        } //$insert_id
        return false;
    }

    public function edit_reminder($data, $id)
    {
        if (isset($data['notify_by_email'])) {
            $data['notify_by_email'] = 1;
        } else {
            $data['notify_by_email'] = 0;
        }

        $data['date']        = to_sql_date($data['date'], true);
        $data['description'] = nl2br($data['description']);

        $this->db->where('id', $id);
        $this->db->update('tblreminders', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function get_events()
    {
        $this->db->select('tblevents.*,tblstaff.firstname,tblstaff.lastname ');
        $this->db->join('tblstaff', 'tblstaff.staffid=tblevents.userid');
        $this->db->order_by('eventid', 'desc');
        $arrResult = $this->db->get('tblevents')->result_array();
        return $arrResult;
    }
    public function get_emails()
    {

        $this->db->select('tblemails.*,tblstaff.firstname,tblstaff.lastname ');
        $this->db->join('tblstaff', 'tblstaff.staffid=tblemails.added_from');
        $this->db->order_by('tblemails.id', 'desc');
        $arrResult = $this->db->get('tblemails')->result_array();

        return $arrResult;
    }

    public function get_emails_by_policy($id)
    {

        $this->db->select('tblemails.*,tblstaff.firstname,tblstaff.lastname ');
        $this->db->join('tblstaff', 'tblstaff.staffid=tblemails.added_from');
        $this->db->where('tblemails.policy_id',$id);
        $this->db->order_by('tblemails.id', 'desc');
        $arrResult = $this->db->get('tblemails')->result_array();

        return $arrResult;
    }
    public function get_notes($rel_id, $rel_type)
    {
//        $this->db->select('tblnotes.*,tblstaff.firstname,tblstaff.lastname,\'normal\' as note_type ');
//        $this->db->join('tblstaff', 'tblstaff.staffid=tblnotes.addedfrom');
//        $this->db->where('rel_id', $rel_id);
//        $this->db->where('rel_type', $rel_type);
//        $this->db->order_by('dateadded', 'desc');
//        $arrResult = $this->db->get('tblnotes')->result_array();
//
//        $this->db->select('tblcall_logs.*,tblstaff.firstname,tblstaff.lastname,\'call_log\' as note_type ');
//        $this->db->join('tblstaff', 'tblstaff.staffid=tblnotes.addedfrom');

        $column_select  ='client_id';
        if($rel_type=='contacts') $column_select='contact_id';
        if($rel_type=='customers') $column_select='client_id';
        if($rel_type=='policies') $column_select='policy_id';


        $query = 'SELECT * FROM
                  ( SELECT id,rel_id,rel_type,description,addedfrom,dateadded,\'normal\' AS note_type,
                    tblstaff.firstname,tblstaff.lastname FROM tblnotes JOIN tblstaff ON tblstaff.staffid=tblnotes.addedfrom
                    WHERE rel_id='.$rel_id.' AND rel_type=\''.$rel_type.'\'

                    UNION ALL
                    
                    SELECT id, contact_id AS rel_id, \''.$rel_type.'\' AS rel_type, CONCAT(\'Call log - \',`subject`) AS description,
                    addedfrom, created_date AS dateadded, \'call_log\' AS note_type,
                    tblstaff.firstname,tblstaff.lastname  FROM tblcall_logs JOIN tblstaff ON tblstaff.staffid=tblcall_logs.addedfrom
                    WHERE '.$column_select .' = '.$rel_id.'
                  ) AS nc ORDER BY dateadded DESC';

        $arrResult = $this->db->query($query)->result_array();
        return $arrResult;
    }
    public function get_note($id)
    {
        $this->db->where('id', $id);

        return $this->db->get('tblnotes')->row();
    }
    public function add_note($data, $rel_type, $rel_id)
    {
        $data['dateadded']   = date('Y-m-d H:i:s');
        $data['addedfrom']   = get_staff_user_id();
        $data['rel_type']    = $rel_type;
        $data['rel_id']      = $rel_id;
        $data['description'] = nl2br($data['description']);
        $this->db->insert('tblnotes', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function edit_note($data, $id)
    {
        do_action('before_update_note', ['data'=>$data,'id'=>$id]);

        $this->db->where('id', $id);
        $this->db->update('tblnotes', [
            'description' => nl2br($data['description']),
        ]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get current db version
     * @deprecated 1.9.8 Use $this->app->get_current_db_version() instead
     * @return mixed
     */
    public function get_current_db_version()
    {
        return $this->app->get_current_db_version();
    }

    public function get_activity_log($limit = 30)
    {
        $this->db->limit($limit);
        $this->db->order_by('date', 'desc');

        return $this->db->get('tblactivitylog')->result_array();
    }
    public function delete_call_log($call_log_id)
    {
        $this->db->where('id', $call_log_id);
        $note = $this->db->get('tblcall_logs')->row();
        if ($note->addedfrom != get_staff_user_id() && !is_admin()) {
            return false;
        }
        $this->db->where('id', $call_log_id);
        $this->db->delete('tblcall_logs');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }
    public function delete_note($note_id)
    {
        do_action('before_delete_note', $note_id);

        $this->db->where('id', $note_id);
        $note = $this->db->get('tblnotes')->row();
        if ($note->addedfrom != get_staff_user_id() && !is_admin()) {
            return false;
        }
        $this->db->where('id', $note_id);
        $this->db->delete('tblnotes');
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get all reminders or 1 reminder if id is passed
     * @since Version 1.0.2
     * @param  mixed $id reminder id OPTIONAL
     * @return array or object
     */
    public function get_reminders($id = '')
    {
        $this->db->join('tblstaff', 'tblstaff.staffid = tblreminders.staff', 'left');
        if (is_numeric($id)) {
            $this->db->where('tblreminders.id', $id);

            return $this->db->get('tblreminders')->row();
        } //is_numeric($id)
        $this->db->order_by('date', 'desc');

        return $this->db->get('tblreminders')->result_array();
    }

    /**
     * Remove client reminder from database
     * @since Version 1.0.2
     * @param  mixed $id reminder id
     * @return boolean
     */
    public function delete_reminder($id)
    {
        $reminder = $this->get_reminders($id);
        if ($reminder->creator == get_staff_user_id() || is_admin()) {
            $this->db->where('id', $id);
            $this->db->delete('tblreminders');
            if ($this->db->affected_rows() > 0) {
                logActivity('Reminder Deleted [' . ucfirst($reminder->rel_type) . 'ID: ' . $reminder->id . ' Description: ' . $reminder->description . ']');

                return true;
            } //$this->db->affected_rows() > 0
            return false;
        } //$reminder->creator == get_staff_user_id() || is_admin()
        return false;
    }

    public function get_tasks_distinct_assignees()
    {
        return $this->db->query("SELECT DISTINCT(tblstafftaskassignees.staffid) as assigneeid, CONCAT(firstname,' ',lastname) as full_name FROM tblstafftaskassignees JOIN tblstaff ON tblstaff.staffid=tblstafftaskassignees.staffid")->result_array();
    }

    public function get_google_calendar_ids()
    {
        $is_admin = is_admin();
        $this->load->model('departments_model');
        $departments       = $this->departments_model->get();
        $staff_departments = $this->departments_model->get_staff_departments(false, true);
        $ids               = [];

        // Check departments google calendar ids
        foreach ($departments as $department) {
            if ($department['calendar_id'] == '') {
                continue;
            }
            if ($is_admin) {
                $ids[] = $department['calendar_id'];
            } else {
                if (in_array($department['departmentid'], $staff_departments)) {
                    $ids[] = $department['calendar_id'];
                }
            }
        }

        // Ok now check if main calendar is setup
        $main_id_calendar = get_option('google_calendar_main_calendar');
        if ($main_id_calendar != '') {
            $ids[] = $main_id_calendar;
        }

        return array_unique($ids);
    }

    /**
     * Get current user notifications
     * @param  boolean $read include and readed notifications
     * @return array
     */
    public function get_user_notifications($read = false)
    {
        $read = $read == false ? 0 : 1;
        $total        = $this->notifications_limit;
        $staff_id = get_staff_user_id();

        $sql = 'SELECT COUNT(*) as total FROM tblnotifications WHERE isread=' . $read . ' AND touserid=' . $staff_id;
        $sql .= ' UNION ALL ';
        $sql .= 'SELECT COUNT(*) as total FROM tblnotifications WHERE isread_inline=' . $read . ' AND touserid=' . $staff_id;

        $res = $this->db->query($sql)->result();

        $total_unread = $res[0]->total;
        $total_unread_inline = $res[1]->total;

        if ($total_unread > $total) {
            $total = ($total_unread - $total) + $total;
        } elseif ($total_unread_inline > $total) {
            $total = ($total_unread_inline - $total) + $total;
        }

        // In case user is not marking the notifications are read this process may be long because the script will always fetch the total from the not read notifications.
        // In this case we are limiting to 30
        $total = $total > 30 ? 30 : $total;

        $this->db->where('touserid', $staff_id);
        $this->db->limit($total);
        $this->db->order_by('date', 'desc');

        return $this->db->get('tblnotifications')->result_array();
    }

    /**
     * Set notification read when user open notification dropdown
     * @return boolean
     */
    public function set_notifications_read()
    {
        $this->db->where('touserid', get_staff_user_id());
        $this->db->update('tblnotifications', [
            'isread' => 1,
        ]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function set_notification_read_inline($id)
    {
        $this->db->where('touserid', get_staff_user_id());
        $this->db->where('id', $id);
        $this->db->update('tblnotifications', [
            'isread_inline' => 1,
        ]);
    }

    public function set_desktop_notification_read($id)
    {
        $this->db->where('touserid', get_staff_user_id());
        $this->db->where('id', $id);
        $this->db->update('tblnotifications', [
            'isread'        => 1,
            'isread_inline' => 1,
        ]);
    }

    public function mark_all_notifications_as_read_inline()
    {
        $this->db->where('touserid', get_staff_user_id());
        $this->db->update('tblnotifications', [
            'isread_inline' => 1,
            'isread'        => 1,
        ]);
    }

    /**
     * Dismiss announcement
     * @param  array  $data  announcement data
     * @param  boolean $staff is staff or client
     * @return boolean
     */
    public function dismiss_announcement($id, $staff = true)
    {
        if ($staff == false) {
            $userid = get_contact_user_id();
        } //$staff == false
        else {
            $userid = get_staff_user_id();
        }
        $data['announcementid'] = $id;
        $data['userid']         = $userid;
        $data['staff']          = $staff;
        $this->db->insert('tbldismissedannouncements', $data);

        return true;
    }

    /**
     * Perform search on top header
     * @since  Version 1.0.1
     * @param  string $q search
     * @return array    search results
     */
    public function perform_search($q)
    {
        $q = trim($q);
        $this->load->model('staff_model');
        $is_admin                       = is_admin();
        $result                         = [];
        $limit                          = get_option('limit_top_search_bar_results_to');
        $have_assigned_customers        = have_assigned_customers();
        $have_permission_customers_view = has_permission('customers', '', 'view');
        if ($have_assigned_customers || $have_permission_customers_view) {

            // Clients
            $this->db->select(implode(',', prefixed_table_fields_array('tblclients')) . ',' . get_sql_select_client_company());

            $this->db->join('tblcountries', 'tblcountries.country_id = tblclients.country', 'left');
            $this->db->join('tblcontacts', 'tblcontacts.userid = tblclients.userid AND is_primary = 1', 'left');
            $this->db->from('tblclients');
            if ($have_assigned_customers && !$have_permission_customers_view) {
                $this->db->where('tblclients.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id=' . get_staff_user_id() . ')');
            }

            $this->db->where('(company LIKE "%' . $q . '%"
                OR vat LIKE "%' . $q . '%"
                OR tblclients.phonenumber LIKE "%' . $q . '%"
                OR tblcontacts.phonenumber LIKE "%' . $q . '%"
                OR city LIKE "%' . $q . '%"
                OR zip LIKE "%' . $q . '%"
                OR state LIKE "%' . $q . '%"
                OR zip LIKE "%' . $q . '%"
                OR address LIKE "%' . $q . '%"
                OR email LIKE "%' . $q . '%"
                OR CONCAT(firstname, \' \', lastname) LIKE "%' . $q . '%"
                OR tblcountries.short_name LIKE "%' . $q . '%"
                OR tblcountries.long_name LIKE "%' . $q . '%"
                OR tblcountries.numcode LIKE "%' . $q . '%"
                )');

            $this->db->limit($limit);
            $result[] = [
                'result'         => $this->db->get()->result_array(),
                'type'           => 'clients',
                'search_heading' => _l('clients'),
            ];
        }


        $staff_search = $this->_search_staff($q, $limit);
        if (count($staff_search['result']) > 0) {
            $result[] = $staff_search;
        }


        $policies_search = $this->_search_policies($q, $limit, '');
        if (count($policies_search['result']) > 0) {
            $result[] = $policies_search;
        }

        $where_contacts = '';
        if ($have_assigned_customers && !$have_permission_customers_view) {
            $where_contacts = 'tblcontacts.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id=' . get_staff_user_id() . ')';
        }


        $contacts_search = $this->_search_contacts($q, $limit, $where_contacts);
        if (count($contacts_search['result']) > 0) {
            $result[] = $contacts_search;
        }

        $tickets_search = $this->_search_tickets($q, $limit);
        if (count($tickets_search['result']) > 0) {
            $result[] = $tickets_search;
        }

        $leads_search = $this->_search_leads($q, $limit);
        if (count($leads_search['result']) > 0) {
            $result[] = $leads_search;
        }

        $proposals_search = $this->_search_proposals($q, $limit);
        if (count($proposals_search['result']) > 0) {
            $result[] = $proposals_search;
        }

        $invoices_search = $this->_search_invoices($q, $limit);
        if (count($invoices_search['result']) > 0) {
            $result[] = $invoices_search;
        }

        $credit_notes_search = $this->_search_credit_notes($q, $limit);
        if (count($credit_notes_search['result']) > 0) {
            $result[] = $credit_notes_search;
        }

        $estimates_search = $this->_search_estimates($q, $limit);
        if (count($estimates_search['result']) > 0) {
            $result[] = $estimates_search;
        }

        $expenses_search = $this->_search_expenses($q, $limit);
        if (count($expenses_search['result']) > 0) {
            $result[] = $expenses_search;
        }

        $projects_search = $this->_search_projects($q, $limit);
        if (count($projects_search['result']) > 0) {
            $result[] = $projects_search;
        }

        $contracts_search = $this->_search_contracts($q, $limit);
        if (count($contracts_search['result']) > 0) {
            $result[] = $contracts_search;
        }

        if (has_permission('surveys', '', 'view')) {
            // Surveys
            $this->db->select()->from('tblsurveys')->like('subject', $q)->or_like('slug', $q)->or_like('description', $q)->or_like('viewdescription', $q)->limit($limit);
            $this->db->order_by('subject', 'ASC');
            $result[] = [
                'result'         => $this->db->get()->result_array(),
                'type'           => 'surveys',
                'search_heading' => _l('surveys'),
            ];
        }

        if (has_permission('knowledge_base', '', 'view')) {
            // Knowledge base articles
            $this->db->select()->from('tblknowledgebase')->like('subject', $q)->or_like('description', $q)->or_like('slug', $q)->limit($limit);

            $this->db->order_by('subject', 'ASC');

            $result[] = [
                'result'         => $this->db->get()->result_array(),
                'type'           => 'knowledge_base_articles',
                'search_heading' => _l('kb_string'),
            ];
        }

        // Tasks Search
        $tasks = has_permission('tasks', '', 'view');
        // Staff tasks
        $this->db->select();
        $this->db->from('tblstafftasks');
        if (!$is_admin) {
            if (!$tasks) {
                $where = '(id IN (SELECT taskid FROM tblstafftaskassignees WHERE staffid = ' . get_staff_user_id() . ') OR id IN (SELECT taskid FROM tblstafftasksfollowers WHERE staffid = ' . get_staff_user_id() . ') OR (addedfrom=' . get_staff_user_id() . ' AND is_added_from_contact=0) ';
                if (get_option('show_all_tasks_for_project_member') == 1) {
                    $where .= ' OR (rel_type="project" AND rel_id IN (SELECT project_id FROM tblprojectmembers WHERE staff_id=' . get_staff_user_id() . '))';
                }
                $where .= ' OR is_public = 1)';
                $this->db->where($where);
            } //!$tasks
        } //!$is_admin
        if (!_startsWith($q, '#')) {
            $this->db->where('(name LIKE "%' . $q . '%" OR description LIKE "%' . $q . '%")');
        } else {
            $this->db->where('id IN
                (SELECT rel_id FROM tbltags_in WHERE tag_id IN
                (SELECT id FROM tbltags WHERE name="' . strafter($q, '#') . '")
                AND tbltags_in.rel_type=\'task\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
        }

        $this->db->limit($limit);
        $this->db->order_by('name', 'ASC');

        $result[] = [
            'result'         => $this->db->get()->result_array(),
            'type'           => 'tasks',
            'search_heading' => _l('tasks'),
        ];


        // Payments search
        $has_permission_view_payments     = has_permission('payments', '', 'view');
        $has_permission_view_invoices_own = has_permission('invoices', '', 'view_own');

        if (has_permission('payments', '', 'view') || $has_permission_view_invoices_own || get_option('allow_staff_view_invoices_assigned') == '1') {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            } elseif (_startsWith($q, get_option('invoice_prefix'))) {
                $q = strafter($q, get_option('invoice_prefix'));
                $q = trim($q);
                $q = ltrim($q, '0');
            }
            // Invoice payment records
            $this->db->select('*,tblinvoicepaymentrecords.id as paymentid');
            $this->db->from('tblinvoicepaymentrecords');
            $this->db->join('tblinvoicepaymentsmodes', 'tblinvoicepaymentrecords.paymentmode = tblinvoicepaymentsmodes.id', 'LEFT');
            $this->db->join('tblinvoices', 'tblinvoices.id = tblinvoicepaymentrecords.invoiceid');

            if (!$has_permission_view_payments) {
                $this->db->where('invoiceid IN (select id from tblinvoices where ' . get_invoices_where_sql_for_staff(get_staff_user_id()) . ')');
            }

            $this->db->where('(tblinvoicepaymentrecords.id LIKE "' . $q . '"
                OR paymentmode LIKE "%' . $q . '%"
                OR tblinvoicepaymentsmodes.name LIKE "%' . $q . '%"
                OR tblinvoicepaymentrecords.note LIKE "%' . $q . '%"
                OR number LIKE "' . $q . '"
                )');

            $this->db->order_by('tblinvoicepaymentrecords.date', 'ASC');

            $result[] = [
                'result'         => $this->db->get()->result_array(),
                'type'           => 'invoice_payment_records',
                'search_heading' => _l('payments'),
            ];
        }


        if (has_permission('goals', '', 'view')) {
            // Goals
            $this->db->select()->from('tblgoals')->like('description', $q)->or_like('subject', $q)->limit($limit);

            $this->db->order_by('subject', 'ASC');

            $result[] = [
                'result'         => $this->db->get()->result_array(),
                'type'           => 'goals',
                'search_heading' => _l('goals'),
            ];
        }

        // Custom fields only admins
        if ($is_admin) {
            $this->db->select()->from('tblcustomfieldsvalues')->like('value', $q)->limit($limit);
            $result[] = [
                'result'         => $this->db->get()->result_array(),
                'type'           => 'custom_fields',
                'search_heading' => _l('custom_fields'),
            ];
        }

        // Invoice Items Search
        $has_permission_view_invoices       = has_permission('invoices', '', 'view');
        $has_permission_view_invoices_own   = has_permission('invoices', '', 'view_own');
        $allow_staff_view_invoices_assigned = get_option('allow_staff_view_invoices_assigned');

        if ($has_permission_view_invoices || $has_permission_view_invoices_own || $allow_staff_view_invoices_assigned == '1') {
            $this->db->select()->from('tblitems_in');
            $this->db->where('rel_type', 'invoice');
            $this->db->where('(description LIKE "%' . $q . '%" OR long_description LIKE "%' . $q . '%")');

            if (!$has_permission_view_invoices) {
                $this->db->where('rel_id IN (select id from tblinvoices where ' . get_invoices_where_sql_for_staff(get_staff_user_id()) . ')');
            }

            $this->db->order_by('description', 'ASC');
            $result[] = [
                'result'         => $this->db->get()->result_array(),
                'type'           => 'invoice_items',
                'search_heading' => _l('invoice_items'),
            ];
        }

        // Estimate Items Search
        $has_permission_view_estimates     = has_permission('estimates', '', 'view');
        $has_permission_view_estimates_own = has_permission('estimates', '', 'view_own');
        $allow_staff_view_estimates_assigned = get_option('allow_staff_view_estimates_assigned');
        if ($has_permission_view_estimates || $has_permission_view_estimates_own || $allow_staff_view_estimates_assigned) {
            $this->db->select()->from('tblitems_in');
            $this->db->where('rel_type', 'estimate');

            if (!$has_permission_view_estimates) {
                $this->db->where('rel_id IN (select id from tblestimates where ' . get_estimates_where_sql_for_staff(get_staff_user_id()) . ')');
            }
            $this->db->where('(description LIKE "%' . $q . '%" OR long_description LIKE "%' . $q . '%")');
            $this->db->order_by('description', 'ASC');
            $result[] = [
                'result'         => $this->db->get()->result_array(),
                'type'           => 'estimate_items',
                'search_heading' => _l('estimate_items'),
            ];
        }

        return $result;
    }

    public function _search_proposals($q, $limit = 0)
    {
        $result = [
            'result'         => [],
            'type'           => 'proposals',
            'search_heading' => _l('proposals'),
        ];

        $has_permission_view_proposals     = has_permission('proposals', '', 'view');
        $has_permission_view_proposals_own = has_permission('proposals', '', 'view_own');

        if ($has_permission_view_proposals || $has_permission_view_proposals_own || get_option('allow_staff_view_proposals_assigned') == '1') {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            } elseif (_startsWith($q, get_option('proposal_number_prefix'))) {
                $q = strafter($q, get_option('proposal_number_prefix'));
                $q = trim($q);
                $q = ltrim($q, '0');
            }

            // Proposals
            $this->db->select('*,tblproposals.id as id');
            $this->db->from('tblproposals');
            $this->db->join('tblcurrencies', 'tblcurrencies.id = tblproposals.currency');

            if (!$has_permission_view_proposals) {
                $this->db->where(get_proposals_sql_where_staff(get_staff_user_id()));
            }

            $this->db->where('(
                tblproposals.id LIKE "' . $q . '%"
                OR tblproposals.subject LIKE "%' . $q . '%"
                OR tblproposals.content LIKE "%' . $q . '%"
                OR tblproposals.proposal_to LIKE "%' . $q . '%"
                OR tblproposals.zip LIKE "%' . $q . '%"
                OR tblproposals.state LIKE "%' . $q . '%"
                OR tblproposals.city LIKE "%' . $q . '%"
                OR tblproposals.address LIKE "%' . $q . '%"
                OR tblproposals.email LIKE "%' . $q . '%"
                OR tblproposals.phone LIKE "%' . $q . '%"
                )');

            $this->db->order_by('tblproposals.id', 'desc');
            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_leads($q, $limit = 0, $where = [])
    {
        $result = [
            'result'         => [],
            'type'           => 'leads',
            'search_heading' => _l('leads'),
        ];

        $has_permission_view = has_permission('leads', '', 'view');

        if (is_staff_member()) {
            // Leads
            $this->db->select();
            $this->db->from('tblleads');

            if (!$has_permission_view) {
                $this->db->where('(assigned = ' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public=1)');
            }

            if (!_startsWith($q, '#')) {
                $this->db->where('(name LIKE "%' . $q . '%"
                    OR title LIKE "%' . $q . '%"
                    OR company LIKE "%' . $q . '%"
                    OR zip LIKE "%' . $q . '%"
                    OR city LIKE "%' . $q . '%"
                    OR state LIKE "%' . $q . '%"
                    OR address LIKE "%' . $q . '%"
                    OR email LIKE "%' . $q . '%"
                    OR phonenumber LIKE "%' . $q . '%"
                    )');
            } else {
                $this->db->where('id IN
                    (SELECT rel_id FROM tbltags_in WHERE tag_id IN
                    (SELECT id FROM tbltags WHERE name="' . strafter($q, '#') . '")
                    AND tbltags_in.rel_type=\'lead\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                    ');
            }


            $this->db->where($where);

            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $this->db->order_by('name', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_tickets($q, $limit = 0)
    {
        $result = [
            'result'         => [],
            'type'           => 'tickets',
            'search_heading' => _l('support_tickets'),
        ];

        if (is_staff_member() || (!is_staff_member() && get_option('access_tickets_to_none_staff_members') == 1)) {
            $is_admin = is_admin();

            $where = '';
            if (!$is_admin && get_option('staff_access_only_assigned_departments') == 1) {
                $this->load->model('departments_model');
                $staff_deparments_ids = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                $departments_ids      = [];
                if (count($staff_deparments_ids) == 0) {
                    $departments = $this->departments_model->get();
                    foreach ($departments as $department) {
                        array_push($departments_ids, $department['departmentid']);
                    }
                } else {
                    $departments_ids = $staff_deparments_ids;
                }
                if (count($departments_ids) > 0) {
                    $where = 'department IN (SELECT departmentid FROM tblstaffdepartments WHERE departmentid IN (' . implode(',', $departments_ids) . ') AND staffid="' . get_staff_user_id() . '")';
                }
            }

            $this->db->select();
            $this->db->from('tbltickets');
            $this->db->join('tbldepartments', 'tbldepartments.departmentid = tbltickets.department');
            $this->db->join('tblclients', 'tblclients.userid = tbltickets.userid', 'left');
            $this->db->join('tblcontacts', 'tblcontacts.id = tbltickets.contactid', 'left');


            if (!_startsWith($q, '#')) {
                $this->db->where('(
                    ticketid LIKE "' . $q . '%"
                    OR subject LIKE "%' . $q . '%"
                    OR message LIKE "%' . $q . '%"
                    OR tblcontacts.email LIKE "%' . $q . '%"
                    OR CONCAT(firstname, \' \', lastname) LIKE "%' . $q . '%"
                    OR company LIKE "%' . $q . '%"
                    OR vat LIKE "%' . $q . '%"
                    OR tblcontacts.phonenumber LIKE "%' . $q . '%"
                    OR tblclients.phonenumber LIKE "%' . $q . '%"
                    OR city LIKE "%' . $q . '%"
                    OR state LIKE "%' . $q . '%"
                    OR address LIKE "%' . $q . '%"
                    OR tbldepartments.name LIKE "%' . $q . '%"
                    )');

                if ($where != '') {
                    $this->db->where($where);
                }
            } else {
                $this->db->where('ticketid IN
                    (SELECT rel_id FROM tbltags_in WHERE tag_id IN
                    (SELECT id FROM tbltags WHERE name="' . strafter($q, '#') . '")
                    AND tbltags_in.rel_type=\'ticket\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                    ');
            }

            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $this->db->order_by('ticketid', 'DESC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_contacts($q, $limit = 0, $where = '')
    {
        $result = [
            'result'         => [],
            'type'           => 'contacts',
            'search_heading' => _l('customer_contacts'),
        ];

        $have_assigned_customers        = have_assigned_customers();
        $have_permission_customers_view = has_permission('customers', '', 'view');

        if ($have_assigned_customers || $have_permission_customers_view) {
            // Contacts
            $this->db->select(implode(',', prefixed_table_fields_array('tblcontacts')) . ',company');
            $this->db->from('tblcontacts');

            $this->db->join('tblclients', 'tblclients.userid=tblcontacts.userid', 'left');
            $this->db->where('(firstname LIKE "%' . $q . '%"
                OR lastname LIKE "%' . $q . '%"
                OR email LIKE "%' . $q . '%"
                OR CONCAT(firstname, \' \', lastname) LIKE "%' . $q . '%"
                OR tblcontacts.phonenumber LIKE "%' . $q . '%"
                OR tblcontacts.title LIKE "%' . $q . '%"
                OR company LIKE "%' . $q . '%"
                )');

            if ($where != '') {
                $this->db->where($where);
            }

            if ($limit != 0) {
                $this->db->limit($limit);
            }

            $this->db->order_by('firstname', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_contact_byname($q, $limit = 0, $where = '')
    {
        $result = [
            'result'         => [],
            'type'           => 'contacts',
            'search_heading' => _l('contacts'),
        ];

        $have_assigned_customers        = have_assigned_customers();
        $have_permission_customers_view = has_permission('customers', '', 'view');

        if ($have_assigned_customers || $have_permission_customers_view) {
            // Contacts

            $this->db->select(implode(',', prefixed_table_fields_array('tblcontacts')));
            $this->db->from('tblcontacts');
            $this->db->where('firstname LIKE "%' . $q . '%" or lastname like "%' . $q . '%" ');

            if ($where != '') {
                $this->db->where($where);
            }

            if ($limit != 0) {
                $this->db->limit($limit);
            }

            $this->db->order_by('firstname', 'ASC');

            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_companies($q, $limit = 0, $where = '')
    {
        $q = trim($q);
        $result = array(
            'result' => array(),
            'type' => 'companies',
            'search_heading' => _l('companies')
        );

        $have_assigned_customers        = have_assigned_customers();
        $have_permission_customers_view = has_permission('customers', '', 'view');

        if ($have_assigned_customers || $have_permission_customers_view) {
            // Contacts
            $this->db->select(implode(',', prefixed_table_fields_array('tblclients')));
            $this->db->from('tblclients');
            $this->db->where('company LIKE "%' . $q . '%" ');

            if ($where != '') {
                $this->db->where($where);
            }

            if ($limit != 0) {
                $this->db->limit($limit);
            }

            $this->db->order_by('userid', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }



    public function _search_contacts_related_to($q, $limit = 0, $where = '')
    {
        $q = trim($q);
        $result = array(
            'result' => array(),
            'type' => 'contacts',
            'search_heading' => _l('contacts')
        );

        $have_assigned_customers        = have_assigned_customers();
        $have_permission_customers_view = has_permission('customers', '', 'view');

        if ($have_assigned_customers || $have_permission_customers_view) {
            // Contacts
            $this->db->select(implode(',', prefixed_table_fields_array('tblcontacts')));
            $this->db->from('tblcontacts');
            $this->db->where('firstname LIKE "%' . $q . '%" or lastname LIKE "%' . $q . '%" or email LIKE "%' . $q . '%" ');
            if ($where != '') {
                $this->db->where($where);
            }

            if ($limit != 0) {
                $this->db->limit($limit);
            }

            $this->db->order_by('id', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_staff($q, $limit = 0)
    {
        $result = [
            'result'         => [],
            'type'           => 'staff',
            'search_heading' => _l('staff_members'),
        ];

        if (has_permission('staff', '', 'view')) {
            // Staff
            $this->db->select();
            $this->db->from('tblstaff');
            $this->db->like('firstname', $q);
            $this->db->or_like('lastname', $q);
            $this->db->or_like("CONCAT(firstname, ' ', lastname)", $q, false);
            $this->db->or_like('facebook', $q);
            $this->db->or_like('linkedin', $q);
            $this->db->or_like('phonenumber', $q);
            $this->db->or_like('email', $q);
            $this->db->or_like('skype', $q);

            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $this->db->order_by('firstname', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_contracts($q, $limit = 0)
    {
        $result = [
            'result'         => [],
            'type'           => 'contracts',
            'search_heading' => _l('contracts'),
        ];

        $has_permission_view_contracts = has_permission('contracts', '', 'view');
        if ($has_permission_view_contracts || has_permission('contracts', '', 'view_own')) {
            // Contracts
            $this->db->select();
            $this->db->from('tblcontracts');
            if (!$has_permission_view_contracts) {
                $this->db->where('tblcontracts.addedfrom', get_staff_user_id());
            }

            $this->db->where('(description LIKE "%' . $q . '%" OR subject LIKE "%' . $q . '%")');

            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $this->db->order_by('subject', 'ASC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_projects($q, $limit = 0, $where = false)
    {
        $result = [
            'result'         => [],
            'type'           => 'projects',
            'search_heading' => _l('projects'),
        ];

        $projects = has_permission('projects', '', 'view');
        // Projects
        $this->db->select();
        $this->db->from('tblprojects');
        $this->db->join('tblclients', 'tblclients.userid = tblprojects.clientid');
        if (!$projects) {
            $this->db->where('tblprojects.id IN (SELECT project_id FROM tblprojectmembers WHERE staff_id=' . get_staff_user_id() . ')');
        }
        if ($where != false) {
            $this->db->where($where);
        }
        if (!_startsWith($q, '#')) {
            $this->db->where('(company LIKE "%' . $q . '%"
                OR description LIKE "%' . $q . '%"
                OR name LIKE "%' . $q . '%"
                OR vat LIKE "%' . $q . '%"
                OR phonenumber LIKE "%' . $q . '%"
                OR city LIKE "%' . $q . '%"
                OR zip LIKE "%' . $q . '%"
                OR state LIKE "%' . $q . '%"
                OR zip LIKE "%' . $q . '%"
                OR address LIKE "%' . $q . '%"
                )');
        } else {
            $this->db->where('id IN
                (SELECT rel_id FROM tbltags_in WHERE tag_id IN
                (SELECT id FROM tbltags WHERE name="' . strafter($q, '#') . '")
                AND tbltags_in.rel_type=\'project\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
        }

        if ($limit != 0) {
            $this->db->limit($limit);
        }

        $this->db->order_by('name', 'ASC');
        $result['result'] = $this->db->get()->result_array();

        return $result;
    }

    public function _search_invoices($q, $limit = 0)
    {
        $result = [
            'result'         => [],
            'type'           => 'invoices',
            'search_heading' => _l('invoices'),
        ];
        $has_permission_view_invoices     = has_permission('invoices', '', 'view');
        $has_permission_view_invoices_own = has_permission('invoices', '', 'view_own');

        if ($has_permission_view_invoices || $has_permission_view_invoices_own || get_option('allow_staff_view_invoices_assigned') == '1') {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            } elseif (_startsWith($q, get_option('invoice_prefix'))) {
                $q = strafter($q, get_option('invoice_prefix'));
                $q = trim($q);
                $q = ltrim($q, '0');
            }
            $invoice_fields = prefixed_table_fields_array('tblinvoices');
            $clients_fields = prefixed_table_fields_array('tblclients');
            // Invoices
            $this->db->select(implode(',', $invoice_fields) . ',' . implode(',', $clients_fields) . ',tblinvoices.id as invoiceid,' . get_sql_select_client_company());
            $this->db->from('tblinvoices');
            $this->db->join('tblclients', 'tblclients.userid = tblinvoices.clientid', 'left');
            $this->db->join('tblcurrencies', 'tblcurrencies.id = tblinvoices.currency');
            $this->db->join('tblcontacts', 'tblcontacts.userid = tblclients.userid AND is_primary = 1', 'left');

            if (!$has_permission_view_invoices) {
                $this->db->where(get_invoices_where_sql_for_staff(get_staff_user_id()));
            }
            if (!_startsWith($q, '#')) {
                $this->db->where('(
                tblinvoices.number LIKE "' . $q . '"
                OR
                tblclients.company LIKE "%' . $q . '%"
                OR
                tblinvoices.clientnote LIKE "%' . $q . '%"
                OR
                tblclients.vat LIKE "%' . $q . '%"
                OR
                tblclients.phonenumber LIKE "%' . $q . '%"
                OR
                tblclients.city LIKE "%' . $q . '%"
                OR
                tblclients.state LIKE "%' . $q . '%"
                OR
                tblclients.zip LIKE "%' . $q . '%"
                OR
                tblclients.address LIKE "%' . $q . '%"
                OR
                tblinvoices.adminnote LIKE "%' . $q . '%"
                OR
                CONCAT(firstname,\' \',lastname) LIKE "%' . $q . '%"
                OR
                tblinvoices.billing_street LIKE "%' . $q . '%"
                OR
                tblinvoices.billing_city LIKE "%' . $q . '%"
                OR
                tblinvoices.billing_state LIKE "%' . $q . '%"
                OR
                tblinvoices.billing_zip LIKE "%' . $q . '%"
                OR
                tblinvoices.shipping_street LIKE "%' . $q . '%"
                OR
                tblinvoices.shipping_city LIKE "%' . $q . '%"
                OR
                tblinvoices.shipping_state LIKE "%' . $q . '%"
                OR
                tblinvoices.shipping_zip LIKE "%' . $q . '%"
                OR
                tblclients.billing_street LIKE "%' . $q . '%"
                OR
                tblclients.billing_city LIKE "%' . $q . '%"
                OR
                tblclients.billing_state LIKE "%' . $q . '%"
                OR
                tblclients.billing_zip LIKE "%' . $q . '%"
                OR
                tblclients.shipping_street LIKE "%' . $q . '%"
                OR
                tblclients.shipping_city LIKE "%' . $q . '%"
                OR
                tblclients.shipping_state LIKE "%' . $q . '%"
                OR
                tblclients.shipping_zip LIKE "%' . $q . '%"
                )');
            } else {
                $this->db->where('tblinvoices.id IN
                (SELECT rel_id FROM tbltags_in WHERE tag_id IN
                (SELECT id FROM tbltags WHERE name="' . strafter($q, '#') . '")
                AND tbltags_in.rel_type=\'invoice\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
            }


            $this->db->order_by('number,YEAR(date)', 'desc');
            if ($limit != 0) {
                $this->db->limit($limit);
            }

            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_credit_notes($q, $limit = 0)
    {
        $result = [
            'result'         => [],
            'type'           => 'credit_note',
            'search_heading' => _l('credit_notes'),
        ];

        $has_permission_view_credit_notes     = has_permission('credit_notes', '', 'view');
        $has_permission_view_credit_notes_own = has_permission('credit_notes', '', 'view_own');

        if ($has_permission_view_credit_notes || $has_permission_view_credit_notes_own) {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            } elseif (_startsWith($q, get_option('credit_note_prefix'))) {
                $q = strafter($q, get_option('credit_note_prefix'));
                $q = trim($q);
                $q = ltrim($q, '0');
            }
            $credit_note_fields = prefixed_table_fields_array('tblcreditnotes');
            $clients_fields     = prefixed_table_fields_array('tblclients');
            // Invoices
            $this->db->select(implode(',', $credit_note_fields) . ',' . implode(',', $clients_fields) . ',tblcreditnotes.id as credit_note_id,' . get_sql_select_client_company());
            $this->db->from('tblcreditnotes');
            $this->db->join('tblclients', 'tblclients.userid = tblcreditnotes.clientid', 'left');
            $this->db->join('tblcurrencies', 'tblcurrencies.id = tblcreditnotes.currency');
            $this->db->join('tblcontacts', 'tblcontacts.userid = tblclients.userid AND is_primary = 1', 'left');

            if (!$has_permission_view_credit_notes) {
                $this->db->where('tblcreditnotes.addedfrom', get_staff_user_id());
            }

            $this->db->where('(
                tblcreditnotes.number LIKE "' . $q . '"
                OR
                tblclients.company LIKE "%' . $q . '%"
                OR
                tblcreditnotes.clientnote LIKE "%' . $q . '%"
                OR
                tblclients.vat LIKE "%' . $q . '%"
                OR
                tblclients.phonenumber LIKE "%' . $q . '%"
                OR
                tblclients.city LIKE "%' . $q . '%"
                OR
                tblclients.state LIKE "%' . $q . '%"
                OR
                tblclients.zip LIKE "%' . $q . '%"
                OR
                tblclients.address LIKE "%' . $q . '%"
                OR
                tblcreditnotes.adminnote LIKE "%' . $q . '%"
                OR
                CONCAT(firstname,\' \',lastname) LIKE "%' . $q . '%"
                OR
                tblcreditnotes.billing_street LIKE "%' . $q . '%"
                OR
                tblcreditnotes.billing_city LIKE "%' . $q . '%"
                OR
                tblcreditnotes.billing_state LIKE "%' . $q . '%"
                OR
                tblcreditnotes.billing_zip LIKE "%' . $q . '%"
                OR
                tblcreditnotes.shipping_street LIKE "%' . $q . '%"
                OR
                tblcreditnotes.shipping_city LIKE "%' . $q . '%"
                OR
                tblcreditnotes.shipping_state LIKE "%' . $q . '%"
                OR
                tblcreditnotes.shipping_zip LIKE "%' . $q . '%"
                OR
                tblclients.billing_street LIKE "%' . $q . '%"
                OR
                tblclients.billing_city LIKE "%' . $q . '%"
                OR
                tblclients.billing_state LIKE "%' . $q . '%"
                OR
                tblclients.billing_zip LIKE "%' . $q . '%"
                OR
                tblclients.shipping_street LIKE "%' . $q . '%"
                OR
                tblclients.shipping_city LIKE "%' . $q . '%"
                OR
                tblclients.shipping_state LIKE "%' . $q . '%"
                OR
                tblclients.shipping_zip LIKE "%' . $q . '%"
                )');


            $this->db->order_by('number', 'desc');
            if ($limit != 0) {
                $this->db->limit($limit);
            }

            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_estimates($q, $limit = 0)
    {
        $result = [
            'result'         => [],
            'type'           => 'estimates',
            'search_heading' => _l('estimates'),
        ];

        $has_permission_view_estimates     = has_permission('estimates', '', 'view');
        $has_permission_view_estimates_own = has_permission('estimates', '', 'view_own');

        if ($has_permission_view_estimates || $has_permission_view_estimates_own || get_option('allow_staff_view_estimates_assigned') == '1') {
            if (is_numeric($q)) {
                $q = trim($q);
                $q = ltrim($q, '0');
            } elseif (_startsWith($q, get_option('estimate_prefix'))) {
                $q = strafter($q, get_option('estimate_prefix'));
                $q = trim($q);
                $q = ltrim($q, '0');
            }
            // Estimates
            $estimates_fields = prefixed_table_fields_array('tblestimates');
            $clients_fields   = prefixed_table_fields_array('tblclients');

            $this->db->select(implode(',', $estimates_fields) . ',' . implode(',', $clients_fields) . ',tblestimates.id as estimateid,' . get_sql_select_client_company());
            $this->db->from('tblestimates');
            $this->db->join('tblclients', 'tblclients.userid = tblestimates.clientid', 'left');
            $this->db->join('tblcurrencies', 'tblcurrencies.id = tblestimates.currency');
            $this->db->join('tblcontacts', 'tblcontacts.userid = tblclients.userid AND is_primary = 1', 'left');

            if (!$has_permission_view_estimates) {
                $this->db->where(get_estimates_where_sql_for_staff(get_staff_user_id()));
            }

            $this->db->where('(
                tblestimates.number LIKE "' . $q . '"
                OR
                tblclients.company LIKE "%' . $q . '%"
                OR
                tblestimates.clientnote LIKE "%' . $q . '%"
                OR
                tblclients.vat LIKE "%' . $q . '%"
                OR
                tblclients.phonenumber LIKE "%' . $q . '%"
                OR
                tblclients.city LIKE "%' . $q . '%"
                OR
                tblclients.state LIKE "%' . $q . '%"
                OR
                tblclients.zip LIKE "%' . $q . '%"
                OR
                address LIKE "%' . $q . '%"
                OR
                tblestimates.adminnote LIKE "%' . $q . '%"
                OR
                tblestimates.billing_street LIKE "%' . $q . '%"
                OR
                tblestimates.billing_city LIKE "%' . $q . '%"
                OR
                tblestimates.billing_state LIKE "%' . $q . '%"
                OR
                tblestimates.billing_zip LIKE "%' . $q . '%"
                OR
                tblestimates.shipping_street LIKE "%' . $q . '%"
                OR
                tblestimates.shipping_city LIKE "%' . $q . '%"
                OR
                tblestimates.shipping_state LIKE "%' . $q . '%"
                OR
                tblestimates.shipping_zip LIKE "%' . $q . '%"
                OR
                tblclients.billing_street LIKE "%' . $q . '%"
                OR
                tblclients.billing_city LIKE "%' . $q . '%"
                OR
                tblclients.billing_state LIKE "%' . $q . '%"
                OR
                tblclients.billing_zip LIKE "%' . $q . '%"
                OR
                tblclients.shipping_street LIKE "%' . $q . '%"
                OR
                tblclients.shipping_city LIKE "%' . $q . '%"
                OR
                tblclients.shipping_state LIKE "%' . $q . '%"
                OR
                tblclients.shipping_zip LIKE "%' . $q . '%"
                )');

            $this->db->order_by('number,YEAR(date)', 'desc');
            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_expenses($q, $limit = 0)
    {
        $result = [
            'result'         => [],
            'type'           => 'expenses',
            'search_heading' => _l('expenses'),
        ];

        $has_permission_expenses_view     = has_permission('expenses', '', 'view');
        $has_permission_expenses_view_own = has_permission('expenses', '', 'view_own');

        if ($has_permission_expenses_view || $has_permission_expenses_view_own) {
            // Expenses
            $this->db->select('*,tblexpenses.amount as amount,tblexpensescategories.name as category_name,tblinvoicepaymentsmodes.name as payment_mode_name,tbltaxes.name as tax_name, tblexpenses.id as expenseid');
            $this->db->from('tblexpenses');
            $this->db->join('tblclients', 'tblclients.userid = tblexpenses.clientid', 'left');
            $this->db->join('tblinvoicepaymentsmodes', 'tblinvoicepaymentsmodes.id = tblexpenses.paymentmode', 'left');
            $this->db->join('tbltaxes', 'tbltaxes.id = tblexpenses.tax', 'left');
            $this->db->join('tblexpensescategories', 'tblexpensescategories.id = tblexpenses.category');

            if (!$has_permission_expenses_view) {
                $this->db->where('tblexpenses.addedfrom', get_staff_user_id());
            }

            $this->db->where('(company LIKE "%' . $q . '%"
                OR paymentmode LIKE "%' . $q . '%"
                OR tblinvoicepaymentsmodes.name LIKE "%' . $q . '%"
                OR vat LIKE "%' . $q . '%"
                OR phonenumber LIKE "%' . $q . '%"
                OR city LIKE "%' . $q . '%"
                OR zip LIKE "%' . $q . '%"
                OR address LIKE "%' . $q . '%"
                OR state LIKE "%' . $q . '%"
                OR tblexpensescategories.name LIKE "%' . $q . '%"
                OR tblexpenses.note LIKE "%' . $q . '%"
                OR tblexpenses.expense_name LIKE "%' . $q . '%"
                )');

            if ($limit != 0) {
                $this->db->limit($limit);
            }
            $this->db->order_by('date', 'DESC');
            $result['result'] = $this->db->get()->result_array();
        }

        return $result;
    }

    public function _search_policies($q, $limit = 0, $where = '')
    {
        $result = [
            'result'         => [],
            'type'           => 'policies',
            'search_heading' => _l('policies'),
        ];

        $have_assigned_customers        = have_assigned_customers();
        $have_permission_customers_view = has_permission('customers', '', 'view');

        if ($have_assigned_customers || $have_permission_customers_view) {
            // Contacts
            $this->db->select(implode(',', prefixed_table_fields_array('tblpolicies')) );
            $this->db->from('tblpolicies');

            $this->db->where('(firstname LIKE "%' . $q . '%"
                OR lastname LIKE "%' . $q . '%"
                OR email LIKE "%' . $q . '%"
                OR CONCAT(firstname, \' \', lastname) LIKE "%' . $q . '%"
                OR phonenumber LIKE "%' . $q . '%"
                OR title LIKE "%' . $q . '%"
                )');

            if ($where != '') {
                $this->db->where($where);
            }

            if ($limit != 0) {
                $this->db->limit($limit);
            }

            $this->db->order_by('firstname', 'ASC');
            $result['result'] = $this->db->get()->result_array();

        }

        return $result;
    }

    public function get_emails_by_added_from($id)
    {

        $this->db->select('tblemails.*,tblstaff.firstname,tblstaff.lastname ');
        $this->db->join('tblstaff', 'tblstaff.staffid=tblemails.added_from');
        $this->db->where('tblemails.added_from',$id);
        $this->db->order_by('tblemails.id', 'desc');
        $arrResult = $this->db->get('tblemails')->result_array();

        return $arrResult;
    }
}

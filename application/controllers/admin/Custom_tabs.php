<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Custom_tabs extends Admin_controller
{
    private $pdf_fields = array();
    private $client_portal_fields = array();
    private $client_editable_fields = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_tabs_model');
        if (!is_admin()) {
            access_denied('Access Custom Tabs');
        }
    }

    /* List all custom fields */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('custom_tabs');
        }
        $data['title'] = _l('custom_tabs');
        $this->load->view('admin/custom_tabs/manage', $data);
    }

    public function tab($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                $id = $this->custom_tabs_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('custom_tab')));
                    redirect(admin_url('custom_tabs'));
                }
            } else {
                $success = $this->custom_tabs_model->update($this->input->post(), $id);
                if (is_array($success) && isset($success['cant_change_option_custom_tab'])) {
                    set_alert('warning', _l('cf_option_in_use'));
                } elseif ($success === true) {
                    set_alert('success', _l('updated_successfully', _l('custom_tab')));
                }
                redirect(admin_url('custom_tabs'));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('custom_tab_lowercase'));
        } else {
            $data['custom_tab'] = $this->custom_tabs_model->get($id);
            $title                = _l('edit', _l('custom_tab_lowercase'));
        }

        $this->load->model('custom_sections_model');
        $data['list_custom_section'] = $this->custom_sections_model->get(false,'');

        $data['title']                = $title;
        $this->load->view('admin/custom_tabs/customtab', $data);
    }

    /* Delete announcement from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('custom_tabs'));
        }
        $response = $this->custom_tabs_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('custom_tab')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('custom_tab_lowercase')));
        }
        redirect(admin_url('custom_tabs'));
    }

    /* Change survey status active or inactive*/
    public function change_custom_tab_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->custom_tabs_model->change_custom_tab_status($id, $status);
        }
    }
}

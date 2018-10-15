<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Custom_sections extends Admin_controller
{
    private $pdf_fields = array();
    private $client_portal_fields = array();
    private $client_editable_fields = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_sections_model');
        if (!is_admin()) {
            access_denied('Access Custom Sections');
        }
    }

    /* List all custom fields */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('custom_sections');
        }
        $data['title'] = _l('custom_sections');
        $this->load->view('admin/custom_sections/manage', $data);
    }

    public function section($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                $id = $this->custom_sections_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('custom_section')));
                    redirect(admin_url('custom_sections'));
                }
            } else {
                $success = $this->custom_sections_model->update($this->input->post(), $id);
                if (is_array($success) && isset($success['cant_change_option_custom_section'])) {
                    set_alert('warning', _l('cf_option_in_use'));
                } elseif ($success === true) {
                    set_alert('success', _l('updated_successfully', _l('custom_section')));
                }
                redirect(admin_url('custom_sections'));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('custom_section_lowercase'));
        } else {
            $data['custom_section'] = $this->custom_sections_model->get($id);
            $title                = _l('edit', _l('custom_section_lowercase'));
        }
        $data['title']                = $title;
        $this->load->view('admin/custom_sections/customsection', $data);
    }

    /* Delete announcement from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('custom_sections'));
        }
        $response = $this->custom_sections_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('custom_section')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('custom_section_lowercase')));
        }
        redirect(admin_url('custom_sections'));
    }

    /* Change survey status active or inactive*/
    public function change_custom_section_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->custom_sections_model->change_custom_section_status($id, $status);
        }
    }
}

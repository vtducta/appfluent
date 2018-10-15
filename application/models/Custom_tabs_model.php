<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Custom_tabs_model extends CRM_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single custom field
     */
    public function get($id = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblcustomtabs')->row();
        }

        return $this->db->get('tblcustomtabs')->result_array();
    }

    public function get_by_section_slug($section_slug ='')
    {

        $this->db->select('tblcustomtabs.*');
        $this->db->join('tblcustomsections', 'tblcustomtabs.custom_section_id=tblcustomsections.id');
        $this->db->where('tblcustomsections.slug', $section_slug);
        $arrResult = $this->db->get('tblcustomtabs')->result_array();
        return $arrResult;
    }

    public function get_tab_show_in_profile($show_in = 'contact_profile')
    {

        $this->db->select('tblcustomtabs.*');
        $this->db->where('tblcustomtabs.only_show_in', $show_in);
        $arrResult = $this->db->get('tblcustomtabs')->result_array();
        return $arrResult;
    }
    /**
     * Add new custom field
     * @param mixed $data All $_POST data
     * @return  boolean
     */
    public function add($data)
    {
        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }

        $data['slug'] = slug_it( $data['name'], array(
            'delimiter' => '_'
        ));
        $slugs_total = total_rows('tblcustomtabs', array('slug'=>$data['slug']));

        if ($slugs_total > 0) {
            $data['slug'] .= '_'.($slugs_total + 1);
        }


        $this->db->insert('tblcustomtabs', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Custom Tab Added [' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update custom field
     * @param mixed $data All $_POST data
     * @return  boolean
     */
    public function update($data, $id)
    {
        $original_field = $this->get($id);

        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }

        $this->db->where('id', $id);
        $this->db->update('tblcustomtabs', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Custom Tab Updated [' . $data['name'] . ']');
            return true;
        }
        return false;
    }

    /**
     * @param  integer
     * @return boolean
     * Delete Custom fields
     * All values for this custom field will be deleted from database
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('tblcustomtabs');
        if ($this->db->affected_rows() > 0) {
            $this->db->query('update tblcustomfields set custom_tab_id = 0 where custom_tab_id= '.$id);
            logActivity('Custom Tab Deleted [' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Change custom field status  / active / inactive
     * @param  mixed $id     customfield id
     * @param  integer $status active or inactive
     */
    public function change_custom_tab_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update('tblcustomtabs', array(
            'active' => $status
        ));
        logActivity('Custom Tab Status Changed [FieldID: ' . $id . ' - Active: ' . $status . ']');
    }



}

<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Custom_sections_model extends CRM_Model
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
    public function get($id = false,$menu='contacts')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblcustomsections')->row();
        }
        if ($menu){
            $this->db->where('menu', $menu);
            return $this->db->get('tblcustomsections')->result_array();
        }
        return $this->db->get('tblcustomsections')->result_array();
    }

    public function get_by_section_slug($slug = false,$menu='contacts')
    {
        if (is_numeric($slug)) {
            $this->db->where('slug', $slug);
            $this->db->where('menu', $menu);
            return $this->db->get('tblcustomsections')->row();
        }

        return false;
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
        $slugs_total = total_rows('tblcustomsections', array('slug'=>$data['slug']));

        if ($slugs_total > 0) {
            $data['slug'] .= '_'.($slugs_total + 1);
        }


        $this->db->insert('tblcustomsections', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Custom Section Added [' . $data['name'] . ']');

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
        $this->db->update('tblcustomsections', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Custom Section Updated [' . $data['name'] . ']');
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
        $this->db->delete('tblcustomsections');
        if ($this->db->affected_rows() > 0) {
            $this->db->query('update tblcustomtabs set custom_section_id = 0 where custom_section_id= '.$id);
            logActivity('Custom Section Deleted [' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Change custom field status  / active / inactive
     * @param  mixed $id     customfield id
     * @param  integer $status active or inactive
     */
    public function change_custom_section_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update('tblcustomsections', array(
            'active' => $status
        ));
        logActivity('Custom Section Status Changed [FieldID: ' . $id . ' - Active: ' . $status . ']');
    }



}

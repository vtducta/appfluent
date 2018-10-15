<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Trigger_condition_Model extends CRM_Model {

    private $table = 'tbltriggers_conditions';

    /**
     * get list conditions for contact category
     * @param
     * @return
 */
    function getConditions() {
        $this->db->select('*');
        $this->db->from($this->table);
        
        return $this->db->get()->result_array();
    }

}

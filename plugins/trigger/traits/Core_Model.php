<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Core_Model
 *
 * @author ducta
 */
trait Core_Model {

    //put your code here
    //protected $table = NULL;
    public function __construct() {
        if (isset($this->columns) && count($this->columns) > 0) {
            $this->columns = implode($this->columns, ',');
        } else {
            $this->columns = '*';
        }
    }

    /**
     * Description
     * @param $param a array ['id'=> 1, 'name=> test]
     * @return
     */
    function store($param, $id = NULL) {
        $rs = FALSE;
        try {
            if ($id) {
                $this->db->where('id', $id);
                $rs = $this->db->update($this->table, $param);
            } else {
                $rs = $this->db->insert($this->table, $param);
            }
        } catch (Exception $e) {
            //var_dump($e->getMessage());
            log_message('error', $e->getMessage());
        }

        return $rs;
    }

    /**
     * Description
     * @param
     * @return
     */
    function insertBatch($param) {
        try {
            
        } catch (Exception $ex) {
            
        }
    }

    /**
     * Description
     * @param
     * @return
     */
    function update($param, $id) {
        $this->db->where('id', $id);
        $this->db->update($this->table, $param);
    }

    /**
     * Description
     * @param
     * @return
     */
    function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
    }

    /**
     * Description
     * @param
     * @return
     */
    function getRecord($id) {
        $this->db->select($this->columns);
        $this->db->from($this->table);
        $this->db->where('id', $id);

        return $this->db->get()->row();
    }

    /**
     * Description
     * @param
     * @return
     */
    function getRecords() {
        $this->db->select($this->columns);
        $this->db->from($this->table);

        return $this->db->get()->result_array();
    }

}

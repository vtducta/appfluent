<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include APPPATH . '/../plugins/trigger/traits/Core_Model.php';

class Trigger_Model extends CRM_Model {

    use Core_Model;

    protected $table = 'tbltriggers';
    protected $IS_CONTACT_ADD = 'CONTACT IS ADDED';
    const CONTACT_TYPE = 'contact';

    /**
     * Description
     * @param
     * @return
     */
    function saveTrigger($param, $id = NULL) {
        $rs = FALSE;
        try {
            if ($id) {
                $rs = $this->db->update($this->table, $param);
            } else {
                $rs = $this->db->insert($this->table, $param);
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }

        return $rs;
    }

    /**
     * run cronjob
     */
    function runCron() {
        print 'Run Trigger' . PHP_EOL;
        $triggers = $this->getTriggers();
        if (!$triggers) {
            return;
        }
        $this->load->model('Newsletter_model');
        foreach ($triggers as $trigger) {
            print "SEND TRIGGER: {$trigger['condition_name']}" . PHP_EOL;
            if (strtolower($trigger['condition_category']) === self::CONTACT_TYPE) {
                print "TRIGGER contact:" . PHP_EOL;
                $this->triggerContact($trigger);
            }
        }
        print "SEND SUB TRIGGER" . PHP_EOL;
        $this->Newsletter_model->runCronTrigger();
    }

    /**
     * Send trigger when add new contact
     * @param
     * @return
     */
    function triggerContact($trigger) {
        $this->load->model('Newsletter_model');
        if (strtoupper($trigger['condition_name']) == $this->IS_CONTACT_ADD) {
            print "TRIGGER {$trigger['condition_name']}" . PHP_EOL;
            $contacts = $this->getLastestContact($trigger['trigger_value']);
//          print "NEW CONTACT {$contact[0]->datecreated}" . PHP_EOL;
            $isNewContact = count($contacts) > 0;//$contact->datecreated > $trigger['trigger_value'];
            print "IS NEW CONTACT {$isNewContact}" . PHP_EOL;
            print "trigger_value {$trigger['trigger_value']}" . PHP_EOL;
            if ($isNewContact) {
                $this->updateCampiagnKinds($contacts, $trigger['campaign_id'], self::CONTACT_TYPE);
                print "SEND EMAIL CAMPAIGN  {$trigger['campaign_id']}" . PHP_EOL;
                $this->Newsletter_model->sendEmail($trigger['campaign_id']);
                $contact = $contacts[0];
                print "UPDATE TRIGGER ID  {$trigger['id']}" . PHP_EOL;
                $this->update(['trigger_value' => date('Y-m-d H:i:s')], $trigger['id']);
            }
        }
    }

    /**
     * Get all trigger to add schedule for job
     * @param
     * @return
     */
    function getTriggers() {
        $this->db->select('cond.condition_name, cond.condition_category, trg.*');
        $this->db->from("{$this->table} AS trg");
        $this->db->join('tbltriggers_conditions as cond', "cond.id = trg.trigger_condition");

        return $this->db->get()->result_array();
    }

    /**
     * get last contact 
     * @param
     * @return
     */
    function getLastestContact($datecreated) {
        print "getLastestContact" . PHP_EOL;
        try {
            print "getLastestContact" . PHP_EOL;
            $query = $this->db->query("SELECT id, datecreated FROM tblcontacts WHERE datecreated > '$datecreated' ORDER BY id DESC");
            $result = $query->result_array();
            print "getLastestContact" . PHP_EOL;
        } catch (Exception $e) {
            print "TRIGGER ERROR {$e->getMessage()}" . PHP_EOL;
        }
        
        return $result;
    }
    
    /**
     * Description
     * @param
     * @return
    */
    function updateCampiagnKinds($contacts, $campaign_id, $type) {
        print "updateCampiagnKinds:" . PHP_EOL;
        foreach ($contacts AS $contact) {
            $param = [
                'kind' => $type,
                'kind_id' => $contact['id'],
                'campaign_id' => $campaign_id
            ];
            print "updateCampiagnKinds: " .json_encode($param) . PHP_EOL;
            $this->storeCampaign($param);
        }
    }
    /**
     * Description
     * @param
     * @return
 */
    function storeCampaign($param) {
        
        $this->db->insert('tblcampaigns_kinds', $param);
    }
}

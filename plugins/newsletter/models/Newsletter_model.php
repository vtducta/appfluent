<?php

class Newsletter_Model extends CRM_Model {

    public function getTemplateLists() {
        $query = $this->db->query("SELECT * FROM tblnewsletter_templates");
        return $query->result_array();
    }

    public function getTemplate($id) {
        $query = $this->db->query("SELECT * FROM tblnewsletter_templates WHERE id='$id'");
        return $query->row_array();
    }

    public function saveTemplate($title, $content, $id = null, $preview = null) {
        $preview = ($preview) ? $preview : $this->processFileUpload();
        //sanitize the content
        $content = str_replace(array("\'", "'"), "\'", $content);
        if (!$id) {
            //insert

            $this->db->query("INSERT INTO tblnewsletter_templates (title,content,preview) VALUES('$title','$content','$preview')");
        } else {
            $template = $this->getTemplate($id);
            if (!$preview)
                $preview = $template['preview'];
            $this->db->query("UPDATE tblnewsletter_templates SET title='$title',content='$content',preview='$preview' WHERE id='$id'");
        }
        return true;
    }

    public function deleteTemplate($id) {
        $this->db->query("DELETE FROM tblnewsletter_templates WHERE id='$id'");
    }

    public function getMailLists() {
        $result = array(
            'customers' => nom('newsletter_customers'),
            'staffs' => nom('newsletter_staffs'),
            'leads' => nom('newsletter_leads')
        );

        $query = $this->db->query("SELECT listid,name FROM tblemaillists ");
        $r = $query->result_array();
        foreach ($r as $fetch) {
            $result[$fetch['listid']] = $fetch['name'];
        }
        return $result;
    }

    public function getStaffs($online = false) {
        $sql = "SELECT * FROM tblstaff WHERE active='1' ";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getListingLists($list) {
        $sql = "SELECT * FROM tbllistemails WHERE listid='$list' ";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function deleteCampaign($id) {
        $this->db->query("DELETE FROM tblnewsletter_campaigns WHERE id='$id'");
        //$this->db->query("DELETE FROM tblnewsletter_campaigns WHERE id='$id'");
    }

    public function getCampaign($id) {
        $query = $this->db->query("SELECT * FROM tblnewsletter_campaigns WHERE id='$id'");
        return $query->row_array();
    }

    public function getCampaigns($conditions = []) {
        //$query = $this->db->query("SELECT * FROM tblnewsletter_campaigns WHERE active=1");
        $this->db->where('active', '1');

        if (count($conditions) > 0) {
            foreach ($conditions AS $key => $value) {
                $this->db->where($key, $value);
            }
        }
        return $this->db->get("tblnewsletter_campaigns")->result_array();
    }

    public function sendCampaign($id) {

        $date = date('Y-m-d');
        $this->db->query("UPDATE  tblnewsletter_campaigns SET status='1',sent_date='$date' WHERE id='$id'");
        $this->autoFinishCampaign($id);
        if (!get_option('newsletter_email_queue')) {
            $this->sendEmail($id);
        }
    }

    public function getRecentCampaigns() {
        $query = $this->db->query("SELECT * FROM tblnewsletter_campaigns ORDER BY id DESC LIMIT 10");
        return $query->result_array();
    }

    public function count($type) {
        $result = 0;
        if ($type == 'opens' or $type == 'clicks') {
            $query = $this->db->query("SELECT * FROM tblnewsletter_campaigns");

            foreach ($query->result_array() as $fetch) {
                $result += ($type == 'opens') ? $fetch['opens'] : $fetch['clicks'];
            }
        }
        switch ($type) {
            case 'mail-sent':
                $query = $this->db->query("SELECT id FROM tblnewsletter_campaigns_list WHERE sent='1' ");
                $result = count($query->result_array());
                break;
            case 'total-campaign':
                $query = $this->db->query("SELECT id FROM tblnewsletter_campaigns ");
                $result = count($query->result_array());
                break;
            case 'total-templates':
                $query = $this->db->query("SELECT id FROM tblnewsletter_templates ");
                $result = count($query->result_array());
                break;
            case 'total-list':
                $result = count($this->getMailLists());
                break;
            case 'in-progress':
                $query = $this->db->query("SELECT id FROM tblnewsletter_campaigns WHERE status='1'");
                $result = count($query->result_array());
                break;
            case 'completed':
                $query = $this->db->query("SELECT id FROM tblnewsletter_campaigns WHERE status='2'");
                $result = count($query->result_array());
                break;
        }
        return $result;
    }

    function datetimeValid($dateOn, $dateAt) {
        $dayOfWeek = date('w');
        $dayOfWeek = ($dayOfWeek == 0) ? 7 : $dayOfWeek;
        $currentTime = date('H:i');

        //print_r($dateAt);
        $orderDay = [
            'Mon' => 1,
            'Tue' => 2,
            'Wed' => 3,
            'Thu' => 4,
            'Fri' => 5,
            'Sat' => 6,
            'Sun' => 7
        ];

        if ($dateOn == 'any_day' && $dateAt == 'any_time') {
            return true;
        } else if ($dateOn == 'any_day' && $dateAt != 'any_time') {
            if ($dateAt == $currentTime) {
                return true;
            }
        } else if ($dateOn !== 'any_day' && ($dateAt == 'any_time' || $currentTime == $dateAt)) {
            print_r('ddđ: ' . strpos($dateOn, '-'));
            if (strpos($dateOn, '-') !== false) {
                $dateOn = str_split($dateOn, '-');
                if ($dayOfWeek >= $orderDay[$dateOn[0]] && $dayOfWeek <= $orderDay[$dateOn[1]]) {
                    return true;
                }
            } else if ($dayOfWeek == $orderDay[$dateOn]) {
                print_r($orderDay[$dateOn]);
                print_r($dayOfWeek);
                return true;
            }
        }

        return false;
    }

    public function sendEmail($id) {
        $campaign = $this->getCampaign($id);
        print "Begin send email with campaign: $id " . PHP_EOL;
        if (!$this->datetimeValid($campaign['send_on'], $campaign['send_at'])) {
            return;
        }
//        if ($campaign['status'] == 2)
//            return true; //emails under this campaign has been sent all
        $date = date('Y-m-d');
        $this->db->query("UPDATE tblnewsletter_campaigns SET sent_date = '$date' WHERE id='$id'");
        //get all emails that yet to be sent
        //$query = $this->db->query("SELECT * FROM tblnewsletter_campaigns_list WHERE campaign_id='$id' AND sent='0'");
        $query = $this->db->query("SELECT * FROM tblnewsletter_campaigns_list WHERE campaign_id='$id'");
        $lists = $query->result_array();
        print "Create email to send: " . PHP_EOL;
        if (count($lists) < 1) {
            //lets mark this campaign has been sent
            $this->db->query("UPDATE tblnewsletter_campaigns SET status='2', send_date='' WHERE id='$id'");
        } else {

            $this->saveCampaignLogs([
                'campaign_id' => $id,
                'campaign_name' => '',
                'description' => 'Send email to contact',
                'date' => date('Y-m-d H:i:s')
            ]);
            $this->load->library('email');

            $template = new StdClass();
            $template->message = $campaign['content'];
            $template->fromname = $campaign['sender_name'];
            $template->subject = $campaign['subject'];
            foreach ($lists as $fetch) {
                $fields = array();
                if ($fetch['source'] == 'contact') {
                    $q2 = $this->db->query("SELECT id,userid FROM tblcontacts WHERE email='" . $fetch['email'] . "'");
                    $r = $q2->row_array();
                    $fields = array_merge($fields, get_client_contact_merge_fields($r['userid'], $r['id']));
                } elseif ($fetch['source'] == 'staff') {
                    $q2 = $this->db->query("SELECT staffid FROM tblstaff WHERE email='" . $fetch['email'] . "'");
                    $r = $q2->row_array();
                    $fields = array_merge($fields, get_staff_merge_fields($r['staffid']));
                }elseif($fetch['source'] == 'email_to') {
                    preg_match('/\{\{(.*)\}\}/', $fetch['email'], $matches, PREG_OFFSET_CAPTURE);
                    if(count($matches) > 0) {
                        $emailMatch = trim($matches[1][0]);
                        if($emailMatch === 'email') {
                            $this->db->select('tblcontacts.email');
                            $this->db->from('tblcontacts');
                            $this->db->join('tblcampaigns_kinds', 'kind_id = tblcontacts.id');
                            $this->db->where('tblcampaigns_kinds.campaign_id', $id);
                            $result = $this->db->get()->result_array();
                        }
                        $fetch['email'] = $result;
                    }
                }

                $template = parse_email_template($template, $fields);
                $cnf = array(
                    'from_email' => $campaign['sender_email'],
                    'from_name' => $campaign['sender_name'],
                    'email' => $fetch['email'],
                    'subject' => $template->subject,
                    'message' => $template->message,
                    'to' => $fetch['email']
                );
                $cnf['message'] = check_for_links($cnf['message']);

                $cnf = do_action('before_send_simple_email', $cnf);
                $this->email = new CI_Email();
                $user = (get_option('smtp_username') == '') ? trim(get_option('smtp_email')) : trim(get_option('smtp_username'));
                $this->email->initialize(array(
                    'protocol' => get_option('email_protocol'),
                    'smtp_host' => trim(get_option('smtp_host')),
                    'smtp_port' => trim(get_option('smtp_port')),
                    'smtp_user' => $user,
                    'smtp_pass' => $this->encryption->decrypt(get_option('smtp_password')),
                    'smtp_crypto' => get_option('smtp_encryption'),
                    'wordwrap' => TRUE,
                    'mailtype' => 'html',
                    'mailpath' => '/usr/bin/sendmail',
                    'useragent' => 'CodeIgniter',
                    'charset' => 'UTF-8',
                    'newline' => "\r\n",
                    'crlf' => "\r\n"
                ));
                $this->email->set_newline("\r\n");
                $this->email->clear(true);
                $this->email->from($cnf['from_email'], $cnf['from_name']);
                $this->email->to($cnf['email']);
                if (isset($cnf['reply_to'])) {
                    $this->email->reply_to($cnf['reply_to']);
                }
                $cnf['message'] = $this->autoPrepareLink($cnf['message'], $id);
                $tracker = base_url("newsletter/track?id=" . $campaign['id']);
                $cnf['message'] .= '<img alt="" src="' . $tracker . '" width="1" height="1" border="0" />';
                //exit($cnf['message']);//let see whats there
                $this->email->subject($cnf['subject']);
                $this->email->message($cnf['message']);
                //var_dump($cnf['message']); die;
                $this->email->set_alt_message(strip_tags($cnf['message']));

                if ($this->email->send()) {
                    logActivity('Email sent to: ' . $cnf['email'] . ' Subject: ' . $cnf['subject']);
                    $this->db->query("UPDATE tblnewsletter_campaigns_list SET sent='1' WHERE id='" . $fetch['id'] . "'");
                    //$this->db->query("UPDATE tblnewsletter_campaigns SET active = 0 WHERE id='$id'");
                    //update this email has sent
                    //return true;
                } else {
                    return false;
                    // exit($this->email->print_debugger());
                    //echo $cnf['email']. ' Not sent ';
                }
            }
        }

        return true;
    }

    public function autoPrepareLink($text, $id) {
        $url = preg_match_all('/href=["\']?([^"\'>]+)["\']?/', $text, $match);
        $i = 0;
        foreach ($match[1] as $link) {
            $replace = $match[0][$i];
            $href = base_url("newsletter/redirect?id=$id&url=" . $link);
            $with = "href='$href'";
            $text = str_replace($replace, $with, $text);
            $i++;
        }

        return $text;
    }

    public function updateCampaign($campaignId, $type) {
        $this->db->query("UPDATE tblnewsletter_campaigns SET $type= $type + 1 WHERE id='$campaignId' ");
    }

    public function activeCampaign($campaignId, $status) {
        $status = 1 - $status;
        $this->db->query("UPDATE tblnewsletter_campaigns SET active=$status,status=1 WHERE id='$campaignId' ");
        $this->db->query("UPDATE tblnewsletter_campaigns_list SET sent=0 WHERE campaign_id='$campaignId' ");
    }

    public function addEmailCampaign($name, $email, $id, $source = 'list') {
        //echo "INSERT INTO tblnewsletter_campaigns_list (name,email,campaign_id,source)VALUES('$name','$email','$id','$source')".'<br/>';
        $this->db->query("INSERT INTO tblnewsletter_campaigns_list (name,email,campaign_id,source)VALUES('$name','$email','$id','$source')");
//        echo print_r($this->db->error());
    }

    public function generateCampaignDestination($id, $lists, $customers, $staffs, $email_to) {
        $result = false;
        if($id) {
            $this->db->query("DELETE FROM tblnewsletter_campaigns_list WHERE campaign_id = $id");
        }
        
        foreach ($lists as $list) {
            if (is_numeric($list)) {
                $emailLists = $this->getListingLists($list);
                foreach ($emailLists as $fetch) {
                    $result = true;
                    $this->addEmailCampaign('', $fetch['email'], $id);
                }
            } elseif ($list == 'leads') {
                $query = $this->db->query("SELECT name,email FROM tblleads");
                $result = $query->result_array();
                foreach ($result as $fetch) {
                    $result = true;
                    $this->addEmailCampaign($fetch['name'], $fetch['email'], $id, 'lead');
                }
            } elseif ($list == 'customers') {
                $query = $this->db->query("SELECT firstname,lastname,email FROM tblcontacts");
                $result = $query->result_array();
                foreach ($result as $fetch) {
                    $result = true;
                    $name = $fetch['firstname'] . ' ' . $fetch['lastname'];
                    $this->addEmailCampaign($name, $fetch['email'], $id, 'contact');
                }
            } elseif ($list == 'staffs') {
                $query = $this->db->query("SELECT firstname,lastname,email FROM tblstaff");
                $result = $query->result_array();
                foreach ($result as $fetch) {
                    $result = true;
                    $name = $fetch['firstname'] . ' ' . $fetch['lastname'];
                    $this->addEmailCampaign($name, $fetch['email'], $id, 'staff');
                }
            }
        }

        foreach ($customers as $customer) {
            $query = $this->db->query("SELECT firstname,lastname,email FROM tblcontacts WHERE userid='$customer'");
            $result = $query->result_array();
            foreach ($result as $fetch) {
                $result = true;
                $name = $fetch['firstname'] . ' ' . $fetch['lastname'];
                $this->addEmailCampaign($name, $fetch['email'], $id, 'contact');
            }
        }

        foreach ($staffs as $staff) {
            $query = $this->db->query("SELECT firstname,lastname,email FROM tblstaff WHERE staffid='$staff'");
            $result = $query->result_array();
            foreach ($result as $fetch) {
                $result = true;
                $name = $fetch['firstname'] . ' ' . $fetch['lastname'];
                $this->addEmailCampaign($name, $fetch['email'], $id, 'staff');
            }
        }
        
        if($email_to) {
            preg_match('/\{\{(.*)\}\}/', $email_to, $matches, PREG_OFFSET_CAPTURE);
            if(count($matches) > 0) {
                $emailMatch = trim($matches[1][0]);
                if($emailMatch === 'owner') {
                    $this->load->model('staff_model');
                    $email_to = $this->staff_model->get(get_staff_user_id())->email;
                }
            }
            
            $this->addEmailCampaign('', $email_to, $id, 'email_to');
        }
        $this->autoFinishCampaign($id);
    }

    public function autoFinishCampaign($id) {
        $campaign = $this->getCampaign($id);
        if ($campaign['status'] == 1 and ! $campaign['send_date']) {
            //count the number of emails under this campaign
            $query = $this->db->query("SELECT id FROM tblnewsletter_campaigns_list WHERE campaign_id='$id'");
            if (count($query->result_array()) == 0) {
                //we need to mark the campaign finished since no email to sent
                $date = date('Y-m-d');
                $this->db->query("UPDATE tblnewsletter_campaigns SET status='2',sent_date='{$date}' WHERE id='$id'");
            }
        }
    }

    public function saveCampaign($dataCampaign, $id = null) {
        $subject = $dataCampaign['subject'];
        $name = $dataCampaign['sender_name'];
        $email = $dataCampaign['sender_email'];
        $content = $dataCampaign['content'];
        $send_date = $dataCampaign['send_date'];
        $send_on = $dataCampaign['send_on'];
        $send_at = $dataCampaign['send_at'];
        $lists = $dataCampaign['lists'];
        $status = $dataCampaign['status'];
        $isInsert = null;
        $email_list = [
            'lists' => $lists,
            'customers' => $dataCampaign['customers'],
            'staffs' => $dataCampaign['staffs'],
            'email_to' =>$dataCampaign['email_to']
        ];
        $email_list_json = json_encode($email_list);
        if (!$id) {
            $this->db->query("INSERT INTO tblnewsletter_campaigns (subject,sender_name,sender_email,content,email_list,send_date,send_on,send_at,status)VALUES(
            '$subject','$name','$email', '$content','$email_list_json','$send_date','$send_on', '$send_at','$status')");
            //$this->db->insert('tblnewsletter_campaigns', $dataCampaign);
            $id = $this->db->insert_id();
            
            if ($dataCampaign['status'] == 1) {
                $this->sendCampaign($id);
            }
            $isInsert = true;
            // Save schedule
            $campaignSchedule['campaign_id'] = $id;
            $campaignSchedule['conditionals'] = $dataCampaign['conditionals'];
            $campaignSchedule['next_campaigns'] = $dataCampaign['next_campaigns'];
            $campaignSchedule['waitings'] = $dataCampaign['waitings'];
            $campaignSchedule['types'] = $dataCampaign['types'];
            //var_dump($campaignSchedule['conditionals']);die;
            $campaignSchedule['conditionals'] = array_filter($campaignSchedule['conditionals']);
            $campaignSchedule['next_campaigns'] = array_filter($campaignSchedule['next_campaigns']);
            if (count($campaignSchedule['conditionals']) > 0 && count($campaignSchedule['next_campaigns']) > 0) {
                $this->saveSchedule($campaignSchedule, $id, $isInsert);
                // Update parrent id
                $this->db->set('parrent_id', $id, FALSE);
                $this->db->where_in('id', $campaignSchedule['next_campaigns']);
                $this->db->update('tblnewsletter_campaigns');
            }
        } else {
            //$this->db->where('id', $id);
            //$this->db->update('tblnewsletter_campaigns', $dataCampaign);
            $this->db->query("UPDATE tblnewsletter_campaigns SET subject='$subject',
                                                                 sender_name='$name',
                                                                 sender_email='$email',
                                                                 content='$content',
                                                                 email_list='$email_list_json',
                                                                 send_date='$send_date',
                                                                 send_on='$send_on',
                                                                 send_at='$send_at' 
                                                            WHERE id='$id'");
        }
        $this->generateCampaignDestination($id, $dataCampaign['lists'], $dataCampaign['customers'], $dataCampaign['staffs'], $dataCampaign['email_to']);
        return true;
    }

    public function saveSchedule($campaignSchedule, $id, $isInsert) {
        if ($isInsert) {
            $conditionals = $campaignSchedule['conditionals'];
            for ($i = 0; $i < count($conditionals); $i++) {
                //$schedule = $schedules[$i];
                $camp = [];
                $camp['campaign_id'] = $campaignSchedule['campaign_id'];
                $camp['conditional'] = $conditionals[$i];
                $camp['next_campaign'] = $campaignSchedule['next_campaigns'][$i];
                $date = date_create();
                $strTime = $campaignSchedule['waitings'][$i] . ' ' . $campaignSchedule['types'][$i];
                date_add($date, date_interval_create_from_date_string($strTime));
                $camp['waiting'] = date_format($date, 'Y-m-d H:i:s');
                ;
                $this->db->insert('tblnewsletter_campaigns_schedule', $camp);
            }
        } else {
            $this->db->where('campaign_id', $id);
            $this->db->update('tblnewsletter_campaigns_schedule', $campaignSchedule);
        }
    }

    public function runCron() {
        $query = $this->db->query("SELECT * FROM tblnewsletter_campaigns WHERE status !='0' AND active =1 AND parrent_id = 0");
        $campaigns = $query->result_array();
        //var_dump($campaigns); die;
        foreach ($campaigns as $campaign) {

            if ($campaign['send_date'] != '' and $campaign['send_date'] != date('Y-m-d'))
                continue;
            $this->sendEmail($campaign['id']);
        }
    }

    public function runCronTrigger() {
        print 'Run Trigger' . PHP_EOL;
        $currentDate = date('Y-m-d H:i:s');
        $query = $this->db->query("SELECT * FROM tblnewsletter_campaigns_schedule WHERE status ='0' AND waiting <= '$currentDate'");
        $triggers = $query->result_array();
        print 'Total triggers: ' . count($triggers) . PHP_EOL;
        if (count($triggers) <= 0) {
            return;
        }
        // Update status
        foreach ($triggers as $trigger) {
            $this->updateTriggerStatus($trigger['id'], '1');
        }
        foreach ($triggers as $trigger) {
            $campaign = $this->getCampaign($trigger['campaign_id']);
            $conditional = $trigger['conditional'];
            $nextCampaign = $trigger['next_campaign'];
            if ($campaign[$conditional]) {
                if ($this->sendEmail($nextCampaign)) {
                    $this->updateTriggerStatus($trigger['id'], '2');
                } else {
                    $this->updateTriggerStatus($trigger['id'], '3');
                }
            } else {
                $this->updateTriggerStatus($trigger['id'], '0');
            }
        }
    }

    public function updateTriggerStatus($id, $status) {
        $query = $this->db->query("UPDATE tblnewsletter_campaigns_schedule SET status = $status WHERE id = $id");
    }

    public function getStatistics($id) {
        $query = $this->db->query("SELECT id FROM tblnewsletter_campaigns_list WHERE campaign_id='$id'");
        $total = count($query->result_array());

        $query = $this->db->query("SELECT id FROM tblnewsletter_campaigns_list WHERE campaign_id='$id' AND sent='1'");
        $sent = count($query->result_array());
        $result = $sent . ' ' . nom('newsletter_of') . ' ' . $total . ' ' . nom('newsletter_sent');
        $percent = ($total == 0) ? 100 : ceil(($sent * 100) / $total);
        $result .= '<div class="progress">
  <div class="progress-bar no-percent-text" role="progressbar" style="width: ' . $percent . '%;" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100">' . $percent . '%</div>
</div>';
        return $result;
    }

    public function processFileUpload() {
        $file = "";
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            //do_action('before_upload_contact_profile_image');

            $path = 'uploads/newsletter/template/';
            // Get the temp file path
            $tmpFilePath = $_FILES['file']['tmp_name'];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                // Getting file extension
                $path_parts = pathinfo($_FILES["file"]["name"]);
                $extension = $path_parts['extension'];
                $extension = strtolower($extension);
                $allowed_extensions = array(
                    'jpg',
                    'jpeg',
                    'png',
                    'gif'
                );

                // Setup our new file path
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                    fopen($path . '/index.html', 'w');
                }
                $filename = unique_filename($path, $_FILES["file"]["name"]);
                $newFilePath = $path . $filename;
                // Upload the file into the company uploads dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {

                    $file = "uploads/newsletter/template/" . $filename;
                }
            }
        }
        return $file;
    }

    /**
     * function get children a campaigns
     */
    function hasChildren($id) {
//        $this->db->query('DROP TEMPORARY TABLE IF EXISTS camp_child');
//        $temp_table = "CREATE TEMPORARY TABLE camp_child
//            SELECT camp.subject, schedule.next_campaign, schedule.conditional, schedule.campaign_id
//            FROM tblnewsletter_campaigns_schedule AS schedule
//            JOIN tblnewsletter_campaigns AS camp ON camp.id=schedule.next_campaign";
//        $this->db->query($temp_table);
//        
        $this->db->select('camp.id, schedule.next_campaign, schedule.conditional');
        $this->db->from('tblnewsletter_campaigns AS camp');
        $this->db->join('tblnewsletter_campaigns_schedule AS schedule', 'camp.id = schedule.campaign_id');
        $this->db->where('camp.id', $id);
        //var_dump( $this->db->get()->result_array());
        return $this->db->get()->result_array();
    }

    function getCampaignSubject($id) {
        $this->db->select('subject');
        $this->db->from('tblnewsletter_campaigns');
        $this->db->where('id', $id);
        return $this->db->get()->row();
    }

    /**
     * addNewTrigger
     */
    function addNewTrigger($data) {

        $this->db->insert('tbltriggers', $data);
    }

    /**
     * get triggers
     */
    function getTriggers($id = NULL) {
        $this->db->select('id, name, trigger_type');
        $this->db->from('tbltriggers');
        if ($id) {
            $this->db->where('id', $id);
        }
        $triggers = $this->db->get()->result_array();
        $triggersGroup = [];
        foreach ($triggers as $trigger) {
            if (!isset($triggersGroup[$trigger['trigger_type']])) {
                $triggersGroup[$trigger['trigger_type']] = [];
            }
            array_push($triggersGroup[$trigger['trigger_type']], $trigger);
        }
        return $triggersGroup;
    }

    /**
     * get triggers
     * @param $id
     */
    function getTrigger($id) {
        $this->db->select('id, name, trigger_type');
        $this->db->from('tbltriggers');
        $this->db->where('id', $id);
        $trigger = $this->db->get()->row();

        return $trigger;
    }

    /**
     * Get Campaign Logs
     */
    function campaignLogs($id, $type) {
        $this->db->select('tblcampaign_activity_log.*,tblnewsletter_campaigns.subject');
        $this->db->from('tblnewsletter_campaigns');
        $this->db->join('tblcampaign_activity_log', 'tblcampaign_activity_log.campaign_id = tblnewsletter_campaigns.id');
        $this->db->join('tblcampaigns_kinds', 'tblcampaigns_kinds.campaign_id = tblnewsletter_campaigns.id');
        $this->db->where('tblcampaigns_kinds.kind_id', $id);
        $this->db->where('tblcampaigns_kinds.kind', $type);
        //var_dump($this->db->get()->result_array()); die;

        return $this->db->get()->result_array();
    }

    function saveCampaignLogs($data) {
        $this->db->insert('tblcampaign_activity_log', $data);
    }

}

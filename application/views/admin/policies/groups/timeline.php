<?php if(isset($policy)){ ?>
    <?php echo form_hidden('policyid',$policy->id); ?>
<h4 class="customer-profile-group-heading"><?php echo _l('policy_timeline_tab'); ?></h4>
 
 <div class="clearfix"></div>
    <div class="panel_s">
        <div class="activity-feed">
            <?php foreach($activity_log as $log){ ?>
                <div class="feed-item">
                    <div class="date"><?php echo time_ago($log['date']); ?></div>
                    <div class="text">
                        <?php if($log['staffid'] != 0){ ?>
                            <a href="<?php echo admin_url('profile/'.$log["staffid"]); ?>">
                                <?php echo staff_profile_image($log['staffid'],array('staff-profile-xs-image pull-left mright5'));
                                ?>
                            </a>
                            <?php
                        }
                        $additional_data = '';
                        if(!empty($log['additional_data'])){
                            $additional_data = unserialize($log['additional_data']);
                            echo ($log['staffid'] == 0) ? _l($log['description'],$additional_data) : $log['full_name'] .' - '._l($log['description'],$additional_data);
                        } else {
                            echo $log['full_name'] . ' - ';
                            if($log['custom_activity'] == 0){
                                echo _l($log['description']);
                            } else {
                                echo _l($log['description'],'',false);
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="col-md-12">
            <?php echo render_textarea('policy_activity_textarea','','',array('placeholder'=>_l('enter_activity')),array(),'mtop15'); ?>
            <div class="text-right">
                <button id="policy_enter_activity" class="btn btn-info" onclick="submit_policy_log();"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
<?php } ?>
<script>
    function submit_policy_log(){
        var message = $('#policy_activity_textarea').val();
        var policyId = $('body').find('input[name="policyid"]').val();
        if (message == '') { return; }
        $.post(admin_url + 'policies/add_activity', {
            policyid: policyId,
            activity: message
        }).done(function(response) {
            response = JSON.parse(response);
            alert_float("success",response.message);
            setTimeout(function() {
                window.location.href=window.location.href;
            }, 2000);

        }).fail(function(data) {
            alert_float('danger', data.responseText);
        });
    }

</script>

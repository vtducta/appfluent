<?php
/**
 * Created by PhpStorm.
 * User: giapta
 * Date: 6/25/18
 * Time: 4:35 PM
 */
//$activity_log = campaign_logs;

?>

<h4 class="customer-profile-group-heading">Campaign logs</h4>
<div class="clearfix"></div>
<div class="panel_s">
    <div class="activity-feed">
        <?php foreach($campaign_logs as $log){ ?>
            <div class="feed-item">
                <div class="date"><?php echo time_ago($log['date']); ?></div>
                <div class="text">
                    <?php echo $log['subject'] .' - '._l($log['description']); ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="clearfix"></div>
</div>
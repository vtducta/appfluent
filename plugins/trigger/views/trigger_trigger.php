<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$CI = get_instance();


$conditionsOptions = [];
$conditionSelected = $trigger->trigger_condition ? $trigger->trigger_condition : $condition_id;
foreach ($conditions as $condition) {
    $conditionsOptions[$condition['id']] = $condition['condition_name'];
}
$campaignsOptions = [];
$campaignSelected = $trigger->campaign_id;
foreach ($campaigns as $campaign) {
    $campaignsOptions[$campaign['id']] = $campaign['subject'];
}

?>
<div class="panel_s">
    <div class="panel-body">
        <form id="trigger-form" action="<?php echo base_url('trigger/save') ?>" method="post" style="width: 50%; margin: 0 auto">
            <div class="row" >
                <div class="form-group col-sm-12" >
                    <label>Name</label>
                    <input required type="text" value="<?php echo $trigger->trigger_name; ?>" name="trigger_name" class="form-control">
                </div>
            </div>
            <div class="row" >
                <div class="form-group col-sm-12" >
                    <label>When this happen</label>
                    <!--<input required type="text" value="" name="trigger_condition" class="form-control">-->
                    <?php echo form_dropdown('trigger_condition', $conditionsOptions, $conditionSelected, ['class'=> "form-control"]); ?>
                </div>
            </div>
            <div class="row" >
                <div class="form-group col-sm-12" >
                    <label>Run this Campaign on the Contact</label>
                    <!--<input required type="text" value="" name="trigger_name" class="form-control">-->
                    <?php echo form_dropdown('campaign_id', $campaignsOptions, $campaignSelected, ['class'=> "form-control"]); ?>
                </div>
            </div>
            <div class="row" >
                <div class="form-group col-sm-12" >
                    <input required type="submit" value="Save" name="btn-save" class="btn btn-success">
                    <input required type="button" value="Cancel" name="btn-cancel" class="btn btn-default" onclick="location.href='<?php echo base_url('trigger?type=triggers') ?>';">
                </div>
            </div>
            <input name="id" type="hidden" value="<?php echo $trigger->id;?>">
        </form>
    </div>
</div>

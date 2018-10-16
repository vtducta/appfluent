<h4 class="customer-profile-group-heading"><?php echo _l('tasks'); ?></h4>
<?php if(isset($policy)){
    init_relation_tasks_table(array( 'data-new-rel-id'=>$policy->id,'data-new-rel-type'=>'policies'));
} ?>

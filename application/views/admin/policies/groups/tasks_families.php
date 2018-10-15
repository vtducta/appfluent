<h4 class="customer-profile-group-heading"><?php echo _l('tasks'); ?></h4>
<?php if(isset($contact)){
    init_relation_tasks_table(array( 'data-new-rel-id'=>$contact->id,'data-new-rel-type'=>'contacts'));
} ?>

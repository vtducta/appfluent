<h4 class="customer-profile-group-heading"><?php echo _l('contracts_tickets_tab'); ?></h4>
<div class="clearfix"></div>
<?php
if(isset($policy)){
    if($policy->id && ((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member())){
        // $contacts in this case will be only 1 active
        echo '<a href="'.admin_url('tickets/add_for_policy?policy_id='.$policy->id).'" class="mbot20 btn btn-info">'._l('new_ticket').'</a>';
    }
 echo AdminTicketsTableStructure('table-tickets-single');
} ?>

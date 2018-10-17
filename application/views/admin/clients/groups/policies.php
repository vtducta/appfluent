<?php if(isset($contact)){ ?>
    <h4 class="customer-profile-group-heading"><?php echo _l('policies_related_tab'); ?></h4>
    <div class="col-md-12">
    <div class="clearfix"></div>
    <div class="row">
        <hr class="hr-panel-heading" />
    </div>
    <div class="clearfix"></div>
    <div class="mtop15">

        <table class="table dt-table scroll-responsive" data-order-col="2" data-order-type="desc">
            <thead>
            <tr>
                <th >
                    <?php echo _l( 'policy_name'); ?>
                </th>
                <th>
                    <?php echo _l( 'policy_title'); ?>
                </th>
                <th>
                    <?php echo _l( 'policy_email'); ?>
                </th>
                <th>
                    <?php echo _l( 'policy_phone'); ?>
                </th>

            </tr>
            </thead>
            <tbody>
            <?php foreach($list_policies as $policy){ ?>
                <tr>
                    <td>
                        <div data-note-description="<?php echo $policy['id']; ?>">
                            <?php echo '<a href="'.admin_url( 'policy/'.$policy['id']). '">' . $policy['firstname'] . ' ' .$policy['lastname'] . '</a>'; ?>
                        </div>
                    </td>
                    <td>
                            <?php echo  $policy['title'] ; ?>
                    </td>
                    <td>
                            <?php echo  $policy['email'] ; ?>
                    </td>
                    <td>
                            <?php echo  $policy['phonenumber'] ; ?>
                    </td>

                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>


<?php } ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row mbot20">
                            <div class="col-md-3" style="float:left">
                                <h3 class="text-success no-margin"><?php echo _l('emails_tab'); ?></h3>
                            </div>

                            <div class="col-md-9 dt-buttons btn-group">
                                <a href="#" class="btn btn-default buttons-collection btn-default-dt-options" style="float: right; margin-left: 10px" data-toggle="modal" data-target="#newEmailModal" >
                                   <span> <?php echo _l('add_email'); ?></span>
                                </a>

                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div class="row mtop15">
                            <div class="col-md-12">
                                <ul class="nav nav-tabs profile-tabs row customer-profile-tabs" role="tablist">


                                    <li role="presentation"  class="<?php if(!$this->input->get('tab')){echo 'active';}; ?>">
                                        <a href="#inbox-email" aria-controls="inbox-email" role="tab" data-toggle="tab">
                                            <?php echo _l( 'inbox-email'); ?>
                                        </a>
                                    </li>
                                    <?php do_action('after_send_email_tab',false); ?>

                                    <li role="presentation">
                                        <a href="#send-email" aria-controls="send-email" role="tab" data-toggle="tab">
                                            <?php echo _l( 'send-email'); ?>
                                        </a>
                                    </li>

                                </ul>


                                <div class="clearfix"></div>
                                <div id="email_data">
                                    <?php $this->load->view('admin/emails/email'); ?>
                                </div>

                                <div class="clearfix"></div>
                                <div class="tab-content">

                                    <?php do_action('after_inbox_email_tab_content',false); ?>
                                    <div role="tabpanel" class="tab-pane active" id="inbox-email">
                                        <div class="mtop15">

                                            <table class="table dt-table scroll-responsive" data-order-col="2" data-order-type="desc">
                                                <thead>
                                                <tr>
                                                    <th width="50%">
                                                        <?php echo _l( 'clients_email_table_subject_heading'); ?>
                                                    </th>
                                                    <th>
                                                        <?php echo _l( 'clients_email_table_from_heading'); ?>
                                                    </th>
                                                    <th>
                                                        <?php echo _l( 'clients_email_table_received_heading'); ?>
                                                    </th>
                                                    <th>
                                                        <?php echo _l( 'options'); ?>
                                                    </th>

                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach($inbox as $item){ ?>
                                                    <tr>
                                                        <td width="50%">
                                                            <div data-note-description="<?php echo $item['id']; ?>">
                                                                <?php echo $item['subject']; ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php echo $item['from']['email'] ?>
                                                        </td>
                                                        <td data-order="<?php echo $item['date']; ?>">
                                                            <?php if(!empty($item['date'])){ ?>
                                                                <span data-toggle="tooltip" data-title="<?php echo date("Y-m-d H:i:s", $item['date']); ?>">
                                                                    <i class="fa fa-calendar-check-o text-success font-medium valign" aria-hidden="true"></i>
                                                                </span>
                                                            <?php } ?>
                                                            <?php echo date("Y-m-d H:i:s", $item['date']); ?>
                                                        </td>
                                                        <td>
                                                            <a href="<?php echo admin_url('emails/delete_inbox/'. $item['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php do_action('after_send_email_tab_content',true); ?>
                                    <div role="tabpanel" class="tab-pane " id="send-email">
                                        <div class="mtop15">

                                            <table class="table dt-table scroll-responsive" data-order-col="2" data-order-type="desc">
                                                <thead>
                                                <tr>
                                                    <th width="50%">
                                                        <?php echo _l( 'clients_email_table_subject_heading'); ?>
                                                    </th>
                                                    <th>
                                                        <?php echo _l( 'clients_email_table_to_heading'); ?>
                                                    </th>
                                                    <th>
                                                        <?php echo _l( 'clients_email_table_start_heading'); ?>
                                                    </th>
                                                    <th>
                                                        <?php echo _l( 'options'); ?>
                                                    </th>

                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach($emails as $email){ ?>
                                                    <tr>
                                                        <td width="50%">
                                                            <div data-note-description="<?php echo $email['id']; ?>">
                                                                <?php echo $email['subject']; ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php echo $email['to'] ?>
                                                        </td>
                                                        <td data-order="<?php echo $email['created_date']; ?>">
                                                            <?php if(!empty($email['created_date'])){ ?>
                                                                <span data-toggle="tooltip" data-title="<?php echo _dt($email['created_date']); ?>">
                                <i class="fa fa-calendar-check-o text-success font-medium valign" aria-hidden="true"></i>
                            </span>
                                                            <?php } ?>
                                                            <?php echo _dt($email[ 'created_date']); ?>
                                                        </td>
                                                        <td>

                                                            <?php if($email['added_from'] == get_staff_user_id() || is_admin()){ ?>
                                                                <a href="<?php echo admin_url('emails/delete_email/'. $email['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


        </div>
    </div>
</div>





<?php init_tail(); ?>

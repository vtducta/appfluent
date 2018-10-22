<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
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
                        <hr class="hr-panel-heading" />
                        <div class="row">
                            <div class="col-md-12">

                                <div class="clearfix"></div>
                                <div id="email_data">
                                    <?php $this->load->view('admin/emails/email'); ?>
                                </div>
                                <div class="clearfix"></div>
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





<?php init_tail(); ?>

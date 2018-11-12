<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body customer-profile-tabs">
                        <h4 class="customer-heading-profile bold">
                            Email
                        </h4>
                        <?php $this->load->view('admin/emails/tabs_email'); ?>

                    </div>
                </div>
            </div>
            <div class="col-md-9">
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
                                                            <div data-note-description="<?php echo $item['auto_id']; ?>">
                                                                <?php echo $item['subject']; ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php $from = json_decode($item['from']);
                                                                    echo $from->mailbox .'@'. $from->host ;?>
                                                        </td>
                                                        <td data-order="<?php echo $item['udate']; ?>">
                                                            <?php if(!empty($item['udate'])){ ?>
                                                                <span data-toggle="tooltip" data-title="<?php echo date("Y-m-d H:i:s", $item['udate']); ?>">
                                                                    <i class="fa fa-calendar-check-o text-success font-medium valign" aria-hidden="true"></i>
                                                                </span>
                                                            <?php } ?>
                                                            <?php echo date("Y-m-d H:i:s", $item['udate']); ?>
                                                        </td>
                                                        <td>
                                                            <a href="<?php echo admin_url('emails/trash_email/'. $item['auto_id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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

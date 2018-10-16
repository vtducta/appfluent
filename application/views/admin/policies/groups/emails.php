<?php if(isset($policy)){ ?>
<h4 class="customer-profile-group-heading"><?php echo _l('emails_tab'); ?></h4>
<div class="col-md-12">

    <a href="#" class="btn btn-success mtop15 mbot10" data-toggle="modal" data-target="#newEmailModal" ><?php echo _l('add_email'); ?></a>
    <div class="clearfix"></div>
    <div class="row">
        <hr class="hr-panel-heading" />
    </div>
    <div class="clearfix"></div>
    <div id="email_data">
        <?php $this->load->view('admin/policies/modals/email'); ?>
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
                            <a href="<?php echo admin_url('policies/delete_email/'. $email['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <script>
        function load_event(client_id, contact_id,email_id) {

            if (typeof(email_id) == 'undefined') {
                email_id = '';
            }
            $.post(admin_url + 'policies/email/' + client_id + '/' + contact_id+'/'+email_id).done(function(response) {
                $('#email_data').html(response);
                $('#newEmailModal').modal({
                    show: true,
                    backdrop: 'static'
                });
                $('body').off('shown.bs.modal','#newEmailModal');
                $('body').on('shown.bs.modal', '#newEmailModal', function() {
                    if (email_id == '') {
                        $('#newEmailModal').find('input[name="subject"]').focus();
                    }
                });
                add_event_search_custom_field();
            }).fail(function(error) {
                var response = JSON.parse(error.responseText);
                alert_float('danger', response.message);
            });
        }


    </script>

    <?php } ?>

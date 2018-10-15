<?php if(isset($contact)){ ?>
<h4 class="customer-profile-group-heading"><?php echo _l('contracts_events_tab'); ?></h4>
<div class="col-md-12">

    <a href="#" class="btn btn-success mtop15 mbot10" data-toggle="modal" data-target="#newEventModal" onclick="clearFormEvent(); return false;"><?php echo _l('add_event'); ?></a>
    <div class="clearfix"></div>
    <div class="row">
        <hr class="hr-panel-heading" />
    </div>
    <div class="clearfix"></div>
    <div id="event_data">
        <?php $this->load->view('admin/clients/modals/event'); ?>
    </div>
    <div class="clearfix"></div>
    <div class="mtop15">

        <table class="table dt-table scroll-responsive" data-order-col="2" data-order-type="desc">
            <thead>
            <tr>
                <th width="50%">
                    <?php echo _l( 'clients_event_table_title_heading'); ?>
                </th>
                <th>
                    <?php echo _l( 'clients_event_table_addedfrom_heading'); ?>
                </th>
                <th>
                    <?php echo _l( 'clients_event_table_start_heading'); ?>
                </th>
                <th>
                    <?php echo _l( 'options'); ?>
                </th>

            </tr>
            </thead>
            <tbody>
            <?php foreach($events as $event){ ?>
                <tr>
                    <td width="50%">
                        <div data-note-description="<?php echo $event['eventid']; ?>">
                            <?php echo $event['title']; ?>
                        </div>
                    </td>
                    <td>
                        <?php echo '<a href="'.admin_url( 'profile/'.$event[ 'userid']). '">'.$event[ 'firstname'] . ' ' . $event[ 'lastname'] . '</a>' ?>
                    </td>
                    <td data-order="<?php echo $event['start']; ?>">
                        <?php if(!empty($event['start'])){ ?>
                            <span data-toggle="tooltip" data-title="<?php echo _dt($event['start']); ?>">
              <i class="fa fa-calendar-check-o text-success font-medium valign" aria-hidden="true"></i>
          </span>
                        <?php } ?>
                        <?php echo _dt($event[ 'start']); ?>
                    </td>
                    <td>

                        <?php if($event['userid'] == get_staff_user_id() || is_admin()){ ?>
                            <a href="#" class="btn btn-default btn-icon" onclick="load_event(<?php echo $client->userid.','.$contact->id.','.$event['eventid']; ?>);return false;"><i class="fa fa-pencil-square-o"></i></a>
                            <a href="<?php echo admin_url('client_families/delete_event/'. $event['eventid']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <script>
        function load_event(client_id, contact_id,event_id) {

            if (typeof(event_id) == 'undefined') {
                event_id = '';
            }
            $.post(admin_url + 'client_families/event/' + client_id + '/' + contact_id+'/'+event_id).done(function(response) {
                $('#event_data').html(response);
                $('#newEventModal').modal({
                    show: true,
                    backdrop: 'static'
                });
                $('body').off('shown.bs.modal','#newEventModal');
                $('body').on('shown.bs.modal', '#newEventModal', function() {
                    if (event_id == '') {
                        $('#newEventModal').find('input[name="title"]').focus();
                    }
                });
                add_event_search_custom_field();
            }).fail(function(error) {
                var response = JSON.parse(error.responseText);
                alert_float('danger', response.message);
            });
        }

        function clearFormEvent() {

            $('#newEventModal').find('input[name="eventid"]').val('');
            $('#newEventModal').find('input[name="title"]').val('');
            $('#newEventModal').find('textarea[name="description"]').val('');
            $('#newEventModal').find('input[name="start"]').val('');
            $('#newEventModal').find('input[name="end"]').val('');
            $('#newEventModal').find('input[name="reminder_before"]').val('0');
            $('#newEventModal').find('select[name="reminder_before_type"]').val('minutes');
            $('#newEventModal').find('input[name="color"]').val('#28B8DA');
        }

    </script>

    <?php } ?>

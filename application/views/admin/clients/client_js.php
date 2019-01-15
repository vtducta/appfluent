<?php
/**
 * Included in application/views/admin/clients/client.php
 */
?>
<script>
Dropzone.options.clientAttachmentsUpload = false;
var customer_id = $('input[name="userid"]').val();
$(function() {

    if ($('#client-attachments-upload').length > 0) {
        new Dropzone('#client-attachments-upload',$.extend({},_dropzone_defaults(),{
            paramName: "file",
            accept: function(file, done) {
                done();
            },
            success: function(file, response) {
                if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                    window.location.reload();
                }
            }
        }));
    }

    // Save button not hidden if passed from url ?tab= we need to re-click again
    if (tab_active) {
        $('body').find('.nav-tabs [href="#' + tab_active + '"]').click();
    }

    $('a[href="#customer_admins"]').on('click', function() {
        $('.btn-bottom-toolbar').addClass('hide');
    });

    $('.profile-tabs a').not('a[href="#customer_admins"]').on('click', function() {
        $('.btn-bottom-toolbar').removeClass('hide');
    });

    $("input[name='tasks_related_to[]']").on('change', function() {
        var tasks_related_values = []
        $('#tasks_related_filter :checkbox:checked').each(function(i) {
            tasks_related_values[i] = $(this).val();
        });
        $('input[name="tasks_related_to"]').val(tasks_related_values.join());
        $('.table-rel-tasks').DataTable().ajax.reload();
    });

    var contact_id = get_url_param('contactid');
    if (contact_id) {
        contact(customer_id, contact_id);
    }

    // consents=CONTACT_ID
    var consents = get_url_param('consents');
    if(consents){
        view_contact_consent(consents);
    }

    // If user clicked save and add new contact
    if (get_url_param('new_contact')) {
        contact(customer_id);
    }

    $('body').on('change', '.onoffswitch input.customer_file', function(event, state) {
        var invoker = $(this);
        var checked_visibility = invoker.prop('checked');
        var share_file_modal = $('#customer_file_share_file_with');
        setTimeout(function() {
            $('input[name="file_id"]').val(invoker.attr('data-id'));
            if (checked_visibility && share_file_modal.attr('data-total-contacts') > 1) {
                share_file_modal.modal('show');
            } else {
                do_share_file_contacts();
            }
        }, 200);
    });

    $('.customer-form-submiter').on('click', function() {
        var form = $('.client-form');
        if (form.valid()) {
            if ($(this).hasClass('save-and-add-contact')) {
                form.find('.additional').html(hidden_input('save_and_add_contact', 'true'));
            } else {
                form.find('.additional').html('');
            }
            form.submit();
        }
    });

    if (typeof(Dropbox) != 'undefined' && $('#dropbox-chooser').length > 0) {
        document.getElementById("dropbox-chooser").appendChild(Dropbox.createChooseButton({
            success: function(files) {
                $.post(admin_url + 'clients/add_external_attachment', {
                    files: files,
                    clientid: customer_id,
                    external: 'dropbox'
                }).done(function() {
                    window.location.reload();
                });
            },
            linkType: "preview",
            extensions: app_allowed_files.split(','),
        }));
    }

    /* Customer profile tickets table */
    $('.table-tickets-single').find('#th-submitter').removeClass('toggleable');

    initDataTable('.table-tickets-single', admin_url + 'tickets/index/false/' + customer_id, undefined, undefined, 'undefined', [$('table thead .ticket_created_column').index(), 'desc']);

    /* Customer profile contracts table */
    initDataTable('.table-contracts-single-client', admin_url + 'contracts/table/' + customer_id, undefined,undefined, 'undefined', [6, 'desc']);

    /* Custome profile contacts table */
    var contactsNotSortable = [];
    <?php if(is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1'){ ?>
        contactsNotSortable.push($('#th-consent').index());
    <?php } ?>
    _table_api = initDataTable('.table-contacts', admin_url + 'clients/contacts/' + customer_id, contactsNotSortable, contactsNotSortable);
    if(_table_api) {
          <?php if(is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1'){ ?>
        _table_api.on('draw', function () {
            var tableData = $('.table-contacts').find('tbody tr');
            $.each(tableData, function() {
                $(this).find('td:eq(1)').addClass('bg-light-gray');
            });
        });
        <?php } ?>
    }
    /* Customer profile invoices table */
    initDataTable('.table-invoices-single-client',
        admin_url + 'invoices/table/' + customer_id,
        'undefined',
        'undefined',
        'undefined', [
            [3, 'desc'],
            [0, 'desc']
        ]);

   initDataTable('.table-credit-notes', admin_url+'credit_notes/table/'+customer_id, ['undefined'], ['undefined'], undefined, [0, 'desc']);

    /* Customer profile Estimates table */
    initDataTable('.table-estimates-single-client',
        admin_url + 'estimates/table/' + customer_id,
        'undefined',
        'undefined',
        'undefined', [
            [3, 'desc'],
            [0, 'desc']
        ]);

    /* Customer profile payments table */
    initDataTable('.table-payments-single-client',
        admin_url + 'payments/table/' + customer_id, undefined, undefined,
        'undefined', [0, 'desc']);

    /* Customer profile reminders table */
    initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + customer_id + '/' + 'customer', undefined, undefined, undefined, [1, 'asc']);

    /* Customer profile expenses table */
    initDataTable('.table-expenses-single-client',
        admin_url + 'expenses/table/' + customer_id,
        'undefined',
        'undefined',
        'undefined', [5, 'desc']);

    /* Customer profile proposals table */
    initDataTable('.table-proposals-client-profile',
        admin_url + 'proposals/proposal_relations/' + customer_id + '/customer',
        'undefined',
        'undefined',
        'undefined', [6, 'desc']);

    /* Custome profile projects table */
    initDataTable('.table-projects-single-client', admin_url + 'projects/table/' + customer_id, undefined, undefined, 'undefined', <?php echo do_action('projects_table_default_order',json_encode(array(5,'asc'))); ?>);

    var vRules = {};
    if (app_company_is_required == 1) {
        vRules = {
            //company: 'required',
            'contact[firstname]': 'required',
            'contact[lastname]': 'required',
        }
    }
    _validate_form($('.client-form'), vRules);

    $('.billing-same-as-customer').on('click', function(e) {
        e.preventDefault();
        $('textarea[name="billing_street"]').val($('textarea[name="address"]').val());
        $('input[name="billing_city"]').val($('input[name="city"]').val());
        $('input[name="billing_state"]').val($('input[name="state"]').val());
        $('input[name="billing_zip"]').val($('input[name="zip"]').val());
        $('select[name="billing_country"]').selectpicker('val', $('select[name="country"]').selectpicker('val'));
    });

    $('.customer-copy-billing-address').on('click', function(e) {
        e.preventDefault();
        $('textarea[name="shipping_street"]').val($('textarea[name="billing_street"]').val());
        $('input[name="shipping_city"]').val($('input[name="billing_city"]').val());
        $('input[name="shipping_state"]').val($('input[name="billing_state"]').val());
        $('input[name="shipping_zip"]').val($('input[name="billing_zip"]').val());
        $('select[name="shipping_country"]').selectpicker('val', $('select[name="billing_country"]').selectpicker('val'));
    });

    $('body').on('hidden.bs.modal', '#contact', function() {
        $('#contact_data').empty();
    });

    $('.client-form').on('submit', function() {
        $('select[name="default_currency"]').prop('disabled', false);
    });

});

function delete_contact_profile_image(contact_id) {
    requestGet('clients/delete_contact_profile_image/'+contact_id).done(function(){
        $('body').find('#contact-profile-image').removeClass('hide');
        $('body').find('#contact-remove-img').addClass('hide');
        $('body').find('#contact-img').attr('src', '<?php echo base_url('assets/images/user-placeholder.jpg'); ?>');
    });
}

function validate_contact_form() {
    _validate_form('#contact-form', {
        firstname: 'required',
        //middle_name: 'required',
        lastname: 'required',
        // password: {
        //     required: {
        //         depends: function(element) {
        //             var sent_set_password = $('input[name="send_set_password_email"]');
        //             if ($('#contact input[name="contactid"]').val() == '' && sent_set_password.prop('checked') == false) {
        //                 return true;
        //             }
        //         }
        //     }
        // },
        //email: {
        //    <?php //if(do_action('contact_email_required',"true") === "true"){ ?>
        //    required: true,
        //    <?php //} ?>
        //    email: true,
        //    // Use this hook only if the contacts are not logging into the customers area and you are not using support tickets piping.
        //    <?php //if(do_action('contact_email_unique',"true") === "true"){ ?>
        //    remote: {
        //        url: admin_url + "misc/contact_email_exists",
        //        type: 'post',
        //        data: {
        //            email: function() {
        //                return $('#contact input[name="email"]').val();
        //            },
        //            userid: function() {
        //                return $('body').find('input[name="contactid"]').val();
        //            }
        //        }
        //    }
        //    <?php //} ?>
        //}
    }, contactFormHandler);
}

function contactFormHandler(form) {
    $('#contact input[name="is_primary"]').prop('disabled', false);

    $("#contact input[type=file]").each(function() {
        if($(this).val() === "") {
            $(this).prop('disabled', true);
        }
    });

    var formURL = $(form).attr("action");
    var formData = new FormData($(form)[0]);

    $.ajax({
        type: 'POST',
        data: formData,
        mimeType: "multipart/form-data",
        contentType: false,
        cache: false,
        processData: false,
        url: formURL
    }).done(function(response){
             response = JSON.parse(response);
            if (response.success) {
                alert_float('success', response.message);
                if(typeof(response.is_individual) != 'undefined' && response.is_individual) {
                    $('.new-contact').addClass('disabled');
                    if(!$('.new-contact-wrapper')[0].hasAttribute('data-toggle')) {
                        $('.new-contact-wrapper').attr('data-toggle','tooltip');
                    }
                }
            }
            if ($.fn.DataTable.isDataTable('.table-contacts')) {
                $('.table-contacts').DataTable().ajax.reload(null,false);
            }
            if (response.proposal_warning && response.proposal_warning != false) {
                $('body').find('#contact_proposal_warning').removeClass('hide');
                $('body').find('#contact_update_proposals_emails').attr('data-original-email', response.original_email);
                $('#contact').animate({
                    scrollTop: 0
                }, 800);
            } else {
                $('#contact').modal('hide');
            }
    }).fail(function(error){
        alert_float('danger', JSON.parse(error.responseText));
    });
    return false;
}

function contact(client_id, contact_id) {
    if (typeof(contact_id) == 'undefined') {
        contact_id = '';
    }
    requestGet('clients/contact/' + client_id + '/' + contact_id).done(function(response) {
        $('#contact_data').html(response);
        $('#contact').modal({
            show: true,
            backdrop: 'static'
        });
        $('body').off('shown.bs.modal','#contact');
        $('body').on('shown.bs.modal', '#contact', function() {
            if (contact_id == '') {
                $('#contact').find('input[name="firstname"]').focus();
            }
        });
        init_selectpicker();
        init_datepicker();
        custom_fields_hyperlink();
        validate_contact_form();
    }).fail(function(error) {
        var response = JSON.parse(error.responseText);
        alert_float('danger', response.message);
    });
}

function update_all_proposal_emails_linked_to_contact(contact_id) {
    var data = {};
    data.update = true;
    data.original_email = $('body').find('#contact_update_proposals_emails').data('original-email');
    $.post(admin_url + 'clients/update_all_proposal_emails_linked_to_customer/' + contact_id, data).done(function(response) {
        response = JSON.parse(response);
        if (response.success) {
            alert_float('success', response.message);
        }
        $('#contact').modal('hide');
    });
}

function do_share_file_contacts(edit_contacts, file_id) {
    var contacts_shared_ids = $('select[name="share_contacts_id[]"]');
    if (typeof(edit_contacts) == 'undefined' && typeof(file_id) == 'undefined') {
        var contacts_shared_ids_selected = $('select[name="share_contacts_id[]"]').val();
    } else {
        var _temp = edit_contacts.toString().split(',');
        for (var cshare_id in _temp) {
            contacts_shared_ids.find('option[value="' + _temp[cshare_id] + '"]').attr('selected', true);
        }
        contacts_shared_ids.selectpicker('refresh');
        $('input[name="file_id"]').val(file_id);
        $('#customer_file_share_file_with').modal('show');
        return;
    }
    var file_id = $('input[name="file_id"]').val();
    $.post(admin_url + 'clients/update_file_share_visibility', {
        file_id: file_id,
        share_contacts_id: contacts_shared_ids_selected,
        customer_id: $('input[name="userid"]').val()
    }).done(function() {
        window.location.reload();
    });
}

function save_longitude_and_latitude(clientid) {
    var data = {};
    data.latitude = $('#latitude').val();
    data.longitude = $('#longitude').val();
    $.post(admin_url + 'clients/save_longitude_and_latitude/'+clientid, data).done(function(response) {
       if(response == 'success') {
            alert_float('success', "<?php echo _l('updated_successfully', _l('client')); ?>");
       }
        setTimeout(function(){
            window.location.reload();
        },1200);
    }).fail(function(error) {
        alert_float('danger', error.responseText);
    });
}

function fetch_lat_long_from_google_cprofile() {
    var data = {};
    data.address = $('#long_lat_wrapper').data('address');
    data.city = $('#long_lat_wrapper').data('city');
    data.country = $('#long_lat_wrapper').data('country');
    $('#gmaps-search-icon').removeClass('fa-google').addClass('fa-spinner fa-spin');
    $.post(admin_url + 'misc/fetch_address_info_gmaps', data).done(function(data) {
        data = JSON.parse(data);
        $('#gmaps-search-icon').removeClass('fa-spinner fa-spin').addClass('fa-google');
        if (data.response.status == 'OK') {
            $('input[name="latitude"]').val(data.lat);
            $('input[name="longitude"]').val(data.lng);
        } else {
            if (data.response.status == 'ZERO_RESULTS') {
                alert_float('warning', "<?php echo _l('g_search_address_not_found'); ?>");
            } else {
                alert_float('danger', data.response.status);
            }
        }
    });
}

function add_phone() {
    var html="";
    var numItems = $('._contact_phone').length+1;
    html = '<div class="row _contact_phone _ctp'+numItems+'">' +
        '                        <div class="col-md-6">' +
        '                            <div class="form-group" app-field-wrapper="contact_info[phone]['+numItems+'][value]">' +
        '                                <input type="text" id="contact_info[phone]['+numItems+'][value]" name="contact_info[phone]['+numItems+'][value]" class="form-control" value="">' +
        '                            </div>' +
        '                        </div>' +
        '                        <div class="col-md-6">' +
        '                            <div style="float:left ;width: 80%" class="form-group" app-field-wrapper="contact_info[phone]['+numItems+'][type]">' +
        '                                <select data-dropup-auto="false" name="contact_info[phone]['+numItems+'][type]" id="contact_info[phone]['+numItems+'][type]"   class="form-control selectpicker" >' +
        '                                    <option value=""></option>' +
        '                                    <option value="Work">Work</option>' +
        '                                    <option value="Home">Home</option>' +
        '                                    <option value="Mobile">Mobile</option>' +
        '                                    <option value="Main">Main</option>' +
        '                                    <option value="Home_fax">Home fax</option>' +
        '                                    <option value="Work_fax">Work fax</option>' +
        '                                    <option value="Other">Other</option>' +
        '                                </select>' +
        '                            </div>' +
        '                            <div style="float: left; font-size: 17px; margin-left: 5px">' +
        '                                <a style="cursor: pointer" href="javascript:remove_phone(\'_ctp'+numItems+'\')">' +
        '                                    <i class="mdi mdi-close-circle-outline"></i>' +
        '                                </a>' +
        '                            </div>' +
        '                        </div>' +
        '                    </div>';
    $('#phone_row_1').after(html);
    $('.selectpicker').selectpicker('refresh');
}

function remove_phone(ctpIndex) {
    $('.'+ctpIndex).remove();

}

function add_mail() {
    var html="";
    var numItems = $('._contact_mail').length+1;
    html = '<div class="row _contact_mail _ctm'+numItems+'">' +
        '                        <div class="col-md-6">' +
        '                            <div class="form-group" app-field-wrapper="contact_info[mail]['+numItems+'][value]">' +
        '                                <input type="text" id="contact_info[mail]['+numItems+'][value]" name="contact_info[mail]['+numItems+'][value]" class="form-control" value="">' +
        '                            </div>' +
        '                        </div>' +
        '                        <div class="col-md-6">' +
        '                            <div style="float:left ;width: 80%" class="form-group" app-field-wrapper="contact_info[mail]['+numItems+'][type]">' +
        '                                <select data-dropup-auto="false" name="contact_info[mail]['+numItems+'][type]" id="contact_info[mail]['+numItems+'][type]"   class="form-control selectpicker" >' +
        '                                    <option value=""></option>' +
        '                                    <option value="work">Work</option>' +
        '                                    <option value="personal">Personal</option>' +
        '                                </select>' +
        '                            </div>' +
        '                            <div style="float: left; font-size: 17px; margin-left: 5px">' +
        '                                <a style="cursor: pointer" href="javascript:remove_mail(\'_ctm'+numItems+'\')">' +
        '                                    <i class="mdi mdi-close-circle-outline"></i>' +
        '                                </a>' +
        '                            </div>' +
        '                        </div>' +
        '                    </div>';
    $('#mail_row_1').after(html);
    $('.selectpicker').selectpicker('refresh');
}

function remove_mail(ctmIndex) {
    $('.'+ctmIndex).remove();

}

function add_website() {
    var html="";
    var numItems = $('._contact_website').length+1;
    html = '<div class="row _contact_website _ctw'+numItems+'">' +
        '                        <div class="col-md-6">' +
        '                            <div class="form-group" app-field-wrapper="contact_info[website]['+numItems+'][value]">' +
        '                                <input type="text" id="contact_info[website]['+numItems+'][value]" name="contact_info[website]['+numItems+'][value]" class="form-control" value="">' +
        '                            </div>' +
        '                        </div>' +
        '                        <div class="col-md-6">' +
        '                            <div style="float:left ;width: 80%" class="form-group" app-field-wrapper="contact_info[website]['+numItems+'][type]">' +
        '                                <select data-dropup-auto="false" name="contact_info[website]['+numItems+'][type]" id="contact_info[website]['+numItems+'][type]"   class="form-control selectpicker" >' +
        '                                    <option value=""></option>' +
        '                                    <option value="Website">Website</option>' +
        '                                    <option value="Skype">Skype</option>' +
        '                                    <option value="Twitter">Twitter</option>' +
        '                                    <option value="LinkedIn">LinkedIn</option>' +
        '                                    <option value="Facebook">Facebook</option>' +
        '                                    <option value="Xing">Xing</option>' +
        '                                    <option value="Blog">Blog</option>' +
        '                                    <option value="Google+">Google+</option>' +
        '                                    <option value="Flickr">Flickr</option>' +
        '                                    <option value="GitHub">GitHub</option>' +
        '                                    <option value="YouTube">YouTube</option>' +
        '                                </select>' +
        '                            </div>' +
        '                            <div style="float: left; font-size: 17px; margin-left: 5px">' +
        '                                <a style="cursor: pointer" href="javascript:remove_website(\'_ctw'+numItems+'\')">' +
        '                                    <i class="mdi mdi-close-circle-outline"></i>' +
        '                                </a>' +
        '                            </div>' +
        '                        </div>' +
        '                    </div>';
    $('#website_row_1').after(html);
    $('.selectpicker').selectpicker('refresh');
}

function remove_website(ctwIndex) {
    $('.'+ctwIndex).remove();

}
</script>

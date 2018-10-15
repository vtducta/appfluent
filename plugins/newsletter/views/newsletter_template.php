<div class="row">
    <div class="col-md-8">
        <div class="panel_s" style="min-height: 700px !important;">
            <div class="panel-body">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label><?php _nom('newsletter_title')?></label>
                        <input required type="text" value="<?php echo ($template) ? $template['title'] : ''?>" name="title" class="form-control"/>
                    </div>

                    <div class="form-group">
                        <label><?php _nom('newsletter_preview_image')?></label>
                        <input type="file" name="file" class="form-control"/>
                    </div>

                    <div class="form-group">
                        <label><?php _nom('newsletter_template_html_code')?></label>
                        <textarea class="form-control tinymce" rows="35" name="content"><?php echo ($template) ? $template['content'] : ''?></textarea>
                    </div>

                    <button class="btn btn-info"><?php _nom('newsletter_save')?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin"><?php _nom('newsletter_available_merge')?></h4>
                <hr/>
                <div class="alert alert-danger">
                    <?php _nom('newsletter_available_merge_note')?>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h5>Customer & Contact</h5>
                        <p><span class="pill">{contact_firstname}</span></p>
                        <p>{contact_lastname}</p>
                        <p>{contact_email}</p>
                        <p>{client_company}</p>
                        <p>{client_phonenumber}</p>
                        <p>{client_country}</p>
                        <p>{client_city}</p>
                        <p>{client_zip}</p>
                        <p>{client_address}</p>
                        <p>{client_state}</p>
                        <p>{client_vat_number}</p>
                        <p>{client_id}</p>

                        <h5>Staff</h5>
                        <p>{staff_firstname}</p>
                        <p>{staff_lastname}</p>
                        <p>{staff_email}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Others</h5>
                        <p{logo_url}</p>
                        <p>{logo_image_with_url}</p>
                        <p>{crm_url}</p>
                        <p>{admin_url}</p>
                        <p>{main_domain}</p>
                        <p>{companyname}</p>

                        <h5>Leads</h5>
                        <p>{lead_name}</p>
                        <p>{lead_email}</p>
                        <p>{lead_position}</p>
                        <p>{lead_website}</p>
                        <p>{lead_description}</p>
                        <p>{lead_phonenumber}</p>
                        <p>{lead_company}</p>
                        <p>{lead_country}</p>
                        <p>{lead_zip}</p>
                        <p>{lead_state}</p>
                        <p>{lead_city}</p>
                        <p>{lead_address}</p>
                        <p>{lead_assigned}</p>
                        <p>{lead_status}</p>
                        <p>{lead_source}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
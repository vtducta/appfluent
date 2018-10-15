<?php init_head(); ?>
<?php $CI = get_instance();?>
<div id="wrapper" >
    <div id="main-newsletter" class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body _buttons">
                        <div class="row">
                            <div class="col-md-6 project-heading">
                                <h4 class=" project-name"><?php _nom('newsletter')?></h4>
                                <div id="project_view_name">

                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="<?php echo base_url('newsletter?type=campaign')?>"  class="btn btn-info"><?php _nom('newsletter_new_campaign')?></a>
                                <a href="<?php echo base_url('newsletter?type=template')?>"  class="btn btn-default"><?php _nom('newsletter_new_template')?></a>
                                <div class="btn-group mleft5">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <?php _nom('newsletter_mailing_list')?> <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right width200 project-actions">
                                        <li>
                                            <a href="<?php echo base_url('admin/surveys/mail_list')?>">
                                                <?php _nom('newsletter_create_mailing_list')?>                  </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo base_url('admin/surveys/mail_lists')?>">
                                                <?php _nom('newsletter_manage_mailing_list')?>                  </a>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body" style="padding: 10px !important;">
                        <ul class="nav nav-tabs" role="tablist" style="margin: 0 !important;">
                            <li role="presentation" class="<?php echo ($type == 'overview') ? 'active' : null?>">
                                <a href="<?php echo base_url('newsletter?type=overview')?>" aria-controls="general" role="tab" ><?php _nom('newsletter_overview')?></a>
                            </li>
                            <li role="presentation" class="<?php echo ($type == 'campaigns') ? 'active' : null?>">
                                <a href="<?php echo base_url('newsletter?type=campaigns')?>" aria-controls="invoice" role="tab" ><?php _nom('newsletter_campaigns')?></a>
                            </li>

                            <li role="presentation" class="<?php echo ($type == 'templates') ? 'active' : null?>">
                                <a href="<?php echo base_url('newsletter?type=templates')?>" aria-controls="invoice" role="tab" ><?php _nom('newsletter_templates')?></a>
                            </li>


                            <!--<li role="presentation" class="<?php echo ($type == 'forms') ? 'active' : null?>">
                        <a href="<?php echo base_url('newsletter?type=forms')?>" aria-controls="invoice" role="tab" ><?php _nom('newsletter_forms')?></a>
                    </li>-->

                            <li role="presentation" class="<?php echo ($type == 'settings') ? 'active' : null?>">
                                <a href="<?php echo base_url('newsletter/settings')?>" aria-controls="invoice" role="tab" ><?php _nom('newsletter_settings')?></a>
                            </li>
                            <li role="presentation" class="<?php echo ($type == 'settings') ? 'active' : null?>">
                                <a href="<?php echo base_url('trigger')?>" aria-controls="invoice" role="tab" ><?php _nom('newsletter_automation')?></a>
                            </li>
                        </ul>

                    </div>
                </div>

                <?php echo $content?>
            </div>
        </div>
    </div>
</div>
<div id="new_version"></div>
<?php init_tail(); ?>


</body>
</html>

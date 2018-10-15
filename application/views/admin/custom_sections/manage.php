<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
             <div class="panel_s">
                 <div class="panel-body">
                    <div class="_buttons">
                        <a href="<?php echo admin_url('custom_sections/section'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_custom_section'); ?></a>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="hr-panel-heading" />
                    <div class="clearfix"></div>
                    <?php render_datatable(
                        array(
                            _l('id'),
                            _l('custom_section_dt_name'),
                            _l('menu'),
                            _l('kb_article_slug'),
                            _l('custom_section_add_edit_active'),
                            _l('options')
                            ),'custom-sections'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
    <script>
        initDataTable('.table-custom-sections', window.location.href);
    </script>
</body>
</html>

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
                                <h4 class=" project-name"><?php lang('trigger_title')?></h4>
                                <div id="project_view_name">

                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="<?php echo base_url('trigger?type=trigger_add')?>"  class="btn btn-info"><?php lang('trigger_new')?></a>
                            </div>
                        </div>
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

<?php $CI = get_instance();?>
<div class="panel_s">
    <div class="panel-body">
        <?php $lists = $CI->Newsletter_model->getTemplateLists()?>

        <?php if(!$lists):?>
            <div class="alert alert-info"><?php _nom('newsletter_no_template_founds')?></div>
        <?php endif?>
        <table class="table table-striped table-staff dataTable no-footer dtr-inline">
            <tr>
                <th style="border: none;width:10%"></th>
                <th style="border: none;width:50%"></th>
                <th style="border: none;width:40%"></th>
            </tr>
            <tbody>
            <?php foreach($lists as $template):?>

                <tr>
                    <td>
                        <img src="<?php echo base_url(($template['preview']) ? $template['preview'] : 'plugins/newsletter/icon.png')?>" style="width: 150px"/>
                    </td>
                    <td><?php echo $template['title']?></td>
                    <td>
                        <a href="<?php echo base_url('newsletter?type=template')?>&id=<?php echo $template['id']?>" class=" btn btn-sm btn-info"><i class="fa fa-pencil"></i></a>
                        <a href="<?php echo base_url('newsletter?type=templates')?>&delete=<?php echo $template['id']?>" class=" btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                    </td>
                </tr>
            <?php endforeach?>
            </tbody>
        </table>
    </div>
</div>
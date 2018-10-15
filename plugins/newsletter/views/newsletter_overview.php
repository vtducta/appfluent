<?php $CI = get_instance()?>
<div class="row">
    <div class="col-md-7">
        <div class="panel_s">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <script>
        function get_newsletter_chart_config() {
                var config = {
            type: 'doughnut',
            data: {
                datasets: [{
                 			data: [
                 			<?php echo $CI->Newsletter_model->count('opens')?>,
                 			 <?php echo $CI->Newsletter_model->count('clicks')?>
                 			 ],
                 			backgroundColor: [
                 				"#FF6600",
                 				'#26C281'
                 			]
                 		}],

                 		labels: [
                        			"<?php _nom('newsletter_opens')?>",
                        			"<?php _nom('newsletter_clicks')?>"
                        		]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'top',
                },
                title: {
                    display: true
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }};

            return config;
            }


            </script>
                        <canvas width="400" height="400" style="width: 250px !important;height: 250px !important;"  id="newsletter-doughnut" class="" />
                    </div>
                    <div class="col-md-6">
                        <div class="" style="background: #03A9F4;color:white;font-size:20px;padding: 15px 20px;border-radius:10px;margin-top:30px;text-align: center">
                            <span><?php echo $CI->Newsletter_model->count('in-progress')?></span>
                            <?php _nom('newsletter_in_progress')?>
                        </div>

                        <div class="" style="background: #49DD6A;color:white;font-size:20px;padding: 15px 20px;border-radius:10px;margin-top:30px;text-align: center">
                            <span><?php echo $CI->Newsletter_model->count('completed')?></span>
                            <?php _nom('newsletter_completed')?>
                        </div>
                    </div>
                </div>

                <h5><?php _nom('newsletter_recent_campaigns')?></h5>

                <table class="table table-striped ">
                    <thead>
                    <tr>
                        <th style=""><?php _nom('newsletter_subject')?></th>
                        <th style=""><?php _nom('newsletter_statistics')?></th>
                        <th style=""><?php _nom('newsletter_opens')?></th>
                        <th style=""><?php _nom('newsletter_clicks')?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach($CI->Newsletter_model->getRecentCampaigns() as $campaign):?>
                            <tr>
                                <td><?php echo $campaign['subject']?></td>
                                <td><?php echo $CI->Newsletter_model->getStatistics($campaign['id'])?></td>
                                <td><span class='label inline-block ' style='color:#03a9f4;border:1px solid #03a9f4'><?php echo $campaign['opens']?></span</td>
                                <td><span class='label inline-block ' style='color:#03a9f4;border:1px solid #03a9f4'><?php echo $campaign['clicks']?></span</td>
                            </tr>
                        <?php endforeach?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="panel_s">
            <div class="panel-body">
                <ul class="list-group">
                    <li class="list-group-item"><?php _nom('newsletter_total_email_sent')?>
                        <span class="badge badge-default badge-pill"><?php echo $CI->Newsletter_model->count('mail-sent')?></span></li>
                    <li class="list-group-item"><?php _nom('newsletter_total_campaign')?>
                        <span class="badge badge-default badge-pill"><?php echo $CI->Newsletter_model->count('total-campaign')?></span></li>
                    <li class="list-group-item"><?php _nom('newsletter_total_templates')?>
                        <span class="badge badge-default badge-pill"><?php echo $CI->Newsletter_model->count('total-templates')?></span></li>
                    <li class="list-group-item"><?php _nom('newsletter_total_email_list')?>
                        <span class="badge badge-default badge-pill"><?php echo $CI->Newsletter_model->count('total-list')?></span></li>

                </ul>

                <?php $lists = $CI->Newsletter_model->getTemplateLists(true)?>
                <?php if($lists):?>
                    <h5><?php _nom('newsletter_latest_email_templates')?></h5>
                    <hr/>
                    <table class="table table-striped table-staff dataTable no-footer dtr-inline">
                        <tr>
                            <th style="border: none;width:10%"></th>
                            <th style="border: none;width:50%"></th>
                        </tr>
                        <tbody>
                        <?php foreach($lists as $template):?>

                            <tr>
                                <td>
                                    <img src="<?php echo base_url(($template['preview']) ? $template['preview'] : 'plugins/newsletter/icon.png')?>" style="width: 150px"/>
                                </td>
                                <td><?php echo $template['title']?></td>
                            </tr>
                        <?php endforeach?>
                        </tbody>
                    </table>
                <?php endif?>
            </div>
        </div>
    </div>
</div>
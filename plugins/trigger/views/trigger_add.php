<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div class="panel_s">
    <div class="panel-body">
        <ul  class="nav nav-pills">
            <?php foreach ($conditions AS $key => $value) : ?>
                <li class="<?php echo ($key == 'Contact') ? "active" : ''; ?>">
                    <a  href="#<?php echo $key; ?>" data-toggle="tab"><?php echo $key; ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <br><br>
        <div class="tab-content clearfix">
            <?php foreach ($conditions AS $key => $value): ?>
                <div class="tab-pane <?php echo ($key == 'Contact') ? "active" : ''; ?>" id="<?php echo $key; ?>">

                    <div class="row">
                        <?php foreach ($value AS $val): ?>
                        <div class="col-sm-3" style="border: 1px solid #CECECE; padding: 5px">
                                <div class="panel wrapper text-center b-a">
                                    <!-- Icon and Title -->
                                    <div>
                                        <div class="icon-contact">
                                            <i class="fa fa-user-o" style="font-size: 40px;"></i>
<!--                                            <i class="icon-user icon-3x"></i>-->
                                        </div>
                                        <b><?php echo $val['condition_name'] ?></b> 
                                    </div>
                                    <br>
                                    <div class="ellipsis-multiline m-b-md" rel="tooltip">
                                        <?php echo $val['description'] ?>
                                    </div>
                                    <!-- Add button -->
                                    <div>
                                        <a class="btn btn-sm btn-default add-trigger" data="CONTACT_IS_ADDED" href="<?php echo base_url('trigger?type=trigger&condition_id=' . $val['id']) ?>">Go</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
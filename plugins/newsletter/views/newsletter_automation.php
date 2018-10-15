<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$CI = get_instance();
?>


<div id="general" class="row">
    <div class="col-md-3 col-sm-3 col-xs-12 inline-block">
        <div class="panel wrapper text-center">
            <!-- Icon and Title -->
            <div class="text-center" style="width: 100%;display: inline-block;">
                <div>
                    <div class="text-center m-t m-b-sm">    
                        <i class="fa fa-user-circle"></i>
                    </div>
                    <div class="text-base m-t-xs">Contact Added</div>
                </div>
                <!-- Add button -->
                <div class="col-md-offset-3">
                    <a class="btn btn-sm btn-default add-automation col-md-8 m-t" data="CONTACT_IS_ADDED">Go</a>
                </div>
            </div>
            <hr class="inline-block" style="width: 100%;display: block;">
            <div class="campaign_list_scroll" style="width: 100%;">
                <div class="">
                    <?php if($contact):?>
                    <ol id="CONTACT_IS_ADDED_AUTOMATION" class="text-left p-l-md"> 
                        <?php foreach($contact as $ct): ?>
                        <li class="l-h-sm" id="workflows-model-list"> 
                            <div class="">
                                <a href="newsletter?type=automation_digram&id=<?php echo $ct['id']; ?>"><?php echo $ct['name'] ?></a>
                            </div>
                            <span class="table-resp animated fadeInRight delete-automation hide" data="5664981267775488" id="camp_delete">
                                <span class="text-ellipsis">
                                    <a href="" title="Delete" class="stop-propagation"><i class="icon icon-trash "></i></a>
                                </span>
                            </span> 
                            <span class="table-resp animated fadeInRight hide" id="camp_history">
                                <span class="">
                                    <a href="#activities/automation/5664981267775488" title="Activities" class="stop-propagation"><i class=" icon icon-hourglass"></i></a>
                                </span>
                            </span>
                            <span class="table-resp animated fadeInRight hide" id="camp_reports">
                                <span class="text-ellipsis">
                                    <a href="#automation-reports/5664981267775488" title="Reports" class="stop-propagation"><i class="icon icon-bar-chart"></i></a>
                                </span>
                            </span> 
                        </li>
                        <?php endforeach;?>
                    </ol>
                    <?php else:?>
                    <ol id="CONTACT_IS_ADDED_AUTOMATION" class="text-left p-l-md"> 

                        <div class="v-middle text-center" style="height:15vh;">
                            <small>No automations created yet.</small>
                        </div>
                    </ol>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12">
        <div class="panel wrapper text-center">
            <!-- Icon and Title -->
            <div class="text-center" style="width: 100%;display: inline-block;">
                <div>
                    <div class="text-center m-t m-b-sm">
                        <i class="material-icons score-icon">whatshot</i>
                    </div>
                    Score Increased
                </div>
                <!-- Add button -->
                <div class="col-md-offset-3">
                    <a class="btn btn-sm btn-default add-automation col-md-8 m-t" data="ADD_SCORE">Go</a>
                </div>
            </div>
            <hr class="inline-block" style="width: 100%;display: block;">
            <div class="campaign_list_scroll" style="width: 100%;">
                <div class="">
                    <ol id="ADD_SCORE_AUTOMATION" class="text-left p-l-md"> 

                        <div class="v-middle text-center" style="height:15vh;">
                            <small>No automations created yet.</small>
                        </div>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12">
        <div class="panel wrapper text-center">
            <!-- Icon and Title -->
            <div class="text-center" style="width: 100%;display: inline-block;">
                <div>
                    <div class="text-center m-t m-b-sm">
                        <i class="material-icons label-icon">label</i>
                    </div>
                    <div class="text-base m-t-xs">Tag Added</div>
                </div>
                <!-- Add button -->
                <div class="col-md-offset-3">
                    <a class="btn btn-sm btn-default add-automation col-md-8 m-t" data="TAG_IS_ADDED">Go</a>
                </div>
            </div>
            <hr class="inline-block" style="width: 100%;display: block;">
            <div class="campaign_list_scroll" style="width: 100%;">
                <div class="">
                    <ol id="TAG_IS_ADDED_AUTOMATION" class="text-left p-l-md">  
                        <div class="v-middle text-center" style="height:15vh;">
                            <small>No automations created yet.</small>
                        </div>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12 inline-block">
        <div class="panel wrapper text-center">
            <!-- Icon and Title -->
            <div class="text-center" style="width: 100%;display: inline-block;">
                <div>
                    <div class="text-center m-t m-b-sm">
                        <i class="material-icons backspace-icon">backspace</i>
                    </div>
                    <div class="text-base m-t-xs">Tag Removed</div>
                </div>
                <!-- Add button -->
                <div class="col-md-offset-3">
                    <a class="btn btn-sm btn-default add-automation col-md-8 m-t" data="TAG_IS_DELETED">Go</a>
                </div>
            </div>
            <hr class="inline-block" style="width: 100%;display: block;">
            <div class="campaign_list_scroll" style="width: 100%;">
                <div class="">
                    <ol id="TAG_IS_DELETED_AUTOMATION" class="text-left p-l-md"> 
                        <div class="v-middle text-center" style="height:15vh;">
                            <small>No automations created yet.</small>
                        </div>
                    </ol>
                </div>
            </div>
        </div>

    </div>
</div>
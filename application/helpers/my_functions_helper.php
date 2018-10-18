<?php

include('plugins/newsletter/init.php');
include('plugins/trigger/init.php');
add_action('after_render_single_aside_menu', 'my_custom_menu_items');

function my_custom_menu_items($order) {
//    if ($order == 6) {
//        echo '<li class="menu-item-campaigns"><a href="' . base_url('newsletter?type=campiagns') . '"><i class="fa fa-user-o menu-icon  "></i>Campaings</a></li>';
//        echo '<li class="menu-item-automations"><a href="' . base_url('automations') . '"><i class="fa fa-cogs menu-icon"></i>Automation</a></li>';
//    }
    
}

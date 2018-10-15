/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(function () {
//    if (newsletter_menu == 1 && $("#side-menu").length > 0) {
//            var a = ($("#main-newsletter").length > 0) ? 'active' : '';
//            $('<li class="menu-item-newsletter '+a+'"><a href="'+site_url+'newsletter" aria-expanded="false"><i class="fa fa-newspaper-o menu-icon"></i>'+newsletterStr+'</a></li>').insertAfter(".menu-item-dashboard")
//        }
//
//        if ($("#settings-form").length > 0) {
//                    $('.col-md-3 .panel-body > ul').append("<li><a href='"+site_url+"newsletter/settings'>Newsletter</a></li>")
//                }

    initDataTable('.table-triggers', site_url + 'trigger?type=triggers', [], [], [], [0, 'DESC']);
});
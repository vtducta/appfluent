function newsletter_save_campaign() {
    $('#newsletter-status').val(0);
    $("#newsletter-campaign-form").submit();
    return false;
}

function newsletter_insert_template(t) {
    $.ajax({
        url: site_url + 'newsletter/gettemplate?id=' + t,
        success: function (c) {
            $("#campaign-text-content").val(c)
            var editor = tinymce.get('campaign-text-content');
            editor.setContent(c);
        }
    })

    return false;
}
$(function () {
//    if (newsletter_menu == 1 && $("#side-menu").length > 0) {
//        var a = ($("#main-newsletter").length > 0) ? 'active' : '';
//        $('<li class="menu-item-newsletter ' + a + '"><a href="' + site_url + 'newsletter" aria-expanded="false"><i class="fa fa-newspaper-o menu-icon"></i>' + newsletterStr + '</a></li>').insertAfter(".menu-item-dashboard")
//    }
//
//    if ($("#settings-form").length > 0) {
//        $('.col-md-3 .panel-body > ul').append("<li><a href='" + site_url + "newsletter/settings'>Newsletter</a></li>")
//    }

    initDataTable('.table-campaigns', site_url + 'newsletter?type=campaigns', [7], [7], [], [0, 'DESC']);

    if ($("#newsletter-doughnut").length > 0) {
        var ctx = document.getElementById('newsletter-doughnut').getContext("2d");
        ;
        var config = get_newsletter_chart_config();
        new Chart(ctx, config);
    }
    $('.add-automation').click(function () {
        var data = $(this).attr('data');
        if (data === 'CONTACT_IS_ADDED') {
            location.href = site_url + 'newsletter?type=automation_digram'
        }
    });
    
    if(schedule_on !== 'undefined' && schedule_on !== null) {
        $('.schedule_on').val(schedule_on);
    }
    if(schedule_at !== 'undefined'  && schedule_at !== null) {
        $('.schedule_at').val(schedule_at);
    }
    
});
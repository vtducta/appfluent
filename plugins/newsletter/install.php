<?php
$CI = get_instance();
$db = $CI->db;
$db->query("CREATE TABLE `tblnewsletter_templates` ( `id` INT NOT NULL AUTO_INCREMENT , `title` VARCHAR(255) NOT NULL , `content` TEXT NOT NULL , `preview` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci;");

$db->query("CREATE TABLE `tblnewsletter_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT ,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sender_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sender_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `email_list` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template` int(11) NOT NULL,
  `sent_date` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `send_date` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `opens` int(11) NOT NULL DEFAULT '0',
  `clicks` int(11) NOT NULL DEFAULT '0',
  `creator` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
$db->query("CREATE TABLE `tblnewsletter_campaigns_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sent` int(11) NOT NULL DEFAULT '0',
  `campaign_id` int(11) NOT NULL,
  `source` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

add_option("newsletter_email_queue", 1);
add_option("newsletter_sender_name", '');
add_option('newsletter_sender_email','');


$content = file_get_contents(newsletter_get_base_path().'/templates/1/template.phtml');
$CI->Newsletter_model->saveTemplate('Summer Template', $content, null, 'plugins/newsletter/templates/1/preview.png');

$content = file_get_contents(newsletter_get_base_path().'/templates/2/template.phtml');
$CI->Newsletter_model->saveTemplate('Welcome Template', $content, null, 'plugins/newsletter/templates/2/preview.png');

$content = file_get_contents(newsletter_get_base_path().'/templates/3/template.phtml');
$CI->Newsletter_model->saveTemplate('Re-Engagement Template', $content, null, 'plugins/newsletter/templates/3/preview.png');

$content = file_get_contents(newsletter_get_base_path().'/templates/4/template.phtml');
$CI->Newsletter_model->saveTemplate('Newsletter Template', $content, null, 'plugins/newsletter/templates/4/preview.png');

$content = file_get_contents(newsletter_get_base_path().'/templates/5/template.phtml');
$CI->Newsletter_model->saveTemplate('Announcement Template', $content, null, 'plugins/newsletter/templates/5/preview.png');



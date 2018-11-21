<?php
$customer_tabs = array(
  array(
    'name'=>'inbox',
    'url'=>admin_url('emails/group/inbox'),
    'icon'=>'fa fa-inbox',
    'lang'=>'Inbox',
    'visible'=>true,
    'order'=>1
    ),
  array(
        'name'=>'drafts',
        'url'=>admin_url('emails/group/drafts'),
        'icon'=>'fa fa-file-o',
        'lang'=>'Drafts',
        'visible'=>true,
        'order'=>2
    ),
  array(
    'name'=>'sent',
    'url'=>admin_url('emails/group/sent'),
    'icon'=>'fa fa-send-o',
    'lang'=>'Sent',
    'visible'=>true,
    'order'=>3
    ),
  array(
    'name'=>'junk',
    'url'=>admin_url('emails/group/junk'),
    'icon'=>'fa fa-window-close-o',
    'lang'=>'Junk',
    'visible'=>true,
    'order'=>4
    ),
    array(
        'name'=>'trash',
        'url'=>admin_url('emails/group/trash'),
        'icon'=>'fa fa-trash-o',
        'lang'=>'Trash',
        'visible'=>true,
        'order'=>5
    ),
    array(
        'name'=>'archive',
        'url'=>admin_url('emails/group/archive'),
        'icon'=>'fa fa-archive',
        'lang'=>'Archive',
        'visible'=>true,
        'order'=>6
    )
  );

$hook_data = do_action('email_tabs',array('tabs'=>$customer_tabs));
$customer_tabs = $hook_data['tabs'];

usort($customer_tabs, function($a, $b) {
  return $a['order'] - $b['order'];
});

?>
<ul class="nav navbar-pills nav-tabs nav-stacked customer-tabs" role="tablist">
   <?php foreach($customer_tabs as $tab){
      if((isset($tab['visible']) && $tab['visible'] == true) || !isset($tab['visible'])){ ?>
      <li class="<?php if($tab['name'] == $group){echo 'active ';} ?>customer_tab_<?php echo $tab['name']; ?>">
        <a data-group="<?php echo $tab['name']; ?>" href="<?php echo $tab['url']; ?>"><i class="<?php echo $tab['icon']; ?> menu-icon" aria-hidden="true"></i><?php echo $tab['lang']; ?>
            <?php
                $total_reminders=0;
                if($tab['name']=='sent'){
                    $total_reminders = total_rows('tblemails_staff');
                }else{
                    $total_reminders = total_rows('tblInboxs_staff',
                        array(
                            'group'=>$tab['name']
                        )
                    );
                }

              if($total_reminders > 0){
                echo '<span class="badge">'.$total_reminders.'</span>';
              }
          ?>
      </a>
  </li>
  <?php } ?>
  <?php } ?>
</ul>

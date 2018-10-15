<h4 class="customer-profile-group-heading"><?php echo $title; ?></h4>
<div class="row">
   <?php echo form_open_multipart($this->uri->uri_string(),array('class'=>'custom-section-form','autocomplete'=>'off')); ?>
   <div class="additional"></div>
    <?php echo form_hidden('contact[contactid]',$contactid); ?>
   <div class="col-md-12">
      <ul class="nav nav-tabs profile-tabs row customer-profile-tabs" role="tablist">
          <?php $index=0;
           foreach ($list_custom_tab as $customtab) { ?>
              <li role="presentation" class="<?php if($index==0){echo 'active';}; ?>">
                  <a href="#<?=$customtab['slug']?>" aria-controls="<?=$customtab['slug']?>" role="tab" data-toggle="tab">
                      <?php echo $customtab['name']; ?>
                  </a>
              </li>
              <?php do_action('after_'.$customtab['slug'].'_tab',isset($contact) ? $contact : false); ?>
          <?php $index++; }?>
      </ul>
      <div class="tab-content">
          <?php $index=0;
          foreach ($list_custom_tab as $customtab) { ?>
              <div role="tabpanel" class="tab-pane <?php if($index==0){echo 'active';}; ?>" id="<?=$customtab['slug']?>">
                  <div class="row">
                      <div class="col-md-12">
                          <?php $rel_id=( isset($contact) ? $contact->id : false); ?>
                          <?php echo render_custom_fields( 'contacts',$rel_id,array('custom_tab_id'=>$customtab['id'])); ?>
                      </div>
                  </div>
              </div>
          <?php $index++;  }?>
      </div>
       <button class="btn btn-info pull-right mbot15">
           <?php echo _l( 'submit'); ?>
       </button>
   </div>

   <?php echo form_close(); ?>
</div>

<!-- /.modal -->






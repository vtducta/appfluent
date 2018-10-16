<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                            <?php if(isset($custom_tab)){ ?>
                            <a href="<?php echo admin_url('custom_tabs/tab'); ?>" class="btn btn-success pull-right"><?php echo _l('new_custom_tab'); ?></a>
                            <div class="clearfix"></div>
                            <?php } ?>
                        </h4>
                        <hr class="hr-panel-heading" />
                            <?php echo form_open($this->uri->uri_string()); ?>
                                <?php
                                $disable = '';
                                if(isset($custom_tab)){
                                  if(total_rows('tblcustomfieldsvalues',array('fieldid'=>$custom_field->id,'fieldto'=>$custom_field->fieldto)) > 0){
                                    $disable = 'disabled';
                                }
                            }
                            ?>

                            <?php $value = (isset($custom_tab) ? $custom_tab->name : ''); ?>
                            <?php echo render_input('name','custom_tab_name',$value); ?>

                        <div  style="width: 100%;margin-bottom: 10px">
                            <label for="custom_section"><?php echo _l('custom_field_add_edit_custom_section'); ?></label>
                            <select name="custom_section_id" id="custom_section_id" class="selectpicker" data-width="100%"  data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <?php

                                foreach ($list_custom_section as $custom_section) {
                                    ?>
                                    <option value="<?= $custom_section['id']?>"
                                        <?php if(isset($custom_tab) && $custom_tab->custom_section_id == $custom_section['id']){echo 'selected';} ?>>
                                        <?php echo ( $custom_section['menu'] .' --- '. $custom_section['name'] ); ?>
                                    </option>

                                    <?php
                                } ?>

                            </select>
                        </div>




                            <?php $value = (isset($custom_tab) ? $custom_tab->order : ''); ?>
                            <?php echo render_input('order','custom_field_add_edit_order',$value,'number'); ?>


                        <div  style="width: 100%;margin-bottom: 10px">
                            <label for="custom_section"><?php echo _l('Show in profile section'); ?></label>
                            <select name="only_show_in" id="only_show_in" class="selectpicker" data-width="100%"  data-none-selected-text="<?php echo _l('dropdown_non_selected_text'); ?>">
                                <option value="" ></option>
                                <option value="contact_profile" <?php if(isset($custom_tab) && $custom_tab->only_show_in == 'contact_profile'){echo 'selected';} ?> >Only show in contact profile</option>
                                <option value="policy_profile" <?php if(isset($custom_tab) && $custom_tab->only_show_in == 'policy_profile'){echo 'selected';} ?> >Only show in policy profile</option>

                            </select>
                        </div>

                            <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
    <script>

        $(function(){
          _validate_form($('form'), {
            name: 'required',
            options: {
                required: {
                    depends:function(element){

                    }
                }
            }
        });

      });
  </script>
</body>
</html>

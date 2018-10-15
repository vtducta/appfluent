<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                            <?php if(isset($custom_section)){ ?>
                            <a href="<?php echo admin_url('custom_sections/section'); ?>" class="btn btn-success pull-right"><?php echo _l('new_custom_section'); ?></a>
                            <div class="clearfix"></div>
                            <?php } ?>
                        </h4>
                        <hr class="hr-panel-heading" />
                            <?php echo form_open($this->uri->uri_string()); ?>


                            <?php $value = (isset($custom_section) ? $custom_section->name : ''); ?>
                            <?php echo render_input('name','custom_section_name',$value); ?>
                        <div  style="width: 100%;margin-bottom: 10px">
                            <label for="menu"><?php echo _l('Belong to menu'); ?></label>
                        <select name="menu" id="menu" class="selectpicker" data-width="100%"  data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <option value=""></option>
                            <option value="contacts" <?php if(isset($custom_section) && $custom_section->menu == 'contacts'){echo 'selected';} ?>> contacts</option>
                            <option value="policies" <?php if(isset($custom_section) && $custom_section->menu == 'policies'){echo 'selected';} ?>> policies</option>

                        </select>
                        </div>

                            <?php $value = (isset($custom_section) ? $custom_section->order : ''); ?>
                            <?php echo render_input('order','custom_section_add_edit_order',$value,'number'); ?>

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

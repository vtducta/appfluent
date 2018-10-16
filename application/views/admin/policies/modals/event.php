<div class="modal fade _event" id="newEventModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo _l('utility_calendar_new_event_title'); ?></h4>
      </div>
      <?php echo form_open('admin/policies/event/'.$event_id,array('id'=>'event-form')); ?>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
              <?php echo form_hidden('eventid',$event_id); ?>
              <?php $value=( isset($event) ? $event->title : ''); ?>
            <?php echo render_input('title','utility_calendar_new_event_placeholder',$value); ?>
              <?php $value=( isset($event) ? $event->description : ''); ?>
            <?php echo render_textarea('description','event_description',$value,array('rows'=>5)); ?>
              <?php $value=( isset($event) ? $event->start : ''); ?>
            <?php echo render_datetime_input('start','utility_calendar_new_event_start_date',$value); ?>
            <div class="clearfix mtop15"></div>
              <?php $value=( isset($event) ? $event->end : ''); ?>
            <?php echo render_datetime_input('end','utility_calendar_new_event_end_date',$value); ?>
            <div class="form-group">
             <div class="row">
              <div class="col-md-12">
                <label for="reminder_before"><?php echo _l('event_notification'); ?></label>
              </div>
              <div class="col-md-6">
                <div class="input-group">
                    <?php $value=( isset($event) ? $event->reminder_before : '0'); ?>
                  <input type="number" class="form-control" name="reminder_before" value="<?php echo $value ?>" id="reminder_before">
                  <span class="input-group-addon"><i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('reminder_notification_placeholder'); ?>"></i></span>
                </div>
              </div>
                 <?php $value=( isset($event) ? $event->reminder_before_type : '');
                 //var_dump($value);die;
                 ?>
              <div class="col-md-6">
               <select name="reminder_before_type" id="reminder_before_type" class="form-control selectpicker" style="display:block !important" data-width="100%">
                 <option <?php if($value=='minutes' || $value=='') echo 'selected'; ?> value="minutes" ><?php echo _l('minutes'); ?></option>
                 <option <?php if($value=='hours') echo 'selected'; ?> value="hours"><?php echo _l('hours'); ?></option>
                 <option <?php if($value=='days') echo 'selected'; ?> value="days"><?php echo _l('days'); ?></option>
                 <option <?php if($value=='weeks') echo 'selected';?> value="weeks"><?php echo _l('weeks'); ?></option>
               </select>
             </div>
           </div>
         </div>
         <hr />
         <p class="bold"><?php echo _l('event_color'); ?></p>
         <?php
         $event_colors = '';
         $favourite_colors = get_system_favourite_colors();
         $value = ( isset($event) ? $event->color : $favourite_colors[0]);
         $i = 0;
         foreach($favourite_colors as $color){
          $color_selected_class = 'cpicker-small';
          if($favourite_colors[$i] == $value){
            $color_selected_class = 'cpicker-big';
          }
          $event_colors .= "<div class='calendar-cpicker cpicker ".$color_selected_class."' data-color='".$color."' style='background:".$color.";border:1px solid ".$color."'></div>";
          $i++;
        }
        echo '<div class="cpicker-wrapper">';
        echo $event_colors;
        echo '</div>';
        echo form_hidden('color',$value);
        ?>
        <div class="clearfix"></div>
        <hr />
        <div class="checkbox checkbox-primary">
           <?php $value = ( isset($event) ? $event->public : '0'); ?>
          <input <?php if($value>=1) echo "checked" ?>  type="checkbox" name="public" id="public">
          <label for="public"><?php echo _l('utility_calendar_new_event_make_public'); ?></label>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
    <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
  </div>
  <?php echo form_close(); ?>
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

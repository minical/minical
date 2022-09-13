<div id="printable-container">
	<div class="app-page-title">
	   <div class="page-title-wrapper">
	        <div class="page-title-heading">
	            <div class="page-title-icon">
	                <i class="pe-7s-notebook text-success"></i>
	            </div>
	              <?php echo l('housekeeping_settings'); ?>
	        </div>
	    </div>
	</div>

<div class="main-card card">
    <div class="card-body">
<form method="post" action="<?php echo base_url();?>settings/room_inventory/housekeeping" autocomplete="off">		

	<div class="form-group form-inline">
		<input type="checkbox"name="housekeeping_auto_clean_is_enabled" id="housekeeping_auto_clean_is_enabled" 
		<?php 
			if(isset($settings->housekeeping_auto_clean_is_enabled)) 
			{
				
				$housekeeping_auto_clean_is_enabled = $settings->housekeeping_auto_clean_is_enabled; 
			}
			else 
			{
				$housekeeping_auto_clean_is_enabled = set_value('housekeeping_auto_clean_is_enabled');
			}
			
			if ($housekeeping_auto_clean_is_enabled)
			{
				echo "checked='true'";
				
			}
		?> />
		<?php echo l('Mark all rooms as "clean" after', true); ?>
		<input type="text" name="hour" maxlength=2 id="hour"  class="form-control"  value="<?php
			if(isset($settings->hour)) 
			{
				echo $settings->hour; 
			}
			else 
			{
				echo set_value('hour');
			}
		?>"/>
		:						
		<input type="text" name="minute" maxlength=2 id="minute"  class="form-control" value="<?php
			if(isset($settings->minute))
			{
				echo $settings->minute; 
			}
			else 
			{
				echo set_value('minute');
			}
		?>"/>					
		<select type="text" name="am_pm" class="form-control">
			<?php
				if(isset($settings->am_pm)) 
				{
					$ampm = $settings->am_pm; 
				}
				else 
				{
					$ampm = set_value('am_pm');
				}
			
			?>
			
			<option value="AM" <?php if ($ampm == "AM") echo "selected"; ?> >AM</option>
			<option value="PM" <?php if ($ampm == "PM") echo "selected"; ?> >PM</option>
		</select>						
	</div>

	<div class="form-group form-inline">
		<input type="checkbox" name="housekeeping_auto_dirty_is_enabled" id="housekeeping_auto_dirty_is_enabled" 
		<?php 
			if(isset($dirty_room_settings->housekeeping_auto_dirty_is_enabled)) 
			{
				
				$housekeeping_auto_dirty_is_enabled = $dirty_room_settings->housekeeping_auto_dirty_is_enabled; 
			}
			else 
			{
				$housekeeping_auto_dirty_is_enabled = set_value('housekeeping_auto_dirty_is_enabled');
			}
			
			if ($housekeeping_auto_dirty_is_enabled)
			{
				echo "checked='true'";
				
			}
		?> />
		<?php echo l('Mark all Checked In rooms as “dirty” after', true); ?>
		<input type="text" name="d_hour" maxlength=2 id="d_hour"  class="form-control"  value="<?php
			if(isset($dirty_room_settings->d_hour)) 
			{
				echo $dirty_room_settings->d_hour; 
			}
			else 
			{
				echo set_value('d_hour');
			}
		?>"/>
		:						
		<input type="text" name="d_minute" maxlength=2 id="d_minute"  class="form-control" value="<?php
			if(isset($dirty_room_settings->d_minute))
			{
				echo $dirty_room_settings->d_minute; 
			}
			else 
			{
				echo set_value('d_minute');
			}
		?>"/>					
		<select type="text" name="d_am_pm" class="form-control">
			<?php
				if(isset($dirty_room_settings->d_am_pm)) 
				{
					$d_ampm = $dirty_room_settings->d_am_pm; 
				}
				else 
				{
					$d_ampm = set_value('d_am_pm');
				}
			
			?>
			
			<option value="AM" <?php if ($d_ampm == "AM") echo "selected"; ?> >AM</option>
			<option value="PM" <?php if ($d_ampm == "PM") echo "selected"; ?> >PM</option>
		</select>						
	</div>

	<div class="form-group">
		<label for="housekeeping_day_interval_for_full_cleaning" class="control-label">
			<?php echo l('housekeeping_report'); ?>
		</label>
		<div class=" form-inline">
			<?php echo l("Change staying guests' bed sheets every", true); ?> 
		
			<input 
				type="text" 
				name="housekeeping_day_interval_for_full_cleaning" 
				id="housekeeping_day_interval_for_full_cleaning" 
				class="form-control"
				size="3" 
				maxlength="2"
                onchange="if(this.value<0){alert(l('Negative values not allowed.', true));this.value=0;}" 
				value="<?php 
				if(isset($settings->housekeeping_day_interval_for_full_cleaning)) 
				{
					echo $settings->housekeeping_day_interval_for_full_cleaning; 
				}
				else 
				{
					echo set_value('housekeeping_day_interval_for_full_cleaning');
				}
			?>" >
			<?php echo l('days', true); ?>				
		</div>
		<span id="helpBlock" class="help-block">
		* <?php echo l("For example, if the number of days is set as '4' and there is a guest is staying From 2012-Oct-1 to 2012-Oct 10,
		the Housekeeping report will say 'Change Sheets' for that guest's room for 2012-Oct-5 and 2012-Oct-9", true); ?>
		<br/><br/>
		** <?php echo l('To disable this feature, enter "0" as the number of the days.', true); ?>
		</span>
	</div>

	<div class="col-sm-12 text-center">
		<input type="submit" value="<?php echo l('Update', true); ?>" class="btn btn-light" />
	</div>
</form>

</div>
</div>
</div>
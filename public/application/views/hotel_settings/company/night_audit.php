
<div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			<div class="page-title-icon">
				<i class="pe-7s-clock text-success"></i>
			</div>
			<div><?php echo l('night_audit_&_date').' '.l('settings'); ?>

		</div>
	</div>
  </div>
</div>


<div class="main-card card">
	<div class="card-body">

<form method="post" action="<?php echo base_url();?>settings/company/night_audit" autocomplete="off">		
	<div class="form-group position-relative ml-3 mb-5">
		<h5 class="card-title"><?php echo l('settings'); ?></h5>						
			
		<div class=" form-inline">
			<input type="text" name="selling_date"  class="form-control" id="selling_date_input" value="<?php 
						echo $company['selling_date']; ?>"/>
			<button id='run-night-audit-button' type="submit" class="btn btn-light"><?php echo l("Manually run Night Audit"); ?></button>
		</div>
		<p class="help-block">
			*<?php echo l("Do not touch this unless you clearly know what you're doing"); ?> :o
		</p>
	</div>
	<div class="form-group position-relative ml-3 mb-5">
		<h5 class="card-title"><?php echo l('automatic_night_audit'); ?></h5>						
		<div class="form-group position-relative form-inline">
		
			<input type="checkbox" name="night_audit_auto_run_is_enabled"
			<?php 
				if(isset($company['night_audit_auto_run_is_enabled'])) 
				{
					$night_audit_auto_run_is_enabled = $company['night_audit_auto_run_is_enabled']; 
				}
				else 
				{
					$night_audit_auto_run_is_enabled = set_value('night_audit_auto_run_is_enabled');
				}
				
				if ($night_audit_auto_run_is_enabled)
				{
					echo "checked='true'";
					
				}
			?> />
            
            <?=l("Automatically change date to the next day after");?>
			<select name="night_audit_auto_run_time" class="form-control">
				<option value="21:00:00" <?php if ($company['night_audit_auto_run_time'] == "21:00:00") echo " SELECTED "; ?>><?php echo l('Same day 09:00 PM', true); ?></option>
				<option value="22:00:00" <?php if ($company['night_audit_auto_run_time'] == "22:00:00") echo " SELECTED "; ?>><?php echo l('Same day 10:00 PM', true); ?></option>
				<option value="23:00:00" <?php if ($company['night_audit_auto_run_time'] == "23:00:00") echo " SELECTED "; ?>><?php echo l('Same day 11:00 PM', true); ?></option>
				<option value="00:00:00" <?php if ($company['night_audit_auto_run_time'] == "00:00:00") echo " SELECTED "; ?>><?php echo l('Midnight', true); ?></option>
				<option value="01:00:00" <?php if ($company['night_audit_auto_run_time'] == "01:00:00") echo " SELECTED "; ?>><?php echo l('Next day 01:00 AM', true); ?></option>
				<option value="02:00:00" <?php if ($company['night_audit_auto_run_time'] == "02:00:00") echo " SELECTED "; ?> ><?php echo l('Next day 02:00 AM', true); ?></option>
				<option value="03:00:00" <?php if ($company['night_audit_auto_run_time'] == "03:00:00") echo " SELECTED "; ?> ><?php echo l('Next day 03:00 AM', true); ?></option>
				<option value="04:00:00" <?php if ($company['night_audit_auto_run_time'] == "04:00:00") echo " SELECTED "; ?> ><?php echo l('Next day 04:00 AM', true); ?></option>
				<option value="05:00:00" <?php if ($company['night_audit_auto_run_time'] == "05:00:00") echo " SELECTED "; ?> ><?php echo l('Next day 05:00 AM', true); ?></option>
				<option value="06:00:00" <?php if ($company['night_audit_auto_run_time'] == "06:00:00") echo " SELECTED "; ?> ><?php echo l('Next day 06:00 AM', true); ?></option>
				<option value="07:00:00" <?php if ($company['night_audit_auto_run_time'] == "07:00:00") echo " SELECTED "; ?> ><?php echo l('Next day 07:00 AM', true); ?></option>
				<option value="08:00:00" <?php if ($company['night_audit_auto_run_time'] == "08:00:00") echo " SELECTED "; ?> ><?php echo l('Next day 08:00 AM', true); ?></option>
				<option value="09:00:00" <?php if ($company['night_audit_auto_run_time'] == "09:00:00") echo " SELECTED "; ?> ><?php echo l('Next day 09:00 AM', true); ?></option>
			</select>
			
			<p class="help-block">
			<?=l("*This option should be disabled if you are manipulating the selling date");?></p>
		</div>
        
		<div class="form-group position-relative form-inline">
		
            <input type="checkbox" class="night_audit_ignore_check_out_date" name="night_audit_ignore_check_out_date"
			<?php 
				if(isset($company['night_audit_ignore_check_out_date'])) 
				{
					$night_audit_ignore_check_out_date = $company['night_audit_ignore_check_out_date']; 
				}
				else 
				{
					$night_audit_ignore_check_out_date = set_value('night_audit_ignore_check_out_date');
				}
				
				if ($night_audit_ignore_check_out_date)
				{
					echo "checked='true'";
					
				}
			?> />
			<?=l("Apply night audit charges to checked-in bookings regardless of their check-out date");?>
		</div>
        
        <div class="form-group position-relative form-inline">
		
			<input type="checkbox" name="night_audit_charge_in_house_only"
			<?php 
				if(isset($company['night_audit_charge_in_house_only'])) 
				{
					$night_audit_charge_in_house_only = $company['night_audit_charge_in_house_only']; 
				}
				else 
				{
					$night_audit_charge_in_house_only = set_value('night_audit_charge_in_house_only');
				}
				
				if ($night_audit_charge_in_house_only)
				{
					echo "checked='true'";
					
				}
			?> />
			<?=l("Apply night audit charges to checked-in bookings only (Not reservations)");?>
		</div>

	</div>

	<div class="form-group position-relative ml-3 mb-5">
		<h5 class="card-title"><?php echo l('default_room_charge_type'); ?></h5>						
		<div class="form-group position-relative form-inline">
			<select name="default_room_charge_type_id" class="form-control">
				<option><?php echo l('None Selected', true); ?></option>
				<?php
					foreach ($charge_types as $charge_type):
				?>
						<option value="<?php echo $charge_type['id']; ?>" <?php if ($charge_type['is_default_room_charge_type'] == '1') echo " SELECTED "; ?>><?php echo $charge_type['name']; ?></option>
				<?php
					endforeach;
				?>
			</select>
			
			<p class="help-block">
			<?=l("* When Night Audit runs, all in-house guests will be charged with this Charge Type");?>
		</div>
	</div>
	<div class="form-group position-relative ml-3 mb-5">
		<h5 class="card-title"><?=l("Automatic Checkout");?></h5>						
		<div class="form-group position-relative form-inline">
		
			<input type="checkbox" name="night_audit_force_check_out" id="night_audit_force_check_out" 
			<?php
				if(isset($company['night_audit_force_check_out'])) 
				{
					$night_audit_force_check_out = $company['night_audit_force_check_out']; 
				}
				else 
				{
					$night_audit_force_check_out = set_value('night_audit_force_check_out');
				}
				if ($night_audit_force_check_out)
				{
					echo "checked='true'";
				}
			?> />
			<?=l("Automatically check-out guests if they are scheduled to check out");?>
		</div>
	</div>

	<div class="col-sm-12">
		<input type="submit" class="btn btn-primary" value="<?php echo l('Update', true); ?>" />
	</div>


</form>

	<!--
				<input type="checkbox" name="night_audit_auto_prompt" id="night_audit_auto_prompt" 
				<?php 
					if(isset($company['night_audit_auto_prompt'])) 
					{
						
						$night_audit_auto_prompt = $company['night_audit_auto_prompt']; 
					}
					else 
					{
						$night_audit_auto_prompt = set_value('night_audit_auto_prompt');
					}
					
					if ($night_audit_auto_prompt)
					{
						echo "checked='true'";
						
					}
				?> />
				<span class="bold">Remind me to run Night Audit if it hasn't been processed after 
					
					<select name="night_audit_auto_prompt_time">
					<?php
						$time = "00:00";
						for ($i = 0; $i < 24; $i++)
						{
							
							if ($company['night_audit_auto_prompt_time'] == "00:00:00")
							{
								$selected = "SELECTED";
							}
							else
							{
								$selected = "";
							}
							echo "<option $selected value='".date("H:i:s", strtotime($time))."'>";
							echo $time;
							echo "</option>\n";
							$time = Date("H:i", strtotime("+1 hour", strtotime($time)));
							
						}
					?>
					
					</select>
				</span>
				<br/>
				<input type="checkbox" name="night_audit_multiple_days" id="night_audit_multiple_days" 
				<?php 
						if(isset($company['night_audit_multiple_days'])) 
					{
						$night_audit_multiple_days = $company['night_audit_multiple_days']; 
					}
					else 
					{
						$night_audit_multiple_days = set_value('night_audit_multiple_days');
					}
					if ($night_audit_multiple_days)
					{
						echo "checked='true'";
					}
				?> />
				<span class="bold">Allow running Night Audit multiple times to catch up to today</span><br/>
				Say your hotel's selling date is behind by 3 days. This option will make Night Audit to run as many times as required to catch up to today.
			-->


	<!-- Hidden night audit dialog-->
	<div id="dialogNightAudit" title="<?php 				
		echo l('run_night_audit', true)."?";				
	?>">
		<br />
		<div id="nightAuditMessage">					
			<?php //echo l('running_night_audit_will').":";?>
				<?php 	
					echo l('running_night_audit_will', true).' '.l('change_selling_date_to', true);
				?>:
				<br/>
				<span class="bold" id="night-audit-resulting-date">
				</span>
				and 
				<?php echo l('charge_staying_guests', true);
				?>						
			<br/>						
			<?php echo l('continue', true); ?>?
			<br/>
			<br/>
			<div class="nightAuditButton btn btn-light" id="submitNightAuditButton"><?php echo l('run_night_audit', true); ?></div>
			<div class="nightAuditButton btn btn-light" id="cancelNightAuditButton"><?php echo l('cancel', true); ?></div>				
			
		</div>
	</div>
	
	<div id="dialogProcessingRequest">
		 <?php echo l('processing_request_please_wait', true);?>
	</div>
</div></div>
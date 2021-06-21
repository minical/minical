
<div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			<div class="page-title-icon">
				<i class="pe-7s-notebook text-success"></i>
			</div>
			<div><?php echo l('general_information'); ?>

		</div>
	</div>
</div>
</div>


<!-- <div class="page-header">
	<h2></h2>
</div> -->

<!-- Image Edit Modal -->
<div class="modal fade" id="image_edit_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo l('edit_image'); ?></h4>
			</div>
			<div class="modal-body text-center" id="image_edit_modal_body">
				<?php echo l('Loading', true); ?>...
			</div>
			<div class="modal-footer image-modal-footer">
				<button type="button" class="btn btn-light" data-dismiss="modal"><?php echo l('close'); ?></button>
			</div>
		</div>
	</div>
</div>


<div class="main-card card">
	<div class="card-body">
		
		<form class="" 
		method="post" 
		action="<?php echo base_url();?>settings/company/general" 
		enctype="multipart/form-data">
		<div class="form-row">
			<div class="col-md-2">
				<div class="position-relative form-group  image-group" id="<?php echo $company['logo_image_group_id']; ?> ">
					<div> <label for="examplelogo11" class=""><?php echo l('company_logo'); ?></label></div>
					<div id="examplelogo11" class="btn btn-primary form-group add-image"
					data-toggle="modal" 
					data-target="#image_edit_modal"
					>
					<?php echo l('add_image'); ?>
				</div>
			</div>
		</div>
		<div class="col-md-10">
			<div class="position-relative form-group" >
				<?php
				if (isset($logo_images)):
					foreach ($logo_images as $image):
						$image_url = "https://inngrid.s3.amazonaws.com/".$company['company_id']."/".$image['filename'];
						?>
						<img 
						class="thumbnail col-md-3 add-image" 
						src="<?php echo $image_url; ?>" 
						title="<?php echo $image['filename']; ?>" 
						data-toggle="modal" 
						data-target="#image_edit_modal" 
						/>

						<?php
					endforeach;
				endif;
				?>
			</div></div>
		</div>
		<div class="form-row">
			<div class="col-md-6">
				<div class="position-relative form-group">
					<label for="exampleCity" class="">	<?php echo l('current_time'); ?></label>
					<span type="text" name="current_time" class="form-control" readonly>
						
					</span>
				</div>
			</div>
			<div class="col-md-6">
				<div class="position-relative form-group">
					<label for="exampleState" class=""><?php echo l('timezone'); ?><span style="color: red;">*</span></label>
					<select name="time_zone" class="form-control">
						<?php
						foreach ($timezones as $timezone => $timezone_name):
							?>
							<option value="<?php echo $timezone; ?>" <?php
							if ($company['time_zone'] == $timezone)
								echo 'selected="selected"';
							else
								echo set_select('time_zone', 'Pacific/Midway'); ?>
							>
							<?php echo $timezone_name ?>
						</option>
						<?php
					endforeach;
					?>
				</select>
			</div>
		</div>
		
	</div>

	<div class="form-row">
		<div class="col-md-6">
			<div class="position-relative form-group">
				<label for="exampleZip" class=""><?php echo l('default_currency'); ?><span style="color: red;">*</span></label>
				<select name="default_currency" class="form-control">
					<?php	foreach ($available_currencies as $available_currency): ?>
						<option 
						value="<?php echo $available_currency['currency_id']; ?>"
						<?php if ($company['default_currency_id'] == $available_currency['currency_id'])
						echo 'selected="selected"';
						?>

						><?php echo $available_currency['currency_name']." (".$available_currency['currency_code'].")"; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="col-md-6">
			<div class="position-relative form-group">
				<label for="exampleCity" class=""><?php echo l('Default Language', true); ?><span style="color: red;">*</span></label>
				<select name="default_language" class="form-control">
					<?php foreach ($languages as $language): ?>
						<option 
						value="<?php echo $language['id']; ?>"
						<?php if ($company['default_language'] == $language['id'])
						echo 'selected="selected"';
						?>

						><?php echo $language['language_name']; ?></option>
					<?php endforeach; ?>
				</select>
				<small><?php echo l('Used for Emails translation'); ?></small>
			</div>

		</div>
	</div>


	<div class="form-row">
		<div class="col-md-6">
			<div class="position-relative form-group">
				<label for="exampleState" class=""><?php echo l('company_name'); ?><span style="color: red;">*</span></label>
				<input type="text" name="company_name" class="form-control" value="<?php 
				if(isset($company)) 
				echo $company['name']; 
				else 
				echo set_value('company_name'); ?>" />
			</div>
		</div>
		<div class="col-md-6">
			<div class="position-relative form-group">
				<label for="exampleZip" class=""><?php echo l('no_of_rooms'); ?><span style="color: red;">*</span></label>
				<input class="form-control" READONLY type="text" name="number_of_rooms" value="<?php 
				if(isset($company)) 
				echo $company['number_of_rooms']; 
				else 
				echo set_value('number_of_rooms'); ?>" />
				(<?php echo l('actually_used'); ?>: <?php echo $actual_number_of_rooms; ?>)
			</div>
		</div>
	</div>

	<div class="form-row">
		<div class="col-md-12">
			<div class="position-relative form-group">
				<label for="exampleAddress" class=""><?php echo l('address'); ?><span style="color: red;">*</span></label>
				<input type="text" name="company_address" class="form-control" value="<?php
				if(isset($company)) 
				echo $company['address']; 
				else
				echo set_value('company_address'); ?>" ?>
			</div>
		</div></div>
		
		<div class="form-row">
			<div class="col-md-4">
				<div class="position-relative form-group">
					<label for="exampleCity" class=""><?php echo l('city'); ?><span style="color: red;">*</span></label>
					<input type="text" name="company_city" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['city']; 
					else
					echo set_value('company_city'); ?>" />
				</div>
			</div>
			<div class="col-md-4">
				<div class="position-relative form-group">
					<label for="exampleState" class=""><?php echo l('region'); ?><span style="color: red;">*</span></label>
					<input type="text" name="company_region" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['region'];
					else
					echo set_value('company_region'); ?>" />
				</div>
			</div>
			<div class="col-md-4">
				<div class="position-relative form-group">
					<label for="exampleZip" class=""><?php echo l('country'); ?><span style="color: red;">*</span></label>
					<input type="text" name="company_country" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['country']; 
					else
					echo set_value('company_country'); ?>" />
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="col-md-6">
				<div class="position-relative form-group">
					<label for="exampleZip" class=""><?php echo l('postal_code'); ?><span style="color: red;">*</span></label>
					<input type="text" name="company_country" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['country']; 
					else
					echo set_value('company_country'); ?>" />	
				</div>
			</div>
			<div class="col-md-6">
				<div class="position-relative form-group">
					<label for="exampleZip" class=""><?php echo l('phone'); ?><span style="color: red;">*</span></label>
					<input type="text" name="company_phone" maxlength="20" class="form-control" value="<?php
					if(isset($company)) 
					echo $company['phone'];
					else
					echo set_value('company_phone'); ?>" />	
				</div>
				
			</div>
		</div>
		<div class="form-row">
			<div class="col-md-6">
				<div class="position-relative form-group">
					<label for="exampleZip" class=""><?php echo l('fax'); ?></label>
					<input type="text" name="company_fax" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['fax']; 
					else
					echo set_value('company_fax'); ?>" />	
				</div>
			</div>
			<div class="col-md-6">
				<div class="position-relative form-group">
					<label for="exampleZip" class=""><?php echo l('email'); ?><span style="color: red;">*</span></label>
					<input type="text" name="company_email" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['email']; 
					else 
					echo set_value('company_email'); ?>" />	
				</div>
				
			</div>
		</div>

		<div class="form-row">
			<div class="col-md-12">
				<div class="position-relative form-group">
					<label for="exampleAddress" class=""><?php echo l('website'); ?></label>
					<input type="text" name="company_website" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['website']; 
					else 
					echo set_value('company_website'); ?>" />
				</div>
			</div>
		</div>
		<button class="mt-2 ml-2 btn btn-primary" ><?php echo l('Update Company Info', true); ?></button>
	</form>
</div>
</div>

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
				<button type="button" class="btn btn-light image_edit_modal_close_btn" data-dismiss="modal"><?php echo l('close'); ?></button>
			</div>
		</div>
	</div>
</div>


<div class="main-card card">
	<div class="card-body">
		
		<form class="" 
		id="basic-form"
        method="post"
		action="<?php echo base_url();?>settings/company/general"
		enctype="multipart/form-data" >
		<div class="form-row">
			<div class="col-md-2">
				<div class="position-relative form-group  image-group" id="<?php echo $company['logo_image_group_id']; ?>">
					<div> <label for="examplelogo11" class=""><?php echo l('company_logo'); ?></label></div>
					<div id="examplelogo11" class="btn btn-primary form-group add-image">
					<?php echo l('add_image'); ?>
				</div>
			</div>
		</div>
		<div class="col-md-10">
			<div class="position-relative form-group logo-image-thumbnails" >
				<?php
				if (isset($logo_images)):
					foreach ($logo_images as $image):
						$image_url = $this->image_url.$company['company_id']."/".$image['filename'];
						?>
						<img 
						class="thumbnail col-md-3 add-image img-0345" 
						src="<?php echo $image_url; ?>" 
						title="<?php echo $image['filename']; ?>"
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
					<label for="time_zone" class=""><?php echo l('timezone'); ?><span style="color: red;">*</span></label>
					<select name="time_zone" id="time_zone" class="form-control">
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
				<label for="default_currency" class=""><?php echo l('default_currency'); ?><span style="color: red;">*</span></label>
				<select name="default_currency" id="default_currency" class="form-control">
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
				<label for="default_language" class=""><?php echo l('Default Language', true); ?><span style="color: red;">*</span></label>
				<select name="default_language" id="default_language" class="form-control">
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
				<label for="company_name" class=""><?php echo l('company_name'); ?><span style="color: red;">*</span></label>
				<input type="text" name="company_name" id="company_name" class="form-control" value="<?php 
				if(isset($company)) 
				echo $company['name']; 
				else 
				echo set_value('company_name'); ?>" />
			</div>
		</div>
		<div class="col-md-6">
			<div class="position-relative form-group">
				<label for="number_of_rooms" class=""><?php echo l('No Of',true).' '.l($this->default_room_plural); ?><span style="color: red;">*</span></label>
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
				<label for="company_address" class=""><?php echo l('address'); ?><span style="color: red;">*</span></label>
				<input type="text" name="company_address" id="company_address" class="form-control" value="<?php
				if(isset($company)) 
				echo $company['address']; 
				else
				echo set_value('company_address'); ?>" ?>
			</div>
		</div></div>
		
		<div class="form-row">
			<div class="col-md-4">
				<div class="position-relative form-group">
					<label for="company_city" class=""><?php echo l('city'); ?><span style="color: red;">*</span></label>
					<input type="text" name="company_city" id="company_city" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['city']; 
					else
					echo set_value('company_city'); ?>" />
				</div>
			</div>
			<div class="col-md-4">
				<div class="position-relative form-group">
					<label for="company_region" class=""><?php echo l('region'); ?><span style="color: red;">*</span></label>
					<input type="text" name="company_region" id="company_region" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['region'];
					else
					echo set_value('company_region'); ?>" />
				</div>
			</div>
			<div class="col-md-4">
				<div class="position-relative form-group">
					<label for="company_country" class=""><?php echo l('country'); ?><span style="color: red;">*</span></label>
					<input type="text" name="company_country" id="company_country" class="form-control" value="<?php 
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
					<label for="company_postal_code" class=""><?php echo l('postal_code'); ?><span style="color: red;">*</span></label>
					<input type="text" name="company_postal_code" id="company_postal_code" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['postal_code']; 
					else
					echo set_value('company_country'); ?>" />	
				</div>
			</div>
			<div class="col-md-6">
				<div class="position-relative form-group">
					<label for="company_phone" class=""><?php echo l('phone'); ?><span style="color: red;">*</span></label>
					<input type="text" id="company_phone"  name="company_phone" maxlength="20" class="form-control" value="<?php
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
					echo set_value('company_fax'); ?>"  />	
				</div>
			</div>
			<div class="col-md-6">
				<div class="position-relative form-group">
					<label for="company_email" class=""><?php echo l('email'); ?><span style="color: red;">*</span></label>
					<input type="text" name="company_email" id="company_email" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['email']; 
					else 
					echo set_value('company_email'); ?>" />	
				</div>
				
			</div>
		</div>

		<div class="form-row">
			<div class="col-md-3">
				<div class="position-relative form-group">
					<label for="exampleAddress" class=""><?php echo l('Website'); ?></label>
					<input type="text" name="company_website" class="form-control" value="<?php 
					if(isset($company)) 
					echo $company['website']; 
					else 
					echo set_value('company_website'); ?>" />
				</div>
			</div>
			<div class="col-md-3">
				<div class="position-relative form-group">
					<label for="company_bussiness_name" class=""><?php echo l('Business Name'); ?></label>
					<input type="text" name="bussiness_name" id="bussiness_name" class="form-control" value="<?php 
					if(isset($company)){
					echo $company['bussiness_name']; 
					} else{
					echo set_value('bussiness_name'); }?>" />
				</div>
			</div>
			<div class="col-md-3">
				<div class="position-relative form-group">
					<label for="company_bussiness_number" class=""><?php echo l('Business/NIFT Number'); ?></label>
					<input type="text" name="bussiness_number" id="bussiness_number" class="form-control" value="<?php 
					if(isset($company)){
					echo $company['bussiness_number']; 
					} else{
					echo set_value('bussiness_number'); }?>" />
				</div>
			</div>
			<div class="col-md-3">
				<div class="position-relative form-group">
					<label for="company_bussiness_fiscal_number" class=""><?php echo l('Fiscal Number'); ?></label>
					<input type="text" name="bussiness_fiscal_number" id="bussiness_fiscal_number" class="form-control" value="<?php 
					if(isset($company)){ 
					echo $company['bussiness_fiscal_number']; 
					}else{
					echo set_value('bussiness_fiscal_number'); }?>" />
				</div>
			</div>
		</div>
		<button class="mt-2 ml-2 btn btn-primary submit" ><?php echo l('Update Company Info', true); ?></button>
	</form>
	
 <!--    <form id="basic-form" action="" method="post">
    <p>
      <label for="name">Name <span>(required, at least 3 characters)</span></label>
      <input id="name" name="name">
    </p>
  <p>
      <label for="age">Your Age <span>(minimum 18)</span></label>
      <input id="age" name="age">
    </p>
    <p>
      <label for="email">E-Mail <span>(required)</span></label>
      <input id="email" name="email">
    </p>
  <p>
    <label for="weight">Weight <span>(required if age over 50)</span></label>
    <input id="weight" name="weight">
    </p>
    <p>
      <input class="submit" type="submit" value="SUBMIT">
    </p>
</form> -->
</div>
</div>

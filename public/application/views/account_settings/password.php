<div class="app-page-title">

	<div class="topnav mb-3">
        <ul>
            <li><a href="<?php echo base_url().'account_settings'?>" class="active"><?php echo l('Password'); ?></a></li>
            <li><a href="<?php echo base_url().'account_settings/company_security'?>"><?php echo l('Security'); ?></a></li>
        </ul>
    </div>


	<div class="page-title-wrapper">
		<div class="page-title-heading">
			<div class="page-title-icon">
				<i class="pe-7s-notebook text-success"></i>
			</div>
			<?php echo l('Password', true); ?>
		</div>
	</div>
</div>

<div class="main-card card">
	<div class="card-body">
		<form 
		class="form-horizontal" 
		method="post" 
		action="<?php echo base_url();?>account_settings/password"
	    enctype="multipart/form-data"
	    autocomplete="off"
		>
		
		<div class="form-group">
			<label for="old_password" class="col-sm-2">
				<?php echo l('Old Password', true); ?>
			</label>
			<div class="col-sm-10">
				<input type="password" id="old_password" name="old_password" class="form-control" autocomplete='false' placeholder="<?php echo l('Old Password', true); ?>">
			</div>
		</div>

		<div class="form-group">
			<label for="new_password" class="col-sm-2">
				<?php echo l('New Password', true); ?>
			</label>
			<div class=" col-sm-10">
				<input type="password" id="new_password" name="new_password" class="form-control" autocomplete=false placeholder="<?php echo l('New Password', true); ?>">
			</div>
		</div>

		<div class="form-group">
			<label for="confirm_new_password" class="col-sm-2">
				<?php echo l('Confirm New Password', true); ?>
			</label>
			<div class=" col-sm-10">
				<input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" autocomplete=false placeholder="<?php echo l('Confirm New Password', true); ?>">
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-4">
			</div>
			<div class=" col-sm-8">
				<button class="btn btn-primary"><?php echo l('Update', true); ?></button>
			</div>
		</div>
		</form>
	</div>
</div>
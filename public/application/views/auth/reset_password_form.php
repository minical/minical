<?php
$new_password = array(
	'name'	=> 'new_password',
	'id'	=> 'new_password',
	'maxlength'	=> $this->config->item('password_max_length', 'tank_auth'),
	'size'	=> 30,
);
$confirm_new_password = array(
	'name'	=> 'confirm_new_password',
	'id'	=> 'confirm_new_password',
	'maxlength'	=> $this->config->item('password_max_length', 'tank_auth'),
	'size' 	=> 30,
);
?>



<form 
	action="<?php echo base_url().$this->uri->uri_string(); ?>" 
	class="
		col-md-push-4
		col-md-4
		col-md-pull-4
		text-center
		form-horizontal
		"
	method="post" 
	accept-charset="utf-8"
	style="
		margin-top:100px;
		margin-bottom:100px;
	"
>		


	<!-- <a href="<?php echo base_url();?>">
            <?php
                $whitelabelinfo = $this->session->userdata('white_label_information');
                if($whitelabelinfo)
                {
                    echo '<img src="'.base_url().'/images/'.$whitelabelinfo['logo'].'">';
                }
                else
                {
                    echo '<img src="'.base_url().'/images/'.$this->config->item('branding_logo').'">';
                }
            ?>
	</a> -->

	<h2 class="form-signin-heading">Set your password</h2>

	<div class="form-group">
		<label for="new_password" class="col-sm-4 control-label">Password</label>
		<div class="col-sm-8">
    		<input class="form-control" title="password" type="password" name="new_password" maxlength="80" >
    		<span style="color: red;">
				<?php //echo form_error($new_password['name']); ?>
				<?php echo isset($errors['blank_new_password']) && $errors['blank_new_password'] ? $errors['blank_new_password'] : ''; ?>
				<?php echo isset($errors['short_new_password']) && $errors['short_new_password'] ? $errors['short_new_password'] : ''; ?>
				<?php echo isset($errors['password_contains_special_characters']) && $errors['password_contains_special_characters'] ? $errors['password_contains_special_characters'] : ''; ?>	
			</span>
    	</div>
	</div>
	<div class="form-group">
		<label for="confirm_new_password" class="col-sm-4 control-label">Confirm Password</label>
		<div class="col-sm-8">
    		<input class="form-control" title="password" type="password" name="confirm_new_password" maxlength="80" >
    		<span style="color: red;">
				<?php //echo form_error($confirm_new_password['name']); ?>
				<?php echo isset($errors['blank_confirm_new_password']) && $errors['blank_confirm_new_password'] ? $errors['blank_confirm_new_password'] : ''; ?>
				<?php echo isset($errors['password_not_match']) && $errors['password_not_match'] ? $errors['password_not_match'] : ''; ?>
				</span>
    	</div>
	</div>
	<div class="form-group">
		<div style="color: red;">
			<?php 
				echo form_error($new_password['name']); 
				echo form_error($confirm_new_password['name']);
			?>
		</div>
		<input class="btn btn-primary btn-block" type="submit" name="submit" value="Set a new password" >
		
	</div>

</form>
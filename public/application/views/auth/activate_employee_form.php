<br/><br/><div id="employee-registration-form">
	<?php
            $whitelabelinfo = $this->session->userdata('white_label_information');
        ?>
    <h1 style="text-align: center;">
	<?php if($whitelabelinfo){ echo ucfirst($whitelabelinfo['name']); }else{ echo 'Minical';} ?> Account Activation
	</h1>
        <p>To activate your <?php if($whitelabelinfo){ echo $whitelabelinfo['name']; }else{ echo 'Minical';} ?> account, you will need to set a password. </p>
	<br />

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

	<?php echo form_open($this->uri->uri_string()); ?>
	<table>
		<tr>
			<td>Your Login:</td>
			<td><b><?php echo $email; ?></b></td>
		</tr>
		<tr></tr>
		<tr>
			<td><?php echo form_label('Password', $new_password['id']); ?></td>
			<td><?php echo form_password($new_password); ?></td>
			<td style="color: red;"><?php echo form_error($new_password['name']); ?><?php echo isset($errors[$new_password['name']])?$errors[$new_password['name']]:''; ?></td>
		</tr>
		<tr>
			<td><?php echo form_label('Confirm Password', $confirm_new_password['id']); ?></td>
			<td><?php echo form_password($confirm_new_password); ?></td>
			<td style="color: red;"><?php echo form_error($confirm_new_password['name']); ?><?php echo isset($errors[$confirm_new_password['name']])?$errors[$confirm_new_password['name']]:''; ?></td>
		</tr>
	</table>
	<br />
	<div>
		By clicking 'I accept' below, you are agreeing to the <a href="<?php echo base_url(); ?>auth/show_terms_of_service"><?php if($whitelabelinfo){ echo $whitelabelinfo['name']; }else{ echo 'Minical';} ?> Terms of Service.</a>
	</div>
	<br />
	<input class="btn btn-success" type="submit" value="I accept" />
	<?php echo form_close(); ?>
</div>
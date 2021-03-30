<?php
$login = array(
	'name'	=> 'login',
	'id'	=> 'login',
	'value' => set_value('login'),
	'maxlength'	=> 80,
	'size'	=> 30,
);
if ($this->config->item('use_username', 'tank_auth')) {
	$login_label = 'Email or login';
} else {
	$login_label = 'Email';
}
?>


<form 
	action="<?php echo base_url();?>auth/forgot_password" 
	class="
		col-md-push-4
		col-md-4
		col-md-pull-4
		text-center
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
                     if($whitelabelinfo){
                        echo '<img src="'.base_url().'/images/'.$whitelabelinfo['logo'].'">';
                    }else{
                        echo '<img src="'.base_url().'/images/'.$this->config->item('branding_logo').'">';
                    }
                 ?>
	</a> -->

	<h2 class="form-signin-heading">Reset your password</h2>
	<div class="form-group">
		<input class="text_assisted_input email form-control" title="email" type="text" name="login" value="email" id="login" maxlength="80" >
	</div>
	<div class="form-group">
		<div style="color: red;">
			<?php 
				echo form_error($login['name']); 
				echo isset($errors[$login['name']])?$errors[$login['name']]:''; 
			?>
		</div>
		<input class="btn btn-primary btn-block" type="submit" name="submit" value="Get a new password" >
	</div>

</form>


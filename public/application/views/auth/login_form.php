<div class="col-md-3"></div>
<div class="col-md-6">
<form

	action="<?php echo base_url();?>auth/login" 
class="text-center"
	method="post" 
	accept-charset="utf-8"
	style="  max-width: 400px;
    margin: auto;"
>

    <a href="<?php echo base_url();?>">
        <?php
        if(isset($whitelabel_detail) && $whitelabel_detail && $whitelabel_detail['logo']) {
            $image_url = $this->image_url . $whitelabel_detail['logo'];
            if (strpos($whitelabel_detail['logo'], '.')) {
                $image_url = base_url(). 'images/' . $whitelabel_detail['logo'];
            }
            echo '<img src="'.$image_url.'" style="max-width: 200px;">';
        }
        ?>
    </a>

    <h2 class="form-signin-heading"><?php  if($whitelabel_detail){  echo ucfirst($whitelabel_detail['name']); }else{echo $this->config->item('branding_name');}?> Login</h2>
	
		<?php $email = '';
            $password = '';

            if(auto_fill_credentials() && current_url() == 'https://demo.minical.io/auth/login'){
	            $email = 'demo@minical.io';
	            $password = '12345';
        	} 
        ?>

        <div style="color: red;">
        	<?php echo isset($errors['incorrect_email']) && $errors['incorrect_email'] ? $errors['incorrect_email'] : ''; ?>
        	<?php echo isset($errors['password']) && $errors['password'] ? $errors['password'] : ''; ?>
        </div>

		<div class="form-group">
			<label for="email" class="sr-only">Email</label>
			<input title="email" class="form-control" type="text" name="login" placeholder="Email" id="Email" maxlength="80" value="<?php echo $email; ?>" required>
		</div>

		<div class="form-group">
			<label for="inputPassword" class="sr-only">Password</label>
			<input type="password" id="password" name="password" class="form-control" placeholder="Password" required value="<?php echo $password; ?>">
		</div>
		<!--
		<div class="checkbox">
			<input type="checkbox" name="remember" value="1" id="remember" style="margin:0;padding:0"  />			
			<label for="remember">Remember me</label>	
		</div>
		-->

		<div class="form-group">
			<input class="btn btn-lg btn-primary btn-block" id="log-in-button" type="submit" name="submit" value="Log In" >
		</div>


		<?php echo anchor('/auth/forgot_password/', 'Forgot Password?'); ?>
		<br/>

		<?php
        if($whitelabel_detail['domain'] != "app.thelobbyboy.com"){
			if(show_registration_link()) {
			 	echo anchor('/auth/register', "Don't have an account? Sign-up with us!"); 
			} 
        }
		?>

        
        <br/>
        <br/>
        <div style="font-size: 13px;"> 
            <?php echo "By logging in, you accept our "; ?>

            <?php if(isset($whitelabel_detail['terms_of_service']) && $whitelabel_detail['terms_of_service']) { ?>
		        <a href="<?php echo $whitelabel_detail['terms_of_service']; ?>" target="_blank">Terms of Use</a>
		    <?php } else { ?>
		        <a href="<?php echo base_url();?>auth/show_terms_of_service" target="_blank">Terms Of Use</a>
		    <?php } ?>
		    <?php echo " and " ?>

		    <?php if(isset($whitelabel_detail['privacy_policy']) && $whitelabel_detail['privacy_policy']) { ?>
		        <a href="<?php echo $whitelabel_detail['privacy_policy']; ?>" target="_blank">Privacy Policy</a>
		    <?php } else { ?>
		        <a href="<?php echo base_url();?>auth/show_privacy_policy" target="_blank">Privacy Policy</a>
		    <?php } ?>

        </div>
	<!--
	<a href="https://www.google.com/intl/en/chrome/browser/" style="text-decoration:none;">
		<img src="../images/chrome-logo.gif" /><br/>
		minical.io works the best with Google Chrome. <br/>Click here to download Google Chrome for free<br/>
	</a>	
	-->
</form>
</div>
<div class="col-md-3"></div>
<!-- Google Code for Arrived sign-up page Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 980305060;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "1HKsCPzqpQoQpIm50wM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/980305060/?label=1HKsCPzqpQoQpIm50wM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>



<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-MLXS7DC');</script>
<!-- End Google Tag Manager -->

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src=“https://www.googletagmanager.com/ns.html?id=GTM-MLXS7DC”
                  height=“0" width=“0” style=“display:none;visibility:hidden”></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel"><?php echo l('Your request has been submitted!', true);?></h4>
      </div>
      <div class="modal-body">
        <?php echo l('We are setting up your property. Please wait...', true);?>
      </div>
    </div>
  </div>
</div>

<div class="text-center">
	

	<h2>
		<a href="<?php echo base_url();?>">
		<?php
                     $whitelabelinfo = $this->session->userdata('white_label_information');
                     if($whitelabelinfo){
                        echo '<img src="'.base_url().'/images/'.$whitelabelinfo['logo'].'">';
                    }else{
                        echo '<img src="'.base_url().'/images/'.$this->config->item('branding_logo').'">';
                    }
            ?>
		</a>
		<br/>
                <?php echo l('Sign up with', true);?> <?php if($whitelabelinfo){ echo ucfirst($whitelabelinfo['name']);}else{echo $this->config->item('branding_name');} ?><br/>
		<small><?php echo l('No Credit Card Information required', true);?></small>
	</h2>
</div>

<form 
	action="<?php echo base_url();?>auth/register"
	method="post" 
	accept-charset="utf-8"
	style="
		margin-top:50px;
		margin-bottom:50px;
	"
	class="container"
>		
		
	
	<div class="col-md-3" >
		<h3> <?php echo l('Our demo comes with', true);?></h3>
		
		<div class="list-group">
			<div class="list-group-item">
			    <h4 class="list-group-item-heading"><?php echo l('Personal training', true);?></h4>
			    <p class="list-group-item-text"><?php echo l('Our customer service specialist will get in touch with you and give you a tutorial over the phone', true);?></p>
		  	</div>
		  	<div class="list-group-item">
		  		<h4 class="list-group-item-heading"><?php echo l('14-day trial', true);?></h4>
			    <p class="list-group-item-text"><?php echo l('Try out all features unlocked!', true);?></p>
		  	</div>
		  	<div class="list-group-item">
		  		<h4 class="list-group-item-heading"><?php echo l('Free consultation', true);?></h4>
			    <p class="list-group-item-text"><?php echo l('Ask us questions on how we can help to make the most out of your business!', true);?></p>
		  	</div>
		</div>
		
	
	</div>
	<div class="col-md-6 text-center" >
		<div class="panel panel-success">
			<div class="panel-heading">
				<h3 class="panel-title">
				<?php echo l('Please tell us about you', true);?>
				</h3>
			</div>
			<div class="panel-body">
				<div class="form-group input-group-lg">
					<label for="email" class="sr-only"><?php echo l('Email', true);?>:</label>
					<input name="email" class="form-control" type="text" placeholder="Email" autocomplete="off" value="<?php echo set_value('email');?>" />
				</div>
				<div class="form-group input-group-lg">
					<label for="email" class="sr-only"><?php echo l('Password', true);?>: </label>
					<input name="password" class="form-control" type="password" placeholder="Password" autocomplete="off" value="<?php echo set_value('password');?>" />
				</div>
                <div style="margin-bottom: 15px" class="g-recaptcha" data-sitekey="<?php echo $this->config->item('recaptcha_site_key', 'tank_auth'); ?>"></div>
                <div class="form-group input-group-lg">
					<input 
						type="submit" 
						class="btn btn-lg btn-success btn-block"  
						data-toggle="modal" 
						data-target="#myModal" 
						data-controls-modal="#myModal" 
					   	data-backdrop="static" 
					   	data-keyboard="false" 
					   	value="Try Minical now!"
                        name="register_submit_form" />
				</div>
			</div>
		</div>
        
		<?php echo "By creating an account you agree to our "; ?> 
        <a href="https://demo.minical.io/auth/show_terms_of_service"><?php echo l('Terms Of Service', true);?></a>
        <?php echo ", and " ?>
        <a href="https://demo.minical.io/auth/show_privacy_policy"><?php echo l('Privacy Policy', true);?></a>
	</div>

	<div class="col-md-3 text-center" >
		<div class="panel panel-success">
			<div class="panel-body">
				<h3 class="panel-title">
				<?php echo l('A real blessing to our business', true);?>
				</h3>
				<br/>

				<p><?php echo l('The best cloud based PMS I’ve ever used.', true);?> </p>
				<?php echo l('Everything from Reservations to Accounting is simple and makes it easy to train employees to use and input data accurately.', true);?>
				<?php echo l('We get more bookings through our website now than any time before.', true);?>
				<br/><br/>
				<?php echo l('Jay, Owner - Welcome Travelier Motel', true);?>
			</div>
			
		</div>
	</div>
	
	
</form>

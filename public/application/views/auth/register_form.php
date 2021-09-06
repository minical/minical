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
        <h4 class="modal-title" id="myModalLabel">Your request has been submitted!</h4>
      </div>
      <div class="modal-body">
        We are setting up your property. Please wait...
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
                        echo '<img style="max-width: 200px;" src="'.base_url().'/images/'.$whitelabelinfo['logo'].'">';
                    }else{
                        echo '<img src="'.base_url().'/images/'.$this->config->item('branding_logo').'">';
                    }
            ?>
		</a>
		<br/>
                Sign up with <?php if($whitelabelinfo){ echo ucfirst($whitelabelinfo['name']);}else{echo $this->config->item('branding_name');} ?><br/>
		<small>No Credit Card Information required</small>
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
		<h3> Our demo comes with</h3>
		
		<div class="list-group">
			<div class="list-group-item">
			    <h4 class="list-group-item-heading">Personal training</h4>
			    <p class="list-group-item-text">Our customer service specialist will get in touch with you and give you a tutorial over the phone</p>
		  	</div>
		  	<div class="list-group-item">
		  		<h4 class="list-group-item-heading">14-day trial</h4>
			    <p class="list-group-item-text">Try out all features unlocked!</p>
		  	</div>
		  	<div class="list-group-item">
		  		<h4 class="list-group-item-heading">Free consultation</h4>
			    <p class="list-group-item-text">Ask us questions on how we can help to make the most out of your business!</p>
		  	</div>
		</div>
		
	
	</div>
	<div class="col-md-6 text-center" >
		<div class="panel panel-success">
			<div class="panel-heading">
				<h3 class="panel-title">
					Please tell us about you
				</h3>
			</div>
			<div class="panel-body">
				<div class="form-group input-group-lg">
					<label for="email" class="sr-only">Email: </label>
					<input name="email" class="form-control" type="text" placeholder="Email" autocomplete="off" value="<?php echo set_value('email');?>" />
				</div>
				<div class="form-group input-group-lg">
					<label for="email" class="sr-only">Password: </label>
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
        <a href="https://demo.minical.io/terms-of-service/">Terms Of Service</a>
        <?php echo ", and " ?>
        <a href="https://demo.minical.io/privacy">Privacy Policy</a>
	</div>

	<div class="col-md-3 text-center" >
		<div class="panel panel-success">
			<div class="panel-body">
				<h3 class="panel-title">
					A real blessing to our business
				</h3>
				<br/>

				<p>The best cloud based PMS I’ve ever used. </p>
				Everything from Reservations to Accounting is simple and makes it easy to train employees to use and input data accurately.
				We get more bookings through our website now than any time before.
				<br/><br/>
				Jay, Owner - Welcome Travelier Motel
			</div>
			
		</div>
	</div>
	
	
</form>

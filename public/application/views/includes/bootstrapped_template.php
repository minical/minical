<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<?php
 $phptoggle = '';
 $classtoggle ='';
 $phptoggle = isset($_COOKIE['sidebartogglevalue']) ? $_COOKIE['sidebartogglevalue'] : true;
 
 if(isset($phptoggle) &&  $phptoggle === "false"){
 	$classtoggle = 'closed-sidebar-mobile closed-sidebar';
 }
?>

<body class="theme-<?=isset($this->company_ui_theme) ? $this->company_ui_theme : 0;?>">
	<div class="app-container  app-theme-white body-tabs-shadow fixed-header fixed-sidebar <?= $classtoggle;?>">

		<?php 

		$protocol = $this->config->item('server_protocol');

		//Generate css and js file arrays if they don't already exist.
		//This prevents clobbering of the variables caused by multiple loading of this file (ie. iframes).
		if (!isset($css_files))
			$css_files = array();
		if (!isset($js_files))
			$js_files = array();
		
		//Load second submenu navigation css if required (load before menu css and js files for proper order) array_unshift prepends
		if (isset($secondary_submenu))
		{
			array_unshift($css_files, base_url() . auto_version('css/secondary_submenu.css'));
		}
		
		//Load second submenu navigation css if required (load before menu css and js files for proper order) array_unshift prepends
		if (isset($tertiary_submenu))
		{
			array_unshift($css_files, base_url() . auto_version('css/tertiary_submenu.css'));
		}
		
		//Load submenu navigation css if required (load before menu css and js files for proper order) array_unshift prepends
		if (isset($submenu))
		{
			array_unshift($css_files, base_url() . auto_version('css/submenu.css'));
		}

		array_unshift($js_files, base_url() . auto_version('js/template.js'));
		
		//Load menu css and js files if required
		if (isset($menu_on))
		{
			if ($menu_on){
				array_unshift($css_files, base_url() . auto_version('css/menu.css'));
				array_unshift($js_files, base_url() . auto_version('js/menu.js'));

				// add fancybox
				array_unshift($js_files, base_url() . 'js/fancybox/helpers/jquery.fancybox-media.js?v=1.0.6');
				array_unshift($js_files, base_url() . 'js/fancybox/jquery.fancybox.pack.js?v=2.1.5');
				array_unshift($css_files, base_url() . 'js/fancybox/jquery.fancybox.css?v=2.1.5');
				
			}
		}
		
		if(isset($this->company_ui_theme) && $this->company_ui_theme != THEME_DEFAULT) {
            $css_files[] = base_url() . auto_version('css/themes/theme-'.$this->company_ui_theme.'.css');
		}
		
		//Load header
		$files = get_asstes_files($this->module_assets_files, $this->router->fetch_module(), $this->controller_name, $this->function_name);
		
		foreach ($files['css_files'] as $key => $value) {
			$css_files[] = $value;
		}

		foreach ($files['js_files'] as $key => $value) {
			$js_files[] = $value;
		}
		
		$data = array ( 'css_files' => $css_files, 'js_files' => $js_files );
		$this->load->view('includes/bootstrapped_header', $data);
		?>

        <input type="hidden" name="project_url" id="project_url" value="<?php echo base_url(); ?>">

		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MLXS7DC"
			height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
			<!-- End Google Tag Manager (noscript) -->



		<?php if (validation_errors() != "" || isset($recaptcha_required) || isset($captcha_validate) ):
		?>
				<div class="container-fluid">
					<div
						class="alert alert-danger alert-dismissible" role="alert"
						style="
							position:fixed; 
							z-index:1000; 
							top:10%; 
							left:50%;
							width: 70%;
							margin-left: -35%;
							"
					>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  	<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
					  	<span class="sr-only"><?php echo l('Error'); ?>:</span>
	  					<strong><?php echo l('Please correct the below error(s)'); ?>:</strong>
                        <?php if(isset($recaptcha_required) && $recaptcha_required) {
                            echo "<br>".$recaptcha_required;
                        }elseif(isset($captcha_validate) && $captcha_validate){
                             echo "<br>".$captcha_validate;
                        }else{
                           echo validation_errors(); 
                        }  ?>
					</div>
				</div>
		<?php
			endif;
        $flash_warning_message = $this->session->flashdata('flash_warning_message');
        $message = $this->session->flashdata('message');
        $flash_type = $this->session->flashdata('flash_type');
        if ($flash_warning_message || $message):
		?>
				<div class="container-fluid">
					<div
						class="alert alert-<?=$flash_type && $flash_type == 'success' ? "success alert-dismissible " : ( $flash_type == 'danger' ? 'danger' : 'warning alert-dismissible')?>" role="alert"
						style="
							position:fixed; 
							z-index:1000; 
							top:10%; 
							left:50%;
							width: 70%;
							margin-left: -35%;
							"
					>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					  	<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
	  					<?php echo $flash_warning_message ? '<span class="sr-only">Warning:</span>'.$flash_warning_message : ""; ?>
                        <?php echo $flash_warning_message && $message ? "<br/>".$message : $message; ?>
					</div>
				</div>
		<?php
			endif;
		?>

			<?php
			//Load Menu
			
			$company_id = $this->session->userdata('current_company_id');
           
			$data['my_companies'] = $this->Company_model->get_companies($this->user_id);

			$language = $this->session->userdata('language');
			$this->lang->load('menu', $language);
			
			if(current_url() != $protocol . $_SERVER['HTTP_HOST'].'/auth/register' && current_url() != $protocol . $_SERVER['HTTP_HOST'].'/auth/login' && current_url() != $protocol . $_SERVER['HTTP_HOST'].'/auth/forgot_password'){
				// $this->load->view('includes/bootstrapped_menu', $data);
				
			}
			
			?>
			<div class="wrapper clearfix">


		<?php if(current_url() == $protocol . $_SERVER['HTTP_HOST']."/auth/login"){
			?>
			<div class="col-md-12 main" style="padding-top: 100px" >
			<?php $this->load->view($main_content);?>

					<div class="push"></div>
								</div>
							<?php } else { ?>

		
				<div class="app-main">
					<div class="app-header__mobile-menu">
					<div>
                    	<button type="button" id="hamburger-01" class="hamburger hamburger--elastic mobile-toggle-nav">
                        <i class="fa fa-bars" style="font-size: 26px;color: #3f6ad8;"></i>
                    	</button>
                		</div>

            		</div>
            	   
					<?php if(isset($menu_on) && $menu_on && current_url() != $protocol . $_SERVER['HTTP_HOST'].'/auth/register' && current_url() != $protocol . $_SERVER['HTTP_HOST'].'/auth/login' && current_url() != $protocol . $_SERVER['HTTP_HOST'].'/auth/forgot_password'){?>
						<div  >
                            
							<?php 

							$this->load->view('includes/bootstrapped_sidebar');
							?>

						</div>
					<?php }?>

				<?php if(isset($menu_on) && $menu_on){ ?>
                    <?php
                        if(
                        	current_url() != $protocol . $_SERVER['HTTP_HOST'].'/booking' &&
                        	!str_ends_with(current_url(), '/public/booking') &&
                        	current_url() != $protocol . $_SERVER['HTTP_HOST'].'/booking/overview' &&
                        	!str_ends_with(current_url(), '/public/booking/overview')
                        ){
                            ?>
                            <div class="app-main__outer main" ><div class="app-main__inner">
                            <div class="alert alert-danger trial_period" style="margin: 10px;display: none;"></div>
                        <?php
                            }else{
                        ?>
                        <div class="app_outer main" > <div class="app_inner">
                        <div class="alert alert-danger trial_period" style="margin: 10px;display: none;"></div>
                        <?php }?>
                            <?php } else { ?>
                            <div class="main" >
                            	
                                <div class="">
                                    <?php }
                                    $this->load->view($main_content);
                                    ?>
                                </div>
                                <div class="push"></div>
                                <?php
                                //Load footer
                                $this->load->view('includes/bootstrapped_footer');
                                ?>
                            </div>
                        </div>
                <?php } ?>
			</div>
		</div>
	</body>

	<input hidden id="sellingDate" name="sellingDate" value="<?php echo isset($this->selling_date)?$this->selling_date:''; ?>" />
	<div class="modal fade" id="reservation-message" data-backdrop="static"
	data-keyboard="false" style="z-index: 9999;"
	>
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">
					<?php echo l('message'); ?>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</h4>

			</div>
			<div class="modal-body message">

			</div>
			<div class="modal-footer">
				<a class="btn btn-success confirm-customer" flag="ok" href="">OK</a>
			</div>

		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
<div class="modal fade" id="cancel-reservation" data-backdrop="static"
data-keyboard="false" style="z-index: 9999;"
>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title">
				<span class="message-heading"><?php echo l('Confirm cancellation'); ?></span>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</h4>
		</div>
		<div class="modal-body message"></div>
		<div class="modal-footer">
			<a class="btn btn-success confirm-customer" flag="yes" href="javascript:">Yes</a>
			<a class="btn btn-danger confirm-customer" flag="no" href="javascript:">No</a>
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
<div class="modal fade" id="dialog-update-billing" data-backdrop="static" 
   data-keyboard="false" 
   >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">
            <?php echo l('Subscription Notice', true); ?>
            <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
        </h4>        
      </div>
      <div class="modal-body">
          <?php echo l('Dear customer', true); ?>, 
        <p class="message"></p>
      </div>
      <div class="modal-footer">
        <a class="btn btn-success" href="<?php echo base_url(); ?>settings/company/view_subscription"><?php echo l('Update payment details', true); ?></a>
      </div>
      
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php if(isset($this->company_security_status) && $this->company_security_status == 1): ?>

<div class="modal fade" id="dialog-timeout" data-backdrop="static"  data-keyboard="false" >
  <div class="modal-dialog modal-sm">
    <div class="modal-content" style="width: 135%;">
      <div class="modal-header">

      	<?php 
      	if(isset($this->company_lock_time)){
      		if($this->company_lock_time == 1){
      			$lock_timer = $this->company_lock_time . " minute";
      		} else {
      			$lock_timer = $this->company_lock_time . " minutes";
      		}
      	} else {
      		$lock_timer = "10 minutes";
      	}
      	?>
        <p><?php echo l("You've been locked out for being inactive for over ".$lock_timer.".", true); ?></p>
      </div>




    <div class="modal-body">
      	<style type="text/css">
        	.otp-input {
			    display: flex;
			    justify-content: space-between;
			}
			.otp-input input {
			    width: 50px;
			    height: 50px;
			    text-align: center;
			    font-size: 24px;
			    margin: 0 5px;
			    border: 1px solid #ccc;
			    border-radius: 5px;
			}

        </style>

        <?php if($this->company_security_status == 1 && !empty($this->security_data)): ?>
        
	        <label for="otp" style="text-align: center;">Please enter your 2-factor code from your Google Authenticator app to continue using Minical
	        </label>
	        <div class="otp-input">
	            <input type="text" maxlength="1" required>
	            <input type="text" maxlength="1" required>
	            <input type="text" maxlength="1" required>
	            <input type="text" maxlength="1" required>
	            <input type="text" maxlength="1" required>
	            <input type="text" maxlength="1" required>
	        </div>

	    <?php endif; if($this->company_security_status == 1 && empty($this->security_data)): ?>

	    	<label for="otp" style="text-align: center;">2-Factor Authentication setup is required. Please visit this link and scan QR code for security
	    		<a href="<?php echo base_url(); ?>account_settings/company_security">Click Here</a>
	    	</label>

	    	<!-- <a href="<?php echo base_url(); ?>auth/logout" class="logout_btn btn btn-warning" style="float: right;">Logout</a> -->
	    <?php endif; ?>
      	
      </div>
      <div class="modal-footer" style="text-align: center;">

        <?php if($this->company_security_status == 1 && !empty($this->security_data)): ?>
	        <a href="javascript:" class="verify_otp btn btn-primary" style="margin: 14px 120px;">Verify</a>
	   <?php endif; ?>


	    

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php endif; ?>
</html>

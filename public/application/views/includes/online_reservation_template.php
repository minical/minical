<?php if($this->session->userdata('online_language_id')){
            load_translations($this->session->userdata('online_language_id'));
        }
        else{
           if(isset($company_data['default_language']) && $company_data['default_language']) {
            	load_translations($company_data['default_language']);
			}
        } 

$files = get_asstes_files($this->module_assets_files, $this->router->fetch_module(), $this->controller_name, $this->function_name);
		
		foreach ($files['css_files'] as $key => $value) {
			$css_files[] = $value;
		}


        ?>

<?php 
$ifieldKey = getenv('CARDKNOX_IFIELD_KEY');
	echo "<script>
    const ifieldKey = '" . $ifieldKey . "';
        </script>";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
		<title><?php echo $company_data['name']." Online Booking " ?></title>
		
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/bootstrap.min.css"  />	
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/bootstrap-theme.min.css"  />	
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/bootstrap-override.css"  />	
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/smoothness/jquery-ui.min.css" />	
		<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/lightbox.css" />	
		<link rel="shortcut icon" href="<?php echo base_url();?>images/favicon.ico" type="image/x-icon" />
		
		<?php if (isset($css_files)) : foreach ($css_files as $path) : ?>
		<link rel="stylesheet" type="text/css" href="<?php echo $path; ?>" />
		<?php endforeach; ?>
		<?php endif; 
		if(isset($company_data['booking_engine_tracking_code']) && $company_data['booking_engine_tracking_code']) {
		   echo html_entity_decode(html_entity_decode($company_data['booking_engine_tracking_code'] , ENT_COMPAT));
		}
		
		?>
		
	</head>

	<?php
		if (validation_errors() != ""):
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
				  	<span class="sr-only"><?php echo l('Error', true); ?>:</span>
  					<strong><?php echo l('Please correct the below error(s)', true); ?>:</strong>
  					<?php echo validation_errors(); ?>
				</div>
			</div>
	<?php
		endif;
	?>

    <input type="hidden" name="project_url" id="project_url" value="<?php echo base_url(); ?>">

	<div class="">
        <div class="panel panel-default" style="border: none;padding-top: 20px;">
			<div class="panel-body">
				<div class="row">
					<div class="col-md-3">
						<?php echo l('You are now making a reservation at').":";
							// ensure there's http:// in front of URL   
								$website = $company_data['website'];
								if (!preg_match("~^(?:f|ht)tps?://~i", $website)) {
							        $website = "https://" . $website;
							    }
						?>
                        <h4 style="margin-top: 4px;"><a href="<?php echo $website; ?>"><?php echo $company_data['name']; ?></a></h4>
					</div>
					<div class="col-md-6 text-center">
						<div class="row bs-wizard hidden-xs" style="border-bottom:0;">
					        <div class="col-xs-4 bs-wizard-step 
					        <?php 
						        	if ($current_step == 1) 
						        		echo "active";
						        	elseif ($current_step > 1)
						        		echo "complete"; 
						        	else 
						        		echo "disabled";
					        ?>">
                                <div class="text-center bs-wizard-stepnum" style="font-size: 15px;"><?php echo l('Select Dates', 1); ?></div>
					          	<div class="progress"><div class="progress-bar"></div></div>
					          	<div class="bs-wizard-dot"></div>
					          	<div class="bs-wizard-info text-center">
					        	</div>
					        </div>
					        
					        <div class="col-xs-4 bs-wizard-step 
					        <?php 
					        	if ($current_step == 2) 
					        		echo "active";
					        	elseif ($current_step > 2)
					        		echo "complete"; 
					        	else 
					        		echo "disabled";
					        ?>">
								<div class="text-center bs-wizard-stepnum" style="font-size: 15px;"><?php 
								if(isset($company_data['default_room_singular']) && $company_data['default_room_singular'] !='' )
                                {
                                   echo l('Select')." ".$company_data['default_room_singular'];
                                }else{
                                	echo l('Select Room', 1);
                                }
								 ?></div>
								<div class="progress"><div class="progress-bar"></div></div>
								<div class="bs-wizard-dot"></div>
								<div class="bs-wizard-info text-center">
								</div>
					        </div>
					        
					        <div class="col-xs-4 bs-wizard-step 
					        <?php
					        	if ($current_step == 3) 
					        		echo "complete"; 
					        	else 
					        		echo "disabled"; 
					        ?>">
								<div class="text-center bs-wizard-stepnum" style="font-size: 15px;"><?php echo l('Guest information', 1); ?></div>
								<div class="progress"><div class="progress-bar"></div></div>
								<a href="#" class="bs-wizard-dot"></a>
								<div class="bs-wizard-info text-center"></div>
					        </div>
					    </div>
					</div>

					<div class="col-md-2 text-right" style="float:right;margin-bottom:5px">
                        <?php $languages = get_enabled_languages(); ?>
                    	<label for="languageMenu"><strong>Language :</strong></label>
                        <a href='#' id="myLanguageMenu" data-toggle="dropdown" aria-expanded="true">
                            <span style="font-size: 16px;" id="current_language">
                            	<?php echo $this->session->userdata('online_language') ? ucfirst($this->session->userdata('online_language')) : (isset($company_data['default_language']) && $company_data['default_language'] ? array_search($company_data['default_language'], array_column($languages, 'id', 'language_name')) : 'English') ;?></span>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="myLanguageMenu">
                            <?php if(!empty($languages)){
                            foreach ($languages as $key => $value) { ?>
                                <li role="presentation" >
                                    <a class='change_language' role="menuitem" tabindex="-1" lang_data="<?php echo $value['id'].','.strtolower($value['language_name']); ?>" href="javascript:" id="account-link" >
                                        <?php echo $value['language_name']; ?>
                                    </a>
                                </li>
	                            <?php }
	                        } ?>
                        </ul>
                	</div>

					<div class="col-md-3 hidden-xs text-right">
						<address>
							<?php
								echo "<strong> ".l('Contact information').": </strong><br/>";
        							echo ($company_data['address'] != "")?"".$company_data['address']."<br/>":'';
								echo ($company_data['city'] != "")?$company_data['city']:'';
								echo ($company_data['region'] != "")?", ".$company_data['region']."<br/>":'';
								echo ($company_data['postal_code'] != "")?" ".$company_data['postal_code']:'';
								echo "<br/>";
							?>
							<div class="text-muted">
								<i class="glyphicon glyphicon-phone-alt" aria-hidden="true"></i>
								<?php echo $company_data['phone']; ?>
							</div>
						</address>
					</div>
				</div>

				

			</div>
		</div>
	</div>
	<?php
		//Generate css and js file arrays if they don't already exist.
		//This prevents clobbering of the variables caused by multiple loading of this file (ie. iframes).
		if (!isset($css_files)) {
			$css_files = array();
		}
		if (!isset($js_files)) {
			$js_files = array();
		}

		

		foreach ($files['js_files'] as $key => $value) {
			$js_files[] = $value;
		}

		// prx($files);
		
		//Load header
		$data = array ( 'css_files' => $css_files, 'js_files' => $js_files );		
		$this->load->view($main_content, $data);
	?>


	<div class="footer hidden-print col-md-12">
	    <?php
        $whitelabelinfo = $this->session->userdata('white_label_information');
        // Set the partner name
        $partner_name =  isset($whitelabelinfo['name']) ? ucfirst($whitelabelinfo['name']) : $this->config->item('branding_name');
	    $time = time() ;
	    $year= date("Y",$time);
	    echo l('powered by', true);
        
        if(empty($whitelabelinfo) || (isset($whitelabelinfo['name']) && $whitelabelinfo['name'] == 'Minical')) {
            echo " <a target='_blank' href='https://www.minical.io'>Minical</a>";
        } else {
        	if(!empty($whitelabelinfo['website'])) {
        		echo '<a target="_blank" href="'.$whitelabelinfo['website'].'" > '.$partner_name.'</a>';
        	} else {
        		echo (!empty($whitelabelinfo['domain']) ? " <a target='_blank' href='https://".$whitelabelinfo['domain']."'>" : " <a href='#'>").$partner_name."</a>";
        	}
            
        }
		echo ''; //Don't bother with showing copyright until a dashbar is built.
	    ?>
	</div>


	<script type="text/javascript" src="<?php echo base_url();?>js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/lightbox.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/validator.min.js"></script>

	<?php if (isset($js_files)) : foreach ($js_files as $path) : ?>
		<script type="text/javascript" src="<?php echo $path; ?>"></script>
	<?php endforeach; ?>
	<?php endif; ?>		
	<?php $is_current_user_admin = $this->User_model->is_admin($this->user_id);
		echo "<script>var base_url = '".$this->config->item('base_url')."'</script>";
		echo "<script>var is_current_user_admin = '".$is_current_user_admin."'</script>";
		
		//echo "<script>var booking_engine_tracking_code = '".html_entity_decode(html_entity_decode($company_data['booking_engine_tracking_code'] , ENT_COMPAT))."'</script>";
	?>

	<script>
    <!-- Below script used for language translation  -->
    <?php 
        $l = addslashes(json_encode(isset($this->all_translations_data) ? $this->all_translations_data : array()));
    ?>
    <!-- Create global variable for language phrases array -->
    var language_phrases = JSON.parse('<?php echo $l; ?>');
    var nonTranslatedKeys = new Array();
    <!-- Below function return a value of phrase key -->


    function l(phrase_key)
    {
        <?php if($this->user_id === SUPER_ADMIN_USER_ID) { ?>
        if (language_phrases[phrase_key] === undefined) {
            nonTranslatedKeys.push(phrase_key);
        }
        <?php } ?>

        return language_phrases[phrase_key] || (language_phrases[phrase_key.toString().toLowerCase()] || phrase_key);
    }

    // add non-translated-keys to DB 
    <?php if($this->user_id === SUPER_ADMIN_USER_ID) { ?>
    setInterval(function () {
        if (nonTranslatedKeys.length > 0){

            $.ajax({
                type: "POST",
                url: getBaseURL() + 'language_translation/insert_non_translated_keys',
                data: { non_translated_keys: nonTranslatedKeys},
                dataType: "json",
                success: function( data ) {
                    // console.log('data', data);
                }
            });

            nonTranslatedKeys = [];
        }
    }, 10000);
    <?php } ?>

</script>
        
</html>

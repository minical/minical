<!DOCTYPE html>
<html lang="en">	
	<head>
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

		<title><?php echo $company_data['website_title']; ?></title>
		
		<link rel="stylesheet" type="text/css" href="<?php echo getenv('BUILDER_URL');?>pages/css/datepicker.css" />	
		<link rel="stylesheet" type="text/css" href="<?php echo getenv('BUILDER_URL');?>pages/css/bootstrap-theme.min.css" />	
		<link rel="stylesheet" type="text/css" href="<?php echo getenv('BUILDER_URL');?>pages/css/bootstrap.min.css" />	
		<link rel="stylesheet" type="text/css" href="<?php echo getenv('BUILDER_URL');?>pages/css/bootstrap-override.css" />	
    	<link rel="stylesheet" type="text/css" href="<?php echo getenv('BUILDER_URL');?>pages/css/flexslider.css" />	
    	<link rel="stylesheet" type="text/css" href="<?php echo getenv('BUILDER_URL');?>pages/css/lightbox.css" />
    	<link rel="stylesheet" type="text/css" href="<?php echo getenv('BUILDER_URL');?>pages/css/style.css" />	
    	
    	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> 
			<!-- jQuery -->
	    <script src="https://code.jquery.com/jquery-3.6.4.min.js" crossorigin="anonymous"></script>
     
		<?php
			if (isset($css_files)) : 
				foreach ($css_files as $path) : 
		?>
					<link rel="stylesheet" type="text/css" href="<?php echo $path; ?>" />
		<?php
			 	endforeach;
			endif; 
		?>

		<style>
			.bg-image {
				background-color: #<?php echo $company_data['website_theme_color'];?>;
			}
		</style>
        <style>
			.help-block.with-errors {
				color: red;
			}
        </style>
		<!-- <script src='https://www.google.com/recaptcha/api.js'></script> -->
	</head>

	<body>
		<nav class="navbar navbar-default navbar-fixed-top bg-image" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<!-- normal site -->
					<a class="navbar-brand visible-xs" href="<?php echo getenv('BUILDER_URL').'pages/page/'.$company_data['website_uri'].'/index'; ?>">
						<?php echo $company_data['name']; ?>
						<br/>
							<small>
								 <?php echo $company_data['phone']; ?>
							</small>
							
					</a>
					<a class="navbar-brand hidden-xs" href="<?php echo getenv('BUILDER_URL').'pages/page/'.$company_data['website_uri'].'/index'; ?>">
						<div id="logo">
							<?php  
								if (isset($logo_images[0]['filename']))
								{
									echo '<image src=https://'. getenv('AWS_S3_BUCKET') .'.s3.amazonaws.com/' .$company_data['company_id'].'/'.$logo_images[0]['filename'].">";
								}
								else
								{
									echo "<h3>".$company_data['name']."</h3>";	
								}
							?>
							<br/>
							<small>
								 <?php echo $company_data['phone']; ?>
							</small>
						</div>
					</a>
			    </div>

			    <!-- Collect the nav links, forms, and other content for toggling -->
			    <div class="collapse navbar-collapse">
					<ul class="nav navbar-nav navbar-right">
						<li>
							<a href="<?php echo getenv('BUILDER_URL').'pages/page/'.$company_data['website_uri'].'/index' ?>" class="nav-link text-right"><span class="glyphicon glyphicon-home"></span> Home</a>
						</li>
						<?php
							if ($company_data['website_is_taking_online_reservation'] == '1'):
						?>
								<li>
									<a href="https://demo.minical.io/online_reservation/select_dates_and_rooms/<?php echo $company_data['company_id']; ?>" class="nav-link text-right">
									<span class="glyphicon glyphicon-calendar nav-link"></span> Book Online</a>
								</li>
						<?php
							endif;
						?>
						<li>
							<a href="<?php echo getenv('BUILDER_URL').'pages/page/'.$company_data['website_uri'].'/room_types' ?>" class="nav-link text-right">
								<span class="glyphicon glyphicon-th-large"></span> Rooms
							</a>
						</li>
						<li>
							<a href="<?php echo getenv('BUILDER_URL').'pages/page/'.$company_data['website_uri'].'/gallery' ?>" class="nav-link text-right">
								<span class="glyphicon glyphicon-picture"></span> Gallery
							</a>
						</li>
						<li>
							<a href="<?php echo getenv('BUILDER_URL').'pages/page/'.$company_data['website_uri'].'/location' ?>" class="nav-link text-right">
								<span class="glyphicon glyphicon-map-marker"></span> Location
							</a>
						</li>
						<li>
							<a href="#contact" data-toggle="modal" data-target="#myModal" class="myModal open-modal">
								<span class="glyphicon glyphicon-envelope"></span> Contact us
							</a>
							<!-- <a href="#contact" data-bs-toggle="modal" data-bs-target="#myModal"> -->

						</li>
					</ul>
			    </div><!-- /.navbar-collapse -->
			</div>
		</nav>

		<?php if(isset($alert)): ?>
			<div class="alert alert-warning alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h3>
						<?php echo $alert; ?>
				</h3>
			</div>
		<?php endif; ?>

		<!-- Modal -->
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div id="contact_form" data-toggle="validator">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="myModalLabel">Contact us</h4>
						</div>
						<div class="modal-body">
						<form id="myForm">
							<div class="form-group">
								<input type="email" name="from_email" id="email" required="required" class="form-control" placeholder="Your Email" />
								<!-- <div class="help-block with-errors"></div> -->
								<div class="help-block with-errors text-danger" id="email-error" aria-live="assertive" aria-atomic="true"></div>

								<textarea name="message" id="message" class="form-control" rows=3  placeholder="Message" required ></textarea>
								<div class="help-block with-errors text-danger" id="message-error" aria-live="assertive" aria-atomic="true"></div>
								<!-- <div class="g-recaptcha" data-sitekey="6LcUXv8SAAAAAG8PgQs9TXjAJDxoX8lA5lNyxX7f" style="margin-top:10px"></div> -->
							</div>
		                </form>	
						</div>
						<div class="modal-footer">
							<button class="send-message btn btn-primary">Send Message</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
		<script>
		    // var baseUrl = "<?php echo getenv('BUILDER_URL'); ?>";
			// var website_uri = "<?php echo $company_data['website_uri']; ?>";

			// $(".send-message").on("click", function(){

			// 	// var baseUrl = window.location.origin + window.location.pathname;
            //     if ($("[name='from_email']").val() == '' || $("[name='message']").val() == '') {
			// 		alert('Please enter required fields!')
					
			// 		return false;
			// 	} else {
			// 		$.ajax({
			// 			type: "POST",
			// 			url: baseUrl + "pages/send_email/",
			// 			data: { 
			// 				uri: website_uri,
			// 				from_email: $("[name='from_email']").val(),
			// 				message: $("[name='message']").val(),
			// 				// 'g-recaptcha-response': $("[name='g-recaptcha-response']").val()
			// 			},
			// 			dataType: "json",
			// 			success: function( data ) {
			// 				console.log(data);
			// 				$('#myModal').modal('hide')
			// 			}
			// 		});
			//     }

			// });
		</script>
<script>
$(document).ready(function() {
	$('.send-message').click(function() {
		$('#email-error, #message-error').empty(); // Clear previous error messages

		// Validate email and message
		var email = $('#email').val();
		var message = $('#message').val();

		if (!email || !isValidEmail(email)) {
			$('#email-error').text('Please enter a valid email address');
			return false;
		}

		if (!message) {
			$('#message-error').text('Please enter your message');
			return false;
		}
		var baseUrl = "<?php echo getenv('BUILDER_URL'); ?>";
		var website_uri = "<?php echo $company_data['website_uri']; ?>";

		$(".send-message").on("click", function(){

			// var baseUrl = window.location.origin + window.location.pathname;
			if ($("[name='from_email']").val() == '' || $("[name='message']").val() == '') {
				alert('Please enter required fields!')
				
				return false;
			} else {
				$.ajax({
					type: "POST",
					url: baseUrl + "pages/send_email/",
					data: { 
						uri: website_uri,
						from_email: $("[name='from_email']").val(),
						message: $("[name='message']").val(),
						// 'g-recaptcha-response': $("[name='g-recaptcha-response']").val()
					},
					dataType: "json",
					success: function( data ) {
						console.log(data);
						$('#myModal').modal('hide')
					}
				});
			}

		});
		// Your form submission logic goes here
		console.log('Form submitted!');
	});

	function isValidEmail(email) {
		// Basic email validation
		var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return emailRegex.test(email);
	}
});    
</script>

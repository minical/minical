<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php echo $company_name; ?> Booking Confirmation: <?php echo $booking_id;?></title>
		<style type="text/css">			
			h2 {font: bold 16px Arial, Helvetica, sans-serif; margin: 0; padding: 0 0 18px; color: black;}
			h3 {font: 16px Arial, Helvetica, sans-serif; margin: 0; color: black;}
			.group {border: 1px solid black; padding: 10px; }			
			#customer-name, #company-name {font-weight: bold; }
		</style>
	</head>
	<body>
		<div style="max-width: 600px;">
			
			<h2>
				Hi <?php echo $customer_name; ?>,<br/><br/>

				Thanks for booking your stay at <?php echo $company_name; ?>.				

			<h2>
				<?php echo $booking_confirmation_email_header; ?>
			</h2>
				
			</h2>

			<h3>Guest Information</h3>
			<div class="group">
				<span class="label">Name:</span>				
				<span id="customer-name"><?php echo $customer_name; ?></span>
				<br />
				<br />
				<span class="label">Address:</span>
				<br />
				<?php echo $customer_address; ?>, 
				<br />
				<?php echo $customer_city; ?>, <?php echo $customer_region; ?>, <?php echo $customer_country; ?>
				<br />
				<?php if (!empty($customer_postal_code)) : ?>
					<?php echo $customer_postal_code; ?>
					<br />
				<?php endif ?>
				<br />
				
				<span class="label">Phone:</span>
				<?php echo $customer_phone; ?>				
				<br />
				<span class="label">Email:</span>				
				<?php echo $customer_email; ?>
				<br />
			</div>
			<br />
			
			<h3>Reservation Information</h3>
			<div class="group">
				<span class="label">Booking ID:</span>
				<?php echo $booking_id; ?>
				<br />
				<br />
				
				<span class="label">Check-in Date:</span>
				<?php echo $check_in_date; ?>
				<br />
				<span class="label">Check-out Date:</span>				
				<?php echo $check_out_date; ?>
				<br />
				<br />
				
				<span class="label">Rooms: </span>
				<br />							
					<?php echo $room_type; ?>
				<br />
				
				<span class="label">Total*: </span>
				<?php echo number_format($rate, 2, ".", ","); ?>
				<br />
				<br />				
				
			</div>
			<br />					

			<h3>Contact Information</h3>
			<div class="group">
				<span id="company-name"><?php echo $company_name; ?></span>
				<br />
				<br />
				
				<?php echo $company_address; ?>,
				<br />						
				<?php echo $company_city; ?>, <?php echo $company_region; ?>, <?php echo $company_country; ?>
				<br />
				<?php if (!empty($company_postal_code)) : ?>
					<?php echo $company_postal_code; ?>
					<br />
				<?php endif ?>
				<br />
				
				<span class="label">Phone:</span>
				<?php echo $company_phone; ?>
				<br />				
				<?php if (!empty($company_fax)) : ?>
					<span class="label">Fax:</span>
					<?php echo $company_fax; ?>
					<br />
				<?php endif ?>				
				<br />
				
				<span class="label">Email:</span>
				<?php echo $company_email; ?>
				<br />						
				<span class="label">Website:</span> 
				<?php echo $company_website; ?>
				<br />

			</div>
			
			<br />


			<h3>Reservation Policies</h3>
			<div class="group">
			
				<?php echo $reservation_policies; ?>
				<br />
			</div>

			<br />

			<div id="disclaimer">
				<span>*Estimated total before tax and service charges.</span>
			</div>
		</div>
	</body>
</html>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php echo $company_name; ?> <?php echo l('Overbooking Alert', true); ?>: <?php echo $booking_id;?></title>
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
				<?php echo l('Source', true); ?>: 
				<?php 
					switch($source) {
						case SOURCE_ONLINE_WIDGET: 
							echo "Online Booking Engine";
							break;
						case SOURCE_BOOKING_DOT_COM: 
							echo "Booking.com";
							break;
						case SOURCE_EXPEDIA: 
							echo "Expedia";
							break;
						case SOURCE_AGODA: 
							echo "Agoda";
							break;
						case SOURCE_TRIPCONNECT: 
							echo "Trip Connect";
							break;
                        case SOURCE_MYALLOCATOR: 
							echo "Myallocator";
							break;
						case SOURCE_SITEMINDER: 
							echo "Siteminder";
							break;
					} 
				?>
			</h2>

			<?php echo l('You have received an overbooking for following reservation', true); ?> : 
			<br /><br />
			
			<b>	<?php echo l('Guest Name', true); ?>: </b><?php echo $customer_name; ?><br />
			<b> <?php echo l('Booking ID', true); ?>: </b><?php echo $booking_id;?><br />
			<b> <?php echo l('Check-in', true); ?>: </b><?php echo $check_in_date; ?> ,<b> <?php echo l('Check-out', true); ?>: </b><?php echo $check_out_date; ?><br />
			<b> <?php echo l('Room', true); ?>: </b><?php echo $room; ?> (<?php echo $room_type; ?>)
			<br/>
			<br />
			<?php echo l('This overbooking might be caused by following reasons', true); ?>: <br />
			<?php if($no_rooms_available){ ?>
					- <?php echo l('No rooms available for booked dates', true); ?>. <br />
					<?php if($source == SOURCE_BOOKING_DOT_COM){ ?>
					- <?php echo l('Auto-replenish feature is enabled on Booking.com', true); ?> (<?php echo l('Contact your Account manager and ask them to disable this feature', true); ?>.) <br />
			        <?php }
			}
			elseif($allow_non_continuous_bookings != 1){ ?>
				- "<?php echo l('Allow OTA Bookings With Non-continuous Rooms/blocks', true); ?>" <?php echo l('feature is disabled', true); ?>. <br />
			<?php }
			elseif($is_non_continuous_available == 'less_blocks'){ ?>
				- <?php echo l('Allowed Non-Continuous rooms/blocks are less than available non-continuous rooms', true); ?>. <br />
			<?php }
			else { ?>
				- <?php echo l('No rooms available for booked dates', true); ?>. <br />
				<?php if($source == SOURCE_BOOKING_DOT_COM){ ?>
					- <?php echo l('Auto-replenish feature is enabled on Booking.com', true); ?> (<?php echo l('Contact your Account manager and ask them to disable this feature', true); ?>.) <br />
			<?php } } ?>
			<br />
			<br />
			<?php $rt_avail_data = $room_type_availability; ?>
			<?php echo l('Availability is as follows', true); ?> :-
			<table border="1" cellpadding="4" cellspacing="0" style="display: block;overflow-x: auto;white-space: nowrap;">
				<tr>
					<td><?php echo l('Date', true); ?> :</td>
					<?php foreach ($rt_avail_data as $date => $availability) { ?>
						<td>&nbsp;&nbsp;<?php echo $date; ?></td>
					<?php } ?>
				</tr>
				<tr>
					<td><?php echo l('Availability', true); ?> :</td>
					<?php foreach ($rt_avail_data as $date => $availability) { ?>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $availability; ?></td>
					<?php } ?>
				</tr>
			</table>
			<br />
			<br />
            <?php echo l('Visit', true); ?> <a href="https://docs.minical.io/other-resources/overbooking-for-otas" target="_blank">https://docs.minical.io/other-resources/overbooking-for-otas</a> <?php echo l('for more details', true); ?>.
            <br />
			<?php echo l('Please contact support if you have any issues', true); ?>.
			<br />
			<br />
			<?php echo l('Thanks & Regards', true); ?> <br />
			Minical
		</div>
	</body>
</html>
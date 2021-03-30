<?php 
    $flag = 1;
    $permissions = $this->session->userdata('permissions');
    if(isset($permissions) && $permissions != NULL)
    {
        if(in_array('bookings_view_only', $permissions) && !(in_array('access_to_bookings', $permissions)))
        {
            $flag = 0;
        }
    }    
?>
<div class="main-card card">
	<div class="card-body">
<!-- Required for opening booking dialog while viewing [Show all...] pages -->
<div id="booking_dialog"></div>

<div class="header_container">
	<!-- insert search title here. Either "Search Result" or "Reservations, Check-outs, or Out of Orders -->
</div>

<br/>
<div class="panel panel-default">
	<div class="panel-body">
		<form class="form-inline" method="GET" accept-charset="utf-8" action="<?php echo base_url()."booking/show_bookings/"; ?>" />
			<!-- <input name="state" type="hidden" value="<?php echo (isset($state))?$state:''; ?>" /> -->
			<input name="order" type="hidden" value="<?php echo (isset($order))?$order:''; ?>" />
			<input name="order_by" type="hidden" value="<?php echo (isset($order_by))?$order_by:''; ?>" />
			
			<input class="form-control" name='search_query' type='text' size='30' value="<?php echo (isset($search_query))?$search_query:''; ?>"/>
			<input class="form-control" type="submit" value="<?php lang('search_bookings') ?>" />

			<div class="pull-right" style="margin-top:7px">
				<?php echo l('Legend', true); ?>: 
				<span class='legend-color state0' style="border: 1px solid rgb(204, 204, 204);padding:10px"><?php echo l('reservation', true); ?></span>
				<span class='legend-color state1' style="border: 1px solid rgb(204, 204, 204);padding:10px"><?php echo l('checked_in', true); ?></span>
				<span class='legend-color state2' style="border: 1px solid rgb(204, 204, 204);padding:10px"><?php echo l('checked_out', true); ?></span>
				<span class='legend-color state3' style="border: 1px solid rgb(204, 204, 204);padding:10px"><?php echo l('cancelled', true); ?></span>
			</div>
		</form>

	</div>
</div>

<?php 
	echo validation_errors();


	// try to echo a current search query or what state it's showing
?>		

<table class="table table-hover">
	<tr>
        <th class="text-center"><a href="?search_query=<?php echo (isset($search_query))?$search_query:''; ?>&order_by=booking_id&order=<?php echo (isset($order)) && $order == "ASC" ? 'DESC':'ASC'; ?>"><?php echo l("booking", true).' '.l('ID', true); ?> </a></th>
		<th class="text-center"><a href="?search_query=<?php echo (isset($search_query))?$search_query:''; ?>&order_by=room_name&order=<?php echo (isset($order)) && $order == "ASC" ? 'DESC':'ASC'; ?>"><?php echo l("room", true); ?></a></th>
		<th class="text-center"><a href="?search_query=<?php echo (isset($search_query))?$search_query:''; ?>&order_by=check_in_date&order=<?php echo (isset($order)) && $order == "ASC" ? 'DESC':'ASC'; ?>"><?php echo l('check_in', true); ?></a></th>
		<th class="text-center"><a href="?search_query=<?php echo (isset($search_query))?$search_query:''; ?>&order_by=check_out_date&order=<?php echo (isset($order)) && $order == "ASC" ? 'DESC':'ASC'; ?>"><?php echo l('check_out', true); ?></a></th>
		<th><a href="?search_query=<?php echo (isset($search_query))?$search_query:''; ?>&order_by=customer_name&order=<?php echo (isset($order)) && $order == "ASC" ? 'DESC':'ASC'; ?>"><?php echo l('customer', true); ?></a></th>
		
	</tr>		
	<?php 
		if (isset($bookings) && $bookings):
			foreach ($bookings as $booking) : 
				if (isset($booking['booking_id'])):
	
	?>
					<tr class='booking <?php 
						echo "state".$booking['state'];
					?>' data-booking-id='<?php echo $booking['booking_id']; ?>'

					<?php 
						if (isset($booking['color']))
						{
							if ($booking['color'])
							{
								echo " style='background-color: #".$booking['color'].";' "; 
							}
						}
					?>

					>
						
						<td class="text-center"><?php echo $booking['booking_id']; ?></td>
						<td class="text-center"><?php echo $booking['room_name'] ? $booking['room_name'] : 'Not Assigned'; ?></td>
						<td class="text-center"><?php echo $booking['check_in_date']; ?></td>
						<td class="text-center"><?php echo $booking['check_out_date']; ?></td>
						<td><?php 
							$customer_name = isset($booking['customer_name'])?$booking['customer_name']:'';
							$guest_name = isset($booking['guest_name'])?$booking['guest_name']:'';
							$number_of_staying_guests = isset($booking['guest_count'])?$booking['guest_count']:'';
							
							$this->load->helper('customer_name');
							echo get_neatly_formatted_customer_names($customer_name, $guest_name, $number_of_staying_guests);
						?></td>
					</tr>
	<?php 
				endif;
			endforeach;
			else: ?>
				<tr class='booking' data-booking-id=''>
						<td></td>
						<td></td>
						<td class="text-center">
            <h3>No bookings found</h3>
						</td>
						<td></td>
						<td></td>
					</tr>


<?php 
endif;
	?>
</table>

<div class="panel panel-default">
	<div class="panel-body text-center h4">
		<?php echo $this->pagination->create_links(); ?>
	</div>
</div>
</div></div>
<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<script>
	$(document).ready(function(){		
        var flag = <?php echo $flag; ?>;        
        innGrid.hasBookingPermission = <?php echo $flag; ?>; 
	});
</script>


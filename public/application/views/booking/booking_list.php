<style>
	.rName{
		word-wrap: break-word;
		font-size: 13px;
	}
</style>

<h4 id='todays-events-title'><?php echo l('today'); ?></h4>
<div class='btn-xs btn-default' id='hide-button' data-text="<?php echo $is_reminder_hidden ? 'show' : 'hide'; ?>"><?php
	if ($is_reminder_hidden)
	{
		echo l('show');
	}
	else
	{
		echo l('hide');
	}
?></div>

<?php
	$this->load->helper('date_format');
	$this->load->helper('customer_name');
	
	//Generate tables for each booking type
	$booking_types = Array(UNCONFIRMED_RESERVATION, RESERVATION, INHOUSE, CHECKOUT, OUT_OF_ORDER);
	foreach($booking_types as $booking_type):
		switch($booking_type) {
			case UNCONFIRMED_RESERVATION: 
				$bookings = (isset($unconfirmed_reservations) && $unconfirmed_reservations) ? $unconfirmed_reservations : array(); 
				$booking_type_name = l('unconfirmed_reservations');
				break;
			case RESERVATION:
				$bookings = (isset($checking_in_today) && $checking_in_today) ? $checking_in_today : array();
				$booking_type_name = l('checking_in_today');
				break;
			case INHOUSE: 
				$bookings = (isset($staying_and_paying) && $staying_and_paying) ? $staying_and_paying : array(); 
				$booking_type_name = l('staying_and_paying');
				break;
			case CHECKOUT: 
				$bookings = (isset($checked_out_today) && $checked_out_today) ? $checked_out_today : array(); 
				$booking_type_name = l('checked_out_today');
				break;
			
			case OUT_OF_ORDER: 
				$bookings = (isset($out_of_order_bookings) && $out_of_order_bookings) ? $out_of_order_bookings : array();
				$booking_type_name = l('out_of_orders');
				break;
			
		}	



		if (sizeof($bookings) > 0) :
					
?>

	
		<div class="booking_table" id="booking_table<?php echo $booking_type; ?>" <?php
			if ($is_reminder_hidden)
			{
				echo "style='display:none;'";
			}
		?>>
			<div id="tableID"><?php echo $booking_type_name." (".sizeof($bookings).")<br/>"; ?></div>
			<div>
				<?php
					// Display header only if there are bookings
						if ($bookings != null) :
							foreach($bookings as $booking): 
								$warning_array = array();					
								$warning = "";
				?>
									
							

								<div class="booking_tr row booking <?php
                                        
                                        $balance = 0;
                                        if($this->is_total_balance_include_forecast == 1)
                                        {
                                            $balance = isset($booking['balance']) ? round(floatval($booking['balance']), 2) : 0;
                                        }
                                        else
                                        {
                                            $balance = isset($booking['balance_without_forecast']) ? round(floatval($booking['balance_without_forecast']), 2) : 0;
                                        }
                                        
										if ($booking['check_in_date'] == $booking['check_out_date'])
										{
											// TODO: use language pack (i.e. l('warning_short_stay'))
											$warning_array[] = "This block's check-in and check-out dates are the same";
										}
										if ($booking['state'] == UNCONFIRMED_RESERVATION)
										{
											$warning_array[] = "This is an unconfirmed reservation. New reservation or new walk-in can be made on top of this block";
										}
										if ($selling_date >= date('Y-m-d', strtotime($booking['check_out_date'])) &&
                                            $selling_date > date('Y-m-d', strtotime($booking['check_in_date'])) && $booking_type == INHOUSE)
										{
											$warning_array[] = "This guest is suppose to check out";
										}
										if ($booking['state'] == CHECKOUT && $balance > 0)
										{
											$warning_array[] = "This guest has an outstanding balance";
										}
										
										if (count($warning_array) > 0)
										{
											$warning = implode(", and ", $warning_array);
											echo " warning-popover border-red";
										}
										
										echo " state".$booking['state'];	

									?>"

									<?php 
										if (isset($booking['color']))
										{
											if ($booking['color'])
											{
												echo "\" style='background-color: #".$booking['color'].";' "; 
											}
										}
										else
										{
											echo "\"";
										}
									?>
									data-content="<?php
												
													if ($warning != "")
													{
														echo $warning;
													}
												?>" 
									rel="popover" 
									data-placement="left" 
									data-booking-id="<?php echo $booking['booking_id']; ?>"  onclick="" 

								>
									<div class="row-container">
										
										<?php
											if ($booking_type == OUT_OF_ORDER):
										?>
												<div class="col-sm-1 text-left" name="<?php echo $booking['room_name']; // Only reason I have this here is to make selenium testing easier ?>">							
													<strong>
														<?php 
															echo $booking['room_name'] ? $booking['room_name'] : l('Not Assigned', true);
														?>
													</strong>
												</div>
												<div class="col-sm-2 text-center">
													<?php echo get_date_div($booking['check_in_date']); ?>
												</div>
												<div class="col-sm-2 text-center">
													<?php echo get_date_div($booking['check_out_date']); ?>
												</div>
												<div class='col-sm-7 out-of-order-booking-notes-div'>
													<?php
														echo $booking['booking_notes'];
													?>
												</div>
									
										<?php
											else:
										?>
												<div class="col-sm-2 rName text-left" name="<?php echo $booking['room_name']; // Only reason I have this here is to make selenium testing easier ?>">
													<strong>
														<?php
														// showing combine room name.
															echo str_replace(',','&rarr;',$booking['room_name'] ? $booking['room_name'] : l('Not Assigned', true)); // &rarr; is the ascii code of arrow symbol
														?>
													</strong>
												</div>
												<div class="col-sm-3 text-center" style="padding: 0;">
													<small>
														<?php echo get_date_div($booking['check_in_date'])." to<br/>".get_date_div($booking['check_out_date']); ?>
													</small>
												</div>
												<div class="col-sm-5 text-left">
													<?php 
														$customer_name = isset($booking['customer_name'])?$booking['customer_name']:'';
														$guest_name = isset($booking['guest_name'])?$booking['guest_name']:'';
														$number_of_staying_guests = isset($booking['guest_count'])?$booking['guest_count']:'';
														
														
														echo get_neatly_formatted_customer_names($customer_name, $guest_name, $number_of_staying_guests);
													
														$balance = ($balance <= 0.01 && $balance >= -0.01)?0:$balance;
													?>
												</div>
												<div class="col-sm-2 text-right">
													<div 
														class ="<?php 
																	if ($balance < 0)
																		echo "btn-primary";
																	elseif ($balance == 0)
																		echo "btn-success";
                                                                    else
																		echo "btn-danger";
																?> btn-sm pull-right invoice-button" 
														title="<?php echo $booking['booking_id']; ?>"  
													>
														<?php 
															echo number_format($balance, 2, ".", ","); 
														?>
													</div>
												</div>
											
												
											

										<?php
											endif;
										?>
										
										
										<div class="clear"></div>	<!-- Mandatory in order to properly adjust the height of the booking-tr -->
									</div>
								</div><!-- /id=div-1-padding /id=div-1 -->

					
				<?php 
							endforeach;
						endif;
					endif;
				?>
			</div>
			<div class="clear"></div>	<!-- Mandatory in order to properly adjust the height of the booking-table -->
		</div>
			
<?php endforeach; ?>	

<div class="booking_table text-center" <?php
			if ($is_reminder_hidden)
			{
				echo "style='display:none;'";
			}
		?>>
	<button 
	data-toggle="modal"
	data-target="#csv-export-modal"
	class="btn btn-default">
		<?php echo l('export_booking_to_csv_file'); ?>
	</button>
</div>

<!--
<a href="<?php echo base_url()."booking/download_csv_export/$selling_date.csv"; ?>"><?php echo l('export_todays_highlights'); ?></a>
-->
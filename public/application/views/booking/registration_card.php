<div class="row">
	<div class="panel panel-default">
		<div class="panel-header h3 text-center">
			<?php echo $company['name']; ?> Registration Card
			<br/>
			<small>Confirmation Number: <?php echo $booking['booking_id']; ?></small>
		</div>
		<div class="panel-body">
			<div class="col-xs-6">
				<strong>Customer information</strong>
				<table class="col-xs-12">
					<ul class="list-unstyled">
						<li>
							Customer Name: <?php echo isset($customer['customer_name'])?$customer['customer_name']:''; ?>
						</li>
						<li>
                            Phone: <?php echo isset($customer['phone'])?$customer['phone']:''; ?>
						</li>
						<li>
                            Address: <?php echo isset($customer['address'])?$customer['address']:''; ?>
						</li>
						<li>
                            Email: <?php echo isset($customer['email'])?$customer['email']:''; ?>
						</li>
						<li>
							Other guests: <?php 
								$staying_customers = Array();
								foreach($booking['staying_customers'] as $staying_customer)
								{
									$staying_customers[] = $staying_customer['customer_name'];
								}
								echo implode(",", $staying_customers);
							?>
						</li>
						<?php
							if (isset($customer_fields))
							{
								foreach ($customer_fields as $field)
								{
									echo "<li>";
									echo $field['name'].": ";
									if (isset($customer['customer_fields'][$field['id']]))
									{
										echo $customer['customer_fields'][$field['id']];
									}
									echo "</li>";
								}
							}
							

						?>
					</ul>
				</table>
			</div>
			<div class="col-xs-6 text-right">
				<strong>Booking Information</strong>
				<ul class="list-unstyled">
					<li>
						Room: <?php echo $booking['room_name']; ?>
					</li>
					<li>
						Room Type: <?php echo $booking['room_type_name']; ?>
					</li>
					<li>
						Check-in Date: <?php echo $booking['check_in_date']; ?>
					</li>
					<li>
						Check-out Date: <?php echo $booking['check_out_date']; ?>
					</li>
					<li>
						Number of Adults: <?php echo $booking['adult_count']; ?>
					</li>
					<li>
						Number of Children: <?php echo $booking['children_count']; ?>
					</li>
					<li>
						Rate*: <?php echo $booking['rate']; ?>
					</li>
				</ul>
			</div>
			
		</div>

		<div class="jumbotron small">
			<?php 
				echo str_replace("\n", "<br/>", $check_in_policies); 
			?>
			<br/><br/>
			<br/><br/>
			Signature(s): _______________________________
			<br/><br/>
			<br/><br/>
			Date: _______________________________
		</div>


	</div>
		
		
</div>

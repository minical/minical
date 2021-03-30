<body>
	<?php if (isset($error_count)) if ($error_count > 0): ?>
	
	<div class="validation-errors">
			<?php echo validation_errors(); ?>
	</div>	
	<?php endif; ?>
	<h2><?php echo l('Customer Log', true); ?></h2>
	<h2>
        <a href="<?php echo base_url()."customer/history/".$customer_id; ?>" class="customer_tr" name="<?php echo $customer_id; ?>">
		<?php 
			$customer_name = (isset($customer_data['customer_name']))?$customer_data['customer_name']:"";
			echo $customer_name." (Customer ID #: ".$customer_data['customer_id'].")";
		?>
		</a>
	</h2>
			
	<div id="customer-edit-container">
		<form method="post" action="<?php echo base_url()."customer/show_customer_log/".$customer_id; ?>" autocomplete="off">
			<input id="customer_id" name="customer_id" type="hidden" value="<?php echo $customer_id; ?>" /> 
			<table id="customer-log-table">
				<thead>
					<tr>
						<th class="text-align-left" width=170>
							<?php echo l('Date', true).' & '.l('Time', true); ?> 
						</th>
						<th class="text-align-left" width=140>
							<?php echo l('Employee', true); ?>
						</th>
						<th class="text-align-left" width=130>
							<?php echo l('Type', true); ?>
						</th>
						<th class="text-align-left">
							<?php echo l('Log', true); ?>
						</th>
					</tr>
				<thead>
				<tbody>
					<?php
						foreach ($rows as $r):
					?>
							<tr>
								<td class="text-align-left" >
									<?php 
										echo convert_to_local_time(new DateTime($r->date_time, new DateTimeZone('UTC')), $time_zone)->format("y-m-d h:i A");													
									?>
								</td>										
								<td class="text-align-left" >
									<?php echo $r->first_name." ".$r->last_name; ?>
								</td>
								<td class="text-align-left" >
									<?php
										if ($r->is_entered_by_user)
											echo l("User entry", true);
										else
											echo l("Auto-generated", true);
									?>
								</td>
								<td style="word-wrap: break-word">
									<?php echo $r->log; ?>
								</td>
							</tr>
					<?php
						endforeach;
					?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan=4 class="text-align-center">
							<textarea id="log" name="log" autocomplete="off"><?php echo set_value('log', (isset($log))?$log:''); ?></textarea>							
							<button type='submit' name='submit' value='add'><?php echo l('Add Log', true); ?></button>						
							<br/>
							<span id="counter"></span> <?php echo l('characters left', true); ?>
						</td>
						
					</tr>
				</tfoot>
			</table>
		</form>
	</div>
	
</body>
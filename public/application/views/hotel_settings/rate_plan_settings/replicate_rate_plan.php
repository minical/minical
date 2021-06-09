



	
        <tr class="rate-plan-row-<?php echo $rate_plan['rate_plan_id']; ?>" id="<?php echo $rate_plan['rate_plan_id']; ?>"  >
            <td>
			<input name="rate-plan-name" class="form-control" type="text" value="<?php echo $rate_plan['rate_plan_name']. " (Copy)";  ?>" readonly/>
            </td>
			<td>
<?php 
								foreach($room_types as $room_type)
								{
							
									if ($rate_plan['room_type_id'] == $room_type['id'])
	
									echo $room_type['name'];
								}
							?>
            </td>
			<td>

			<?php 
								foreach($charge_types as $charge_type)
								{
							
									if ($rate_plan['charge_type_id'] == $charge_type['id'])
									{
										echo $charge_type['name'];
									}
									
								}
							?>
			</td>
			<td>
			<?php 
								foreach($currencies as $currency)
								{
									
									if ($rate_plan['currency_id'] == $currency['currency_id'])
									{
										echo $currency['currency_code'].'('.$currency['currency_name'].')';
									}
								
								}
							?>
			
			</td>
			<td>
			 <?php if ($rate_plan['is_shown_in_online_booking_engine'] == '1'){echo "yes";}else{
				 echo "No";
			 }  ?>
			</td>
			<td><div class="btn-group pull-right" role="group">
			<button	class="btn btn-default edit-new-rate-plan" data-room_type_id="<?=$rate_plan['room_type_id'];?>" id='<?php echo $rate_plan['rate_plan_id']?>'> <?php echo l('Edit Rate Plan', true); ?></button>
							<a 
								href="<?php echo base_url()."settings/rates/edit_rates/".$rate_plan['rate_plan_id']; ?>" 
								class="btn btn-success">
								<?php echo l('Edit Rate', true); ?>
							</a>
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<?php echo l('More', true); ?> 
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu pull-right other-actions" role="menu">
								<li>
									<a href=# class="replicate-rate-plan-button" id=<?php echo $rate_plan['rate_plan_id'];?>>
										<?php echo l('Replicate (Make a copy)', true); ?>
									</a>
								<li>
									<a href=# class="delete-rate-plan-button" id=<?php echo $rate_plan['rate_plan_id'];?>><?php echo l('Delete', true); ?></a>
								</li>

							</ul>
						</div>
						</td>
			</tr>
			
	
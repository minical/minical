
<!-- Modal -->
<div class="modal fade" id="room_notes_modal" tabindex="-1" role="dialog" aria-labelledby="label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo l('Edit', true).' '.l($this->default_room_singular).' '.l('Notes', true); ?></h4>
			</div>
			<div class="modal-body">
				<textarea id="room-notes" class="form-control" rows=5>
				</textarea>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="save-room-notes-button"><?php echo l('Save changes', true); ?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo l('Close', true); ?></button>
			</div>
		</div>
	</div>
</div>


<div class="page-header">
	<div class="h3">
		<?php
			if ($form_type == "edit"):
		?>
				<?php echo l('Customer Profile', true); ?>
		<?php
			elseif ($form_type == "new"):
		?>
				<?php echo l('Create New Customer', true); ?>
		<?php
			endif;
		?>
	</div>
</div>

<div class="container">
	<div class="col-md-6">
		<form method="post" action="
			<?php
				if ($form_type == "edit")
				{
					echo base_url()."customer/show_profile/".$customer_id;	
				}
				elseif ($form_type == "new")
				{
					echo base_url()."customer/create_new_customer";	
				}
			?>" 
			autocomplete="off" class="form-horizontal">
			
			<?php
					if ($form_type == "edit"):
			?>
				<input name="customer-id" type="hidden" value="<?php echo $customer_id; ?>" />
			<?php
				endif;
			?>
			
					<div class="form-group">
						<label class="col-sm-4 control-label"><?php echo l('Customer Name', true); ?></label>
						<div class="col-sm-8">
							<input class="form-control" name="customer-name" value="<?php echo set_value('customer_name', (isset($customer_name))?$customer_name:''); ?>" autocomplete="off" >
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-4 control-label"><?php echo l('Customer Type', true); ?></label>
						<div class="col-sm-8">
							<select name="customer-type" class="form-control">
								<?php
									if (!isset($customer_type))
									{
										$customer_type = 'PERSON';
									}
								?>
								<option value="PERSON" <?php if ($customer_type == 'PERSON') echo "selected"; ?> ><?php echo l('PERSON', true); ?></option>
								<option value="COMPANY" <?php if ($customer_type == 'COMPANY') echo "selected"; ?> ><?php echo l('COMPANY', true); ?></option>
							</select>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-4 control-label"><?php echo l('Phone', true); ?></label>
						<div class="col-sm-8">
							<input class="form-control" name="phone" type="text" maxlength="15" value="<?php echo set_value('customer_phone', (isset($phone))?$phone:''); ?>" autocomplete="off" />
						</div>
					</div>
						
					<div class="form-group">
						<label class="col-sm-4 control-label"><?php echo l('Fax', true); ?></label>
						<div class="col-sm-8">
							<input class="form-control" name="fax" type="text" maxlength="15" value="<?php echo set_value('customer_phone', (isset($fax))?$fax:''); ?>" autocomplete="off" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-4 control-label"><?php echo l('Email', true); ?></label>
						<div class="col-sm-8">
							<input class="form-control" name="email" type="email" maxlength="45" size="35" value="<?php echo set_value('email', (isset($email))?$email:''); ?>" autocomplete="off" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-4 control-label"><?php echo l('Address', true); ?></label>
						<div class="col-sm-8">
							<textarea class="form-control" rows=3 name="address" type="text" autocomplete="off" /><?php echo set_value('address', (isset($address))?$address:''); ?></textarea>
						</div>
					</div>
						
					<div class="form-group">
						<label class="col-sm-4 control-label"><?php echo l('City', true); ?></label>
						<div class="col-sm-8">
							<input class="form-control" name="city" type="text" autocomplete="off" value="<?php echo set_value('city', (isset($city))?$city:''); ?>" />
						</div>
					</div>
						
					<div class="form-group">
						<label class="col-sm-4 control-label"><?php echo l('State / Province', true); ?></label>
						<div class="col-sm-8">
							<input class="form-control" name="region" type="text" autocomplete="off" value="<?php echo set_value('region', (isset($region))?$region:''); ?>" />
						</div>
					</div>
						
					<div class="form-group">
						<label class="col-sm-4 control-label"><?php echo l('Country', true); ?></label>
						<div class="col-sm-8">
							<input class="form-control" name="country" type="text" autocomplete="off" value="<?php echo set_value('country', (isset($country))?$country:''); ?>" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-4 control-label"><?php echo l('Zip / Postal Code', true); ?></label>
						<div class="col-sm-8">
							<input class="form-control" name="postal-code" type="text" autocomplete="off" value="<?php echo set_value('postal-code', (isset($postal_code))?$postal_code:''); ?>" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-4 control-label"><?php echo l('Customer notes', true); ?></label>
						<div class="col-sm-8">
							<textarea class="form-control" rows=6 name="customer-notes" autocomplete="off"><?php echo set_value('customer-notes', (isset($customer_notes))?$customer_notes:''); ?></textarea>
						</div>
					</div>

				<?php
					if ($form_type == "edit"):
				?>
						<button type='submit' class="btn btn-primary" name='submit' value='save'><?php echo l('Update Information', true); ?></button>
						<a href="<?php echo base_url()."customer/history/".$customer_id; ?>" class="btn btn-default"><?php echo l('Cancel', true); ?></a>
				<?php
					elseif ($form_type == "new"):
				?>
						<button type='submit' class="btn btn-primary" name='submit' value='save'><?php echo l('Create Customer', true); ?></button>
						<a href="<?php echo base_url()."customer/"; ?>" class="btn btn-default" ><?php echo l('Cancel', true); ?></a>
				<?php
					endif;
				?>
		</form>
	</div>
	<div class="col-md-6">
		<?php
			if ($form_type == "edit"):
		?>
				
				<div id="customer-comments" class="print-hidden">
			
					<b><?php echo l('Customer Log', true); ?></b><br/>
					<?php if (sizeof($rows) > 0) : ?>
						<table id="customer-log-table">
							
							<tbody>
								<?php
									foreach ($rows as $r):
								?>
									<tr>
										<td class="customer-log-content">
                                            <?php
												$this->load->helper('string_helper');
												
												echo convert_to_local_time(new DateTime($r->date_time, new DateTimeZone('UTC')), $time_zone)->format("Y-m-d");													
												echo " by ".$r->first_name." - ";												
												echo string_capper($r->log, 40);
											?>
										</td>
										
									</tr>
								<?php
									endforeach;
								?>
							</tbody>
						</table>
					<?php else: ?>
						<?php echo l('No comments have been created yet.', true); ?>
					<?php endif; ?>

				</div>
			<a href='<?php echo base_url()."customer/show_customer_log/".$customer_id; ?>' class='btn btn-success'><?php echo l('Write a log', true); ?></a>
		<?php
			endif;
		?>

		

	</div>
</div>
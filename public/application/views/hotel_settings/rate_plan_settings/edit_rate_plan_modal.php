<div class="panel panel-default rate-plan-div" id="<?php echo $rate_plan['rate_plan_id']; ?>">
			<div class="panel-body form-horizontal">
				
				<div class="alert alert-success hidden updated-message" role="alert"><?php echo l('Updated', true); ?>!</div>

				<div class="form-group">
					<label class="col-sm-3 control-label">
						<?php echo l('Rate Plan Name', true); ?>
					</label>
					<div class="col-sm-6">
						<input name="rate-plan-name" class="form-control" type="text" value="<?php echo $rate_plan['rate_plan_name']; ?>"/>
					</div>
				
				</div>
				
				<div class="form-group">
					<label class="col-sm-3 control-label">
						<?php echo l('Settings', true); ?>
					</label>
					<div class="col-sm-3">
						<select name="room-type-id" class="form-control">
							<option>--<?php echo l('Select Room Type', true); ?>--</option>
							<?php 
								foreach($room_types as $room_type)
								{
									echo "<option value='".$room_type['id']."' ";
									if ($rate_plan['room_type_id'] == $room_type['id'])
									{
										echo " SELECTED=SELECTED ";
									}
									echo ">".$room_type['name']."</option>\n";
								}
							?>
						</select>
					</div>
					<div class="col-sm-3">
						<select name="charge-type-id" class="form-control">
                            <?php if (!$this->allow_free_bookings){
                                echo '<option>--'.l('Select Charge Type', true).'--</option>';
                            } ?>
							<?php 
								foreach($charge_types as $charge_type)
								{
									echo "<option value='".$charge_type['id']."' ";
									if ($rate_plan['charge_type_id'] == $charge_type['id'])
									{
										echo " SELECTED=SELECTED ";
									}
									echo ">".$charge_type['name']."</option>\n";
								}
							?>
                            <?php if ($this->allow_free_bookings){
                                echo '<option '.($rate_plan['charge_type_id'] ? '' : 'SELECTED=SELECTED').' value="">'.l('None (can be booked for free)', true).'</option>';
                            } ?>
						</select>
					</div>
				
					<div class="col-sm-3">
						<select name="currency-id" class="form-control">
							<option>--<?php echo l('Select Currency', true); ?>--</option>
							<?php 
								foreach($currencies as $currency)
								{
									echo "<option value='".$currency['currency_id']."' ";
									if ($rate_plan['currency_id'] == $currency['currency_id'])
									{
										echo " SELECTED=SELECTED ";
									}
									echo ">".$currency['currency_code']." (".$currency['currency_name'].")</option>\n";
								}
							?>
						</select>
					</div>
				</div>


				<div class="form-group">
					<label class="col-sm-3 control-label">
					</label>
					<div class="col-sm-9">
						<select name="is-shown-in-online-booking-engine" class="form-control">
							<option value='1' <?php if ($rate_plan['is_shown_in_online_booking_engine'] == '1') echo "SELECTED"; ?>><?php echo l('Display on Online Booking Engine', true); ?></option>
							<option value='0' <?php if ($rate_plan['is_shown_in_online_booking_engine'] == '0') echo "SELECTED"; ?>><?php echo l('Do not display on Online Booking Engine', true); ?></option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-3 control-label">
						<?php echo l('Product Items', true); ?>
					</label>
					<div class="col-sm-9">
				
						<select name="extra[]" class="select-extras" multiple="multiple" style="width: 100%;">
                            <?php
                            foreach($extras as $extra) { ?>
								<option value="<?= $extra['extra_id'];?>" 
									<?php for($i = 0; $i < count($extras); $i++) {
										if (isset($rate_plan['extras'][$i]) && $extra['extra_id'] == $rate_plan['extras'][$i]['extra_id']) {echo " SELECTED=SELECTED ";}
									}  ?>>
                                 	<?= $extra['extra_name'];?>
                             	</option>
                           <?php }  ?>
                        </select>
					</div>
				</div>


				<div class="form-group">
					<label for="description" class="col-sm-3 control-label">
						<?php echo l('Description', true); ?>
					</label>
					<div class="col-sm-9">
						<textarea class="enter form-control rate_desc" rows='4'  name="description" type="text" autocomplete="off" ><?php 
							echo $rate_plan['description'];
						?></textarea>
					</div>
				</div>
				<?php
					if (isset($rate_plan['images'])):
				?>
					
					<div class="form-group">
				
							
								<label class="col-sm-3 control-label">
									<?php echo l('Rate Plan Images', true); ?>
								</label>
						
								<div class="col-sm-9">
								<span class="image-group" id="<?php echo $rate_plan['image_group_id']; ?>">
									<a href=#
										class="add-image btn btn-primary"
										style="float:left;"
									>
										<?php echo l('Add Image', true); ?> 
									</a>
						    <?php
								foreach ($rate_plan['images'] as $image):
					  				$image_url = $this->image_url.$company_id."/".$image['filename'];
							?>
								   	<img 
								   		class="thumbnail col-md-3 add-image" 
								   		src="<?php echo $image_url; ?>" 
								   		title="<?php echo $image['filename']; ?>"
								   	/>
									
						    <?php
					    		endforeach;
						    ?>
									</span>

							</div>
				</div>
				<?php
					endif;
				?>
				<!-- <div class="col-md-12">	
					
					<div class="form-inline form-group">
						<label class="col-sm-3 control-label">
							
						</label>
						<div class="col-sm-9">
							
							<button 
								class="btn btn-default update-rate-plan-button" count="<?php echo $count; ?>"
							>
								<?php echo l('Update Rate Plan', true); ?>
							</button>
						</div>
					</div>
				</div> -->
				
			</div>
		</div>

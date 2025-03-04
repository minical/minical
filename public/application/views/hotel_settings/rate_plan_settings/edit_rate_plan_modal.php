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
				<?php if($this->is_nestpay_enabled || $this->is_nestpaymkd_enabled || $this->is_nestpayalb_enabled || $this->is_nestpaysrb_enabled){ ?>
				<div class="form-group">
					<label class="col-sm-3 control-label">
						<?php echo l('Select Policy', true); ?><span style="color:red;">*</span>
					</label>
					<div class="col-sm-9">
				
						<select name="policy_name" class="select-policy form-control" style="width: 70%;" required>
							<option value="0"> Select Policy</option>
                            <?php
                            if(isset($polices) && $polices !=''){
                            foreach($polices as $policy) { 
                            		$pdetails =  json_decode($policy['option_value'],true);
                            		?>
								<option value="<?= $policy['option_id'];?>" 
									<?php 
										if (isset($rate_plan['policy_code']) && $policy['option_id'] == $rate_plan['policy_code']) {echo " SELECTED=SELECTED ";}
									  ?>>
                                 	<?= $pdetails['policy_title'];?>
                             	</option>
                           <?php }}  ?>
                        </select>
					</div>
				</div>
                 <?php }  ?>
                 <?php if(isset($this->is_derived_rate_enabled) && ($this->is_derived_rate_enabled == 1)){ 
                    if(isset($Ddetails) && $Ddetails !=''){
                 	?>
                 	<div class="form-group">
                    <label for="derived_rate" class="col-sm-3 control-label">
					</label>
                    <div class="col-sm-9">
                        <input type="checkbox" name="derived_rate_enable" value="1" class="derived-rate-checkbox" autocomplete="off"<?php if($Ddetails['derived_rate_enable'] == 1){ echo 'checked';} ?>>
                        <strong> <?php echo l('Derived rate plan', true); ?></strong>
                    </div>
                     </div>
                     <div class="derived_html" <?php if($Ddetails['derived_rate_enable'] == 1){ echo 'style="display:block;"';}else{ echo 'style="display:none;"'; } ?>>
                    <legend style="padding-left:95px;font-size: 18px;">Derived Options</legend>
                    <div class="form-group">
					<label for="parent_roomtype" class="col-sm-3 control-label">
						<?php echo l('Parent Room Type', true); ?><span style="color:red;">*</span>
					</label>
					<div class="col-sm-9">
						<select name="parent_room_type" class="select_parent_room form-control" style="width: 70%;" disabled>
							<option value=""> Select Room Type</option>
                             <?php
		                    if (isset($room_types)){
		                        ?>
		                            <?php
		                             foreach($room_types as $room_type)
		                             {
		                             	echo "<option value='".$room_type['id']."' ";
		                           
			                                if ($room_type['id'] == $Ddetails['parent_room_type'])
			                                {
			                                    echo " SELECTED=SELECTED ";
			                                }
			                        
	                                	echo ">".$room_type['name']."</option>\n";
		                            }
		                            ?>
		                        
		                        <?php
		                    }
		                    ?>
                        </select>
					</div>
				   </div>
				   <div class="form-group">
					<label for="parent_rateplan" class="col-sm-3 control-label">
						<?php echo l('Parent Rate Plan', true); ?><span style="color:red;">*</span>
					</label>
					<div class="col-sm-9">
						<select name="parent_rate_plan" class="select_parent_rate form-control" style="width: 70%;" disabled>
                           <?php
                           	//echo $Ddetails['parent_rate_plan'];
                           	//die('hi');

		                    if (isset($Drate_plan)){
		                        ?>
		                            <?php
		                             foreach($Drate_plan as $rate_plan)
		                             {
		                             	echo "<option value='".$rate_plan['rate_plan_id']."' ";
		                           
			                                if ($rate_plan['rate_plan_id'] == $Ddetails['parent_rate_plan'])
			                                {
			                                    echo " SELECTED=SELECTED ";
			                                }
			                        
	                                	echo ">".$rate_plan['rate_plan_name']."</option>\n";
		                            }
		                            ?>
		                        
		                        <?php
		                    }
		                    ?>
                        </select>
					</div>
				   </div>
				   <div class="form-group">
                    <label for="Inherit_from_parent" class="col-sm-3 control-label">
                    	<?php echo l('Inherit from parent', true); ?>
					</label>
                    <div class="col-sm-9">
                        <input type="checkbox" name="derived_rate" value="derived_rate" class="derived-rate" autocomplete="off" disabled <?php if($Ddetails['rate'] == 1){ echo 'checked';} ?>>
                         <?php echo l('Rate', true); ?>
                         <br>
                         <input type="checkbox" name="min_stay_arrival" value="1" class="min-stay-arrival" autocomplete="off" disabled <?php if($Ddetails['mims_a'] == 1){ echo 'checked';} ?>>
                         <?php echo l('Min Stay Arrival', true); ?>
                         <br>
                         <input type="checkbox" name="max_stay_arrival" value="1" class="max-stay-arrival" autocomplete="off" disabled <?php if($Ddetails['mams_a'] == 1){ echo 'checked';} ?>>
                         <?php echo l('Max Stay Arrival', true); ?>
                         <br>
                         <!-- <input type="checkbox" name="max_stay_arrival" value="max_stay_arrival" class="max-stay-arrival" autocomplete="off">
                         <?php //echo l('Max Stay Arrival', true); ?>
                         <br> -->
                         <input type="checkbox" name="closed_to_arrival" value="1" class="closed-to-arrival" autocomplete="off" disabled <?php if($Ddetails['cta'] == 1){ echo 'checked';} ?>>
                         <?php echo l('Closed To Arrival', true); ?>
                         <br>
                         <input type="checkbox" name="closed_to_departure" value="1" class="closed-to-departure" autocomplete="off" disabled  <?php if($Ddetails['ctd'] == 1){ echo 'checked';} ?>>
                         <?php echo l('Closed To Departure', true); ?>
                         <br>
                         <input type="checkbox" name="stop_sell" value="1" class="stop-sell" autocomplete="off"  disabled <?php if($Ddetails['stop_sell'] == 1){ echo 'checked';} ?>>
                         <?php echo l('Stop Sell', true); ?>
                         <br>
                    </div>
                     </div>
                     <div class="form-group">
                    <label for="rate_logic" class="col-sm-3 control-label">
                    	<strong> <?php echo l('Rate Logic', true); ?></strong><span style="color:red;">*</span>
					</label>
                    <div class="col-sm-5">
                    	<select name="rate_logic" class="rate_logic form-control" style="width: 100%;" required>
                    		<option value=""> </option>
                    		<option value="ASP" <?php if($Ddetails['rate_logic'] == 'ASP'){ echo " SELECTED = SELECTED";} ?>> Amount Same As Parent</option>
                           <option value="IBA" <?php if($Ddetails['rate_logic'] == 'IBA'){ echo " SELECTED = SELECTED";} ?>> Increase By Amount</option>
                           <option value="DBA"<?php if($Ddetails['rate_logic'] == 'DBA'){ echo " SELECTED = SELECTED";} ?>> Decrease By Amount</option>
                           <option value="IBP"<?php if($Ddetails['rate_logic'] == 'IBP'){ echo " SELECTED = SELECTED";} ?>> Increase By Percent</option>
                           <option value="DBP"<?php if($Ddetails['rate_logic'] == 'DBP'){ echo " SELECTED = SELECTED";} ?>> Decrease By Percent</option>
                        </select>
                    </div>
                    <div class="col-sm-4">    
                        <input type="number" name="rate_logic_amount" class="rate-logic-amount form-control" value ="<?php echo $Ddetails['rate_logic_amount'];?>" required>
                    </div>   
                    
                     </div>
                     <hr></hr>
                 </div>
                 	<?php } } ?>

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

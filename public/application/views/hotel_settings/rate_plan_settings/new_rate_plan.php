<div class="panel panel-default new-rate-plan-modal rate-plan-div new-panel" id="<?php echo $rate_plan_id; ?>">
	<div class="panel-body form-horizontal">
		<div class="alert alert-success hidden updated-message" role="alert"><?php echo l('Updated', true); ?>!</div>
		<div class="alert alert-danger hidden error-message" role="alert">
            <?php echo l('Please enter all valid details', true); ?>!
        </div>

		<div class="form-group">
			<label class="col-sm-3 control-label">
				<?php echo l('Rate Plan Name', true); ?>
			</label>
			<div class="col-sm-9">
				<input name="rate-plan-name" class="form-control" type="text" value="<?php echo $rate_plan_name; ?>"/>
			</div>
            <!--<div class="col-sm-3">
                <div class="btn-group pull-right" role="group">
					<a href="<?php echo base_url()."settings/rates/edit_rates/".$rate_plan_id; ?>" class="btn btn-success">
						<?php echo l('Edit Rates', true); ?>
					</a>
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<?php echo l('More', true); ?> 
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu pull-right other-actions" role="menu">
						<li class="image-group" id="<?php echo $image_group_id; ?>">	
							<a href="#" class="add-image" data-toggle="modal" data-target="#image_edit_modal">
								<?php echo l('Add Image', true); ?>
							</a>
						</li>
						<li>
							<a href="#" class="replicate-rate-plan-button">
								<?php /*echo l('Replicate (Make a copy)', true); */?>
							</a>
						</li><li>
							<a href="#" class="delete-rate-plan-button"><?php /*echo l('Delete', true); */?></a>
						</li>
					</ul>
				</div>
			</div>-->
		</div>
		
		<div class="form-group">
			<label class="col-sm-3 control-label">
				<?php echo l('Room types', true); ?>
			</label>
			<div class="col-sm-9">
				<select name="room-type-id" class="select-room-types required" multiple="multiple" style="width: 100%;">
                    <option disabled>--<?php echo l('Select Room Type', true); ?>--</option>
					<?php 
						foreach($room_types as $room_type)
						{
							echo "<option value='".$room_type['id']."' ";
							if ($room_type_id == $room_type['id'])
							{
								echo " SELECTED=SELECTED ";
							}
							echo ">".$room_type['name']."</option>\n";
						}
					?>
				</select>
			</div>
			
		</div>


		<div class="form-group">
			<label class="col-sm-3 control-label">
			<?php echo l('Settings', true); ?>
			</label>
			<div class="col-sm-3">
				<select name="is-shown-in-online-booking-engine" class="form-control">
					<option value="1"><?php echo l('Display on Online Booking Engine', true); ?></option>
					<option value="0"><?php echo l('Do not display on Online Booking Engine', true); ?></option>
				</select>
			</div>
			<div class="col-sm-3">
				<select name="charge-type-id" class="form-control">
                    <?php if (!$allow_free_bookings){
                        echo '<option>--Select Charge Type--</option>';
                    } ?>
					<?php 
						foreach($charge_types as $charge_type)
						{
							echo "<option value='".$charge_type['id']."' ";
							if ($charge_type_id == $charge_type['id'])
							{
								echo " SELECTED=SELECTED ";
							}
							echo ">".$charge_type['name']."</option>\n";
						}
					?>
                    <?php if ($allow_free_bookings){
                        echo '<option value="">'.l("None (can be booked for free)", true).'</option>';
                    } ?>
				</select>
			</div>
		
			<div class="col-sm-3">
				<select name="currency-id" class="form-control">
					<?php 
						foreach($currencies as $currency)
						{
							echo "<option value='".$currency['currency_id']."' ";
							if ($currency_id == $currency['currency_id'])
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
				<?php echo l('Extras', true); ?>
			</label>
			<div class="col-sm-9">
				<select name="extra[]" class="select-extras" multiple="multiple" style="width: 100%;">
                    <!-- <option value="">All</option> -->
                    <?php
                        foreach($extras as $extra)
                        { 
                        	?>
							<option value="<?= $extra['extra_id'];?>">
                             	<?= $extra['extra_name'];?>
                         	</option>
                        
                   <?php } ?>
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
									>
                                 	<?= $pdetails['policy_title'];?>
                             	</option>
                           <?php }}  ?>
                        </select>
					</div>
				</div>
                 <?php }  ?>
                 <?php if(isset($this->is_derived_rate_enabled) && $this->is_derived_rate_enabled == 1){ ?>
                 	<div class="form-group">
                    <label for="derived_rate" class="col-sm-3 control-label">
					</label>
                    <div class="col-sm-9">
                        <input type="checkbox" name="derived_rate_enable" value="1" class="derived-rate-checkbox" autocomplete="off">
                        <strong> <?php echo l('Derived rate plan', true); ?></strong>
                    </div>
                     </div>
                     <div class="derived_html" style="display:none;">
                    <legend style="padding-left:95px;font-size: 18px;">Derived Options</legend>
                    <div class="form-group">
					<label for="parent_roomtype" class="col-sm-3 control-label">
						<?php echo l('Parent Room Type', true); ?><span style="color:red;">*</span>
					</label>
					<div class="col-sm-9">
						<select name="parent_room_type" class="select_parent_room form-control" style="width: 70%;" required>
							<option value=""> Select Room Type</option>
                             <?php
		                    if (isset($room_types)){
		                        ?>
		                            <?php
		                             foreach($room_types as $room_type)
		                             {
		                             	echo "<option value='".$room_type['id']."' ";
		                            // 	if (isset($room_rates)):
		                            // 	foreach ($room_rates as $key => $value) {

		                            // 		$option_value = json_decode($value['option_value'], true);

			                                
			                        //         if ($room_type['id'] == $option_value['room_type_id'] && $customer_type['id'] == $option_value['customer_type_id'])
			                        //         {
			                        //             echo " SELECTED=SELECTED ";
			                        //         }
			                        //     }
			                        // endif;
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
						<select name="parent_rate_plan" class="select_parent_rate form-control" style="width: 70%;" required>
                           
                        </select>
					</div>
				   </div>
				   <div class="form-group">
                    <label for="Inherit_from_parent" class="col-sm-3 control-label">
                    	<?php echo l('Inherit from parent', true); ?>
					</label>
                    <div class="col-sm-9">
                        <input type="checkbox" name="derived_rate" value="derived_rate" class="derived-rate" autocomplete="off" disabled>
                         <?php echo l('Rate', true); ?>
                         <br>
                         <input type="checkbox" name="min_stay_arrival" value="1" class="min-stay-arrival" autocomplete="off">
                         <?php echo l('Min Stay Arrival', true); ?>
                         <br>
                         <input type="checkbox" name="max_stay_arrival" value="1" class="max-stay-arrival" autocomplete="off">
                         <?php echo l('Max Stay Arrival', true); ?>
                         <br>
                         <!-- <input type="checkbox" name="max_stay_arrival" value="max_stay_arrival" class="max-stay-arrival" autocomplete="off">
                         <?php //echo l('Max Stay Arrival', true); ?>
                         <br> -->
                         <input type="checkbox" name="closed_to_arrival" value="1" class="closed-to-arrival" autocomplete="off">
                         <?php echo l('Closed To Arrival', true); ?>
                         <br>
                         <input type="checkbox" name="closed_to_departure" value="1" class="closed-to-departure" autocomplete="off">
                         <?php echo l('Closed To Departure', true); ?>
                         <br>
                         <input type="checkbox" name="stop_sell" value="1" class="stop-sell" autocomplete="off">
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
                    		<option value="ASP"> Amount Same As Parent</option>
                           <option value="IBA"> Increase By Amount</option>
                           <option value="DBA"> Decrease By Amount</option>
                           <option value="IBP"> Increase By Percent</option>
                           <option value="DBP"> Decrease By Percent</option>
                        </select>
                    </div>
                    <div class="col-sm-4">    
                        <input type="number" name="rate_logic_amount" class="rate-logic-amount form-control" required>
                    </div>   
                    
                     </div>
                     <hr></hr>
                 </div>
                 	<?php }  ?>
		<div class="form-group">
			<label for="description" class="col-sm-3 control-label">
				<?php echo l('Description', true); ?>
			</label>
			<div class="col-sm-9">
				<textarea class="enter form-control" rows="4" name="description" type="text" autocomplete="off"></textarea>
			</div>
		</div>
		
	</div>
</div>
<style type="text/css">
    .select2-drop.select2-drop-active {
        z-index: 2147483647 !important;
    }
    .rate-plan-div.new-panel {
        border: 0;
        box-shadow: unset;
    }
    #image_edit_modal, #croppicModal {
        z-index: 99999999;
    }
</style>
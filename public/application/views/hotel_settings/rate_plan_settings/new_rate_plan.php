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
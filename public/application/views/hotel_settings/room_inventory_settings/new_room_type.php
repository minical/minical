<div class="panel panel-default room-type-div new-room-type-modal  new-panel" id="<?php echo $id; ?>">
	<div class="panel-body form-horizontal">
		<div class="alert alert-success hidden updated-message" role="alert"><?php echo l('Updated', true); ?>!</div>
		

		<div class="form-group">
			<label for="description" class="col-sm-3 control-label">
				<?php echo l('room_type_name'); ?>
			</label>
			<div class="col-sm-3">
				<input name="room-type-name" class="form-control" type="text" value="<?php echo $name; ?>">
			</div>
			<label for="description" class="col-sm-2 control-label">
				<?php echo l('acronym'); ?>
			</label>
			<div class="col-sm-3">
				<input name="room-type-acronym" class="form-control" type="text" size="6" maxlength="6" value="<?php echo $acronym; ?>">	
			</div>
			<!-- <div class="col-sm-2">
					<button class="delete-room-type-button btn btn-danger pull-right"><?php echo l('Delete'); ?></button>
			</div> -->
		</div>	
        <div class="form-group">
            <div class="clearfix" style="margin-bottom: 10px;">
                <div class="col-md-1"></div>
                <label class="col-md-4 control-label"><?php echo l('minimum_occupancy'); ?></label>
                <div class="col-md-1"></div>
            	<label class="col-md-4 control-label"><?php echo l('maximum_occupancy'); ?></label>
            </div>

			<div class="clearfix range_occupancy">
                <div class="col-md-1"></div>
                <div class="col-md-3">
                	<input type="number" name="min-occupancy" class="ranged_slider_value min_occupancy" value="1" size="1" min="1" max="30" style="float: right; margin-top: -6px;">
                </div>
            	<div class="col-md-5 min-max-occupancy-range">
                    <div class="slider-range" data-min="1" data-max="8"></div>
				</div>
				<div class="col-md-1 rt-occupancy">
                	<input type="number" name="max-occupancy" class="ranged_slider_value max_occupancy" value="8" size="1" min="1" max="30" style="float: right; margin-top: -6px;">
                </div>
            </div>                                    
        </div>

        <div class="form-group">
            <div class="clearfix" style="margin-bottom: 10px;">
                <div class="col-md-3"></div>
            	<label class="col-md-4 control-label" style="text-align: left;"><?php echo l('maximum_adults'); ?></label>
                <div class="col-md-1"></div>
                <label class="col-md-4 control-label" style="text-align: left;"><?php echo l('maximum_children'); ?></label>
            </div>
            
			<div class="clearfix">
                <div class="col-md-3"></div>
            	<div class="col-md-3 occupancy-range" style="padding-right: 0;">
                    <input type="range" name="max-adults" min="1" max="30" value="4" class="rt-occupancy-slider" id="max-adults-range">
				</div>
				<div class="col-md-1 rt-occupancy">
                	<input type="number" class="slider_value" value="4" size="1" min="1" max="30" style="float: right; margin-top: -6px;">
                </div>
                <div class="col-md-1"></div>
				<div class="col-md-3 occupancy-range" style="padding-right: 0;">
					<input type="range" name="max-children" min="0" max="30" value="4" class="rt-occupancy-slider" id="max-children-range">
				</div>
				<div class="col-md-1 rt-occupancy">
                	<input type="number" class="slider_value" value="4" size="1" min="1" max="30" style="float: right; margin-top: -6px;">
                </div>
            </div>                                    
        </div>	

        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo l('show_on_website'); ?></label>
			<div class="col-sm-2">
                <select class="enter form-control" name="can-be-sold-online">
                    <option value="0" <?php if ($room_type['can_be_sold_online'] == "0") echo "SELECTED"; ?>><?php echo l('No', true); ?></option>
                    <option value="1" <?php if ($room_type['can_be_sold_online'] == "1") echo "SELECTED"; ?>><?php echo l('Yes', true); ?></option>
                </select>
            </div>

            <label class="col-sm-3 control-label"><?php echo l('Default Room Charge'); ?></label>
			<div class="col-sm-4">
                <select class="enter form-control" name="default_room_charge">
                    <?php if (!$this->allow_free_bookings) {
						echo '<option>--Select Charge Type--</option>';
					} ?>
                	<optgroup label="Charge Types">
                	<?php foreach ($charge_types as $charge_type) { ?>
                		<option value="<?php echo $charge_type['id']; ?>" <?php echo $charge_type['id'] == $room_type['default_room_charge'] ? 'selected' : ''; ?>><?php echo $charge_type['name']; ?></option>
            		<?php } ?>

            		<optgroup label="Rate Plans">
                	<?php foreach ($rate_plans as $rp) { ?>
					<option value="<?php echo $rp['rate_plan_id']; ?>" 
					<?php echo $rp['rate_plan_id'] == $room_type['default_room_charge'] ? 'selected' : ''; ?>
						><?php echo $rp['rate_plan_name']; ?></option>
            		<?php 
					} ?>
                    <?php if ($this->allow_free_bookings) {
						echo '<option ' . ($room_type['default_room_charge'] ? '' : 'SELECTED=SELECTED') . ' value="">' . l("None (FREE)", true) . '</option>';
					} ?>
                </select>
            </div>
        </div>

		<div class="form-group">
			<label for="description" class="col-sm-3 control-label">
				<?php echo l('Description', true); ?>
			</label>
			<div class="col-sm-9">
				<textarea class="enter form-control" rows="4" id="desc_<?php echo $id;?>" name="description" type="text" autocomplete="off">
				</textarea>
			</div>
		</div>	
		
		
		
		<!-- <div class="col-md-12 image-group" id="<?php echo $image_group_id; ?>">
			<div class="form-inline form-group">
				<label class="col-sm-3 control-label">
					<?php echo l('images'); ?> <button class="btn btn-primary form-control add-image" data-toggle="modal" data-target="#image_edit_modal">
							<?php echo l('Add Image'); ?>
						</button>
					
				</label>
				<div class="col-sm-9"></div>
			</div>
		</div>	 -->
		<div class="col-md-12 image-group" id="12678">
			<!-- <div class="form-inline form-group">
				<label class="col-sm-3 control-label">
				</label>
				<div class="col-sm-9">
					<button class="btn btn-success update-room-type-button" count="">
						<?php echo l('add_room_type'); ?>
					</button>
				</div>
			</div> -->
		</div>
	</div>
</div>
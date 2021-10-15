<div class="panel room-type-div new-room-type-modal ">
	<div class="panel-body form-horizontal">
		<div class="alert alert-success hidden updated-message" role="alert"><?php echo l('Updated', true); ?>!
		</div>

		<div class="form-group">
			<label for="description" class="col-sm-3 control-label">
				<?php echo l('room_type_name'); ?>
			</label>
			<div class="col-sm-3">
				<input name="room-type-name" class="form-control" type="text" value="New Room Type">
			</div>
			<label for="description" class="col-sm-2 control-label">
				<?php echo l('acronym'); ?>
			</label>
			<div class="col-sm-3">
				<input name="room-type-acronym" class="form-control" type="text" size="6" maxlength="6" value="NRT">	
			</div>
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
                	<input type="number" name="min-occupancy" class="ranged_slider_value min_occupancy min-wep" value="1" size="1" min="1" max="30" style="float: right; margin-top: -6px;">
                </div>
            	<div class="col-md-5 min-max-occupancy-range">
                    <div class="slider-range" data-min="1" data-max="8"></div>
				</div>
				<div class="col-md-1 rt-occupancy">
                	<input type="number" name="max-occupancy" class="ranged_slider_value max_occupancy max-wep" value="8" size="1" min="1" max="60" style="float: right; margin-top: -6px;">
                </div>
            </div>                                    
        </div>

        <div class="form-group">
            <div class="clearfix" style="margin-bottom: 10px;">
                <div class="col-md-3"></div>
            	<label class="col-md-4 control-label label-adults" style="text-align: left;"><?php echo l('maximum_adults'); ?></label>
                <div class="col-md-1"></div>
                <label class="col-md-4 control-label label-child" style="text-align: left;"><?php echo l('maximum_children'); ?></label>
            </div>
            
			<div class="clearfix">
                <div class="col-md-3"></div>
            	<div class="col-md-3 occupancy-range" style="padding-right: 0;">
                    <input type="range" name="max-adults" min="1" max="30" value="4" class="rt-occupancy-slider range-wep" id="max-adults-range">
				</div>
				<div class="col-md-1 rt-occupancy">
                	<input type="number" class="slider_value input-first" value="4" size="1" min="1" max="30" style="float: right; margin-top: -6px;">
                </div>
                <div class="col-md-1"></div>
				<div class="col-md-3 occupancy-range" style="padding-right: 0;">
					<input type="range" name="max-children" min="0" max="30" value="4" class="rt-occupancy-slider label-child" id="max-children-range">
				</div>
				<div class="col-md-1 rt-occupancy">
                	<input type="number" class="slider_value input-second" value="4" size="1" min="1" max="30" style="float: right; margin-top: -6px;">
                </div>
            </div>                                    
        </div>	

        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo l('show_on_website'); ?></label>
			<div class="col-sm-2">
                <select class="enter form-control" name="can-be-sold-online">
                    <option value="1"><?php echo l('Yes', true); ?></option>
                    <option value="0"><?php echo l('No', true); ?></option>
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
                		<option value="<?php echo $charge_type['id']; ?>" <?php echo $charge_type['is_default_room_charge_type'] == '1' ? 'selected' : ''; ?>><?php echo $charge_type['name']; ?></option>
            		<?php } ?>

            		<optgroup label="Rate Plans">
                	<?php foreach ($rate_plans as $rp) { ?>
					<option value="<?php echo $rp['rate_plan_id']; ?>" 
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
				<textarea class="enter form-control" rows="4" id="desc_new_roomtype" name="description" type="text" autocomplete="off">
				</textarea>
			</div>
		</div>	
	</div>
	</div>
</div>
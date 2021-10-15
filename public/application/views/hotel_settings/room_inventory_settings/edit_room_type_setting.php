	
    <?php
				if (isset($room_type)) :
					$count = 1;
					 ?>
	
						<div class="panel panel-default room-type-div" id="<?php echo $room_type['id']; ?>">
							<div class="panel-body form-horizontal">
								<div class="alert alert-success hidden updated-message" role="alert"><?php echo l('Updated', true); ?>!</div>
								

								<div class="form-group">
									<label for="description" class="col-sm-3 control-label">
										<?php echo l('room_type_name'); ?>
									</label>
									<div class="col-sm-3">
										<input name="room-type-name" class="form-control" type="text" value="<?php echo $room_type['name']; ?>"/>
									</div>
									<label for="description" class="col-sm-2 control-label">
										<?php echo l('acronym'); ?>
									</label>
									<div class="col-sm-2">
										<input name="room-type-acronym" class="form-control" type="text" size="6" maxlength="6" value="<?php echo $room_type['acronym']; ?>" />	
									</div>
							
								</div>
                                
                                <div class="form-group">
                                    <div class="clearfix" style="margin-bottom: 10px;">
                                        <div class="col-md-3"></div>
                                        <label class="col-md-4 control-label" style="text-align: left"><?php echo l('minimum_occupancy'); ?></label>
                                        <div class="col-md-1"></div>
                                    	<label class="col-md-4 control-label"><?php echo l('maximum_occupancy'); ?></label>
                                    </div>
									<div class="clearfix range_occupancy">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-1 ">
	                                    	<input type="number" name="min-occupancy" class="ranged_slider_value min_occupancy min-wep" value="<?php echo $room_type['min_occupancy']; ?>" size="1" min="1" max="30" style="float: right; margin-top: -6px;">
	                                    </div>
                                    	<div class="col-md-7 min-max-occupancy-range">
  											<div class="slider-range" data-min="<?php echo $room_type['min_occupancy']; ?>" data-max="<?php echo $room_type['max_occupancy']; ?>"></div>
										</div>
										<div class="col-md-1 rt-occupancy">
	                                    	<input type="number" name="max-occupancy" class="ranged_slider_value max_occupancy max-wep" value="<?php echo $room_type['max_occupancy']; ?>" size="1" min="1" max="60" style="float: right; margin-top: -6px;">
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
  											<input type="range" name="max-adults" min="1" max="30" value="<?php echo $room_type['max_adults']; ?>" class="rt-occupancy-slider range-wep" id="max-adults-range">
										</div>
										<div class="col-md-1 rt-occupancy">
	                                    	<input type="number" class="slider_value input-first" value="<?php echo $room_type['max_adults']; ?>" size="1" min="1" max="30" style="float: right; margin-top: -6px;">
	                                    </div>
                                        <div class="col-md-1"></div>
										<div class="col-md-3 occupancy-range" style="padding-right: 0;">
											<input type="range" name="max-children" min="0" max="30" value="<?php echo $room_type['max_children']; ?>" class="rt-occupancy-slider label-child" id="max-children-range">
										</div>
										<div class="col-md-1 rt-occupancy">
	                                    	<input type="number" class="slider_value input-second" value="<?php echo $room_type['max_children']; ?>" size="1" min="1" max="30" style="float: right; margin-top: -6px;">
	                                    </div>
                                    </div>                                    
                                </div>
								
								<div class="form-group">
                                    <label class="col-sm-3 control-label"><?php echo l('show_on_website'); ?></label>
									<div class="col-sm-2">
                                        <select class="enter form-control" name="can-be-sold-online">
                                            <option value="1" <?php if ($room_type['can_be_sold_online'] == "1") echo "SELECTED"; ?>><?php echo l('Yes', true); ?></option>
                                            <option value="0" <?php if ($room_type['can_be_sold_online'] == "0") echo "SELECTED"; ?>><?php echo l('No', true); ?></option>
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
									<div class="col-sm-3 text-right">
										<input type="checkbox" name="prevent_inline_booking" class="prevent_inline_booking" <?php
																															echo $room_type['prevent_inline_booking'] == 1 ? 'checked' : '';
																															?>>
									</div>
                                    <div class="col-sm-9">
                                        <label for="prevent_inline_booking">
                                            <span class="text-danger"><?php echo l('COVID Prevention:', true); ?></span>
                                            <span style="font-weight: normal">
                                                <?= l('Prevent any booking to be made online within 24 hours of another booking’s checkout date', true); ?>
                                            </span>
                                        </label>
                                    </div>
								</div>

								<div class="form-group">
									<label for="description" class="col-sm-3 control-label">
										<?php echo l('description'); ?>
									</label>
									<div class="col-sm-9">
										<textarea class="enter form-control des" rows='4' id="desc_<?php echo $room_type['id'] ?>" name="description" type="text" autocomplete="off" ><?php
																																											echo $room_type['description'];
																																											?></textarea>
                                        <?php //$textName = 'desription_'.$room_type['id']; 
										?>
										<?php //echo $this->ckeditor->editor($textName, $room_type['description']); 
										?>
									</div>
								</div>	
								
								<div class="col-md-12 image-group" id="<?php echo $room_type['image_group_id']; ?>">
									<div class="form-group">
										<label class="col-sm-3 control-label">
											<?php echo l('images'); ?> 
											
					    					
										</label>
										<div class="col-sm-9">
										<button 
													class="btn btn-primary btn-sm add-image"
													data-toggle="modal" 
													data-target="#image_edit_modal"
												>
													<?php echo l('Add Image', true); ?>
												</button><br/>
											<?php
										
											if (isset($room_type['images'])) :

												foreach ($room_type['images'] as $image) :
													$image_url = $this->image_url . $company_id . "/" . $image['filename'];
											?>
													   	<img 
													   		class="thumbnail col-md-3 add-image" 
													   		src="<?php echo $image_url; ?>" 
													   		title="<?php echo $image['filename']; ?>" 
													   		data-toggle="modal" 
													   		data-target="#image_edit_modal" 
													   	/>
													
										    <?php
												endforeach;
											endif;
											?>
										</div>
									</div>
								</div>	
								<div class="col-md-12 image-group" id="<?php echo $room_type['image_group_id']; ?>">
									<div class="form-inline form-group">
										<label class="col-sm-3 control-label">
										</label>
										<div class="col-sm-9">
											<!-- <button 
												class="btn btn-success update-room-type-button" count="<?php echo $count; ?>"
											>
												<?php echo l('update_room_type'); ?>
											</button> -->
										</div>
									</div>
								</div>
							</div>
						</div>
							
				<?php $count++;
                 ?>
                 	<?php else : ?>
			<h3>No Room Type(s) have been recorded</h3>
		<?php endif; ?>
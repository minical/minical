

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-home text-success"></i>
            </div>
           <?php echo l($this->default_room_singular).' '.l('Type Settings',true); ?>
        </div>
    </div>
  </div>



<div class="main-card mb-3 card">
    <div class="card-body">

<!-- Modal -->
<div class="modal fade" id="image_edit_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo l('edit_image'); ?></h4>
			</div>
			<div class="modal-body text-center" id="image_edit_modal_body">
			</div>
			<div class="modal-footer image-modal-footer">
				<button type="button" class="btn btn-light" data-dismiss="modal"><?php echo l('close'); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="col-sm-12">

	<div id="side-menu">
		<h5>
			<button id="add-room-type-button" class="btn btn-primary"><?php echo l('Add',true).' '.l($this->default_room_singular).' '.l('Type',true); ?></button>
		</h5>
		<!-- <div id="room-type-list"></div> -->
	</div>

	<div class="">
		<div>
		<div class="table-responsive">
			<table class="table table-hover rooms rate-plans-table">
				<thead>
					<tr>
						<th>
							<?php echo l('Name'); ?>
						</th>
						<th>
							<?php echo l('Acronym'); ?>
						</th>
						<th>
							<?php echo l('Show on Website'); ?>
						</th>
						<th>
							<?php echo l($this->default_room_singular).' '.l('Charge',true); ?>
						</th>
						<th>
							<?php echo l('Min / Max Occupancy'); ?>
						</th>
						<th>
							<?php echo l('Maximum Adults') ?>
						</th>
						<th>
							<?php echo l('Maximum Children') ?>
						</th>
						<th style="text-align: center;">
							<?php echo l('Action'); ?>
						</th>
					</tr>
				</thead>
				<tbody id="sortable">
					<?php
					if (isset($room_types)) :
						$count = 1;
						foreach ($room_types as $room_type) : ?>

							<tr class="booking-source-tr ui-sortable-handle room-type-div" id="<?php echo $room_type['id']; ?>">
								<td class="glyphicon_icon">
									<span class="grippy"></span>
									<!-- <input name="room-type-name" class="form-control"  value="<?php echo $room_type['name']; ?>" readonly /> -->
								 <?php echo $room_type['name']; ?>
								</td>
								<td><?php echo $room_type['acronym']; ?></td>
								<td><?php if ($room_type['can_be_sold_online'] == "1") echo "Yes"; ?><?php if ($room_type['can_be_sold_online'] == "0") echo "No"; ?></td>
								<td>
									<?php
									if(count($charge_types) > 0){
										foreach ($charge_types as $charge_type) {
											if ($charge_type['id'] == $room_type['default_room_charge']) {
												echo $charge_type['name'];
											}
										}
									}
									
									if(isset($rate_plans) && count($rate_plans) > 0){
										foreach ($rate_plans as $rate_plan) {
											if(isset($rate_plan) && count($rate_plan) > 0){
												foreach ($rate_plan as $rp) {
												if ($rp['rate_plan_id'] == $room_type['default_room_charge']) {
													echo $rp['rate_plan_name'];
													}
												}
											}
										}
									}
									?>
								</td>
								<td>
									<?php echo $room_type['min_occupancy']; ?>/ <?php echo $room_type['max_occupancy']; ?>
								</td>
								<td>
									<?php echo $room_type['max_adults']; ?>
								</td>
								<td>
									<?php echo $room_type['max_children']; ?>
								</td>
								<td style="width: 13%">
									<div class="btn-group pull-right" role="group">
										<button class="btn btn-sm btn-light edit_room_type" id="<?php echo $room_type['id'] ?>" data-min_occupancy="<?php echo $room_type['min_occupancy']; ?>" data-max_occupancy="<?php echo $room_type['max_occupancy']; ?>"><?php echo l('Edit'); ?></button>
										<button class="delete-room-type-button btn btn-sm btn-danger"><?php echo l('Delete'); ?>
										</button>
									</div>
								</td>
							</tr>
						<?php $count++;
						endforeach; ?>
					<?php else : ?>
						<h3><?= l('No',true).' '.l($this->default_room_singular).' '.l('Type(s) have been recorded',true);?></h3>
					<?php endif; ?>

				</tbody>
			</table>
					</div>

		<div class="modal fade" id="room_type_model" data-backdrop="static" data-keyboard="false">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" style="text-align: center;">
							<?php echo l('Edit',true).' '.l($this->default_room_singular).' '.l('Type',true); ?>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						<div class="col-md-12">

							<div class="form-group">
								<div class="text-right" style="float: right">
									<button 
												class="btn btn-success update-room-type-button" count="<?php echo $count; ?>"
											>
												<?php echo l('Update',true).' '.l($this->default_room_singular).' '.l('Type',true); ?>
											</button>
								</div>
							</div>
						</div>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->


		<!-- add new room type  model -->

		<div class="modal fade" id="addnew_room_type_model" data-backdrop="static" data-keyboard="false">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" style="text-align: center;">
							<?php echo l('Add',true).' '.l($this->default_room_singular).' '.l('Type',true); ?>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						<div class="col-md-12">

							<div class="form-group">
								<div class="col-sm-12 text-right" style="float: right">
									<button type="button" class="btn btn-danger" data-dismiss="modal">
										<?php echo l('Cancel', true); ?>
									</button>
									<button class="btn btn-success add_room_type" count="">
							            <?php echo l('Add',true).' '.l($this->default_room_singular).' '.l('Type',true); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
		</div>
	</div>

</div></div></div>

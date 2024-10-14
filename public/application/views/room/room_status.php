<!-- Modal -->
<div class="modal fade" id="room_notes_modal" tabindex="-1" role="dialog" aria-labelledby="label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo l('Edit',true).' '.l($this->default_room_singular).' '.l('Notes',true) ; ?></h4>
			</div>
			<div class="modal-body">
				<textarea id="room-notes" class="form-control" rows=5>
				</textarea>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="save-room-notes-button"><?php echo l('save_changes'); ?></button>
				<button type="button" class="btn btn-light" data-dismiss="modal"><?php echo l('close'); ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="room_instructions_modal" tabindex="-1" role="dialog" aria-labelledby="label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo l('Edit',true).' '.l($this->default_room_singular).' '.l('Instructions',true) ; ?></h4>
			</div>
			<div class="modal-body">
				<textarea id="room-instructions" class="form-control" rows=5>
				</textarea>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="save-room-instructions-button"><?php echo l('save_changes'); ?></button>
				<button type="button" class="btn btn-light" data-dismiss="modal"><?php echo l('close'); ?></button>
			</div>
		</div>
	</div>
</div>

<!-- SHow Rating Modal -->
<div class="modal fade" id="room_rating_modal" tabindex="-1" role="dialog" aria-labelledby="label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo l('Reviews'); ?></h4>
			</div>
			<div class="modal-body view-rating">
			</div>
			<div class="modal-footer">
				<!-- <button type="button" class="btn btn-primary" id="save-room-notes-button"><?php echo l('save_changes'); ?></button> -->
				<button type="button" class="btn btn-light" data-dismiss="modal"><?php echo l('close'); ?></button>
			</div>
		</div>
	</div>
</div>



<div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			<div class="page-title-icon">
				<i class="pe-7s-home text-success"></i>
			</div>
			<div><?php echo l($this->default_room_singular).' '.l('Status',true) ; ?>
			<span class="<?=$this->session->userdata('user_role') == "is_housekeeping" ? "hidden" : "";?>">
		<!-- <a href="<?php echo base_url() . 'reports/room/show_housekeeping_report';?>">
			<?php echo l('housekeeping_report'); ?>
		</a>
		| -->
	</span>
</div>
</div>
<div class="page-title-actions m-008">
	<a href="<?php echo base_url() . 'settings/room_inventory/rooms';?>" class="btn-shadow  btn btn-dark">
		<?php echo l('Edit', true) .' '. l($this->default_room_plural); ?>
	</a>

</div>    </div>
</div>


<div class="main-card mb-3 card m-33">
	<div class="card-body">
	<div class="table-responsive">
		<table class="table table-hover table-rating">
			<tr>
				<th class="td-room-name text-center"><?php echo l($this->default_room_plural); ?></th>
				<th class="td-room-type text-center"><?php echo l($this->default_room_singular).' '.l('Types',true) ; ?></th>
				<th class="td-customer-name text-left"><?php echo l('customer'); ?></th>
				<th class="td-room-status text-center" style="width: 100px">
					<button class="btn btn-primary" id="set_rooms_clean">
						<?php echo l('Clean All',true).' '.l($this->default_room_plural) ; ?>
					</button>
				</th>
				<th class="text-left"><?php echo l($this->default_room_singular).' '.l('Notes',true) ; ?></th>
				<th class="text-left"><?php echo l('Check-In Instructions'); ?></th>

				<?php if ($this->review_management_settings) { ?>
				<th class="text-left" data-content="Score must be betweeen 0 to 10, a float number. 10 as the highest" rel="popover" data-placement="top" data-container="body" data-trigger="hover"><?php echo l('Score'); ?>
					&nbsp;<i class="fa fa-question-circle" aria-hidden="true"></i>
				</th>
				<th class="text-center"><?php echo l('Rating'); ?></th>
			<?php } ?>
			<th style="width: 100px"></th>
		</tr>			
		<?php 
		if(isset($rows)) : 
			foreach ($rows as $r) :
				?>
				<tr class='room_tr' name='<?php echo $r->room_id; ?>' onclick="">
					<td class="text-center"><?php echo $r->room_name; ?></td>
					<td class="text-center"><?php echo $r->acronym; ?></td>
					<td class="td-customer-name text-left" ><div class="booking_tr" name="<?php echo $r->booking_id; ?>"><?php echo $r->customer_name; 
						if( isset($this->is_housekeeper_manage_enabled) && $this->is_housekeeper_manage_enabled == 1){

                        	if($r->booking_status == 2) {
								echo " (Check-out)";
							}else if($r->booking_status == 1){
								echo " (Stay)";
							}else{
								echo "";
							}
                           }
				     ?></div></td>
					<td class="text-center">
						<select autocomplete="off" class="room_status form-control">
							<option <?php if ($r->status == 'Clean') {echo 'selected="selected"';}?>><?php echo l('Clean', true); ?></option>
							<option <?php if ($r->status == 'General Cleaning') {echo 'selected="selected"';}?>><?php echo l('General Cleaning', true); ?></option>
							<option <?php if ($r->status == 'Dirty') {echo 'selected="selected"';}?>><?php echo l('Dirty', true); ?></option>					
							<option <?php if ($r->status == 'Inspected') {echo 'selected="selected"';}?>><?php echo l('Inspected', true); ?></option>					
						</select>
						<p style="color:red;margin-top: 5px;" class="text-center"><?php
						if(!empty($r->booking_status)) {
							if($r->booking_status == 3) {
								echo l("OUT OF ORDER", true);
							} 
						} ?>
					</p>
				</td>
				<td class="text-left"><?php echo str_replace("\n", "<br/>", $r->notes); ?></td>
				<td style="width: 16%;">
					<?php echo str_replace("\n", "<br/>", $r->instructions); ?>
				</td>
				<?php if ($this->review_management_settings) { ?>
					<td class="text-left">
						<input class="form-control room_score" type="number" min="0" max="10" name="room_score" value="<?php echo $r->score; ?>" style="width: 80px;">
					</td>
					<td class="text-center">
						<input class="star-rating" name="star-rating" type="hidden" value="<?php echo isset($r->rating) && $r->rating ? $r->rating : 0; ?>" />
						<a href="javascript:" class="show_rating" data-room_id="<?php echo $r->room_id; ?>" data-room_rating="<?php echo isset($r->rating) && $r->rating ? $r->rating : 0; ?>" >
							<div class="rateit stars" data-rateit-starwidth="16" data-rateit-starheight="16" style="pointer-events: none;"></div>
						</a>
						<br/>
						<?php echo isset($r->total_ratings) && $r->total_ratings ? $r->total_ratings > 1 ? '('.$r->total_ratings.' '.l("ratings", true).')' : '('.$r->total_ratings.' '.l("rating", true).')' : ''; ?>
					</td>
					<?php } ?>
					<td class="td-room-notes text-right">
						<button class='room-notes-button btn btn-light' style="margin-bottom: 5px">
							<?php echo l('Edit',true).' '.l($this->default_room_singular).' '.l('Note',true) ; ?>
						</button>
						<button class='room-instructions-button btn btn-light'>
							<?php echo l('Edit Instructions', true); ?>
						</button>
					</td>

				</tr>
				<?php
			endforeach;
		else : 
			?>
			<h1><?php echo l('No',true).' '.l($this->default_room_singular).' '.l('types have been recorded.',true) ; ?></h1>
		<?php endif; ?>
	</table>
		</div>
</div></div>


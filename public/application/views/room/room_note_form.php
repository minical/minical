<head>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/smoothness/jquery-ui-1.10.3.custom.min.css" />	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/room/room_edit_form.css" />

	<script type="text/javascript" src="<?php echo base_url();?>js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery-ui-1.10.3.custom.min.js"></script>	
	<script type="text/javascript" src="<?php echo base_url() . auto_version('js/innGrid-base.js');?>"></script>
	<script type="text/javascript" src="<?php echo base_url() . auto_version('js/helpers.js');?>"></script>
</head>
<body>
			
	<form id="room_note_form" method="post" action="
		<?php 
				echo base_url()."room/view_room_note_form/".$room_id;
		?>
	" autocomplete="off">
			<!-- variables that are used in booking controller -->
			<input id="room_id" name="room_id" type="hidden" value="<?php echo $room_id; ?>" />		
			
			<label><?php echo l($this->default_room_singular).' '.l('Notes',true); ?></label>
			<br/>
			<textarea id="notes" name="notes" autocomplete="off"><?php echo set_value('notes', $notes);?></textarea>
			
			<br/>
			<div id="booking_dialog_buttons">
				<button class='action_button' type='submit' name='submit' value='save'><?php echo l('Save changes', true); ?></button>
			</div>
		
	</form>
	
</body>
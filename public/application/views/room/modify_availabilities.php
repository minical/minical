<!-- this whole thing is an irame!! -->

<head>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
	<style>
		.modified {
/*			background: url(../../../images/edit.png) right no-repeat;*/
			
		}
	</style>
</head>


<body>
	<table class='ota-availability-data'>
		<thead>
			<?php echo l('Modify availabilities for', true); ?> <?php echo implode(', ', $room_type_names);?>
			<br><br>
			<th colspan=2>
				<input name='room_type_ids' value='<?php echo $room_type_ids; ?>' hidden/>
				<input name='channel_id' value='<?php echo $channel_id; ?>' hidden/>
				<input name='date_start' id='date_start' class='date' value='<?php echo $date_start; ?>' > <?php echo l('to', true); ?> 
				<input name='date_end' id='date_end' class='date'><br/>
				<input type=checkbox name='monday' checked=checked> <?php echo l('Mon', true); ?>
				<input type=checkbox name='tuesday' checked=checked> <?php echo l('Tue', true); ?>
				<input type=checkbox name='wednesday' checked=checked> <?php echo l('Wed', true); ?>
				<input type=checkbox name='thursday' checked=checked> <?php echo l('Thu', true); ?>
				<input type=checkbox name='friday' checked=checked> <?php echo l('Fri', true); ?>
				<input type=checkbox name='saturday' checked=checked> <?php echo l('Sat', true); ?>
				<input type=checkbox name='sunday' checked=checked> <?php echo l('Sun', true); ?>
				<br/><br/>
			</th>	
		</thead>
		<tbody>
			<tr>
				<td>
					<?php echo l('Set max availabilities to', true); ?>*:
					<input name='availability'>
					<br><br>
					*<?php echo l('If the actual number of available',true).' '.l($this->default_room_plural).' '.l('is lower, that will be used instead',true) ; ?>.
				</td>
			</tr>
		</tbody>
	</table>
	<br>
    <input type="hidden" name="project_url" id="project_url" value="<?php echo base_url(); ?>">
	<button class='button' id='allocate_availabilities_button'><?php echo l('Allocate Availabilities', true); ?></button>
	<a href=# id='close_button'><?php echo l('Cancel', true); ?></a>

	<span id="loading_avail_img" style="display:none;">
      	<img src="<?php echo base_url().'images/loading.gif' ?>"  style='width: 5%;
    float: left;'/>
  	</span>
  	
</body>

<script type="text/javascript" src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="https://code.jquery.com/ui/1.10.3/jquery-ui.min.js"></script>

<script type="text/javascript" src="<?php echo base_url() . auto_version('js/helpers.js');?>"></script>
<script type="text/javascript" src="<?php echo base_url() . auto_version('js/modify_availabilities.js'); ?>"></script>

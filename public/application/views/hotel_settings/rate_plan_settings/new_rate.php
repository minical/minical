<head>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
	<style>
		.modified {
/*			background: url(../../../images/edit.png) right no-repeat;*/
			
		}
	</style>
</head>


<body>
<div class="table-responsive">
	<table class='rate-data'>
		<thead>
			<th colspan=3>
				<?php echo l('Modify rates between', true); ?><br/>
				<input name='rate_plan_id' value='<?php echo $rate_plan_id; ?>' hidden/>
				<input name='date_start' id='date_start' class='date' value='<?php echo $selling_date; ?>' > <?php echo l('and', true); ?> 
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
					<b><?php echo l('Name', true); ?></b>
				</td>
				<td>
					<b><?php echo l('Value', true); ?></b>
				</td>
				<td>
					<b><?php echo l('Modified', true); ?></b>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Rate', true); ?> (1 <?php echo l('Adult', true); ?>)
				</td>
				<td>
					<input name='adult_1_rate' class="modifiable">
				</td>
				<td>
					<input type=checkbox name='adult_1_rate_modified' class="modified">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Rate', true); ?> (2 <?php echo l('Adult', true); ?>)
				</td>
				<td>
					<input name='adult_2_rate' class="modifiable">
				</td>
				<td>
					<input type=checkbox name='adult_2_rate_modified' class="modified">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Rate', true); ?> (3 <?php echo l('Adult', true); ?>)
				</td>
				<td>
					<input name='adult_3_rate' class="modifiable">
				</td>
				<td>
					<input type=checkbox name='adult_3_rate_modified' class="modified">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Rate', true); ?> (4 <?php echo l('Adult', true); ?>)
				</td>
				<td>
					<input name='adult_4_rate' class="modifiable">
				</td>
				<td>
					<input type=checkbox name='adult_4_rate_modified' class="modified">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Addiitonal Adult Rate', true); ?>
				</td>
				<td>
					<input name='additional_adult_rate' class="modifiable">
				</td>
				<td>
					<input type=checkbox name='additional_adult_rate_modified' class="modified">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Addiitonal Child Rate', true); ?>
				</td>
				<td>
					<input name='additional_child_rate' class="modifiable">
				</td>
				<td>
					<input type=checkbox name='additional_child_rate_modified' class="modified">
				</td>
			</tr>
			
			
			<tr>
				<td>
					<?php echo l('Minimum Length of Stay', true); ?>

				</td>
				<td>
					<input name='minimum_length_of_stay' value="1" class="modifiable">
				</td>
				<td>
					<input type=checkbox name='minimum_length_of_stay_modified' class="modified">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Maximum Length of Stay', true); ?>
				</td>
				<td>
					<input name='maximum_length_of_stay' value="365" class="modifiable">
				</td>
				<td>
					<input type=checkbox name='maximum_length_of_stay_modified' class="modified">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Min. Length of Stay Arrival', true); ?>

				</td>
				<td>
					<input name='minimum_length_of_stay_arrival' value="1" class="modifiable">
				</td>
				<td>
					<input type=checkbox name='minimum_length_of_stay_arrival_modified' class="modified">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Max. Length of Stay Arrival', true); ?>
				</td>
				<td>
					<input name='maximum_length_of_stay_arrival' value="365" class="modifiable">
				</td>
				<td>
					<input type=checkbox name='maximum_length_of_stay_arrival_modified' class="modified">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Closed to Arrival', true); ?>
				</td>
				<td>
					<input type=checkbox name='closed_to_arrival' class="modifiable">
				</td>
				<td>
					<input type=checkbox name='closed_to_arrival_modified' class="modified">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Closed to Departure', true); ?>
				</td>
				<td>
					<input type=checkbox name='closed_to_departure' class="modifiable">
				</td>
				<td>
					<input type=checkbox name='closed_to_departure_modified' class="modified">
				</td>
			</tr>
			<tr>
				<td>
					<?php echo l('Can be sold online', true); ?>
				</td>
				<td>
					<input type=checkbox name='can_be_sold_online' class="modifiable">
				</td>
				<td>
					<input type=checkbox name='can_be_sold_online_modified' class="modified">
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<br/><br/>
	<button class='button' id='modify_rates_button'><?php echo l('Modify Rates', true); ?></button>
	<a href=# id='close_button'><?php echo l('Cancel', true); ?></a>
</body>

<script type="text/javascript" src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="https://code.jquery.com/ui/1.10.3/jquery-ui.min.js"></script>

<script type="text/javascript" src="<?php echo base_url() . auto_version('js/helpers.js');?>"></script>
<script type="text/javascript" src="<?php echo base_url() . auto_version('js/innGrid-base.js');?>"></script>	
<script type="text/javascript" src="<?php echo base_url() . auto_version('js/hotel-settings/new-rate.js'); ?>"></script>


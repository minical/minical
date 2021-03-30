<script type="text/javascript" src="<?php echo base_url();?>js/jquery.tablesorter.min.js"></script> 
<div id="booking_dialog"></iframe></div>

<div id="booking_list_container" align=center>
	<br/>
	<div class="header_container">
		<!-- insert search title here. Either "Search Result" or "Reservations, Check-outs, or Out of Orders -->
	</div>
	<div id="search_field">
		<?php
			echo form_open('booking/search/0/'.$state);
			echo "<input id='query' name='query' type='text' size='30' />";  
			echo form_submit('submit', 'Search');
			echo form_close();
		?>
	</div>
	
	<div class="body_container">
		
		<?php 
			echo validation_errors();


			// try to echo a current search query or what state it's showing
		?>		

		<table id="booking_table">
			<tr>
				<th class="text-align-center" width=80><?php echo $this->lang->line('booking'); ?> ID</th>
				<th class="text-align-center" width=90><?php echo $this->lang->line('room'); ?>#</th>
				<th class="text-align-center"><?php echo $this->lang->line('customer'); ?></th>			
				<th class="text-align-center" width=90><?php echo $this->lang->line('check_in'); ?></th>
				<th class="text-align-center" width=90><?php echo $this->lang->line('check_out'); ?></th>
			</tr>		
			<?php 
				if (isset($rows)) foreach ( $rows as $r) : 
			?>
			<tr class='booking_tr' id='<?php echo "state".$r->state; ?>' name='<?php echo $r->booking_id; ?>' onclick="" >
				<td class="left_border"><?php echo $r->booking_id; ?></td>
				<td><?php echo $r->room_name; ?></td>
				<td><?php echo $r->customer_names; ?></td>			
				<td><?php echo $r->check_in_date; ?></td>
				<td class="right_border"><?php echo $r->check_out_date; ?></td>
				
			</tr>
			<?php 
				endforeach;				
			?>
		</table>
	</div>
		<?php echo $this->pagination->create_links(); ?>
</div>

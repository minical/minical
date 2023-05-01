<!-- Hidden delete dialog-->
<div id="confirm_delete_dialog" ></div>	


<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-wallet text-success"></i>
            </div>
            <div><?php echo l('charge_type')." ".l('settings'); ?></h2>
	<div class="page-title-subheading"><span id="helpBlock" class="help-block">
		<?php echo l('Click existing fields to edit.', true); ?>
	</span></div>

        </div>
    </div>
  </div>
</div>


<div class="main-card mb-3 card">
    <div class="card-body">

<div class="table-responsive">

<table id="other-charge-types-table" class="table">
	<?php //if(isset($other_charge_types)) : ?>
	<?php if(isset($all_charge_types)) : ?>
	<tr>
		<th class="text-left"><?php echo l('charge_type'); ?></th>
		<th class="text-left"><?php echo l('applicable_taxes'); ?></th>
		<th class="text-center"><?php echo l($this->default_room_singular).' '.l('Charge',true); ?></th>
                <th class="text-center"><?php echo l('report_ad_tax_exempt'); ?></th>
		<th class="text-center"><?php echo l('Delete'); ?></th>                
	</tr>
	<?php //foreach ($other_charge_types as $charge_type) : ?>
	<?php foreach ($all_charge_types as $charge_type) : ?>
	<tr id="<?php echo $charge_type['id']; ?>">
		<td class="text-left">
			<div class="charge-type-name-editable" id="<?php echo $charge_type['id']; ?>"><?php echo $charge_type['name']; ?></div>
		</td>
		<td class="text-left">
			<?php if (isset($tax_types)) : ?>
				<?php foreach ($tax_types as $tax_type) : ?>
						<input class="charge-type-tax" type="checkbox" name="tax_type[]" autocomplete="off" value="<?php echo $tax_type->tax_type_id; ?>"
							<?php //foreach ($other_charge_type_tax_list as $charge_type_tax) : ?>
							<?php foreach ($all_charge_type_tax_list as $charge_type_tax) : ?>
								<?php 
									if($charge_type_tax['tax_type_id'] == $tax_type->tax_type_id &&
											$charge_type_tax['charge_type_id'] == $charge_type['id'])
									{
										echo 'checked="checked"';
									}
								?>
							<?php endforeach; ?>
						/>
						
						<?php echo $tax_type->tax_type; ?> (<?php 
																echo ($tax_type->is_brackets_active == '1') ? $tax_type->tax_rate_range : $tax_type->tax_rate; 
																echo ($tax_type->is_percentage)?"%":" ".$currency_code;
							?>)
						<br/>
				<?php endforeach; ?>
			<?php else : ?>
			<?php echo l('No Taxes Recorded.', true); ?>
			<?php endif; ?>
		
		</td>
		<td class="text-center">
			<input class="room-charge-type" type="checkbox" value="<?php echo $charge_type['id'];?>" name="is_room_charge_type" autocomplete="off" 
				<?php 
					if($charge_type['is_room_charge_type']==1){
						echo 'checked="checked"';
					}
				?>
			/>
		</td>
                <td class="text-center">
			<input class="is_tax_exempt" type="checkbox" value="<?php echo $charge_type['id'];?>" name="is_tax_exempt autocomplete="off" 
				<?php 
					if($charge_type['is_tax_exempt']==1){
						echo 'checked="checked"';
					}
				?>
			/>
		</td>
		<td class="text-center">
			<?php
				if ($charge_type['is_default_room_charge_type'] != 1):
			?>

					<div class="delete-charge-type btn btn-light" id="<?php echo $charge_type['id']; ?>">X</div>
			<?php
				endif;
			?>

		</td>                
	</tr>
	<?php endforeach; ?>
	<?php else : ?>
	<h3><?php echo l('No charge types have been recorded.', true); ?></h3>
	<?php endif; ?>
</table>

</div>
<button id="add-charge-type" class="btn btn-primary"><?php echo l('add_charge_type'); ?></button>
</div></div>
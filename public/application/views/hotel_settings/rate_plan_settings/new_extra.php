<tr class="extra-field-tr" id="<?php echo $extra_id; ?>">
	<td>
		<input name="name" type="text"  class="form-control" value="<?php echo $extra_name; ?>" maxlength="250" style="width:250px"/>
	</td>
	<td class="text-center">
		<select name="charging-scheme" class="form-control">
            <option value=""><?php echo l('None', true); ?></option>
            <option value="on_start_date"><?php echo l('On start date', true); ?></option>
			<option value="once_a_day_inclusive_end_date"><?php echo l('Once a day (end date inclusive)', true); ?></option>
            <option value="once_a_day_exclusive_end_date"><?php echo l('Once a day (end date exclusive)', true); ?></option>
		</select>
	</td>
    
    <td class="text-center">
        <select name="extra-charge-type-id" class="form-control">
			<?php foreach ($charge_types as $charge_type) : ?>
				<option value="<?php echo $charge_type['id']; ?>" >
					<?php echo $charge_type['name']; ?>
				</option>
			<?php endforeach; ?>
		</select>
    </td>

    <td class="text-center">
        <input name="default-rate" type="number" min="0" class="form-control" value="0"/>
    </td>
    <td class="text-center">
    	<div class="checkbox">
            <label>
        		<input type="checkbox" name="show_on_pos" checked />
        	</label>
        </div>
    </td>
	<td class="text-center">
		<div class="delete-extra-button btn btn-default" id="<?php echo $extra_id; ?>">X</div>
	</td>
</tr>
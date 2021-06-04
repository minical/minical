<tr class="customer-field-tr" id="<?php echo $id; ?>">
	<td>
		<input name="name" type="text"  class="form-control" value="<?php echo $name; ?>" maxlength="50" style="width:250px"/>
	</td>
	<td class="text-center">
		<div class="checkbox">
				<label>
	  			<input type="checkbox" name="show_on_customer_form" autocomplete="off" checked="checked"/>
			</label>
		</div>
	</td>
	<td class="text-center">
		<div class="checkbox">
				<label>
	  			<input type="checkbox" name="show_on_registration_card" autocomplete="off" checked="checked"/>
			</label>
		</div>
	</td>
    <td class="text-center">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="show_on_in_house_report" autocomplete="off" checked="checked"/>
            </label>
        </div>
    </td>
    <td class="text-center">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="show_on_invoice" autocomplete="off" checked="checked"/>
            </label>
        </div>
    </td>
    <td class="text-center">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="is_required" autocomplete="off" />
            </label>
        </div>
    </td>
	<td class="text-center">
		<div class="delete-customer-field btn btn-default" id="<?php echo $id; ?>">X</div>
	</td>
</tr>
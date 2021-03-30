<tr class="tax-type-tr" id="<?php echo $tax_type_id; ?>">
	<td>
		<input name="tax-type-name" type="text"  class="form-control" value="<?php echo $tax_type; ?>" maxlength="50" style="width:250px"/>
	</td>
	<td>
		<input name="tax-rate" type="text"  class="form-control" value="0.0000" maxlength="50" style="width:150px;float: left;"/>
		<i class="fa fa-sliders fa-2x open-price-brackets" data-toggle="modal" data-target="#price-brackets-modal"></i>
	</td>
	<td>
		<select name="is-percentage" class="form-control" >
			<option value="1">
				%
			</option>
			<option value="0">
				<?php echo $currency_symbol; ?>
			</option>
		</select>
	</td>
	<td style="text-align: center;">
		<input type="checkbox" name="is-tax-inclusive" class="form-check-input">
	</td>
	<td>
		<div class="delete-tax-type btn btn-light" id="<?php echo $tax_type_id; ?>">X</div>
	</td>
</tr>
<div class="page-header">
	<h2><?php echo l('Accounting Settings', true); ?></h2>
</div>

<form method="post" class="form_is_total_balance_include_forecast" autocomplete="off">		
	<div class="form-group">
		<div class="form-group form-inline">
            <input type="checkbox" name="is_total_balance_include_forecast" <?=$company_data['is_total_balance_include_forecast'] == 1 ? 'checked=checked' : '';?>/>
            <label for="is_total_balance_include_forecast"><?php echo l('Total and balance includes forecast charges', true); ?></label>	
        </div>
	</div>
	<div class="col-sm-12 ">
		<input type="submit" class="btn btn-light" value="<?php echo l('Update', true); ?>" />
	</div>
</form>

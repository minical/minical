<div class="page-header">
	<h2><?php echo l('Tooltip Settings', true); ?></h2>
</div>

<form method="post" class="form_is_display_tooltip" autocomplete="off">		
	<div class="form-group">
		<div class="form-group form-inline">
            <input type="checkbox" name="is_display_tooltip" <?=$company_data['is_display_tooltip'] == 1 ? 'checked=checked' : '';?>/>
            <label for="is_display_tooltip"><?php echo l('When mouse hovers over a booking, show booking information', true); ?></label>	
        </div>
	</div>
	<div class="col-sm-12 ">
		<input type="submit" class="btn btn-light" value="<?php echo l('Update', true); ?>" />
	</div>
</form>

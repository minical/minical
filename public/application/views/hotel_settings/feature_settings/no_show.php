<div class="page-header">
	<h2><?php echo l('Enable Auto No Show', true); ?></h2>
</div>

<form method="post" class="form_auto_no_show" autocomplete="off">		
	<div class="form-group">
		<div class="form-group form-inline">
            <input type="checkbox" name="auto_no_show" <?=$company_data['auto_no_show'] == 1 ? 'checked=checked' : '';?>/>
            <label for="auto_no_show"><?php echo l('No show to happens automatically when Night Audit runs', true); ?></label>	
        </div>
	</div>
	<div class="col-sm-12 ">
		<input type="submit" class="btn btn-light" value="<?php echo l('Update', true); ?>" />
	</div>
</form>

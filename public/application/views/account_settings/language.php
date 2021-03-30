<div class="page-header">
	<h2><?php lang('language') ?></h2>
</div>

<form 
	class="form-horizontal container" 
	method="post" 
	action="<?php echo base_url();?>account_settings/language"
    enctype="multipart/form-data"
    autocomplete="off"
	>
	<div class="form-group">
		<label for="old_password" class="control-label col-sm-4">
			<?php echo l('Language', true); ?>
		</label>
		<div class="col-sm-8">
                    <!-- Call helper to get languages -->
                    <?php $languages = get_enabled_languages(); ?>

                    <!-- Display Languages Dynamically -->
                        <select class="form-control" name="language">
                            <?php if(!empty($languages)){ foreach ($languages as $key => $value) { ?>
                                <option value="<?php echo $value['id'].','.strtolower($value['language_name']); ?>" <?php if($current_language==strtolower($value['language_name'])) { echo("selected='selected'"); } ?>><?php echo $value['language_name']; ?></option>
                            <?php }} ?>
                        </select>
		</div>
	</div>

	<div class="form-group">
		<div class="col-sm-4">
		</div>
		<div class=" col-sm-8">
			<button class="btn btn-primary"><?php echo l('Update', true); ?></button>
		</div>
	</div>

</form>
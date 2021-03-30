<div id="submenu">
	<div class="max-width container">
		<ul>
			<?php $selected_submenu = isset($selected_submenu) && $selected_submenu ? str_replace(" ", "_", strtolower($selected_submenu)) : ""; ?>
            <li>
				<a href="<?php echo base_url(); ?>account_settings/password" <?php if ($selected_submenu == 'password') echo 'id="selected_submenu"'; ?> ><?php echo $this->lang->line('password'); ?></a>
			</li>
			<li>
				<a href="<?php echo base_url(); ?>account_settings/language" <?php if ($selected_submenu == 'language') echo 'id="selected_submenu"'; ?> ><?php echo $this->lang->line('language'); ?></a>
			</li>
            
		</ul>
	</div>
</div>
<br/><br/>
<?php echo l('Running night audit will:', true);?><br/>
<li><?php echo l('Charge nightly charges to all', true).' '.l($this->default_room_plural);?></li>
<li><?php echo l('Change selling date to:', true);?> <?php echo $selling_date?><li>
<?php echo l('Continue?', true);?>
<button href=""><?php echo l('yes', true);?></button>
<button href=""><?php echo l('no', true);?></button>
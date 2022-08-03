<div class="h3">
	<?php echo l('My Properties', true); ?>
    <a class="btn btn-primary" data-toggle="modal"
									data-target="#add-property-modal"><?php echo l('Add Property', true); ?></a>
</div>
	

<!-- Modal -->
<div class="modal fade" id="add-property-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php echo l('Add Property', true); ?></h4>
      </div>
      <div class="modal-body form-horizontal">
  			<div class="form-group">
				<label for="property_name" class="col-sm-4 control-label">
					<?php echo l('Property Name', true); ?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="property_name">
				</div>
			</div>
			<div class="form-group">
				<label for="number_of_rooms" class="col-sm-4 control-label">
					<?php echo l('Number of ', true).' '.l($this->default_room_plural); ?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="number_of_rooms">
				</div>
			</div>
			<div class="form-group">
				<label for="region" class="col-sm-4 control-label">
					<?php echo l('Region', true); ?>
				</label>
				<div class="col-sm-8">
					<select name="region" class="form-control">
						<option value="NA"><?php echo l('North America', true); ?></option>
						<option value="SA"><?php echo l('South America', true); ?></option>
						<option value="ANZ"><?php echo l('Austrialia and New Zealand', true); ?></option>
						<option value="ASIA"><?php echo l('Asia', true); ?></option>
						<option value="EAF"><?php echo l('Europe and Africa', true); ?></option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="subscription_type" class="col-sm-4 control-label">
					<?php echo l('Pricing Plan', true); ?>
				</label>
				<div class="col-sm-8">
					<select name="subscription_type" class="form-control">
						<option value="BASIC"><?php echo l('Basic', true); ?></option>
						<option value="PREMIUM"><?php echo l('Premium', true); ?></option>
					</select>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-success" id="add_property_button">
				<?php echo l('Add Property', true); ?>
			</button>
			<button type="button" class="btn btn-default" data-dismiss="modal">
				<?php echo l('Close', true); ?>
			</button>
		</div>
    </div>
  </div>
</div>


<div class="properties">
	<?php 
		if(isset($properties)) : 
			foreach ($properties as $property) : ?>
				<div class="panel panel-default property-div" id="<?php echo $property['company_id']; ?>">
					
					<div class="panel-body form-inline">
						
						<div class="form-group">
	  						<label>
								<i class="glyphicon glyphicon-home"></i> <?php echo $property['name']; ?> (ID: <?php echo $property['company_id']; ?>)
							</label>
						</div>
						<a href="<?php echo base_url()."menu/select_hotel/".$property['company_id']; ?>" class="btn btn-success pull-right">
							<?php echo l('Open Property', true); ?>
						</a>
						
					</div>
				</div>
					
		<?php endforeach; ?>
	<?php endif; ?>
</div>


</div>

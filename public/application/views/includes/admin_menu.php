<nav class="navbar navbar-default" role="navigation">
    <div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
    </div>
    
    <!-- Brand -->
    <div class="navbar-collapse collapse">

        <ul class="nav navbar-nav" style="width: 84%;">
			<li>
				<a href="<?php echo base_url(); ?>admin/dashboard"><?php echo l('Dashboard', true);?></a>
			</li>
			<li>
				<a href="<?php echo base_url(); ?>admin/translate_keywords"><?php echo l('Language Translation', true);?></a>
			</li>
			<?php if($this->company_email == SUPER_ADMIN): ?>
				<li>
					<a href="<?php echo base_url(); ?>admin/whitelabel_partners"><?php echo l('Whitelabel Partners', true);?></a>
				</li>
			<?php endif; ?>
			<li>
				<a href="<?php echo base_url(); ?>auth/logout" ><?php echo l('Logout', true);?></a>
			</li>
      	</ul>
	    
	    <div class="col-sm-1 col-md-1">
            <form class="navbar-form" role="search" method="GET" action="<?php echo base_url()."admin/company_list"; ?>" autocomplete="off" style="width: 235px;">
				<div class="input-group">
		            <input class="form-control" type="text" name="search_query" placeholder="Search company" value="<?php echo $this->input->get('search_query'); ?>"/>
					<div class="input-group-btn">
						<button class="btn btn-light" type="submit">
							<i class="glyphicon glyphicon-search"></i>
						</button>
					</div>
				</div>
			</form>   
	    </div>
	</div>
</nav>
<!-- Modal -->
<div class="modal fade" id="create-new-company-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Add Property</h4>
      </div>
      <div class="modal-body form-horizontal">
  			<div class="form-group">
				<label for="property_name" class="col-sm-4 control-label">
					<?php echo l('Property Name', true);?>
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="property_name">
				</div>
			</div>
			<div class="form-group">
				<label for="number_of_rooms" class="col-sm-4 control-label">
					<?php echo l('Number of Rooms', true);?>
				</label>
				<div class="col-sm-8">
					<input type="number" min="1" class="form-control" name="number_of_rooms">
				</div>
			</div>
			<div class="form-group">
				<label for="region" class="col-sm-4 control-label">
					<?php echo l('Region', true);?>
				</label>
				<div class="col-sm-8">
					<select name="region" class="form-control">
						<option value="NA">North America</option>
						<option value="SA">South America</option>
						<option value="ANZ">Austrialia and New Zealand</option>
						<option value="ASIA">Asia</option>
						<option value="EAF">Europe and Africa</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="subscription_type" class="col-sm-4 control-label">
					<?php echo l('Pricing Plan', true);?>
				</label>
				<div class="col-sm-8">
					<select name="subscription_type" class="form-control">
						<option value="BASIC"><?php echo l('Basic',true);?></option>
						<option value="PREMIUM"><?php echo l('Premium',true);?></option>
					</select>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-success" id="add_new_company_button">
			<?php echo l('Add Property',true);?>
			</button>
			<button type="button" class="btn btn-light" data-dismiss="modal">
			<?php echo l('Close',true);?>
			</button>
		</div>
    </div>
  </div>
</div>




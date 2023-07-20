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
				<a href="<?php echo base_url(); ?>admin/dashboard">Dashboard</a>
			</li>
			<li>
				<a href="<?php echo base_url(); ?>admin/translate_keywords">Language Translation</a>
			</li>
			<?php if($this->is_super_admin){ ?>
			 <li>
				<a href="<?php echo base_url(); ?>admin/show_monthly_report">Monthly Report</a>
			</li>
			<li>
				<a href="<?php echo base_url(); ?>admin/property_list">Property List</a>
			</li>
		   <?php }  ?>
		   <?php if($this->user_email != SUPER_ADMIN && $this->is_partner_owner){ ?>
				<li>
					<a href="<?php echo base_url()."admin/usage_report/".$this->vendor_id?>">Partner Usage</a>
				</li>
			<?php } ?>
			<?php if($this->user_email == SUPER_ADMIN): ?>
				<li>
					<a href="<?php echo base_url(); ?>admin/whitelabel_partners">Whitelabel Partners</a>
				</li>
			<?php else: ?>
				<li>
					<a href="<?php echo base_url(); ?>admin/profile_setting">Settings</a>
				</li>
			<?php endif; ?>
			<li>
				<a href="<?php echo base_url(); ?>auth/logout" >Logout</a>
			</li>
      	</ul>
	    
	    <div class="col-sm-1 col-md-1">
            <form class="navbar-form" role="search" method="GET" action="<?php echo base_url()."admin/company_list"; ?>" autocomplete="off" style="width: 195px;">
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
					Property Name
				</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="property_name">
				</div>
			</div>
			<div class="form-group">
				<label for="number_of_rooms" class="col-sm-4 control-label">
					Number of Rooms
				</label>
				<div class="col-sm-8">
					<input type="number" min="1" class="form-control" name="number_of_rooms">
				</div>
			</div>
			<div class="form-group">
				<label for="region" class="col-sm-4 control-label">
					Region
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
					Pricing Plan
				</label>
				<div class="col-sm-8">
					<select name="subscription_type" class="form-control">
						<option value="BASIC">Basic</option>
						<option value="PREMIUM">Premium</option>
					</select>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-success" id="add_new_company_button">
			Add Property
			</button>
			<button type="button" class="btn btn-light" data-dismiss="modal">
			Close
			</button>
		</div>
    </div>
  </div>
</div>




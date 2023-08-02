<!-- Hidden delete dialog-->
<div id="confirm_delete_dialog" ></div>

<div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			<div class="page-title-icon">
				<i class="pe-7s-users text-success"></i>
			</div>
			<div><?php echo l('Roles'); ?></div>
		</div>
  	</div>
</div>

<div class="main-card mb-3 card">
	<div class="card-body">
		<div class="container-fluid">
			<h3><?php echo l('Add New Role', true); ?></h3>
			<form  class="form-inline" method="post" action="<?php echo base_url();?>settings/company/roles" autocomplete="off" id="role-settings-form">
				<div class="form-group">
					<label class="sr-only" for="role_name"><?php echo l('role_name'); ?></label>
					<input type="text" class="form-control" name="role_name" value="<?php echo set_value('role_name');?>" placeholder="<?php echo l('Role Name', true); ?>">
				</div>
				<input type="submit" class="btn btn-light" value="<?php echo l('Add Role', true); ?>" />
			</form>
		</div>

		<div class="container-fluid">
			<h3><?php echo l('Modify existing roles', true); ?></h3>
			<p><?php echo l("Only the 'owner' can change permissions.", true); ?></p>
			<br/>
			<div class="table-responsive">
				<table class="table table-striped table-bordered">
					<tr>
						<th><?php echo l('role'); ?></th>
						<th><?php echo l('Permissions'); ?></th>
						<th><?php echo l('Users'); ?></th>
						<th><?php echo l('Action'); ?></th>
					</tr>
					<?php 

						if(!empty($roles)) : 
							foreach ($roles as $role) : ?>
	                    		<tr>
									<td id="<?php echo $role['role_id']; ?>" data-user_id="<?php echo $role['user_id']; ?>" class="role-name">
		                                <span><?php echo $role['role'];?></span>
		                                <input required type="text" class="hidden form-control" name="role-name" value="<?php echo $role['role'];?>" />
									</td>
									<td <?php if(!$role['is_existed']) { ?> style="background-color: #BDD8F3; border: solid 1px black;" <?php } ?> class="permission_count <?php echo !$role['is_existed'] ? 'user_roles' : '';  ?>">
										<?php foreach ($permission_count as $key => $value) {
											if($value['role_id'] == $role['role_id'] && !$role['is_existed']) {
												echo $value['permission_count'];
												echo '  <i class="glyphicon glyphicon-lock" aria-hidden="true" style="margin-right: 3px;"></i>';
											}
										} ?>
									</td>
									<td class="user_count">
										<?php foreach ($user_count as $key => $value) {
											if($value['role_id'] == $role['role_id']) {
												echo $value['total_users'];
												echo '  <i class="glyphicon glyphicon-user" aria-hidden="true" style="margin-right: 3px;"></i>';
											}
										} ?>
									</td>
								  
	                            	<td style="width:115px">
	                            		<?php if(!$role['is_existed']) : ?>
											<div class="btn-success btn btn-sm btn-block col-sm-6 edit-role-info <?php echo isset($can_edit_roles) && $can_edit_roles ? '' : 'hidden'; ?>">
												<?php echo l("Edit", true); ?>
											</div>
											<div class="btn-danger btn btn-sm btn-block col-sm-6 delete_role <?php echo isset($can_edit_roles) && $can_edit_roles ? '' : 'hidden'; ?>">
												<?php echo l("Delete", true); ?>
											</div>
		                                    <div class="clearfix"></div>
		                                <?php endif; ?>
									</td>				
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
						<tr>
							<td colspan="3">
								<h4><?php echo l('No roles', true); ?></h4>
							</td>
						</tr>
						<?php endif; ?>
				</table>
			</div>
		</div>
	</div>
</div>


<!-- The Modal -->
<div class="modal fade" id="user-permission-modal">
    <div class="modal-dialog">
      	<div class="modal-content">
	        <!-- Modal Header -->
	        <div class="modal-header">
	          <h4 class="modal-title">User Permissions</h4>
	          <button type="button" class="close" data-dismiss="modal">&times;</button>
	        </div>

	        <!-- Modal Body -->
	        <div class="modal-body">
	        	
	        </div>

	        <!-- Modal Footer -->
	        <div class="modal-footer">
	        	<input type="hidden" name="role_id" id="role_id" value="">
	          	<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	        </div>
        
      	</div>
    </div>
</div>
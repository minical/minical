<!-- Hidden delete dialog-->
<div id="confirm_delete_dialog" ></div>


<div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			<div class="page-title-icon">
				<i class="pe-7s-users text-success"></i>
			</div>
			<div><?php echo l('team'); ?>

		</div>
	</div>
  </div>
</div>





<div class="main-card mb-3 card">
	<div class="card-body">

<div class="container-fluid">
	<h3><?php echo l('team_settings'); ?></h3>
	<form class="form-inline" method="post" action="<?php echo base_url();?>settings/company/employee_auto_logout_settings/">
		<div class="form-group">
			<input name='employee_auto_logout_is_enabled' type="checkbox" autocomplete="off" 
				<?php
					if ($employee_auto_logout_is_enabled)
					{
						echo "checked='true'";
					}
				?>

			/> <?php echo l('Automatically log out user if he/she is inactive for 30 minutes', true); ?>
				
			<input type="submit" class="btn btn-light" name="submit"  value="<?php echo l('Update', true); ?>" />
		</div>
	</form>		
</div>

<div class="container-fluid">
	<h3><?php echo l('Add new User', true); ?></h3>
	<form  class="form-inline" method="post" action="<?php echo base_url();?>settings/company/employees" autocomplete="off" id="employee-settings-form" onsubmit="return warnSuperAdmin()">
		<div class="form-group">
			<label class="sr-only" for="employee_first_name"><?php echo l('first_name'); ?></label>
			<input type="text" class="form-control" name="employee_first_name" value="<?php echo set_value('employee_first_name');?>" placeholder="<?php echo l('First Name', true); ?>">
		</div>
		<div class="form-group">
			<label class="sr-only" for="employee_last_name"><?php echo l('last_name'); ?></label>
			<input type="text" class="form-control" name="employee_last_name" value="<?php echo set_value('employee_last_name');?>" placeholder="<?php echo l('Last Name', true); ?>">
		</div>
		<div class="form-group">
			<label class="sr-only" for="employee_email"><?php echo l('email'); ?></label>
			<input type="email" name="employee_email" class="form-control" value="<?php echo set_value('employee_email');?>" autocomplete="off" placeholder="<?php echo l('Email', true); ?>" />
		</div>
		<input type="submit" class="btn btn-light" value="<?php echo l('Add User', true); ?>" />
		<p class="help-block">
			* <?php echo l("You do not need to assign the user's password here. Users will receive an email containing instruction on how to setup their password.", true); ?>
		</p>
		
	</form>
</div>



<div class="container-fluid">
	<h3><?php echo l('Modify existing users', true); ?></h3>
	<p><?php echo l("Only the 'owner' can change permissions.", true); ?></p>
	<br />
	<div class="table-responsive">
	<table class="table table-striped table-bordered">
		<tr>
			<th><?php echo l('Name', true); ?></th>
			<th><?php echo l('status'); ?></th>
			<th><?php echo l('role'); ?></th>
			<th><?php echo l('permissions'); ?></th>
			<th><?php echo l('email'); ?></th>
			<th><?php echo l('Actions'); ?></th>
		</tr>
		<?php 
		
		$permission_types = Array(
                Array("title" => l("Bookings View Only", true), "value" => "bookings_view_only"),
				Array("title" => l("Access to Bookings", true), "value" => "access_to_bookings"),
				Array("title" => l("Access to Accounting", true), "value" => "access_to_customers"),
				Array("title" => l("Access to Rooms", true), "value" => "access_to_rooms"),
				Array("title" => l("Access to Reports", true), "value" => "can_view_reports"),
				Array("title" => l("Access to Ledger Reports (Must also have access to reports)", true), "value" => "access_to_ledger_reports"),
                Array("title" => l("Can change Settings", true), "value" => "can_change_settings"),
				Array("title" => l("Access to Integrations (Must also have access to settings)", true), "value" => "access_to_integrations"),
                Array("title" => l("Can post charges & payments", true), "value" => "can_post_charges_payments"),
                Array("title" => l("Can modify charges", true), "value" => "can_modify_charges"),
                Array("title" => l("Can delete/refund charges & payments", true), "value" => "can_edit_invoices"),
				Array("title" => l("Can delete bookings", true), "value" => "can_delete_bookings"),
                Array("title" => l("Can date manage", true), "value" => "can_date_manage"),
                //Array("title" => "Can delete payments", "value" => "can_delete_payments"),
                Array("title" => l("Minical reseller (access to /admin)", true), "value" => "is_salesperson"),
                Array("title" => l("Can view extensions & functionality", true), "value" => "access_to_extensions")
				);
            if(isset($employees)) : 
		?>
			<?php 

			foreach ($employees as $key => $employee) {

				if(isset($employee['permission']) && $employee['permission'] == 'is_admin') {
					unset($employees[$key]);
				}
			}

			foreach ($employees as $employee) : ?>
				
				<?php 
					if(	(
							$employee['permission'] != 'is_admin' &&
							$employee['is_admin'] != '1'
						) ||
						$this->session->userdata('user_role') == "is_admin" 
					): //Hide admin account?>
						
                    <tr>
							<td id="<?php echo $employee['user_id']; ?>" class="employee-name">
								<span><?php echo $employee['first_name'] . ' ' . $employee['last_name'];?></span>
                                <input required type="text" class="hidden form-control" name="employee-name" value="<?php echo $employee['first_name'] . ' ' . $employee['last_name'];?>" />
							</td>
							
							<td>
								<?php
									if (!$employee['activated'])
										echo l("Waiting for email activation", true);
									else 
										echo l("Active", true); 
								?>
							</td>
							<td>
                                <?php 
                                if(isset($can_edit_employees) && $can_edit_employees){									
                                ?>
                                <select name ="user-role-change" onChange="userRoleChange(this)">
                                    <option <?php if ($employee['permission'] == 'is_owner'): echo 'selected'; else: echo ''; endif; ?> value="is_owner"><?php echo l('Owner', true); ?></option>
                                    <option <?php if ($employee['permission'] == 'is_employee'): echo 'selected'; else: echo ''; endif; ?> value="is_employee" ><?php echo l('Employee', true); ?></option>
									<option <?php if ($employee['permission'] == 'is_housekeeping'): echo 'selected'; else: echo ''; endif; ?> value="is_housekeeping" ><?php echo l('Housekeeper', true); ?></option>
                                </select>
								<?php
                                }else{
									if ($employee['permission'] == 'is_owner') 
										echo l('Owner', true);
									elseif ($employee['permission'] == 'is_employee') 
										echo l('Employee', true);
									elseif ($employee['permission'] == 'is_housekeeping') 
										echo l('Housekeeper', true);
                                }
								?>
							</td>

							<?php if (!$can_edit_employees) : ?>
								<td><?php 
									if ($employee['permission'] == 'is_owner') 
										echo l('Owner', true);
									elseif ($employee['permission'] == 'is_employee') 
										echo l('Employee', true);
									elseif ($employee['permission'] == 'is_housekeeping') 
										echo l('Housekeeper', true); 
									else
									echo $employee['permission']; ?>
								</td>
							<?php else : ?>
								<?php if ($employee['permission'] == 'is_employee') { ?>						
									<td class="permissions">
										
										<?php
											foreach ($permission_types as $permission_type):
                                                if($permission_type['value'] == "is_salesperson" && $this->user_id != SUPER_ADMIN_USER_ID) {
                                                    continue;
                                                }
										?>
												<div class="checkbox">
												    <label>
												    	<input class="user-permission" type="checkbox"  name="<?php echo $permission_type['value']; ?>-checkbox" autocomplete="off" value="<?php echo $permission_type['value']; ?>"
															<?php
																if (isset($company_permissions)) {
																	foreach ($company_permissions as $permission) 
																	{												
																		if($permission['user_id'] == $employee['user_id'] &&
																				$permission['permission'] == $permission_type['value'])
																		{
																			echo 'checked="checked"';
																		}
																	}
																}
															?>
                                                            <?php
																if (isset($company_permissions)) {
																	foreach ($company_permissions as $permission) 
																	{												
																		if(
																		        $permission['user_id'] == $employee['user_id'] &&
																				(
																				        $permission_type['value'] == 'can_post_charges_payments' ||
                                                                                        $permission_type['value'] == 'can_modify_charges' ||
                                                                                        $permission_type['value'] == 'can_delete_bookings' ||
                                                                                        $permission_type['value'] == 'can_delete_payments' ||
                                                                                        $permission_type['value'] == 'can_edit_invoices' ||
                                                                                        $permission_type['value'] == 'access_to_bookings'
                                                                                ) &&
                                                                                $permission['permission'] == 'bookings_view_only'
                                                                        )
																		{
																			echo 'disabled';
																		}
																	}
																}
															?>
														/>
														<?php echo $permission_type['title']; ?>
													</label>
												</div>
										<?php
											endforeach;
										?>
									</td>			
							  <?php } else { ?>
									<td class="permissions"><?php 
										if ($employee['permission'] == 'is_owner') 
											echo l('owner', true);
										elseif ($employee['permission'] == 'is_employee') 
											echo l('employee', true);
										elseif ($employee['permission'] == 'is_housekeeping') 
											echo '<input class="user-permission hidden" type="checkbox" name="is_housekeeping-checkbox" autocomplete="off" value="is_housekeeping" checked=checked> '.l('Housekeeping', true); 
										else
											echo $employee['permission']; ?></td>
								<?php } ?>
							<?php endif; ?>
							
							<td class="employee_email"><span><?php echo $employee['email']; ?></span><input required type="email" class="hidden form-control" name="employee-email" value="<?php echo $employee['email'];?>" /></td>
							
							
							
                            <td style="width:115px">
								<?php if($employee['activated'] != 1){ ?>
									<div class="btn btn-primary btn-sm btn-block resend_email"><?php echo l('Resend activation email', true); ?></div>
								<?php } ?>
								<?php $edit_hidden_btn = $employee['permission'] == 'is_owner' ? 'class="btn-success btn btn-sm btn-block col-sm-6 edit-user-info hidden"' : 'class="btn-success btn btn-sm btn-block col-sm-6 edit-user-info"';
								$delete_hidden_btn = $employee['permission'] == 'is_owner' ? 'class="btn-sm btn btn-block btn-danger delete_employee col-sm-6 hidden"' : 'class="btn-sm btn btn-block btn-danger delete_employee col-sm-6"';
									if (
											$this->session->userdata('user_role') == "is_admin" ||
											(
												$employee['permission'] == 'is_employee' &&
												$employee['permission'] != 'is_owner' &&
												$employee['is_admin'] != '1'
											)

									) 
									{
										echo '<div '.$edit_hidden_btn.'>
												'.l("Edit", true).'
											</div>
											<div '.$delete_hidden_btn.'>
												'.l("Delete", true).'
											</div>'; 
                                        echo '<div class="clearfix"></div>';
									}
								?>
							</td>					
						</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php else : ?>
		<h3><?php echo l('No employees have been recorded', true); ?></h3>
		<?php endif; ?>
	</table>
	</div>
</div>

<input type="hidden" name="subscription_level" class="subscription_level" value="<?php echo $this->company_subscription_level; ?>">
<input type="hidden" name="subscription_state" class="subscription_state" value="<?php echo $this->company_subscription_state; ?>">
<input type="hidden" name="limit_feature" class="limit_feature" value="<?php echo $this->company_feature_limit; ?>">
<input type="hidden" name="user_count" class="user_count" value="<?php echo count($employees) ; ?>">
</div></div>
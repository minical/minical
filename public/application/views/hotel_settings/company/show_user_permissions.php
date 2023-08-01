<?php 
	$permission_types = Array(
        Array("title" => l("Bookings View Only", true), "value" => "bookings_view_only"),
		Array("title" => l("Access to Bookings", true), "value" => "access_to_bookings"),
        Array("title" => l("Can post charges & payments", true), "value" => "can_post_charges_payments"),
        Array("title" => l("Can modify charges", true), "value" => "can_modify_charges"),
        Array("title" => l("Can delete/refund charges & payments", true), "value" => "can_edit_invoices"),
		Array("title" => l("Can delete bookings", true), "value" => "can_delete_bookings"),
		Array("title" => l("Access to Accounting", true), "value" => "access_to_customers"),
		Array("title" => l("Access to Rooms", true), "value" => "access_to_rooms"),
		Array("title" => l("Access to Reports", true), "value" => "can_view_reports"),
		Array("title" => l("Access to Ledger Reports (Must also have access to reports)", true), "value" => "access_to_ledger_reports"),
		Array("title" => l("Access to Integrations (Must also have access to settings)", true), "value" => "access_to_integrations"),
        Array("title" => l("Can change Settings", true), "value" => "can_change_settings"),
        Array("title" => l("Can date manage", true), "value" => "can_date_manage"),
        Array("title" => l("Minical reseller (access to /admin)", true), "value" => "is_salesperson"),
        Array("title" => l("Can view extensions & functionality", true), "value" => "access_to_extensions")
	); 
?>

<h4>Role - <?php echo $role['role']; ?></h4>

<div class="table-responsive">
    <table class="table table-striped table-bordered permission-table">
        <tr class="user_roles">
            <td>
                <?php foreach ($permission_types as $permission_type):
                    if($permission_type['value'] == "is_salesperson" && $this->user_id != SUPER_ADMIN_USER_ID) {
                        continue;
                    } ?>
                    <div class="checkbox checbox-switch switch-primary permission-div">
                        <div class="text-container">
                            <?php echo $permission_type['title']; ?>
                        </div>
                        <label class="permission_name">
                            <input class="user-permission" type="checkbox"  name="<?php echo $permission_type['value']; ?>-checkbox" autocomplete="off" value="<?php echo $permission_type['value']; ?>"
                                <?php
                                    if (isset($company_permissions)) {
                                        foreach ($company_permissions as $permission)
                                        {                                                
                                            if(
                                                $permission['role_id'] == $role['role_id'] &&
                                                $permission['permission'] == $permission_type['value']
                                            )
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
                                                $permission['role_id'] == $role['role_id'] &&
                                                (
                                                    $permission_type['value'] == 'can_post_charges_payments' ||
                                                    $permission_type['value'] == 'can_modify_charges' ||
                                                    $permission_type['value'] == 'can_delete_bookings' ||
                                                    $permission_type['value'] == 'can_edit_invoices' ||
                                                    $permission_type['value'] == 'access_to_bookings'
                                                ) &&
                                                $permission['permission'] == 'bookings_view_only'
                                            )
                                            {
                                                echo 'disabled ';
                                            }
                                        }
                                    }
                                ?>
                            />
                            <span></span>
                        </label>
                        
                    </div>

                <?php endforeach; ?>
            </td>                
        </tr>
    </table>
</div>

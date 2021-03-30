
$(function() {

    // Checking/unchecking employee permissions
	$('.user-permission').click(function () {
		var that = this;
		var permissionName = $(this).parent().find('label').text();
        var permission = $(that).attr('value');
        var is_checked = $(that).prop('checked');
		var userID = $(that).parent().parent().parent().parent().find('.employee-name').attr('id');
		$(this).parent().find('label').text('Saving...');
		
		$.post(getBaseURL() + 'user/change_permission', {
				user_id: userID,
				permission: $(that).attr('value'),
				is_checked: $(that).prop('checked')
		}, function (result) {		
			$(that).parent().find('label').text(permissionName);
            if(is_checked && permission == 'bookings_view_only')
            {
                $(that).parent().parent().parent().find('.user-permission').each(function(){
                    if(
                        $(this).attr('value') != 'bookings_view_only' &&
                        (
                            $(this).attr('value') == 'access_to_bookings' ||
                            $(this).attr('value') == 'can_edit_invoices' ||
                            $(this).attr('value') == 'can_post_charges_payments' ||
                            $(this).attr('value') == 'can_modify_charges' ||
                            $(this).attr('value') == 'can_delete_bookings' ||
                            $(this).attr('value') == 'can_delete_payments'
                        )
                    )
                    {
                        $(this).removeAttr('checked');
                        $(this).prop("disabled", true);
                    }
                });
            }
            else
            {
                $(that).parent().parent().parent().find('.user-permission').each(function(){
                    if($(this).attr('value') != 'bookings_view_only')
                    {
                        $(this).prop("disabled", false);
                    }
                    
                });
            }
		}, 'json');
	});

	// Resending confirmation email
	$('.resend_email').click(function () {
		$.post(getBaseURL() + 'settings/company/re_email', {
			email: $(this).parent().parent().find('.employee_email').text()
			}, function (result) 
			{ 
				alert(l('Email verfication resent', true)); 
			});
	});


	$('.delete_employee').click(function () {
		var that = this;
		
		//Set custom buttons for delete dialog
		$("#confirm_delete_dialog")
		.html(l('Delete employee?',true))
		.dialog({
			title:l('Delete Employee',true),
			buttons: {
				"Confirm Delete":function() {					
					$.post(getBaseURL() + 'settings/company/remove_employee_access', {
						user_id: $(that).parent().parent().find('.employee-name').attr('id')
						}, function (results) {							
							if (results.isSuccess == true){
                                    $(that).parent().parent().remove();  //delete line of X button
                                    var user_count = parseInt($("input[name='user_count']").val());
									$("input[name='user_count']").val(user_count-1);
									//alert(results.message);
								}
								else {
									//alert(results.message);
								}
							}, 'json');
					$(this).dialog("close");
				},
				"Cancel": function() {
					$(this).dialog("close");
				}
			}
		});
		
		$("#confirm_delete_dialog").dialog("open");
	});

    $('.edit-user-info').click(function(){
        var that = this;
        var saveBtn = $(that).text(l('Save',true));
        var editableRow = $(that).parent().parent();
        editableRow.find('.employee-name span, .employee_email span').addClass('hidden');
        editableRow.find('.employee-name input[type="text"], .employee_email input[type="email"]').removeClass('hidden');
        saveBtn.on('click', function(){
            var userName = editableRow.find('.employee-name input[name="employee-name"]').val();
            var userEmail = editableRow.find('.employee_email input[name="employee-email"]').val();
            $.ajax({
                url: getBaseURL()+'settings/company/update_users_AJAX',
                type: "POST",
                data: {
                    user_id: editableRow.find('.employee-name').attr('id'),
                    user_name: userName,
                    user_email: userEmail,
                },
                success: function(data){
                    if(data == ''){
                        location.reload();
                    }
                }
            });
        });
    });
           
});

function userRoleChange(obj)
{
    var currentSelectedval = $(obj).val();
    var allEmployees = $('select[name="user-role-change"]').map(function(){return $(this).val()});
    if(currentSelectedval == 'is_employee' && $.inArray('is_owner', allEmployees) == -1)
    {
        alert(l("Can't remove owner. There must be one owner of a company. First create new owner.", true));
        $(obj).val('is_owner');
    }
    else if(currentSelectedval == 'is_employee' || currentSelectedval == 'is_housekeeping')
    {
        $.ajax({
            url: getBaseURL()+'settings/company/update_users_AJAX',
            type: "POST",
            data: {
                user_id: $(obj).parent().parent().find('.employee-name').attr('id'),
                new_user_role: $(obj).val(),
            },
            success: function(data){
                if(data == ''){
                   location.reload();
                }
            }
        });
    }
    else
    {
        var confirmval = confirm(l('Do you really want this employee to be owner of this company?', true));
        if(confirmval == true){
            $.ajax({
                url: getBaseURL()+'settings/company/update_users_AJAX',
                type: "POST",
                data: {
                    user_id: $(obj).parent().parent().find('.employee-name').attr('id'),
                    new_user_role: $(obj).val(),
                },
                success: function(data){
                    if(data == ''){
                       location.reload();
                    }
                }
            });
        }else{
            $(obj).val('is_employee');
        }
    }
}

function validateUserRestriction()
{
    var user_count = parseInt($("input[name='user_count']").val());
    var companySubscriptionLevel = $("input[name='subscription_level']").val();
    var companySubscriptionState = $("input[name='subscription_state']").val();
    var companyFeatureLimit      = $("input[name='limit_feature']").val();

    if(
        companyFeatureLimit == 1 && 
        companySubscriptionState != 'trialing' &&
        (
            (companySubscriptionLevel == STARTER && user_count >= 1) ||
            (companySubscriptionLevel == BASIC && user_count >= 3) ||
            (companySubscriptionLevel == PREMIUM && user_count >= 5)
        )
    )
    {
        $("#access-restriction-message").modal("show");
        $('#access-restriction-message .restriction_message').html(l('You have reached maximum number of users. Please upgrade your subscription plan to add more users.', true));
        return false;
    }
    return true;
}

function warnSuperAdmin() {
    if(is_current_user_superadmin) {
        alert(l('Superadmin Alert: Please make sure the hotel owner is aware of this new user, this could be a hacking attempt.', true));
    }
}

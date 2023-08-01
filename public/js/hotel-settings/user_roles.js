
$(function() {

    $('.user_roles').click(function () {
        var roleID = $(this).find('.role-name').attr('id');
        
        $.ajax({
            url: getBaseURL()+'settings/company/show_user_permissions/' + roleID,
            type: "POST",
            data: {},
            success: function(resp){
                $('#user-permission-modal').find('.modal-body').html(resp);
                $('#user-permission-modal').find('.modal-footer').find('#role_id').val(roleID);
                $('#user-permission-modal').modal('show');
            }
        });

    });

    // Checking/unchecking employee permissions
	$('#user-permission-modal').on('shown.bs.modal', function() {
        // Attach the change event handler to the checkboxes with class 'user-permission'
        $('.user-permission').on('change', function() {

            var that = this;
            var permissionName = $(this).closest('.permission-div').find('.text-container').text().trim();
            var permission = $(that).attr('value');
            var is_checked = $(that).prop('checked');
            var roleID = $('#role_id').val();
            $(this).parent().find('label').text('Saving...');
            
            $.post(getBaseURL() + 'user/change_role_permission', {
                    role_id: roleID,
                    permission: $(that).attr('value'),
                    is_checked: $(that).prop('checked')
            }, function (result) {
                console.log('result',result);
                if(result.success){
                    $(that).parent().find('label').text(permissionName);
                    if(is_checked && permission == 'bookings_view_only')
                    {
                        $(that).parent().parent().parent().find('.user-permission').each(function(){
                            console.log('come',$(this).attr('value'));
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
                                $(this).next('span').css('background-color', 'darkgrey');
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
                } else {
                    alert(result.message);
                    $(that).prop('checked', false);
                }
            }, 'json');






        });
    });

	$('.delete_role').click(function () {
		var that = this;
		
		//Set custom buttons for delete dialog
		$("#confirm_delete_dialog")
		.html(l('Delete role?',true))
		.dialog({
			title:l('Delete Role',true),
			buttons: {
				"Confirm Delete":function() {					
					$.post(getBaseURL() + 'settings/company/remove_role', {
						role_id: $(that).parent().parent().find('.role-name').attr('id')
						}, function (results) {							
							if (results.isSuccess == true){
                                    $(that).parent().parent().remove();  //delete line of X button
									//alert(results.message);
								}
								else {
									alert(results.message);
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

    $('.edit-role-info').click(function(){
        var that = this;
        var saveBtn = $(that).text(l('Save',true));
        var editableRow = $(that).parent().parent();
        editableRow.find('.role-name span').addClass('hidden');
        editableRow.find('.role-name input[type="text"]').removeClass('hidden');
        saveBtn.on('click', function(){
            var roleName = editableRow.find('.role-name input[name="role-name"]').val();
            $.ajax({
                url: getBaseURL()+'settings/company/update_roles_AJAX',
                type: "POST",
                data: {
                    role_id: editableRow.find('.role-name').attr('id'),
                    role_name: roleName
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
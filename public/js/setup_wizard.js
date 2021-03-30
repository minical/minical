$(function () {
	
	//room types setup wizard is different form hotel settings
	$('#setup-wizard-add-room-type-button').click(function () {		
		$.post(getBaseURL() + 'room_type/create_room_type', function (data) {	
			window.location.reload();
		});
	});
	
	//room types setup wizard is different form hotel settings
	$('.room-type-name-editable').editable(getBaseURL() + 'room_type/change_room_type_name', {
		indicator: 'Saving...',
		tooltip:'Click to edit...',
		cancel: 'Cancel',
		submit: 'Ok',
		id: 'room_type_id',
        name: 'room_type_name',
		callback: innGrid.manageValidation
	});
	
	$('.room-type-acronym-editable').editable(getBaseURL() + 'room_type/change_acronym', {
		indicator: 'Saving...',
		tooltip:'Click to edit...',
		cancel: 'Cancel',
		submit: 'Ok',
		id: 'room_type_id',
        name: 'acronym',
		callback: innGrid.manageValidation
	});
	
	$('.delete-room-type').on('click' , function () {
		var that = this;
		
		var roomTypeId = $(this).attr('id');
		var roomTypeName = $(this).parent().parent().find('.room-type-name-editable').text();		
		
		//Set custom buttons for delete dialog
		$("#confirm_delete_dialog")
		.html('Are you sure you want to delete ' + roomTypeName + '?')
		.dialog({
			title:'Delete Room Type',
			buttons: {
				"Delete Room Type": {
					text: 'Delete Room Type', 
					id: 'submit-delete-button',
					click: function() {
						$.post(getBaseURL() + 'room_type/delete_room_type', {
							room_type_id: roomTypeId
							}, function (results) {
								if (results.isSuccess == true){
										$(that).parent().parent().remove();   //delete line of X button
										//alert(results.message);
									}
									else {
										//alert(results.message);
									}
								}, 'json');
						$(this).dialog("close");
					}
				},
				"Cancel": function() {
					$(this).dialog("close");
				}
			}
		});
		
		$("#confirm_delete_dialog").dialog("open");
	});
	
	$('#menu-nav, #selling-date, #innGrid-settings').hide();
});	
innGrid.closeDialog = function() {
	window.jQuery('#booking_dialog').dialog('close');
}

$(function(){
	
	$("#room_close_button").on('click', function() {			
		innGrid.closeDialog();
		parent.location.reload(); // reload parent page to reflect on changes
	});
	
});
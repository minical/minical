
$(document).on('click', '.extension-status-button', function () {
	var extensionName = $(this).attr('name');
    // var extensionStatus = $(this).data('status');

    if ($(this).prop("checked")) {
      
         var extension_action = extensionStatus = 0;
    } else {
    
    	 var extension_action = extensionStatus = 1;
    }

    var extension_action = extensionStatus == 1 ? "deactivate" : "activate";
	
	var r = confirm(l('Are you sure you want to '+extension_action+' this extension?'));
	if (r == true) {
	    $.post(getBaseURL() + 'extensions/change_extension_status', {
		extension_name: extensionName,
        extension_status: extensionStatus
		}, function (results) {
			if (results.success == true){
					location.reload();
				}
				else {
					//alert(results.message);
				}
			}, 'json');
	}
});

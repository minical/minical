$(function() {

	$('#add-charge-type').click(function () {
		$.post(getBaseURL() + 'settings/accounting/create_charge_type', function (){
			window.location.reload();
		});		
	});
	

	$('.charge-type-name-editable').editable(getBaseURL() + 'settings/accounting/change_charge_type_name', {
		indicator: 'Saving...',
		tooltip: 'Click to edit...',
		cancel: 'Cancel',
		submit: 'Ok',
		id: 'charge_type_id',
		name: 'charge_type_name',
        onsubmit: function(settings, el) {
            var editId = el.id;
            var editName = $('input[name=charge_type_name]').val();
            var checkChargeType = false;
            $('.charge-type-name-editable').each(function(){ 
                var chargeTypeExitId =  $(this).attr('id');
                 var chargeTypeExitName =  $(this).text();
                 if(chargeTypeExitId != editId){
                     if(editName == chargeTypeExitName){
                         alert(l('Charge type already exist!')); 
                         checkChargeType = true;
                         return false;
                     }
                 }
                 
             });
             if(checkChargeType == true){
                 return false;
             }
        }
	});
	
	$('.charge-type-tax').click(function () {
		var that = this;
		var taxTypeName = $(this).parent().find('label').text();
		var chargeTypeID = $(that).closest('tr').attr('id');
		$(this).parent().find('label').text('Saving...');
		
		$.post(getBaseURL() + 'settings/accounting/change_charge_type_tax', {
				charge_type_id: chargeTypeID,
				tax_type_id: $(that).attr('value'),
				is_checked: $(that).prop('checked')
				}, function (result) {
			$(that).parent().find('label').text(taxTypeName);
		}, 'json');
	});
	
	$('.room-charge-type').click(function () {
		var that = this;
		var chargeTypeID = $(that).attr('value');
		
		$.post(getBaseURL() + 'settings/accounting/change_room_charge_type', {
				charge_type_id: chargeTypeID,
				is_checked: $(that).prop('checked')
				}, function (result) {
			
		}, 'json');
	});
        
        $('.is_tax_exempt').click(function () {
		var that = this;
		var chargeTypeID = $(that).attr('value');
		
		$.post(getBaseURL() + 'settings/accounting/change_tax_exempt', {
				charge_type_id: chargeTypeID,
				is_checked: $(that).prop('checked')
				}, function (result) {
			
		}, 'json');
	});
	

	$('.delete-charge-type').click(function () {		
		var that = this;
		var chargeTypeName = $(that).parent().parent().find('.charge-type-name-editable').text();
		
		//Set custom buttons for delete dialog
		$("#confirm_delete_dialog")
		.html(l('Are you sure you want to remove charge type')+ '<span class="charge-type-name-editable">' + chargeTypeName + '</span>?')
		.dialog({
			title:l('Delete Charge Type'),
			buttons: {				
				"Confirm Delete":function() {					
					$.post(getBaseURL() + 'settings/accounting/delete_charge_type', {
						charge_type_id: $(that).attr('id')
						}, function (results) {							
							if (results.isSuccess == true){
									$(that).parent().parent().remove();  //delete line of X button
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
	
});
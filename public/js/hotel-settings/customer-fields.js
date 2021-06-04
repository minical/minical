innGrid.saveAllCustomerFields = function () {
    var updatedCustomerFields = {};
	$(".customer-field-tr").each(function()
	{
		var customerFieldTr = $(this);
		var customerFieldId = customerFieldTr.attr('id');
		var customerFieldName = customerFieldTr.find('[name="name"]').val();
		//console.log(customerFieldName);
		// if (customerFieldName == 'New Customer Field') {
        //     alert('Please fill in customer field name');
        //     customerFieldTr.find('[name="name"]').focus();
        //     return false;
        // }
        
        updatedCustomerFields[customerFieldId] = {
            id: customerFieldId,
            name: customerFieldName,
            show_on_customer_form: (customerFieldTr.find('[name="show_on_customer_form"]').prop('checked'))?1:0,
            show_on_registration_card: (customerFieldTr.find('[name="show_on_registration_card"]').prop('checked'))?1:0,
            show_on_in_house_report: (customerFieldTr.find('[name="show_on_in_house_report"]').prop('checked'))?1:0,
			show_on_invoice: (customerFieldTr.find('[name="show_on_invoice"]').prop('checked'))?1:0,
            is_required: (customerFieldTr.find('[name="is_required"]').prop('checked')) ? 1 : 0 
        };
	});
    //console.log(updatedCustomerFields);
    //Populate updates to standard customer field information
	$.post(getBaseURL() + 'settings/reservations/update_customer_field', {
			updated_customer_fields: updatedCustomerFields
		}, function (result) {
			//To Do: error checking.
			//This is incomplete because it doesn't check whether the update_rate posts are complete
			//To Do: complete checking with update-rates
			if(result.success)
			{
				alert(l('All customer fields saved'));
			}
			else
			{
				alert((result.error));
			}
	}, 'json');
}

$(function() {

	$('#add-customer-field').click(function () {
		$.post(getBaseURL() + 'settings/reservations/get_new_customer_field_div', function (div){
			//console.log(div);
			$('#customer-fields').append(div);
		});		
	});
	

	$('#save-all-customer-fields-button').on("click", function () {	
		innGrid.customerFieldSavedCount = 0;		
		innGrid.saveAllCustomerFields();
	});

	$(document).on('click', '.delete-customer-field', function () {		
		var that = this;
		//Set custom buttons for delete dialog
		$("#confirm_delete_dialog")
		.html(l('Are you sure ?'))
		.dialog({
			title:l('Delete Customer Field'),
			buttons: {				
				"Confirm Delete":function() {					
					$.post(getBaseURL() + 'settings/reservations/delete_customer_field', {
						id: $(that).parent().parent().attr('id')
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
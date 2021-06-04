innGrid.saveAllCustomerTypes = function () {
    var updatedCustomerTypes = {};
    var sort = 0;
    $(".customer-type-tr").each(function()
	{
		var customerTypeTr = $(this);
		var customerTypeId = customerTypeTr.attr('id');
		var customerTypeName = customerTypeTr.find('[name="customer-type-name"]').val();
        var customerTypeSortOrder = sort;
        
		if (customerTypeName == 'New Customer Type') {
            alert(l('Please fill in customer type name'));
            customerTypeTr.find('[name="customer-type-name"]').focus();
            return false;
        }
        updatedCustomerTypes[customerTypeId] = {
            id: customerTypeId,
            name: customerTypeName,
            sort_order: customerTypeSortOrder
        };
        sort++;
    });
    
    $.post(getBaseURL() + 'settings/reservations/update_customer_type', {
            updated_customer_types: updatedCustomerTypes
        }, function (result) {
            if(result.success)
            {
                alert(l('All customer types saved'));
            }
            else
            {
                alert(result.error);
            }
    }, 'json');
}

$(function() {

	$('#add-customer-type').click(function () {
		$.post(getBaseURL() + 'settings/reservations/get_new_customer_type_div', function (div){
			console.log(div);
			$('#customer-types').append(div);
		});		
	});
	

	$('#save-all-customer-types-button').on("click", function () {	
		innGrid.customerTypeSavedCount = 0;		
		innGrid.saveAllCustomerTypes();
	});

	$(document).on('click', '.delete-customer-type', function () {		
		var that = this;
		//Set custom buttons for delete dialog
		$("#confirm_delete_dialog")
		.html(l('Are you sure ?'))
		.dialog({
			title:l('Delete Customer Type'),
			buttons: {				
				"Confirm Delete":function() {					
					$.post(getBaseURL() + 'settings/reservations/delete_customer_type', {
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
	
    $( "#sortable" ).sortable();
});
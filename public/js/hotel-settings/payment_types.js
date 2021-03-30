
$(function() {


	$('#add-payment-type').click(function () {
		$.post(getBaseURL() + 'settings/accounting/create_payment_type', function (){
			window.location.reload();
		});		
	});


	$('.payment-type-name-editable').editable(getBaseURL() + 'settings/accounting/change_payment_type_name', {
		indicator: 'Saving...',
		tooltip: 'Click to edit...',
		cancel: 'Cancel',
		submit: 'Ok',
		id: 'payment_type_id',
		name: 'payment_type_name',
         onsubmit: function(settings, el) {
            var editId = el.id;
            var editName = $('input[name=payment_type_name]').val();
            var checkPaymentType = false;
            $('.payment-type-name-editable').each(function(){ 
                var paymentTypeExitId =  $(this).attr('id');
                 var paymentTypeExitName =  $(this).text();
                 if(paymentTypeExitId != editId){
                     if(editName == paymentTypeExitName){
                         alert(l('Payment type already exist!')); 
                         checkPaymentType = true;
                         return false;
                     }
                 }
                 
             });
             if(checkPaymentType == true){
                 return false;
             }
         }
	});
	
	
	$('.delete-payment-type').click(function () {		
		var that = this;
		var paymentTypeName = $(that).parent().parent().find('.payment-type-name-editable').text();
		
		//Set custom buttons for delete dialog
		$("#confirm_delete_dialog")
		.html(l('Are you sure you want to remove payment type')+'<span class="payment-type-name-editable">' + paymentTypeName + '</span>?')
		.dialog({
			title:l('Delete Payment Type'),
			buttons: {				
				"Confirm Delete":function() {					
					$.post(getBaseURL() + 'settings/accounting/delete_payment_type', {
						payment_type_id: $(that).attr('id')
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
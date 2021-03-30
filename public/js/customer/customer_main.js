$(function() {	
	
	$("#create-new-customer").on("click", function() {
		$(document).openCustomerModal();
	});


	$(".customer-profile").on("click", function() {
		$(document).openCustomerModal({
			customer_id: $(this).attr("id")
		});
	});

	// delete customer
	$(".delete_customer").on('click', function(e) {	
		if(!confirm(l('Delete this permanently?')))
			return;

		var button = $(this);
		$.post( getBaseURL() + "index.php/customer/delete_customer_JSON/",
		{ customer_id: button.attr('name'),show_deleted: button.attr('showdelete') },
		function(data){
			if (data.status == "fail")
				alert(data.msg);
			else
				button.parents("tr").remove(); // remove the tr on page.
		}, "json");
		return false; // prevent default click action from happening!
	});	
    
	$("#search_date").datepicker({ dateFormat: 'yy-mm-dd' });
    
	$(".per_page, #search_date").on('change',function(){
       $('#search_submit').click();
	});
	
});


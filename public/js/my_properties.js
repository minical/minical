$(function() {

    $("#add_property_button").on("click", function () {
        var that = this;
        $(that).prop("disabled", true);

		$.ajax({
			type: "POST",
			url: getBaseURL() + "auth/create_company/",
			data: {
				name: 		$("[name='property_name']").val(),
				number_of_rooms: 	$("[name='number_of_rooms']").val(),
				region: 			$("[name='region']").val(),
				subscription_type: 	$("[name='subscription_type']").val(),
				created_by:         'admin'
			},
			dataType: "json",
			success: function(data) 
			{
				window.location.reload();			
			}
		});	

	});

});
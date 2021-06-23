
// update Current Time based on timezone
innGrid.updateCurrentTime = function() {
	
	$.post( getBaseURL() + "company/get_current_time_in_JSON/", 
		{
			time_zone: $('select[name="time_zone"]').val() 
		}, function (data) {
			$("[name='current_time']").text(data);
		}, 'json'
	);
}

$(function() {
	innGrid.updateCurrentTime();
	$("select[name='time_zone']").change(innGrid.updateCurrentTime);
});

$(document).ready(function() {
  $("#basic-form").validate({
    rules: {
      company_phone : {
        required: true,
        minlength: 3
      },
       company_name : {
        required: true,
        minlength: 3
      },
      company_address : {
      	required: true,
      	minlength: 3
      },
      company_city: {
        required: true,
      },
      company_region: {
      	required: true,
      },
      company_country: {
      	required: true,
      },
      company_postal_code: {
      	required: true,
      },
      company_email: {
        required: true,
        email: true
      },
    },
    messages : {
    	 company_name: {
      	 required: "Please enter your Company Name",
        minlength: "company_phone should be at least 3 characters"
      },
      company_phone: {
      	 required: "Please enter your Phone Number",
        minlength: "company_phone should be at least 3 characters"
      },
      company_address: {
      	 required: "Please enter your Company Address",
        minlength: "company_phone should be at least 3 characters"
      },
      company_city: {
      	required: "Please enter your City."
      },
      company_region: {
      	required: "Please enter your Region."
      },
      company_country: {
      	required: "Please enter your Country."
      },
      company_postal_code: {
      	required: "Please enter your Postal Code."
      }, 
      company_email: {
      	required:"Please enter your Email",
        email: "The email should be in the format: abc@domain.tld"
      }
    }
  });
});
$(function() {

	var partnerData = "";

    $(".contact_partner").on("click", function () {
    	var partnerName = $(this).data('partner_name');
    	var partnerEmail = $(this).data('partner_email');
    	$.ajax({
                type: "POST",
                url: getBaseURL() + 'partners/get_partners_details',
                dataType: "json",
                data: {}, // serializes the form's elements.
                success: function(response)
                {
                    console.log('response', response);
                    if(response.success){
                        partnerData = response.partners;
                    
			    	console.log('partnerData', partnerData);
			        partnerFormHtml = '<div class="modal fade" id="partner_contact_form">'+
                                '<div class="modal-dialog" role="document">'+
                                    '<div class="modal-content">'+
                                        '<div class="modal-header">'+
                                            '<h5 class="modal-title">'+l('miniCal Customer - Partner Contact Form')+'</h5>'+
                                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close">'+
                                              '<span aria-hidden="true">&times;</span>'+
                                            '</button>'+
                                        '</div>'+
                                        '<div class="modal-body">'+
                                            '<form id="contact_partner_form">'+
                                                '<div class="form-group">'+
                                                    '<label for="company_name" class="col-form-label">'+l("What's your company's name?")+'</label>'+
                                                    ' <span class="required" style="color:red;">*</span>'+
                                                    '<input type="text" class="form-control" id="company_name" name="company_name" value="'+partnerData.company_name+'">'+
                                                '</div>'+
                                                '<div class="form-group">'+
                                                    '<label for="property_manage" class="col-form-label">'+l("How many properties do you manage?")+'</label>'+
                                                    ' <span class="required" style="color:red;">*</span>'+
                                                    '<input type="text" class="form-control" id="property_manage" name="property_manage" value="'+partnerData.property_manage+'">'+
                                                '</div>'+
                                                '<div class="form-group">'+
                                                    '<label for="name" class="col-form-label">'+l("What's your name?")+'</label>'+
                                                    ' <span class="required" style="color:red;">*</span>'+
                                                    '<input type="text" class="form-control" id="name" name="name" value="'+partnerData.name+'">'+
                                                '</div>'+
                                                '<div class="form-group">'+
                                                    '<label for="email" class="col-form-label">'+l("What's your email?")+'</label>'+
                                                    ' <span class="required" style="color:red;">*</span>'+
                                                    '<input type="text" class="form-control" id="email" name="email" value="'+partnerData.email+'">'+
                                                '</div>'+
                                                '<div class="form-group">'+
                                                    '<label for="company_location" class="col-form-label">'+l("Where is your company located?")+'</label>'+
                                                    ' <span class="required" style="color:red;">*</span>'+
                                                    '<input type="text" class="form-control" id="company_location" name="company_location" value="'+partnerData.location+'">'+
                                                '</div>'+
                                                '<div class="form-group">'+
                                                    '<label for="description" class="col-form-label">'+l("Can you tell us more about your business? Are there any features you are looking for?")+'</label>'+
                                                    '<textarea class="form-control" rows="5" id="description" name="description"></textarea>'+
                                                '</div>'+
                                                '<input type="hidden" id="partner_name" name="partner_name" value="'+partnerName+'">'+
                                                '<input type="hidden" id="partner_email" name="partner_email" value="'+partnerEmail+'">'+
                                            '</form>'+
                                        '</div>'+
                                        '<div class="modal-footer">'+
                                            '<button type="button" class="btn btn-primary submit_contact_form">'+l('Submit')+'</button>'+
                                            '<button type="button" class="btn btn-secondary" data-dismiss="modal">'+l('Close')+'</button>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>';
                        $('body').append(partnerFormHtml);
                        $('#partner_contact_form').modal('show');
                } 
            }
        });
    });

	$('body').on('click', '.submit_contact_form', function(){

        var company_name = $('#company_name').val();
        var property_manage = $('#property_manage').val();
        var name = $('#name').val();
        var email = $('#email').val();
        var company_location = $('#company_location').val();
        var description = $('#description').val();
        var partner_name = $('#partner_name').val();
        var validate = '';

        if(company_name == ''){
            validate = validate+l("Company name is required");
        }
        if(property_manage == ''){
            validate = validate+"\n"+l('Property info is required');
        }
        if(name == ''){
            validate = validate+"\n"+l('Name is required');
        }
        if(email == ''){
            validate = validate+"\n"+l('Email is required');
        }
        if(company_location == ''){
            validate = validate+"\n"+l('Company location is required');
        }

        $(this).text('Processing...');
        $(this).prop('disabled', true);
        
        if(validate == ''){
            $.ajax({
                type: "POST",
                url: getBaseURL() + 'partners/send_email_to_partner',
                dataType: "json",
                data: $('#contact_partner_form').serialize(), // serializes the form's elements.
                success: function(response)
                {
                    console.log('response', response);
                    if(response.success){
                    	$(this).prop('disabled', false);
                        alert(response.message);
                        location.reload();
                    } 
                }
            });
        }
        else {
            alert(validate);
        }
    });

});
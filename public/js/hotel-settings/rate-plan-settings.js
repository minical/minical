innGrid.populateRoomTypeList = function(roomTypeID = null)
{
	
	$.getJSON(getBaseURL() + 'settings/room_inventory/get_room_types_JSON', 
        function (roomTypes) {
        	roomTypeListDiv = $("<ul/>", {
        		class: "nav nav-pills nav-stacked",
                id   : 'sortable'
        	});
        	$.each(roomTypes, function(key, roomType) { 
        		$(roomTypeListDiv).append(
        			$("<li/>", {
        				role 	: 'presentation',
        				class 	: 'glyphicon_icon room-type room-type-'+roomType['id'],
        				room_type_id 	: roomType['id'],        				
        			}).append(
        				$("<a/>", {
        					href: '#'+roomType['id'],
        					text: roomType['name']+" ("+roomType['rate_plan_count']+")",
                            style: "padding-left: 5px;"
        				})
                        .prepend($('<span/>', {class: "grippy", style: "margin-right: 10px;"}))
        			)
        		);
        	});

        	$("#room-type-list").html(roomTypeListDiv);
            
            if(roomTypeID) {
                innGrid.populateRatePlans(roomTypeID);
            }
        	else if(roomTypeID = window.location.hash.replace('#',''))
            {
                innGrid.populateRatePlans(roomTypeID);
            }
			else if (roomTypeID == '')
			{
				innGrid.populateRatePlans($(".room-type").first().find("a").attr("href").replace('#',''));
			}
            sortRoomTypes();
        }
    );
}

innGrid.populateRatePlans = function(roomTypeID)
{
	$(".room-type").each(function() {
		$(this).removeClass("active");
	})
	
	$(".rate-plans").hide();
	
	$(".room-type-"+roomTypeID).addClass("active");
	$.get(getBaseURL() + 'settings/rates/get_rate_plans', 
		{
			room_type_id: roomTypeID
		},
        function (data) {
        	$(".rate-plans").html(data);
        	$(".rate-plans").fadeIn('slow');
                
                var btn_rate_count = 1;
                var rate_count = 1;
                        setTimeout(function(){ 
                            $( '.update-rate-plan-button').each( function() { 
                                $(this).attr('count',btn_rate_count);
                                btn_rate_count++;
                           });
                        }, 3000); 
                setTimeout(function(){ 
                    $( 'textarea').each( function() { 
                        $(this).attr('id','rateDesc_'+rate_count);
                        CKEDITOR.replace( $(this).attr('id'),{
                           customConfig: '/application/third_party/ckeditor/config.js'
                        } );
                        rate_count++;
                    });
               }, 3000);  

            $(".select-extras").select2({
                placeholder: l("Select Extra Items (These are bookable on Online booking engine.)"),
                allowClear: true
            });
        }
    );
}

innGrid.updateRates = function(start_date, end_date, rate_plan_id) {
    
	$.ajax({
	    type   : "POST",
	    url    : getBaseURL() + "channel_manager/update_rates",
	    data   : {
	        start_date: start_date,
	        end_date: end_date,
	        rate_plan_id: rate_plan_id
	    },
	    dataType: "json",
	    success: function (data) {
	        console.log(data);
	    }
	});
}

innGrid.updateRatePlan = function(ratePlan) {
	
	var ratePlanID = ratePlan.attr('id');
	var descrip_id = 'rateDesc_'+ratePlanID;
	var description = CKEDITOR.instances[descrip_id].getData();
	var ratePlanName = ratePlan.find('[name="rate-plan-name"]').val();
	var roomTypeID = ratePlan.find('[name="room-type-id"]').val();
	var currencyID = ratePlan.find('[name="currency-id"]').val();
	var chargeTypeID = ratePlan.find('[name="charge-type-id"]').val();
	var isShownInOnlineBookingEngine = ratePlan.find('[name="is-shown-in-online-booking-engine"]').val();
	var extraIds = ratePlan.find('[name="extra[]"]').val();
	//Populate updates to standard room type information
	$.post(getBaseURL() + 'settings/rates/update_rate_plan_AJAX', {
			rate_plan_id: ratePlanID,
			rate_plan_name: ratePlanName,
			room_type_id: roomTypeID,
			charge_type_id: chargeTypeID,
			currency_id: currencyID,
			description: description,
			is_shown_in_online_booking_engine: isShownInOnlineBookingEngine,
            extras: extraIds,
        }, function (result) {
				ratePlan.find(".updated-message").removeClass("hidden");
                innGrid.updateRates("","",ratePlanID);
				window.setTimeout(function() {
					ratePlan.find(".updated-message").fadeTo(500, 0).slideUp(500, function(){
						ratePlan.find(".updated-message").addClass("hidden");
						ratePlan.removeClass('new-panel');
					});
				}, 1000);
				//innGrid.populateRoomTypeList();
				setTimeout(function(){ location.reload(); }, 200);
			}
	, 'json');
}

innGrid.addRatePlan = function(ratePlan) {
	var descrip_id = 'rateDesc_new_plan';
	var description = CKEDITOR.instances[descrip_id].getData();
	var ratePlanName = ratePlan.find('[name="rate-plan-name"]').val();
	var roomTypeID = ratePlan.find('[name="room-type-id"]').val();
	var currencyID = ratePlan.find('[name="currency-id"]').val();
	var chargeTypeID = ratePlan.find('[name="charge-type-id"]').val();
	var isShownInOnlineBookingEngine = ratePlan.find('[name="is-shown-in-online-booking-engine"]').val();
	var extraIds = ratePlan.find('[name="extra[]"]').val();
	//Populate updates to standard room type information
	if (roomTypeID != null && ratePlanName != "") {
		$.post(getBaseURL() + 'settings/rates/create_rate_plan_AJAX', {
				rate_plan_name: ratePlanName,
				room_type_id: roomTypeID,
				charge_type_id: chargeTypeID,
				currency_id: currencyID,
				description: description,
				is_shown_in_online_booking_engine: isShownInOnlineBookingEngine,
				extras: extraIds,
			}, function (result) {
				ratePlan.find(".updated-message").removeClass("hidden");
				$(result.new_rate_plan_ids).each(function (e, ratePlanID) {
					innGrid.updateRates("", "", ratePlanID);
				});
				window.setTimeout(function () {
					ratePlan.find(".updated-message").fadeTo(500, 0).slideUp(500, function () {
						ratePlan.find(".updated-message").addClass("hidden");
						ratePlan.removeClass('new-panel');
					});
				}, 1000);
				//innGrid.populateRoomTypeList();
				setTimeout(function () {
					window.location.reload();
				}, 200);
			}
			, 'json');
	} else {
		$('.error-message').removeClass("hidden");
		$('#new-rate-plan').animate({ scrollTop: 0 }, 'slow');
	}
}

$(function() {
	innGrid.populateRoomTypeList();
	$(document).on("click", ".room-type",  function(e) {
		var roomTypeID = $(this).find("a").attr("href").replace('#','');
		console.log(roomTypeID)
		innGrid.populateRatePlans(roomTypeID);
	})
	
	$('.add-rate-plan-button').click(function () {	
        var companySubscriptionLevel = $("input[name='subscription_level']").val();
        var companySubscriptionState = $("input[name='subscription_state']").val();
        var companyFeatureLimit      = $("input[name='limit_feature']").val();

        if(
            companyFeatureLimit == 1 && 
            companySubscriptionState != 'trialing' &&
            (companySubscriptionLevel == STARTER)
        )
        {
            $("#access-restriction-message").modal("show");
            $('#access-restriction-message .restriction_message').html(l('This feature is not active for your current subscription. \n\nPlease upgrade your subscrition to use this feature.'));
            return false;
        }	
		$.post(getBaseURL() + 'settings/rates/add_new_rate_plan_AJAX', {
			room_type_id: $(".room-type.active").find("a").attr("href").replace('#','')
		}, function (data) {	
			$("#new-rate-plan").modal("show");
			
			$("#new-rate-plan .modal-body").html(data);
			setTimeout(function() {
				$("#new-rate-plan .modal-body .select-extras").select2({
					placeholder: l("Select Extra Items (These are bookable on Online booking engine.)"),
					allowClear: true
				});
				$("#new-rate-plan .modal-body .select-room-types").select2({
					placeholder: l("Select room types"),
					allowClear: true
				});
				$("#new-rate-plan .modal-body").find('textarea').each(function () {
					$(this).attr('id', 'rateDesc_new_plan');
					CKEDITOR.replace($(this).attr('id'), {
						customConfig: '/application/third_party/ckeditor/config.js'
					});
				});
			}, 100);

		


	


			//$('.rate-plans').prepend($(data).fadeIn('slow'));
			/*innGrid.populateRoomTypeList();
                        var rate_count = 1;
                        var btn_rate_count = 1;
                        setTimeout(function(){ 
                            $( '.update-rate-plan-button').each( function() { 
                                $(this).attr('count',btn_rate_count);
                                btn_rate_count++;
                           });
                        }, 3000); 
                        setTimeout(function(){ 
                            $( 'textarea').each( function() {
                                $(this).attr('id','rateDesc_'+rate_count);
                                CKEDITOR.replace( $(this).attr('id'),{
                                   customConfig: '/application/third_party/ckeditor/config.js'
                                } );
                                rate_count++;
                            });
                        }, 4000);*/
		});
	});

	$('body').on('click','.edit-new-rate-plan',function () {	

        var companySubscriptionLevel = $("input[name='subscription_level']").val();
        var companySubscriptionState = $("input[name='subscription_state']").val();
        var companyFeatureLimit      = $("input[name='limit_feature']").val();

        if(
            companyFeatureLimit == 1 && 
            companySubscriptionState != 'trialing' &&
            (companySubscriptionLevel == STARTER)
        )
        {
            $("#access-restriction-message").modal("show");
            $('#access-restriction-message .restriction_message').html('This feature is not active for your current subscription. \n\nPlease upgrade your subscrition to use this feature.');
            return false;
		}	
		var rate_plan_id= $(this).attr('id');
		var room_type_id = $(this).data('room_type_id');
		$.post(getBaseURL() + 'settings/rates/edit_rate_plan_AJAX', {
			rate_plan_id: rate_plan_id,
			room_type_id: room_type_id,
		}, function (data) {	
			$("#edit-rate-plan").modal("show");
			
			$("#edit-rate-plan .modal-body").html(data);
			setTimeout(function() {
				$("#edit-rate-plan .modal-body .select-extras").select2({
					placeholder: "Select Extra Items (These are bookable on Online booking engine.)",
					allowClear: true
				});
				
				$('.rate-plan-div').attr('id', rate_plan_id);
				
				$("#edit-rate-plan .modal-body").find('textarea').each(function () {
					$(this).attr('id', 'rateDesc_'+rate_plan_id);
					CKEDITOR.replace($(this).attr('id'), {
						customConfig: '/application/third_party/ckeditor/config.js'
					});
				});
			}, 100);	});
		});

	$(document).on('click', '.replicate-rate-plan-button', function () {
		var ratePlanID= $(this).attr('id');
        var companySubscriptionLevel = $("input[name='subscription_level']").val();
        var companySubscriptionState = $("input[name='subscription_state']").val();
        var companyFeatureLimit      = $("input[name='limit_feature']").val();

        if(
            companyFeatureLimit == 1 && 
            companySubscriptionState != 'trialing' &&
            (companySubscriptionLevel == STARTER)
        )
        {
            $("#access-restriction-message").modal("show");
            $('#access-restriction-message .restriction_message').html(l('This feature is not active for your current subscription. \n\nPlease upgrade your subscrition to use this feature.'));
            return false;
        }		
		
		$.post(getBaseURL() + 'settings/rates/replicate_rate_plan_AJAX', {
			rate_plan_id: ratePlanID,
		}, function (data) {
			console.log(data);
			$('.rate-plans-table').prepend($(data).fadeIn('slow'));
			innGrid.populateRoomTypeList($('.room-type.active').attr('room_type_id'));
		});
	});

	$(document).on('click', '.delete-rate-plan-button', function () {
		
		var ratePlanID = $(this).attr('id');
		
		//Set custom buttons for delete dialog
		var r = confirm(l('Are you sure you want to delete this rate plan?'));
		if (r == true) {
		    $.post(getBaseURL() + 'settings/rates/delete_rate_plan_AJAX', {
			rate_plan_id: ratePlanID
			}, function (results) {
				if (results.isSuccess == true){
						$('.rate-plan-row-'+ratePlanID).fadeOut("slow");  //delete line of X button
						innGrid.populateRoomTypeList($('.room-type.active').attr('room_type_id'));
						innGrid.updateRates("","",ratePlanID);
					}
					else {
						//alert(results.message);
					}
				}, 'json');
		}
	});

	$(document).on('click', '.update-rate-plan-button', function () {	
		var ratePlan = $('#edit-rate-plan').find('.rate-plan-div');
		innGrid.updateRatePlan(ratePlan);
	});
	
	$(document).on('click', '.add-new-rate-plan-button', function () {
		var ratePlan = $('#new-rate-plan').find('.new-rate-plan-modal');
		innGrid.addRatePlan(ratePlan);
	});
});	

function sortRoomTypes(){
    $('body #sortable').sortable({
        connectWith: '#sortable',
        update: function(event, ui) {
            
        var sort = 1;
        var updatedRoomTypes = {};
        $(".room-type").each(function()
        {
            var roomType = $(this);
            var roomTypeId = roomType.attr('room_type_id');
            var roomTypeSortOrder = sort;

            updatedRoomTypes[roomTypeId] = {
                id: roomTypeId,
                sort_order: roomTypeSortOrder
            };
            sort++;
        });
        
        $.post(getBaseURL() + 'settings/rates/updated_room_types', {
            updated_room_types: updatedRoomTypes
                }, function (result) {}, 'json');
        }
    });
};


setTimeout(function(){ 
    $( 'textarea').each( function() { 
    CKEDITOR.replace( $(this).attr('id'),{
        customConfig: '/application/third_party/ckeditor/config.js'
    } );
});
}, 4000);


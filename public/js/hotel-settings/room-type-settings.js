innGrid.populateRoomTypeList = function()
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
                        room_type_id 	: roomType['id']
        			}).append(
        				$("<a/>", {
        					href: '#'+roomType['id'],
        					text: roomType['name'],
                            style: "padding-left: 5px;"
        				})
                        .prepend($('<span/>', {class: "grippy", style: "margin-right: 10px;"}))
        			)
        		);
        	});

        	$("#room-type-list").html(roomTypeListDiv);
            sortRoomTypes();
        }
    );
}

innGrid.updateAvailabilities = function(start_date, end_date) {
	$.ajax({
	    type   : "POST",
	    url    : getBaseURL() + "channel_manager/update_availabilities",
	    data   : {
	        start_date: start_date,
	        end_date: end_date
	    },
	    dataType: "json",
	    success: function (data) {
	        console.log(data);
	    }
	});
}

innGrid.updateRoomType = function(roomType) {
    
    var roomTypeID = roomType.attr('id');
    var descrip_id = 'desc_'+roomTypeID;
  
    // var descrip_id = 'desc_'+count; 
 
	var description = CKEDITOR.instances[descrip_id].getData();
	var roomTypeId = roomType.attr('id');
	var roomTypeName = roomType.find('[name="room-type-name"]').val();
	var roomTypeAcronym = roomType.find('[name="room-type-acronym"]').val();
	var maxAdults = roomType.find('[name="max-adults"]').val();
	var maxChildren = roomType.find('[name="max-children"]').val();		
	var canBeSoldOnline = roomType.find('[name="can-be-sold-online"]').val();
    var maxOccupancy = roomType.find('[name="max-occupancy"]').val();
    var minOccupancy = roomType.find('[name="min-occupancy"]').val();
    var defaultRoomCharge = roomType.find('[name="default_room_charge"]').val();
    var preventInlineBooking = roomType.find('[name="prevent_inline_booking"]').prop('checked') ? 1 : 0;
        
	//Populate updates to standard room type information
	$.post(getBaseURL() + 'settings/room_inventory/update_room_type_AJAX', {
            room_type_id: btoa(unescape(encodeURIComponent(roomTypeId))),
            room_type_name: btoa(unescape(encodeURIComponent(roomTypeName))),
            acronym: btoa(unescape(encodeURIComponent(roomTypeAcronym))),
            description: description,
            max_occupancy: btoa(unescape(encodeURIComponent(maxOccupancy))),
            min_occupancy: btoa(unescape(encodeURIComponent(minOccupancy))),
            max_adults: btoa(unescape(encodeURIComponent(maxAdults))),
            max_children: btoa(unescape(encodeURIComponent(maxChildren))),
            can_be_sold_online: btoa(unescape(encodeURIComponent(canBeSoldOnline))),
            default_room_charge: btoa(unescape(encodeURIComponent(defaultRoomCharge))),
            prevent_inline_booking: btoa(unescape(encodeURIComponent(preventInlineBooking)))

		}, function (result) {
                    if(result.error == 'error')
                    {
                        alert(result.value);
                        return false;
                    }
                    else
                    {
                        innGrid.updateAvailabilities("","");
                        roomType.find(".updated-message").removeClass("hidden");
                        window.setTimeout(function() {
                            roomType.find(".updated-message").fadeTo(500, 0).slideUp(500, function(){
                                roomType.find(".updated-message").addClass("hidden");
                                roomType.removeClass('new-panel');
                            });
                        }, 1000);
                        //  innGrid.populateRoomTypeList();
                        window.location.reload();
                    }
                    
	}, 'json');
}

innGrid.createRoomType = function(roomType) {
    
    var descrip_id = 'desc_new_roomtype';
 
    var description = CKEDITOR.instances[descrip_id].getData();
    // var roomTypeId = roomType.attr('id');
    var roomTypeName = roomType.find('[name="room-type-name"]').val();
    var roomTypeAcronym = roomType.find('[name="room-type-acronym"]').val();
    var maxAdults = roomType.find('[name="max-adults"]').val();
    var maxChildren = roomType.find('[name="max-children"]').val();     
    var canBeSoldOnline = roomType.find('[name="can-be-sold-online"]').val();
    var maxOccupancy = roomType.find('[name="max-occupancy"]').val();
    var minOccupancy = roomType.find('[name="min-occupancy"]').val();
    var defaultRoomCharge = roomType.find('[name="default_room_charge"]').val();
    var preventInlineBooking = roomType.find('[name="prevent_inline_booking"]').prop('checked') ? 1 : 0;
        
    //Populate updates to standard room type information
    $.post(getBaseURL() + 'settings/room_inventory/create_room_type', {
            // room_type_id: roomTypeId,
            room_type_name: btoa(unescape(encodeURIComponent(roomTypeName))),
            acronym: btoa(unescape(encodeURIComponent(roomTypeAcronym))),
            description: description,
            max_occupancy: btoa(unescape(encodeURIComponent(maxOccupancy))),
            min_occupancy: btoa(unescape(encodeURIComponent(minOccupancy))),
            max_adults: btoa(unescape(encodeURIComponent(maxAdults))),
            max_children: btoa(unescape(encodeURIComponent(maxChildren))),
            can_be_sold_online: btoa(unescape(encodeURIComponent(canBeSoldOnline))),
            default_room_charge: btoa(unescape(encodeURIComponent(defaultRoomCharge))),
            prevent_inline_booking: btoa(unescape(encodeURIComponent(preventInlineBooking)))
        }, function (result) {
                    if(result.error == 'error')
                    {
                        alert(result.value);
                        return false;
                    }
                    else
                    {
                        // innGrid.updateAvailabilities("","");
                        // roomType.find(".updated-message").removeClass("hidden");
                        // window.setTimeout(function() {
                        //     roomType.find(".updated-message").fadeTo(500, 0).slideUp(500, function(){
                        //         roomType.find(".updated-message").addClass("hidden");
                        //         roomType.removeClass('new-panel');
                        //     });
                        // }, 1000);
                        //  innGrid.populateRoomTypeList();
                        window.location.reload();
                    }
                    
    }, 'json');
}


$('.edit_room_type').click(function(){
 
        // $("#room_type_model").modal("show");
        var room_type_id= $(this).attr('id');
        var min_occupancy= $(this).data('min_occupancy');
        var max_occupancy= $(this).data('max_occupancy');
       
        $.post(getBaseURL() + 'settings/room_inventory/edit_room_type', {
            room_type_id: room_type_id
            }, function (data) {
      
            $("#room_type_model").modal("show");
            $("#room_type_model .modal-body").html(data);

            $("#room_type_model .modal-body")
                .find(".slider-range")
                .first()
                .slider({
                    range: true,
                    min: 1,
                    max: 30,
                    values: [ min_occupancy, max_occupancy ],
                    slide: function( event, ui ) {
                        $(this).parents('.range_occupancy').find('.min_occupancy').val(ui.values[ 0 ]);
                        $(this).parents('.range_occupancy').find('.max_occupancy').val(ui.values[ 1 ]);
                    }
                });
            
            setTimeout(function() {
				$('.room-type-div').attr('id', room_type_id);
				
				$("#room_type_model .modal-body").find('textarea').each(function () {
					$(this).attr('id', 'desc_'+room_type_id);
					CKEDITOR.replace($(this).attr('id'), {
						customConfig: '/application/third_party/ckeditor/config.js'
					});
				});
			}, 100);	});
          

});



$(function() {
	innGrid.populateRoomTypeList();
	
	//Performs a smooth page scroll to an anchor on the same page.
	var top = $('#side-menu').offset().top - parseFloat($('#side-menu').css('marginTop').replace(/auto/, 100));
	$(window).scroll(function (event) {
		var scrollPercent = 100 * $(window).scrollTop() / ($(document).height() - $(window).height());
		// what the y position of the scroll is
		var y = $(this).scrollTop();
		// console.log(top-y);
		
		// whether that's below the form
		if (y >= top) {
			// if so, ad the fixed class
			$('#side-menu').css("position","fixed").css("top", (top-y)*(scrollPercent*0.0001));
		} else {
			// otherwise remove it
			$('#side-menu').css("position","").css("top", 0);
		}
	});

	// smooth animation scroll menu
	$(function() {
	  $(document).on('click', 'a[href*=#]:not([href=#])', function() {
	    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
	      var target = $(this.hash);
	      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
	      if (target.length) {
	        $('html,body').animate({
	          scrollTop: target.offset().top
	        }, 300);
	        return false;
	      }
	    }
	  });
    });
    
    // $(document).on('click', '#add-room-type-button', function () {
	// 	var ratePlan = $('#addnew_room_type_model').find('.new-room-type-modal');
	// 	innGrid.addRatePlan(ratePlan);
    // });
    
   $('.add_room_type').click(function () {
   
        var roomType = $('#addnew_room_type_model').find('.new-room-type-modal');
        innGrid.createRoomType(roomType);
           
   });

    $('#add-room-type-button').click(function(){
        $.post(getBaseURL() + 'settings/room_inventory/add_room_type', function (data) {
            $("#addnew_room_type_model").modal("show");
            $("#addnew_room_type_model .modal-body").html(data);

            $("#addnew_room_type_model .modal-body")
                .find(".slider-range")
                .first()
                .slider({
                    range: true,
                    min: 1,
                    max: 30,
                    values: [ 1, 8 ],
                    slide: function( event, ui ) {
                        $(this).parents('.range_occupancy').find('.min_occupancy').val(ui.values[ 0 ]);
                        $(this).parents('.range_occupancy').find('.max_occupancy').val(ui.values[ 1 ]);
                    }
                });

            setTimeout(function(){
                $("#addnew_room_type_model .modal-body").find('textarea').each(function () {
                        $(this).attr('id', 'desc_new_roomtype');
                        CKEDITOR.replace($(this).attr('id'), {
                            customConfig: '/application/third_party/ckeditor/config.js'
                        });
                    });
            }, 300);
        });
    });

  
	// $('#add-room-type-button').click(function () {		

	// 	$.post(getBaseURL() + 'settings/room_inventory/create_room_type', function (data) {

 //            $("#addnew_room_type_model").modal("show");
			
 //            $("#addnew_room_type_model .modal-body").html(data);

	// 	    $("#addnew_room_type_model .modal-body")
 //                // .append($(data).fadeIn('slow'))
 //                .find(".slider-range")
 //                .first()
 //                .slider({
 //                    range: true,
 //                    min: 1,
 //                    max: 30,
 //                    values: [ 1, 8 ],
 //                    slide: function( event, ui ) {
 //                        $(this).parents('.range_occupancy').find('.min_occupancy').val(ui.values[ 0 ]);
 //                        $(this).parents('.range_occupancy').find('.max_occupancy').val(ui.values[ 1 ]);
 //                    }
 //                });

 //            innGrid.populateRoomTypeList();
 //            innGrid.updateAvailabilities("","");

 //            mixpanel.track(l("Room Type created in settings", true));
 //            // Intercom tracking
 //            var metadata = {
 //                room_type_id: $(data).attr('id')
 //            };
 //            // Intercom('trackEvent', 'roomtype-created', metadata);


 //            var room_count = $(data).attr('id');
 //            var button_count = 1001;

 //            setTimeout(function(){
 //                $( '.update-room-type-button').each( function() {
 //                    $(this).attr('count',button_count);
 //                    button_count++;
 //               });
 //            }, 3000);

 //            setTimeout(function(){
 //                $( 'textarea').each( function() {
 //                    $(this).attr('id','desc_'+room_count);

 //                    CKEDITOR.replace( $(this).attr('id'),{
 //                        customConfig: '/application/third_party/ckeditor/config.js'
 //                    } );
 //                    // room_count++;
 //                });
 //           }, 3000);
 //        });
        
    	

	// });             
        
	$(document).on('click', '.delete-room-type-button', function () {
		var roomType = $(this).closest(".room-type-div");
		var roomTypeId = roomType.attr('id');
		var roomTypeName = roomType.find("[name='room-type-name']").val();
		
		//Set custom buttons for delete dialog
		var r = confirm(l('Are you sure you want to delete', true)+' '+ roomTypeName + '?');
		if (r == true) {
		    $.post(getBaseURL() + 'settings/room_inventory/delete_room_type', {
			room_type_id: roomTypeId
			}, function (results) {
				if (results.isSuccess == true){
					roomType.fadeOut("slow");  //delete line of X button
					//alert(results.message);
				}
				else {
					//alert(results.message);
				}
				innGrid.populateRoomTypeList();
                innGrid.updateAvailabilities("","");
			}, 'json');
		}
	});
                   
	$(document).on('click', '.update-room-type-button', function () {	
        var roomType = $('#room_type_model').find('.room-type-div');
		innGrid.updateRoomType(roomType);
	});
	
});	

function sortRoomTypes(){
    $('body #sortable').sortable({
        connectWith: '#sortable',
        update: function(event, ui) {
            
        var sort = 1;
        var updatedRoomTypes = {};
        $(".room-type-div").each(function()
        {
            var roomType = $(this);
            var roomTypeId = roomType.attr('id');
            var roomTypeSortOrder = sort;

            updatedRoomTypes[roomTypeId] = {
                id: roomTypeId,
                sort_order: roomTypeSortOrder
            };
            sort++;
        });
        console.log('updatedRoomTypes',updatedRoomTypes);
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
}, 3000);  

$('body').on('change', '.max-occupancy', function(){
    var maxOccupancyOptions = '';
    var minOccupancyOptions = '';
    var roomTypeId = $(this).closest('.room-type-div').attr('id');
    var maxOccupancy = $(this).closest('[name="max-occupancy"]').val();
    for ($i=1; $i <= maxOccupancy; $i++){
        maxOccupancyOptions += "<option value='"+$i+"'>"+$i+" "+l('adults', true)+"</option>";
    }
    $('#max-adults-'+roomTypeId).html(maxOccupancyOptions);
    
    for ($i=0; $i < maxOccupancy; $i++){
        minOccupancyOptions += "<option value='"+$i+"'>"+$i+" "+l('children', true)+"</option>";
    }
    $('#max-children-'+roomTypeId).html(minOccupancyOptions);
});

$('body').on('input', '.rt-occupancy-slider', function(){
    var range = $(this).val();

    var maxAdultsRange = $('#max-adults-range').val();
    var maxChildrenRange = $('#max-children-range').val();

    var total = parseInt(parseInt(maxAdultsRange) + parseInt(maxChildrenRange));

    $('.max_occupancy').parents('.range_occupancy').find(".slider-range").slider('values', 1, total);
    $('.max_occupancy').val(total);
    $(this).parent('div.occupancy-range').next('div.rt-occupancy').find('.slider_value').val(range);
});

$('body').on('input', '.slider_value', function(){
    var range = $(this).val();
    if(range > 30)
    {
        $(this).val('30');
    }
    else if(range < 1)
    {
        $(this).val('1');
    }

    var range = $(this).val();
    $(this).parent('div.rt-occupancy').prev('div.occupancy-range').find('.rt-occupancy-slider').val(range);
});

$( ".slider-range" ).each(function() {
    $(this).slider({
        range: true,
        min: 1,
        max: 60,
        values: [ $(this).data('min'), $(this).data('max') ],
        slide: function( event, ui ) {
            $(this).parents('.range_occupancy').find('.min_occupancy').val(ui.values[ 0 ]);
            $(this).parents('.range_occupancy').find('.max_occupancy').val(ui.values[ 1 ]);
        }
    });
});

$(document).on("change", ".min_occupancy", function() {
    $(this).parents('.range_occupancy').find(".slider-range").slider('values', 0, $(this).val());
});
$(document).on('change', ".max_occupancy", function() {
    $(this).parents('.range_occupancy').find(".slider-range").slider('values', 1, $(this).val());
});

//todo: Improve validation and error checking
innGrid.saveAllRooms = function () {
	var roomData = [];
	$(".room-tr").each(function()
	{
		var room = $(this);
        var canBeSoldOnline = '0';
		var roomID = room.attr('id');
		var roomName = room.find('[name="room-name"]').val();
		var roomTypeID = room.find('[name="room-type"]').val();
        var roomLocationID = room.find('[name="room-location"]').val();
        var roomFloorID = room.find('[name="room-floor"]').val();
        var group_id = room.find('[name="room-group-id"]').val();
        var sortOrder = room.find('[name="sort_order"]').val();
        if(sortOrder == '')
            sortOrder = 'NULL';
        if (room.find('.can-be-sold-online-checkbox').prop('checked') === true) {
            canBeSoldOnline = '1';
        }

		roomData.push({
                    room_id: roomID,
                    room_name: roomName,
                    room_type_id: roomTypeID,
                    location_id: roomLocationID,
                    floor_id: roomFloorID,
                    can_be_sold_online: canBeSoldOnline,
                    group_id: group_id,
                    sort_order:sortOrder
		});

	});   
    console.log('roomData',roomData);

    var roomsData = roomData;
    var data = {};
    var count = 0;
    var total = true;
    for(var key in roomsData) {

        if (count == 100) {
            // send ajax request with data

        	$.ajax({
                url: getBaseURL() + 'settings/room_inventory/save_rooms_AJAX',
                type: "POST",
                data: {room_data : data},
                success: function(response){
                	console.log('response',response);
                    innGrid.updateAvailabilities("","");
                    if(response != 1){
                    	alert(response);
                    } else {
                		// alert(l('All rooms saved', true));
                    }
                }
            });

            console.log(data);
                
            data = {}; // reset data
            count = 0; // reset counter
            total = false;
        }

        data[key] = roomsData[key];
        count ++;
    }

    if (count > 0) {
        // send ajax request with data
        $.ajax({
            url: getBaseURL() + 'settings/room_inventory/save_rooms_AJAX',
            type: "POST",
            data: {room_data : data},
            success: function(response){
                console.log('response',response);
                innGrid.updateAvailabilities("","");
                if(response != 1){
                    alert(response);
                } else {
                    alert(l('All rooms saved', true));
                }
            }
        });
        
        console.log(data);
        
        data = {}; // reset data
        count = 0; // reset counter
    }
};
      var check_api_response = 0;
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

innGrid.saveAllRoomLocations = function () {
	var roomData = [];
	$(".room-tr").each(function()
	{
		var room = $(this);
        var locationID = room.attr('id');
		var locationName = room.find('[name="room-name"]').val();
		roomData.push({
			location_id: locationID,
			location_name: locationName
		});

	});
       
	$.ajax({  
        url: getBaseURL() + 'settings/room_inventory/save_room_locations_AJAX',
        type: "POST",
        dataType: 'json',
        data: JSON.stringify(roomData),
        success: function(response){
        	alert(l('All room locations saved', true));
        }
    });
};
innGrid.saveAllFloor = function () {
	var roomData = [];
	$(".room-tr").each(function()
	{
		var room = $(this);
        var floorID = room.attr('id');
		var floorName = room.find('[name="room-name"]').val();
		roomData.push({
			floor_id: floorID,
			floor_name: floorName
		});

	});
    $.ajax({
        url: getBaseURL() + 'settings/room_inventory/save_floor_AJAX',
        type: "POST",
        dataType: 'json',
        data: JSON.stringify(roomData),
        success: function(response){
        	alert(l('All floor saved', true));
        }
    });
};

$(function() {
	var previous;
	$("select[name='room-type']").on('focus', function () {
        previous = this.value;
    }).on("change", function(x) {
		if ($(this).val() === 'create_new')
		{	
			var r = confirm(l("Are you sure that you want to leave this page? Any unsaved changes will be lost.", true));
			if (r == true) {
			    window.location = getBaseURL() + 'settings/room_inventory/room_types';
			} 
            else {
			    $(this).val(previous);
			}
		}
		
	})

	$('#add-room-button').click(function () {
        $('#add-multiple-rooms').modal('show');
	});
        
    $('#add_multiple_rooms').click(function () {
        var room_type_id = $("[name='room_type']").val();
        var room_count = $("[name='room_count']").val();
        for(var i = 1; i <= room_count; i++)
        {
            $.post(getBaseURL() + 'settings/room_inventory/create_room_AJAX/'+room_type_id, function (data) {
                innGrid.updateAvailabilities("","");
                $('.rooms tbody').prepend(data);
            });
        }
        $('#add-multiple-rooms').modal('hide');
	});

	$(document).on('click', '.all-can-be-sold-online-checkbox', function() {
		var checked = $(this).prop('checked');
		$('.can-be-sold-online-checkbox').each(function() {
			$(this).prop('checked', checked);
		})
	});

	$(document).on('click', '.delete-room-button', function () {
        $(this).attr('disabled', 'disabled');
		var room = $(this).closest(".room-tr");
		var roomID = room.attr('id');
		var roomName = room.find("[name='room-name']").val();
		
		//Set custom buttons for delete dialog
		var r = confirm(l('Are you sure you want to Hide', true)+' '+ roomName + '?');
		if (r == true) {
		    $.post(getBaseURL() + 'settings/room_inventory/delete_room_AJAX', {
                room_id: roomID
                }, 
                function (results) {
                    if(results.future_reservation_available)
                    {
                        alert(l('There are future reservations available for this room, please delete the reservations first!', true));
                        $('.delete-room-button').prop('disabled', false);
                        return;
                    }
                    if (results.isSuccess == true){
                        innGrid.updateAvailabilities("","");
                        //alert(results.message);
                        $('.delete-room-button').prop('disabled', false);
                        window.location.reload();
                    }
                }, 'json');
		}else{
            $('.delete-room-button').prop('disabled', false);
        }
	});
	// show hidden rooms
    $(document).on('click', '#show-hidden-rooms', function () {
        if($(this).prop('checked'))
        {
           $('.rooms .room-tr').removeClass('hidden');
        }
        else
        {
            $('.rooms .room-tr').each(function(){
                if($(this).data('is-hidden') == 1)
                {
                    $(this).addClass('hidden');
                }
            });
        }
    });
    
    // restore hidden room 
    $(document).on('click', '.restore-room-button', function () {
        $(this).attr('disabled', 'disabled');
        var room = $(this).closest(".room-tr");
		var roomID = room.attr('id');
		var roomName = room.find("[name='room-name']").val();
        
        var r = confirm(l('Are you sure you want to Restore Room', true)+' '+ roomName + '?');
		if (r == true) {
		    $.post(getBaseURL() + 'settings/room_inventory/restore_hidden_room_AJAX', {
			room_id: roomID
			}, function (results) {
				if (results.isSuccess == true){
                        innGrid.updateAvailabilities("","");
                        //alert(results.message);
                        $('.restore-room-button').prop('disabled', false);
                        window.location.reload();
					}
				}, 'json');
		}else{
            $('.restore-room-button').prop('disabled', false);
        }
    });
    
	$('#save-all-rooms-button').on("click", function () {	
		innGrid.saveAllRooms();
		mixpanel.track(l("Save all rooms button clicked in settings", true));
	});
    
    $('#add-room-location-button').click(function () {	
       $.post(getBaseURL() + 'settings/room_inventory/create_room_location_AJAX', function (data) {
			$('.room_locations tbody').prepend(data);
		});
	});
    
    $('#save-all-rooms-locations-button').on("click", function () {
        innGrid.saveAllRoomLocations();
	});
        
    $(document).on('click', '.delete-room-location-button', function () {
		var location = $(this).closest(".room-tr");
		var locationID = location.attr('id');
		var locationName = location.find("[name='room-name']").val();
		//Set custom buttons for delete dialog
		var r = confirm(l('Are you sure you want to delete', true)+' '+ locationName + '?');
		if (r == true) {
		    $.post(getBaseURL() + 'settings/room_inventory/delete_room_location_AJAX', {
                location_id: locationID
			}, function (results) {
                console.log(results);
				if (results.isSuccess == true){
                        location.remove();  //delete line of X button
					}
				}, 'json');
		}
	});
	
                            
                                             
    $('#add-floor-button').click(function () {
       $.post(getBaseURL() + 'settings/room_inventory/create_floor_AJAX', function (data) {
			$('.floor tbody').prepend(data);
    	});
	});       
        
    $('#save-all-floor-button').on("click", function () {
       innGrid.saveAllFloor();
	});
    
    $(document).on('click', '.delete-floor-button', function () {
		var floor = $(this).closest(".room-tr");
		var floorID = floor.attr('id');
		var floorName = floor.find("[name='room-name']").val();
		//Set custom buttons for delete dialog
        var r = confirm(l('Are you sure you want to delete', true)+' '+ floorName + '?');
		if (r == true) {
		    $.post(getBaseURL() + 'settings/room_inventory/delete_floor_AJAX', {
			floor_id: floorID
			}, function (results) {
				if (results.isSuccess == true){
						floor.remove();  //delete line of X button
					}
				}, 'json');
		}
	});
	
});
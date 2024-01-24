
$(window).load(function() {
	var latitude = $("#website_latitude").val();
	var longitude = $("#website_longitude").val();
    new Maplace({
        locations: [{
	        lat: latitude, 
	        lon: longitude,
	        zoom: 14
	    }],
        controls_on_map: false
    }).Load();

});

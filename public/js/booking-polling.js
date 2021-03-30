var socket = io.connect('https://' + window.location.hostname, {secure: true});

$(function() {
	
	socket.on('connected', function(data) {		
		socket.emit('userInfo', { 
			'companyId': $('#current-hotel').val(),
			'userEmail': $('#user-email').html()
		});
		
		console.log('connected');
	});
	
	socket.on('new_user', function (data) {
		console.log(data);
	});
	
	socket.on('updateBookings', function (data) {
		console.log(data);
		console.log('received');
		innGrid.reloadBookings();
	});

});
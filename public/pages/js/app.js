// Can also be used with $(document).ready()
$(window).load(function() {

	// Flex Slider
	$('.flexslider').flexslider({
		animation: "slide",
		animationLoop: false
		
	});

	// Smooth scroll
	$('a.nav-link').bind("click", function(e){
      var anchor = $(this);
      $('html, body').stop().animate({
         scrollTop: $(anchor.attr('href')).offset().top -25
      }, 1000);
      e.preventDefault();
   });
   
	// Online Booking Date Pickers
	$('.datepicker').datepicker({
		format: 'yyyy-mm-dd'
	});

	var nowTemp = new Date();
	var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
	
	var checkin = $("input[name='check-in-date']").datepicker({
	  onRender: function(date) {
	  	console.log(date);
	    return date.valueOf() < now.valueOf() ? 'disabled' : '';
	  }
	}).on('changeDate', function(ev) {
	  if (ev.date.valueOf() > checkout.date.valueOf()) {
	    var newDate = new Date(ev.date)
	    console.log(newDate);
	    newDate.setDate(newDate.getDate() + 1);
	    checkout.setValue(newDate);
	  }
	  checkin.hide();
	  $("input[name='check-out-date']")[0].focus();
	}).data('datepicker');
	var checkout = $("input[name='check-out-date']").datepicker({
	  onRender: function(date) {
	  	console.log(date);
	    return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
	  }
	}).on('changeDate', function(ev) {
	  checkout.hide();
	}).data('datepicker');


	// validator for email & message input fields
	$(".form").validator();

	var path = $(location).attr('pathname');
	path.indexOf(1);
	path.toLowerCase();
	uri = path.split("/")[1];


// 	$(".send-message").on("click", function(){
// // 		var currentUrl = window.location.href;
// // console.log(currentUrl);
// // var baseUrl = window.location.origin + window.location.pathname;
// // console.log(window.location.origin);
// console.log(getBaseURL());
// 		console.log(baseUrl + "send_email/" + website_uri)

// 		$.ajax({
// 			type: "POST",
// 			// url: getBaseURL() + "page/send_email/",
// 			// url: baseUrl + "send_email/" + website_uri,
// 			url: "http://localhost/minical/pages/send_email/",
// 			data: { 
// 				uri: website_uri,
// 				from_email: $("[name='from_email']").val(),
// 				// message: $("[name='message']").val(),
// 				// 'g-recaptcha-response': $("[name='g-recaptcha-response']").val()
// 			},
// 			dataType: "json",
// 			success: function( data ) {
// 				console.log(data);
// 				$('#myModal').modal('hide')
// 			}
// 		});
// 	});

});

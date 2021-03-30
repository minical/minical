$(function() {

	$(".bigstars").bind('rated', function() { $("input[name='star-rating']").val($(this).rateit('value')); });

	setTimeout(function(){
		$('.rate-stars').each(function(i,v){
			var width = $(this).find('.show-rating').val()*32;
			$('.bigstars').find('.rateit-selected').css('width',width+'px');
		});
	},1000);
});
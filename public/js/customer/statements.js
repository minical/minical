$(function() {	
    $('.statement').on("click", function(){
        window.location.assign(getBaseURL() + 'customer/statements/'+$("#customer_id").val()+"/"+$(this).attr('data-statement-id'));
    });
});
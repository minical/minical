$(document).ready(function()
{
    //set max length
    var max_length = 200;
 
    //load in max characters when page loads
    $("#counter").html(max_length);
 
    //run listen key press
	$("#log").keydown(whenkeydown);
	$("#log").keyup(whenkeydown);
});
 
whenkeydown = function()
{
    max_length = 200;
	//check if the appropriate text area is being typed into
        if(document.activeElement.id === "log")
        {
            //get the data in the field
            var text = $(this).val();
 
            //set number of characters
            var numofchars = text.length;
 
            //set the chars left
            var chars_left = max_length - numofchars;
 
            //check if we are still within our maximum number of characters or not
            if(chars_left > 0)
            {
                //set the length of the text into the counter span
                $("#counter").html("").html(chars_left);
            }
            else
            {
                chars_left = 0;
				$("#counter").html("").html(chars_left);
				$("#log").val(text.substr(0,max_length));
				
            }
	}
}
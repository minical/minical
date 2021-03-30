function del_confirm(event)
{     
    var r  = confirm('Are you sure?');
    
    if (r == true) {
       
    } else {
        event.preventDefault();
    }
}
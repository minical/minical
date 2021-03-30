/*
* Datepicker for Jeditable (currently buggy, not for production)
*
* Copyright (c) 2007-2008 Mika Tuupola
*
* Licensed under the MIT license:
* http://www.opensource.org/licenses/mit-license.php
*
* Depends on Datepicker jQuery plugin by Kelvin Luck:
* http://kelvinluck.com/assets/jquery/datePicker/v2/demo/
*
* Project home:
* http://www.appelsiini.net/projects/jeditable
*
* Revision: $Id$
*
*/
 
$.editable.addInputType('datepicker', {
    element : function(settings, original) {
        var input = $('<input>');
        if (settings.width  != 'none') { input.width(settings.width);  }
        if (settings.height != 'none') { input.height(settings.height); }
        input.attr('autocomplete','off');
        $(this).append(input);
        return(input);
    },
    plugin : function(settings, original) {
        /* Workaround for missing parentNode in IE */
        var form = this;
        settings.onblur = 'ignore';
        $(this).find('input').datepicker({
				dateFormat: 'yy-mm-dd'
			}).bind('click', function() {
                $(this).datepicker('show');
            return false;
        }).bind('dateSelected', function(e, selectedDate, $td) {
            $(form).submit();
        });
    }
});

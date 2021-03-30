// -----------------------------------------------------------------------
// Eros Fratini - eros@recoding.it
// jqprint 0.3
//
// - 19/06/2009 - some new implementations, added Opera support
// - 11/05/2009 - first sketch
//
// Printing plug-in for jQuery, evolution of jPrintArea: http://plugins.jquery.com/project/jPrintArea
// requires jQuery 1.3.x
//
// Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
//------------------------------------------------------------------------

// Modified to accept callback (By Jaeyun 2011-11-27)
// Modified to always print by opening a new tab instead of using iframe(By Jaeyun 2012-07-03)

(function($) {
    var opt;
	
    $.fn.jqprint = function (options, callback) {
        opt = $.extend({}, $.fn.jqprint.defaults, options);
        var $element = (this instanceof jQuery) ? this : $(this);        
		var tab, doc;
		
		// if IE, user iframe, otherwise, open a new tab
			if (opt.openNewTab) 
			{
				tab = window.open();	
				tab.document.open();
				doc = tab.document;				
			}
			else // do not open new Tab (For innGrid registration cards ONLY)
			{
				window.print();
				//window.close(); // disabled the .close since Chrome update closes the tab before the print finishes (since July 10-2013)
				return;
			}
			
		
		 
		if (opt.importCSS)
        {
            if ($("link[media=print]").length > 0) 
            {
				
                $("link[media=print]").each( function() {
                    doc.write("<link type='text/css' rel='stylesheet' href='" + $(this).attr("href") + "' media='print' />");
                });
            }
            else 
            {
                $("link").each( function() {
                    doc.write("<link type='text/css' rel='stylesheet' href='" + $(this).attr("href") + "' />");
                });
            }
        }
        
        if (opt.printContainer) {
			doc.write($element.outer()); 
		}
        else 
		{ 
			$element.each( function() { doc.write($(this).html()); }); 
		}
        
        doc.close();
		
		tab.focus();

        setTimeout( function() {
			tab.print();
			if (tab) { 
				tab.close(); 
			}
			
			// written by jaeyun - 2011-11-27
			// to close window after printing
			if(typeof callback == "function"){
				callback();
			}
			
		}, 1000);
		
    }
    
    $.fn.jqprint.defaults = {
		debug: false,
		importCSS: true, 
		printContainer: true,
		operaSupport: true,
		openNewTab: true
	};

	
    // Thanks to 9__, found at http://users.livejournal.com/9__/380664.html
    jQuery.fn.outer = function() {
      return $($('<div></div>').html(this.clone())).html();
    } 
	
})(jQuery);
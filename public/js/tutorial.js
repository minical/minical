$(function (){
	/**
     * Guiders are created with guiders.createGuider({settings}).
     *
     * You can show a guider with the .show() method immediately
     * after creating it, or with guiders.show(id) and the guider's id.
     *
     * guiders.next() will advance to the next guider, and
     * guiders.hideAll() will hide all guiders.
     *
     * By default, a button named "Next" will have guiders.next as
     * its onclick handler.  A button named "Close" will have
     * its onclick handler set to guiders.hideAll.  onclick handlers
     * can be customized too.
     */
     
    guiders.createGuider({
      buttons: [{name: "End tutorial"}, {name: "Next"}],
      title: "Welcome to innGrid!",
	  description: "Through this tour, we will introduce you the main features of innGrid's. Please click on \"Next\" to begin.",
      id: "intro",
      next: "menu-bookings",
      overlay: true     
    }).show();
   
    guiders.createGuider({
      attachTo: "#li-bookings",
      buttons: [{name: "End tutorial"}, {name: "Back"}, {name: "Next"}],
      description: "There are four menu items in innGrid. Currently, we are in Bookings. Here, you create new reservations, check-in guests, and check out guests",
      id: "menu-bookings",
      next: "menu-customers",
      position: 6,
      title: "Bookings"
    });
	
	   
    guiders.createGuider({
      attachTo: "#li-customers",
      buttons: [{name: "End tutorial"}, {name: "Back"}, {name: "Next"}],
      description: "In customers, you can track customers' information such as the amount they owe or the last time they stayed at the hotel.",
      id: "menu-customers",
      next: "menu-rooms",
      position: 6,
      title: "Customers"
    });

	guiders.createGuider({
      attachTo: "#li-rooms",
      buttons: [{name: "End tutorial"}, {name: "Back"}, {name: "Next"}],
      description: "In Rooms, you can mark rooms as dirty or clean. Also you can write notes about the rooms.",
      id: "menu-rooms",
      next: "menu-reports",
      position: 6,
      title: "Rooms"
    });

	guiders.createGuider({
      attachTo: "#li-reports",
      buttons: [{name: "End tutorial"}, {name: "Back"}, {name: "Next"}],
      description: "In Reports, you can check daily/month sales report and produce housekeeping report",
      id: "menu-reports",
      next: "todays-highlights",
      position: 6,
      title: "Reports"
    });
	
	guiders.createGuider({
      attachTo: "#todays-highlights",
      buttons: [{name: "End tutorial"}, {name: "Back"}, {name: "Next"}],
      description: "Today's highlights show events that are happening today. For example, it shows the reservations that are checking in today, the guests that are currently staying at the hotel, and the guests that checked out today.",
      id: "todays-highlights",
      next: "selling-date",
      position: 12,
      title: "Today's highlights"
    });
	
	guiders.createGuider({
      attachTo: "#selling-date",
      buttons: [{name: "End tutorial"}, {name: "Back"}, {name: "Next"}],
      title: "Current Selling Date",
	  description: "Selling Date represent the current business date. To change your Selling Date to the next day, Run Night Audit. Remember, it charges everyone staying at the hotel as well.",
      id: "selling-date",
      next: "settings",
      position: 6      
    });
	
	guiders.createGuider({
      attachTo: "#innGrid-settings",
      buttons: [{name: "End tutorial"}, {name: "Back"}, {name: "Next"}],
      title: "Customize your hotel",
	  description: "You can modify your current property's settings such as Room Types and Rates. Also, you can invite new users to use innGrid with you",
      id: "settings",
      next: "help",
      position: 6      
    });
	
	guiders.createGuider({
      attachTo: "#help-link",
      buttons: [{name: "End tutorial"}, {name: "Back"}, {name: "Next"}],
      title: "Need assistance?",
	  description: "Here, you can watch our tutorial videos. It's a great place to start learning innGrid. You can find our contact information here as well.",
      id: "help",
      next: "finally",
      position: 6      
    });
	
	 guiders.createGuider({
      buttons: [{name: "End tutorial", classString: "primary-button"}],
      description: "If you haven't had 1-on-1 demonstration yet, please call <b>+1 (403) 708 0563</b> to schedule one. <br/>The demonstration is 100% free!",
      id: "finally",
      overlay: true,
      title: "You are ready to start!",
      width: 500
    });
});

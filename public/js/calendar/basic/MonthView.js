
fcViews.month = MonthView;

function MonthView(element, calendar) {
	var t = this;

	// exports
	t.render = render;
	
	// imports
	BasicView.call(t, element, calendar, 'month');
	var opt = t.opt;
	var renderBasic = t.renderBasic;
	var formatDate = calendar.formatDate;
	
	
	function render(date, delta) {
		if (delta || opt('monthFromBeginningOnly')) {
			addMonths(date, delta);
			date.setDate(1);
		}
		var start = cloneDate(date, true);
		var end = addMonths(cloneDate(start), 1);
		var visStart = cloneDate(start);
		var visEnd = cloneDate(end);
		var firstDay = opt('firstDay');
		var nwe = opt('weekends') ? 0 : 1;
		if (nwe) {
			skipWeekend(visStart);
			skipWeekend(visEnd, -1, true);
		}

		t.title = formatDates(start, addDays(cloneDate(end),-1), opt('titleFormat'));
		t.start = start;
		t.end = end;
		t.visStart = visStart;
		t.visEnd = visEnd;
		
		var cols = dayDiff(visEnd,visStart);
		var rowCnt = (t.calendar.rooms !== undefined)? t.calendar.rooms.length : 0;
		  
		renderBasic(rowCnt, cols, false);
	}	
}

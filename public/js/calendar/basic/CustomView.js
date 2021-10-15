fcViews.custom = CustomView;

function CustomView(element, calendar) {
	var t = this;

    t.isCustomRangeSelected = false;
    t.onDateStartChanged = null;
    t.onDateEndChanged = null;

	// exports
	t.render = render;

	// imports
	BasicView.call(t, element, calendar, 'custom');
	var opt = t.opt;
	var renderBasic = t.renderBasic;
	var formatDates = calendar.formatDates;

	function render(date, delta) {
        if (!t.isCustomRangeSelected) {
            var before = opt('daysBeforeToday') || 0;
            var after = opt('daysAfterToday') || 0;

            if (delta) {
                addDays(date, delta * (before + after + 1));
            }

            t.start = addDays(cloneDate(date), -before);
            t.end = addDays(cloneDate(date), after);
        }
        else {
            var range = dayDiff(t.end, t.start) + 1;

            t.start = addDays(cloneDate(t.start), range * delta);
            t.end = addDays(cloneDate(t.end), range * delta);
        }

        t.visStart = cloneDate(t.start);
        t.visEnd = cloneDate(t.end);

        if (!opt('weekends')) {
            skipWeekend(t.visStart);
            skipWeekend(t.visEnd, -1, true);
        }

        t.title = formatTitle();

        var cols = dayDiff(t.visEnd, t.visStart) + 1;
        var maxCols = dayDiff(t.end, t.start) + 1;
        var rowArray = [];
		var rowLength = 0;
		for(k = 0; k < t.calendar.rooms.length; k++)
		{
			if(t.calendar.rooms[k].room_type_id in rowArray)
			{
			}else{
				rowLength++;
				rowArray[t.calendar.rooms[k].room_type_id] = t.calendar.rooms[k].room_type;
			}
		}
		if(!innGrid.isOverviewCalendar)
		{
			var rowCnt = (t.calendar.rooms !== undefined)? t.calendar.rooms.length : 0;
		}
		else
		{
			var rowCnt = rowLength;
			for(k = 0; k < t.calendar.rooms.length; k++)
			{
				if(t.calendar.rooms[k].room_type_id in rowArray)
				{
				}else{
					rowArray[t.calendar.rooms[k].room_type_id] = t.calendar.rooms[k].room_type;
				}
			}
		}
        renderBasic(rowCnt, cols, false, maxCols, rowArray);
	}

    function formatTitle () {
        var $content, $dateStart, $dateEnd, datepickerOptions;

        $dateStart = $('<input>').attr({
            class: 'form-control'
        });
        $dateEnd = $('<input>').attr({
            class: 'form-control'
        });

        $dateStart.datepicker({
            autoSize: false,
            dateFormat: 'yy-mm-dd'
        });

        $dateEnd.datepicker({
            autoSize: false,
            dateFormat: 'yy-mm-dd',
            minDate: addDays(cloneDate(t.start), 10),
            maxDate: addDays(cloneDate(t.start), 31)
        });

        $dateStart.datepicker('setDate', t.start);
        $dateEnd.datepicker('setDate', t.end);

        $dateStart.change(function () {
            var selectedDate = $dateStart.datepicker('getDate');

            if (selectedDate && selectedDate !== t.start) {
                t.isCustomRangeSelected = true;

                t.start = selectedDate;
                t.end = addDays(cloneDate(t.start), 25);

                $dateEnd.datepicker('setDate', t.end);

                if (t.onDateStartChanged) t.onDateStartChanged();
            }
        });

        $dateEnd.change(function () {
            var selectedDate = $dateEnd.datepicker('getDate');

            if (selectedDate && selectedDate !== t.end) {
                t.isCustomRangeSelected = true;
                t.end = selectedDate;

                if (t.onDateEndChanged) t.onDateEndChanged();
            }
        });

        $content = $('<div>')
            .addClass('fc-header-title-custom form-group m-055')
            .append(l('show')+': ')
            .append($dateStart)
            .append(' '+l('to')+' ')
            .append($dateEnd);

        return $content;
    }
}
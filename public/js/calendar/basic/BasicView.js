
var tdHeightBug;

setDefaults({
	weekMode: 'fixed'
});


function BasicView(element, calendar, viewName) {
	var t = this;
	
	
	// exports
	t.renderBasic = renderBasic;
	t.setHeight = setHeight;
	t.setWidth = setWidth;
	t.renderDayOverlay = renderDayOverlay;
	t.defaultSelectionEnd = defaultSelectionEnd;
	t.renderSelection = renderSelection;
	t.clearSelection = clearSelection;
	t.dragStart = dragStart;
	t.dragStop = dragStop;
	t.defaultEventEnd = defaultEventEnd;
	t.getHoverListener = function() { return hoverListener };
	t.colContentLeft = colContentLeft;
	t.colContentRight = colContentRight;
	t.dayOfWeekCol = dayOfWeekCol;
	t.dateCell = dateCell;
	t.cellDate = cellDate;
	t.cellIsAllDay = function() { return true };
	t.allDayTR = allDayTR;
	t.allDayBounds = allDayBounds;
	t.getRowCnt = function() { return rowCnt };
	t.getColCnt = function() { return colCnt };
	t.getColWidth = function() { return colWidth };
	t.getDaySegmentContainer = function() { return daySegmentContainer };
	t.syncRowsHeights = syncRowsHeights;
	
	// imports
	View.call(t, element, calendar, viewName);
	OverlayManager.call(t);
	SelectionManager.call(t);
	BasicEventRenderer.call(t);
	var opt = t.opt;
	var trigger = t.trigger;
	var clearEvents = t.clearEvents;
	var renderOverlay = t.renderOverlay;
	var clearOverlays = t.clearOverlays;
	var daySelectionMousedown = t.daySelectionMousedown;
	var formatDate = calendar.formatDate;
	
	
	// locals
	var rtl, dis, dit;
	var firstDay;
	var nwe;
	var rowCnt, colCnt;
	var colWidth;
	var viewWidth, viewHeight;
	var thead, tbody;
	var thead0, tbody0;
	var daySegmentContainer;
	var coordinateGrid;
	var hoverListener;
	var colContentPositions;
	
	
	
	/* Rendering
	------------------------------------------------------------*/
	
	
	disableTextSelection(element.addClass('fc-grid'));
	
	
	function renderBasic(r, c, showNumbers, maxColumns, rowCount) {		
		if(innGrid.isOverviewCalendar)
		{
	       renderOverViewBasic(r, c, showNumbers, maxColumns, rowCount);	
		   return;
		}	
		maxColumns = maxColumns || 31;

		rowCnt = r;
		colCnt = c;

		rtl = opt('isRTL');

		if (rtl) {
			dis = -1;
			dit = colCnt - 1;
		} else {
			dis = 1;
			dit = 0;
		}

		firstDay = opt('firstDay');
		nwe = opt('weekends') ? 0 : 1;
		
		var tm = opt('theme') ? 'ui' : 'fc';
		var colFormat = opt('columnFormat');
		var month = t.start.getMonth();
		var today = (opt('today') !== undefined)? clearTime(new Date(opt('today'))) : clearTime(new Date());
		var s, i, j, d = cloneDate(t.visStart);
		
        var buildGridBody = function () {
            var exttable = $("<table/>").appendTo(element);
            var tr = $("<tr/>").appendTo(exttable);
            var td1 = $("<td class='room-column calendar_room'/>").appendTo(tr);
            var td2 = $("<td/>").appendTo(tr);

            var table0 = $("<table id='RoomTable' class='calendar_room_section'/>").appendTo(td1);
            td2 = $("<div style='position:relative;' />").appendTo(td2);
            var table = $("<table id='CalendarTable'/>").appendTo(td2);
            daySegmentContainer = $("<div style='position:absolute;z-index:8;top:0;left:0'/>").appendTo(td2);

            thead = $("<thead><tr></tr></thead>").appendTo(table);
            tbody = $("<tbody></tbody>").appendTo(table);
            thead0 = 
            		$("<thead/>").append(
            			$("<tr/>").append(
	            			$("<th/>", {
	            				class: tm +"-state-default calendar_room_section",
                                                text: l('room_name'),
                                                style: 'padding-left:4px; width:130px'
	            			})
						).append(
	            			$("<th/>", {
	            				class: tm +"-state-default calendar_room_section",
                                                text: ' '+l('type'),
                                                style: 'padding-left:4px;'
	            			})
						)
					);
 
            thead0.appendTo(table0);
            tbody0 = $("<tbody></tbody>").appendTo(table0);
        }

        var updateGrid = function () {

            var formatDayContentDiv = function () {
                return "<div class='fc-day-content'><div style='position: relative' /></div>";
            };

            var currColCnt, currRowCnt;

            // first time, build all cells from scratch
            if (!tbody) {
                buildGridBody();
            }

            currColCnt = thead.find("> tr > th").length;
            currRowCnt = tbody.find("> tr").length;

            if (colCnt < currColCnt) {
                // remove extra columns
                thead.find('> tr').find('> th:gt(' + (colCnt - 1) + ')').remove();
                tbody.find('> tr').find('> td:gt(' + (colCnt - 1) + ')').remove();
            }
            else if (colCnt > currColCnt) {
                var s;

                s = "";
                for (var i = currColCnt; i < colCnt; i++) {
                    s += "<th class='" + tm + '-state-default' + (i == dit ? ' fc-leftmost' : '') + "'></th>";
                }
                $(s).appendTo(thead.find("> tr"));

                s = "";
                for (var i = currColCnt; i < colCnt; i++) {
                    s += "<td class='" + tm + '-state-default fc-new fc-day' + (i*colCnt+j) + (j==dit ? ' fc-leftmost' : '') + "'>" + formatDayContentDiv() + "</td>";
                }
                $(s).appendTo(tbody.find("> tr"));
            }

            if (rowCnt < currRowCnt) {
                tbody.find('> tr:gt(' + (rowCnt - 1) + ')').remove(); // remove extra rows
            }
            else if (rowCnt > currRowCnt) {
                var s = "";
                var s0 = "";

                for (var i = currRowCnt; i < rowCnt; i++) {
                    s += "<tr class='fc-week" + i + "'>";

                    for (j = 0; j < colCnt; j++) {
                        s += "<td class='" + tm + '-state-default fc-new fc-day' + (i*colCnt+j) + (j==dit ? ' fc-leftmost' : '') + "'>" + formatDayContentDiv() + "</td>";
                    }

                    s += "</tr>";
                    var rmtypeColor = '';
                    
                    var room = t.calendar.rooms[i];
                    
                    if(room.status == 'Dirty')
                    {
                        rmtypeColor = 'rgb(247, 229, 225)';
                    }
                    else if(room.status == 'Inspected')
                    {
                        rmtypeColor = '#24bb27';
                    }

                    var roomName = room.name;
                    var roomID = room.room_id;
                    var roomType = room.room_type;
                    var color = rmtypeColor;
					var editIcon = (roomID == '' || roomID == 'unassigned' || roomID == 'unassigned-'+room.room_type_id) ? "" : "<div class='edit-icon'></div>";
                    
					s0 += "<tr><td class='fc-state-default ' style='background-color:"+color+";' ><div class='fc-day-content'><div class='room-name' id='"+roomID+"'>"+roomName+editIcon+"</div></div></td><td class='fc-state-default'><div class='fc-day-content'>"+roomType+"</div></td></tr>";

                }

                $(s).appendTo(tbody);
                $(s0).appendTo(tbody0);

                dayBind(tbody.find('> tr > td.fc-new').removeClass('fc-new'));
            }
        }

        var updateHeadersData = function() {
            var d = cloneDate(t.visStart);
            thead.find('th').each(function() {
                var $th = $(this);
                var dt_to = $.datepicker.formatDate('yy-mm-dd', d);
                    
                $th.attr("data-day", dayIDs[d.getDay()]);
                $th.attr("data-today", +d == +today);
                var color_code = innGrid.color;
                var color_check = 0;
                    for(var i=0;i<color_code.length;i++){
                       if(dt_to >= color_code[i]['start_date'] && dt_to <= color_code[i]['end_date']){
                            $th.attr("data-color",color_code[i]['color_code']);
                            $th.css("background-color",color_code[i]['color_code']);
                            if($th.attr("data-today") === 'true'){
                                var gradiant_color = 'linear-gradient(to left, #FFFF00,#FFFF00 50%,'+color_code[i]['color_code']+' 50%)';
                                $th.css("background",gradiant_color);
                            }
                            color_check = 1;
                        }
                        if(color_check === 0){
                            $th.removeAttr("data-color");
                            $th.removeAttr("style");
                            $th.css("height",'58px');
                        }
                    }
                var dayString = d.toString();
                var month = dayString.split(" ")[1];
				month = l(month);
                
                var colVal = month+'<br>'+formatDate(d, colFormat);
                
                if(colFormat == "dd<br>ddd"){
                    var translatedDayName = l(formatDate(d, "ddd"));
					colVal = '<span class="day">' + translatedDayName + '</span>'+'<br>'+formatDate(d, "dd");
                    colVal = colVal + '<br><span class="month">'+month+'</span>';
                }
                
                $th.html(colVal);

                addDays(d, 1);

                if (nwe) {
                    skipWeekend(d);
                }
            });
        }

        var updateRowsData = function () {
            tbody.find('> tr').each(function () {
                var $tr = $(this);
                var d = cloneDate(t.visStart);
                
                var $td, $dayNumberDiv, $dayContentDiv, dayClassName;

                if (!showNumbers) {
                    $("> td > div.fc-day-number", $tr).remove();
                }

                $tr.find('> td').each(function () {
                    $td = $(this);
                    var dt_to = $.datepicker.formatDate('yy-mm-dd',d);
                    $td.attr("data-day", dayIDs[d.getDay()]);
                    $td.attr("data-other-month", rowCnt > 1 && d.getMonth() != month);
                    $td.attr("data-today", +d == +today);
                    var color_code = innGrid.color;
                    var color_check = 0;
                    for(var i=0;i<color_code.length;i++){
                        if(dt_to >= color_code[i]['start_date'] && dt_to <= color_code[i]['end_date']){
                            $td.attr("data-color",color_code[i]['color_code']);
                            $td.css("background-color",color_code[i]['color_code']);
                            if($td.attr("data-today") === 'true'){
                                var gradiant_color = 'linear-gradient(to left, #FFFF00,#FFFF00 50%,'+color_code[i]['color_code']+' 50%)';
                                $td.css("background",gradiant_color);
                            }
                            color_check = 1;
                        }
                        if(color_check === 0){
                            $td.removeAttr("data-color");
                            $td.removeAttr("style");
                        }
                    }
                    if (showNumbers) {
                        $dayNumberDiv = $("> .fc-day-number", $td);

                        if ($dayNumberDiv.length === 0) {
                            $dayNumberDiv = $("<div class='fc-day-number'>");
                            $td.append($dayNumberDiv);
                        }

                        $dayNumberDiv.text(d.getDate());
                    }

                    addDays(d, 1);

                    if (nwe) {
                        skipWeekend(d);
                    }
                });
            });
        }

        var updateData = function () {
            clearEvents();
            updateHeadersData();
            updateRowsData();
        }

        updateGrid();
        updateData();
	}
	
    function renderOverViewBasic(r, c, showNumbers, maxColumns, rowCount) {
		if(!innGrid.isOverviewCalendar)
		{
		  renderBasic(r, c, showNumbers, maxColumns, false);			
		}
		
		maxColumns = maxColumns || 31;
		rowCnt = r;
		colCnt = c;

		rtl = opt('isRTL');

		if (rtl) {
			dis = -1;
			dit = colCnt - 1;
		} else {
			dis = 1;
			dit = 0;
		}

		firstDay = opt('firstDay');
		nwe = opt('weekends') ? 0 : 1;
		
		var tm = opt('theme') ? 'ui' : 'fc';
		var colFormat = opt('columnFormat');
		var month = t.start.getMonth();
		var today = (opt('today') !== undefined)? clearTime(new Date(opt('today'))) : clearTime(new Date());
		var s, i, j, d = cloneDate(t.visStart);
		
        var buildGridBody = function () {
            var exttable = $("<table/>").appendTo(element);
            var tr = $("<tr/>").appendTo(exttable);
            var td1 = $("<td class='room-column'/>").appendTo(tr);
            var td2 = $("<td/>").appendTo(tr);

            var table0 = $("<table id='RoomTable'/>").appendTo(td1);
            td2 = $("<div style='position:relative;' />").appendTo(td2);
            var table = $("<table id='CalendarTable'/>").appendTo(td2);
            daySegmentContainer = $("<div style='position:absolute;z-index:8;top:0;left:0'/>").appendTo(td2);

            thead = $("<thead><tr></tr></thead>").appendTo(table);
            tbody = $("<tbody></tbody>").appendTo(table);
            thead0 = 
            		$("<thead/>").append(
            			$("<tr/>").append(
	            			$("<th/>", {
	            				class: tm +"-state-default",
                                                text: ' '+l('Room Type'),
                                                style: 'padding-left:4px;'
	            			})
						)
					);
            thead0.appendTo(table0);
            tbody0 = $("<tbody></tbody>").appendTo(table0);
        }

        var updateGrid = function () {

            var formatDayContentDiv = function () {
                return "<div class='fc-day-content'><div style='position: relative' /></div>";
            };

            var currColCnt, currRowCnt;

            // first time, build all cells from scratch
            if (!tbody) {
                buildGridBody();
            }

            currColCnt = thead.find("> tr > th").length;
            currRowCnt = tbody.find("> tr").length;
			
            if (colCnt < currColCnt) {
                // remove extra columns
                thead.find('> tr').find('> th:gt(' + (colCnt - 1) + ')').remove();
                tbody.find('> tr').find('> td:gt(' + (colCnt - 1) + ')').remove();
            }
            else if (colCnt > currColCnt) {
                var s;

                s = "";
                for (var i = currColCnt; i < colCnt; i++) {
                    s += "<th class='" + tm + '-state-default' + (i == dit ? ' fc-leftmost' : '') + "'></th>";
                }
                $(s).appendTo(thead.find("> tr"));

                s = "";
                for (var i = currColCnt; i < colCnt; i++) {
                    s += "<td class='" + tm + '-state-default fc-new fc-day' + (i*colCnt+j) + (j==dit ? ' fc-leftmost' : '') + "'>" + formatDayContentDiv() + "</td>";
                }
                $(s).appendTo(tbody.find("> tr"));
            }

            if (rowCnt < currRowCnt) {

                tbody.find('> tr:gt(' + (rowCnt - 1) + ')').remove(); // remove extra rows
            }
            else if (rowCnt > currRowCnt) {
				
                var s = "";
                var s0 = "";
                var rmType = [];
				
                for (var i = currRowCnt; i < rowCnt; i++) {
                    s += "<tr class='fc-week" + i + "'>";

                    for (j = 0; j < colCnt; j++) {
                        s += "<td class='" + tm + '-state-default fc-new fc-day' + (i*colCnt+j) + (j==dit ? ' fc-leftmost' : '') + "'>" + formatDayContentDiv() + "</td>";
                    }

                    s += "</tr>";
                    var rmtypeColor = '';
                    
                    var room = t.calendar.rooms[i];
					                  
					
                    if(room.status == 'Dirty')
                    {
                        rmtypeColor = 'rgb(247, 229, 225)';
                    }
                    else if(room.status == 'Inspected')
                    {
                        rmtypeColor = '#24bb27';
                    }

                   // var roomName = room.name;
                    var roomID = room.room_id;
                    var roomType = room.room_type;
                    
                    var color = rmtypeColor;
					
                }

				for(var index in rowCount)
				{
					s0 += "<tr><td class='fc-state-default'><div class='fc-day-content' data-id='"+rowCount[index]+"' data-room_type_id='"+index+"'>"+rowCount[index]+"</div></td></tr>";
				}

                $(s).appendTo(tbody);
                $(s0).appendTo(tbody0);

                dayBind(tbody.find('> tr > td.fc-new').removeClass('fc-new'));
            }
        }

        var updateHeadersData = function() {
            var d = cloneDate(t.visStart);
            thead.find('th').each(function() {
                var $th = $(this);
                var dt_to = $.datepicker.formatDate('yy-mm-dd', d);
                    
                $th.attr("data-day", dayIDs[d.getDay()]);
                $th.attr("data-today", +d == +today);
                var color_code = innGrid.color;
                var color_check = 0;
                    for(var i=0;i<color_code.length;i++){
                       if(dt_to >= color_code[i]['start_date'] && dt_to <= color_code[i]['end_date']){
                            $th.attr("data-color",color_code[i]['color_code']);
                            $th.css("background-color",color_code[i]['color_code']);
                            if($th.attr("data-today") === 'true'){
                                var gradiant_color = 'linear-gradient(to left, #FFFF00,#FFFF00 50%,'+color_code[i]['color_code']+' 50%)';
                                $th.css("background",gradiant_color);
                            }
                            color_check = 1;
                        }
                        if(color_check === 0){
                            $th.removeAttr("data-color");
                            $th.removeAttr("style");
                            $th.css("height",'58px');
                        }
                    }
                var dayString = d.toString();
                var month = dayString.split(" ")[1];
				month = l(month);
                
                var colVal = month+'<br>'+formatDate(d, colFormat);
                
                if(colFormat == "dd<br>ddd"){
                    var translatedDayName = l(formatDate(d, "ddd"));
					colVal = '<span class="day">' + translatedDayName + '</span>'+'<br>'+formatDate(d, "dd");
                    colVal = colVal + '<br><span class="month">'+month+'</span>';
                }
                
                $th.html(colVal);

                addDays(d, 1);

                if (nwe) {
                    skipWeekend(d);
                }
            });
        }

        var updateRowsData = function () {
            tbody.find('> tr').each(function () {
                var $tr = $(this);
                var d = cloneDate(t.visStart);
                
                var $td, $dayNumberDiv, $dayContentDiv, dayClassName;

                if (!showNumbers) {
                    $("> td > div.fc-day-number", $tr).remove();
                }

                $tr.find('> td').each(function () {
                    $td = $(this);
                    var dt_to = $.datepicker.formatDate('yy-mm-dd',d);
                    $td.attr("data-day", dayIDs[d.getDay()]);
                    $td.attr("data-other-month", rowCnt > 1 && d.getMonth() != month);
                    $td.attr("data-today", +d == +today);
					$td.removeClass (function (index, className) {
						return (className.match (/(^|\s)cell-\S+/g) || []).join(' ');
					});
					$td.addClass('cell-'+dt_to +'-'+ $tr.index());
                    var color_code = innGrid.color;
                    var color_check = 0;
                    for(var i=0;i<color_code.length;i++){
                        if(dt_to >= color_code[i]['start_date'] && dt_to <= color_code[i]['end_date']){
                            $td.attr("data-color",color_code[i]['color_code']);
                            $td.css("background-color",color_code[i]['color_code']);
                            if($td.attr("data-today") === 'true'){
                                var gradiant_color = 'linear-gradient(to left, #FFFF00,#FFFF00 50%,'+color_code[i]['color_code']+' 50%)';
                                $td.css("background",gradiant_color);
                            }
                            color_check = 1;
                        }
                        if(color_check === 0){
                            $td.removeAttr("data-color");
                            $td.removeAttr("style");
                        }
                    }
                    if (showNumbers) {
                        $dayNumberDiv = $("> .fc-day-number", $td);

                        if ($dayNumberDiv.length === 0) {
                            $dayNumberDiv = $("<div class='fc-day-number'>");
                            $td.append($dayNumberDiv);
                        }

                        $dayNumberDiv.text(d.getDate());
                    }

                    addDays(d, 1);

                    if (nwe) {
                        skipWeekend(d);
                    }
                });
            });
        }

        var updateData = function () {
            clearEvents();
            updateHeadersData();
            updateRowsData();
        }

        updateGrid();
        updateData();
	}
	function setHeight(height) {
		   viewHeight = height;
		   var leftTDs = tbody.find('tr td:first-child'),
				   tbodyHeight = viewHeight - thead.height(),
				   rowHeight1, rowHeight2;
		   if (opt('weekMode') == 'variable') {
				   rowHeight1 = rowHeight2 = Math.floor(tbodyHeight / (rowCnt==1 ? 2 : 6));
		   }else{
				   rowHeight1 = Math.floor(tbodyHeight / rowCnt);
				   rowHeight2 = tbodyHeight - rowHeight1*(rowCnt-1);
		   }

		   if(opt('rowsHeight') !== undefined)
				   rowHeight1 = rowHeight2 = opt('rowsHeight');

		rowHeight1 = rowHeight2 = '25px';

		   if (tdHeightBug === undefined) {
				   // bug in firefox where cell height includes padding
				   var tr = tbody.find('tr:first'),
						   td = tr.find('td:first');
				   td.height(rowHeight1);
				   tdHeightBug = rowHeight1 != td.height();
		   }

			var leftTHs = thead.find('tr th');
			var table0THs = thead0.find('tr th');
			var headerHeight = (leftTHs.height());
			table0THs.height(headerHeight);
			leftTHs.height(headerHeight);

		   if (tdHeightBug) {
		   		   leftTDs.slice(0, -1).height(rowHeight1);
				   leftTDs.slice(-1).height(rowHeight2);
				   var table0TDs = tbody0.find('tr td:first-child');
				   table0TDs.slice(0, -1).height(rowHeight1);
				   table0TDs.slice(-1).height(rowHeight2);
				   // bug in IE7
				   var dh = table0THs.height() - headerHeight;
				   table0THs.height(headerHeight - dh);
		   }else{

				   setOuterHeight(leftTDs.slice(0, -1), rowHeight1);
				   setOuterHeight(leftTDs.slice(-1), rowHeight2);
				   var table0TDs = tbody0.find('tr td:first-child');
				   setOuterHeight(table0TDs.slice(0, -1), rowHeight1);
				   setOuterHeight(table0TDs.slice(-1), rowHeight2);
		   }
	}
	
  
  function syncRowsHeights() {
		var leftDivs = tbody0.find('tr').find('td:first div.fc-day-content > div'); // room table
		var rightDivs = tbody.find('tr').find('td:first div.fc-day-content > div'); // calendar table
		for(i=0; i<leftDivs.length; i++)
		{
			$(leftDivs[i]).height($(rightDivs[i]).height());
		}
	}
  
	
	function setWidth(width) {
		viewWidth = width;
		colContentPositions.clear();
		colWidth = Math.floor((viewWidth ) / colCnt);
		//setOuterWidth(thead.find('th')/*.slice(0, -1)*/, colWidth);
		tbody.width = width;
		var ths = thead.find('th');
		
		//commented this out to remove % width for each th columns - Jaeyun
		//ths.width((100.0 / ths.length)+'%'); 
		
		/*
		ths.css('max-width',colWidth);
		ths.css('min-width',colWidth);
		*/
		
		var newViewWidth = 0;
		var ths = thead.find('th:visible');

		$.each(ths, function(index, value) { 
			newViewWidth += $(value).outerWidth();
		});
		
		viewWidth = newViewWidth;
	}
	
	
	
	/* Day clicking and binding
	-----------------------------------------------------------*/
	
	
	function dayBind(days) {
		days.click(dayClick)
			.mousedown(daySelectionMousedown);
	}
	
	
	function dayClick(ev) {
		if (!opt('selectable')) { // SelectionManager will worry about dayClick
			var n = parseInt(this.className.match(/fc\-day(\d+)/)[1]),
				date = addDays(
					cloneDate(t.visStart),
					Math.floor(n/colCnt) * 7 + n % colCnt
				);
			// TODO: what about weekends in middle of week?
			trigger('dayClick', this, date, true, ev);
		}
	}
	
	
	
	/* Semi-transparent Overlay Helpers
	------------------------------------------------------*/
	
	function renderDayOverlay(overlayStart, overlayEnd, refreshCoordinateGrid ,cell, origCell) { // overlayEnd is exclusive
		if (refreshCoordinateGrid) {
			coordinateGrid.build();
		}
		var rowStart = cloneDate(t.visStart);
		var rowEnd = addDays(cloneDate(rowStart), colCnt);
 
		var stretchStart = new Date(Math.max(rowStart, overlayStart));
		var stretchEnd = new Date(Math.min(rowEnd, overlayEnd));
		if (origCell['col'] <= cell['col']) {
            t.lastSelectedRow = origCell['row'];
            var cellOverlay = renderCellOverlay(origCell['row'], origCell['col'], origCell['row'], cell['col']);
            overlayHint(origCell['row'], stretchStart, stretchEnd, cellOverlay);
            dayBind(
				cellOverlay
			);
		}else{
            t.lastSelectedRow = null;
        }
	}
 
	function renderCellOverlay(row0, col0, row1, col1) { // row1,col1 is inclusive
		var rect = coordinateGrid.rect(row0, col0, row1, col1, element);
		return renderOverlay(rect, element);
	}
 
    function overlayHint(row, startDate, endDate, cellOverlay){
 
        var roomName = t.calendar.rooms[row].name;
        var roomID = t.calendar.rooms[row].room_id;
        var roomType = t.calendar.rooms[row].room_type;
        var offset = $(cellOverlay).position();
 
        $('#notification-drag-box .from').text(formatDate(startDate, ($('#companyDateFormat').val().toLowerCase() == 'dd-mm-yy') ? 'dd-MM-yyyy' : 'yyyy-MM-dd'));
        $('#notification-drag-box .to').text(formatDate(endDate, ($('#companyDateFormat').val().toLowerCase() == 'dd-mm-yy') ? 'dd-MM-yyyy' : 'yyyy-MM-dd'));
        $('#notification-drag-box .room').text(roomName + " " + roomType);
 
        $('#notification-drag-box:hidden').show().css({
            left: offset['left'] + 20,
            top: offset['top'] + 164
        });
 
    }


    /*
	function renderDayOverlay(overlayStart, overlayEnd, refreshCoordinateGrid) { // overlayEnd is exclusive
		if (refreshCoordinateGrid) {
			coordinateGrid.build();
		}
		var rowStart = cloneDate(t.visStart);
		var rowEnd = addDays(cloneDate(rowStart), colCnt);

		var stretchStart = new Date(Math.max(rowStart, overlayStart));
		var stretchEnd = new Date(Math.min(rowEnd, overlayEnd));
		if (stretchStart < stretchEnd) {
			var colStart, colEnd;
			if (rtl) {
				colStart = dayDiff(stretchEnd, rowStart)*dis+dit+1;
				colEnd = dayDiff(stretchStart, rowStart)*dis+dit+1;
			}else{
				colStart = dayDiff(stretchStart, rowStart);
				colEnd = dayDiff(stretchEnd, rowStart);
			}
			dayBind(
				renderCellOverlay(0, colStart, rowCnt-1, colEnd-1)
			);
		}

	}
	
	
	function renderCellOverlay(row0, col0, row1, col1) { // row1,col1 is inclusive
		var rect = coordinateGrid.rect(row0, col0, row1, col1, element);
		return renderOverlay(rect, element);
	}
	
	*/
	
	/* Selection
	-----------------------------------------------------------------------*/
	
	
	function defaultSelectionEnd(startDate, allDay) {
		return cloneDate(startDate);
	}
	
	/*
	function renderSelection(startDate, endDate, allDay) {
		renderDayOverlay(startDate, addDays(cloneDate(endDate), 1), true); // rebuild every time???
	}
	*/

	function renderSelection(startDate, endDate, allDay, cell, origCell) {
		renderDayOverlay(startDate, addDays(cloneDate(endDate), 1), true ,cell, origCell); // rebuild every time???
	}
	
	function clearSelection() {
		clearOverlays();
	}
	
	
	
	/* External Dragging
	-----------------------------------------------------------------------*/
	
	
	function dragStart(_dragElement, ev, ui) {
		hoverListener.start(function(cell) {
			clearOverlays();
			if (cell) {
				renderCellOverlay(cell.row, cell.col, cell.row, cell.col);
			}
		}, ev);
	}
	
	
	function dragStop(_dragElement, ev, ui) {
		var cell = hoverListener.stop();
		clearOverlays();
		if (cell) {
			var d = cellDate(cell);
			trigger('drop', _dragElement, d, true, ev, ui);
		}
	}
	
	
	
	/* Utilities
	--------------------------------------------------------*/
	
	
	function defaultEventEnd(event) {
		return cloneDate(event.start);
	}
	
	
	coordinateGrid = new CoordinateGrid(function(rows, cols) {
		var e, n, p;
		var tds = tbody.find('tr:first td');

		if (rtl) {
			tds = $(tds.get().reverse());
		}
		tds.each(function(i, _e) {
			e = $(_e);
			n = e.offset().left;
			if (i) {
				p[1] = n;
			}
			p = [n];
			cols[i] = p;
		});
		p[1] = n + e.outerWidth();
		tbody.find('tr').each(function(i, _e) {
			e = $(_e);
			n = e.offset().top;
			if (i) {
				p[1] = n;
			}
			p = [n];
			rows[i] = p;
		});
		p[1] = n + e.outerHeight();
	});
	
	
	hoverListener = new HoverListener(coordinateGrid);
	
	
	colContentPositions = new HorizontalPositionCache(function(col) {
		return tbody.find('td:eq(' + col + ') div div');
	});
	
	
	function colContentLeft(col) {
		return colContentPositions.left(col);
	}
	
	
	function colContentRight(col) {
		return colContentPositions.right(col);
	}
	
	
	function dayOfWeekCol(dayOfWeek) {
		return (dayOfWeek - Math.max(firstDay, nwe) + colCnt) % colCnt;
	}
	
	
	function dateCell(date) {
		return {
			row: Math.floor(dayDiff(date, t.visStart)/* / 7*/),
			col: 1//dayOfWeekCol(date.getDay())*dis + dit
		};
	}
	
	
	function cellDate(cell) {
		return addDays(cloneDate(t.visStart), /*cell.row*7*/ + cell.col*dis+dit);
		// TODO: what about weekends in middle of week?
	}
	
	
	function allDayTR(i) {
		return tbody.find('tr:eq('+i+')');
	}


	function allDayBounds(i) {
		return {
			left: 0,
			right: viewWidth
		};
	}
	
	
}

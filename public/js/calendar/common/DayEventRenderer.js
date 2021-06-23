function DayEventRenderer() {
	var t = this;

	// exports
	t.renderDaySegs = renderDaySegs;
	t.resizableDayEvent = resizableDayEvent;
	t.draggableDayEvent = draggableDayEvent;

	// imports
	var opt = t.opt;
	var trigger = t.trigger;
	var eventEnd = t.eventEnd;
	var reportEventElement = t.reportEventElement;
	var showEvents = t.showEvents;
	var hideEvents = t.hideEvents;
	var eventResize = t.eventResize;
	var getRowCnt = t.getRowCnt;
	var getColCnt = t.getColCnt;
	var getColWidth = t.getColWidth;
	var allDayTR = t.allDayTR;
	var allDayBounds = t.allDayBounds;
	var colContentLeft = t.colContentLeft;
	var colContentRight = t.colContentRight;
	var dayOfWeekCol = t.dayOfWeekCol;
	var dateCell = t.dateCell;
	var compileDaySegs = t.compileDaySegs;
	var getDaySegmentContainer = t.getDaySegmentContainer;
	var bindDaySeg = t.bindDaySeg; //TODO: streamline this
	var formatDates = t.calendar.formatDates;
	var renderDayOverlay = t.renderDayOverlay;
	var clearOverlays = t.clearOverlays;
	var clearSelection = t.clearSelection;
	var getHoverListener = t.getHoverListener;
	
	var eventElementHandlers = t.eventElementHandlers;
	var reportEventClear = t.reportEventClear;
	var reportEvents = t.reportEvents;
	var eventDrop = t.eventDrop;
	var clearEvents = t.clearEvents;
	
	/* Rendering
	-----------------------------------------------------------------------------*/

	function renderDaySegs(segs, modifiedEventId) {
		var segmentContainer = getDaySegmentContainer();
		var rowDivs;
		var rowCnt = getRowCnt();
		var colCnt = getColCnt();
		var i = 0;
		var rowI;
		var levelI;
		var colHeights;
		var j;
		var segCnt = segs.length;
		var seg;
		var top;
		var k;
		// console.time('total');
		// console.time('t1');
        buildSegElements(segmentContainer, segs);
		// console.timeEnd('t1');
		// console.time('t2');
		daySegElementResolve(segs, segmentContainer.children());
		// console.timeEnd('t2');
		// console.time('t3');

		daySegElementReport(segs);
		// console.timeEnd('t3');
		// console.time('t4');
		daySegHandlers(segs, segmentContainer, modifiedEventId);
		// console.timeEnd('t4');
		// console.time('t5');
		daySegCalcHSides(segs);
		// console.timeEnd('t5');
		// console.time('t6');
		daySegSetWidths(segs);
		// console.timeEnd('t6');
		// console.time('t7');
		daySegCalcHeights(segs);
		// console.timeEnd('t7');
		// console.time('t8');

		rowDivs = getRowDivs();
		// set row heights, calculate event tops (in relation to row top)
		for (rowI=0; rowI<rowCnt; rowI++) {
			levelI = 0;
			colHeights = [];
			for (j=0; j<colCnt; j++) {
				colHeights[j] = 0;
			}
			while (i<segCnt && (seg = segs[i]).row == rowI) {
				// loop through segs in a row
				top = arrayMax(colHeights.slice(seg.startCol, seg.endCol));
				seg.top = top;
				top += seg.outerHeight;
				for (k=seg.startCol; k<seg.endCol; k++) {
					colHeights[k] = top;
				}
				i++;
			}
			rowDivs[rowI].height(arrayMax(colHeights));
		}
		// console.timeEnd('t8');
		// console.time('t9');
		daySegSetTops(segs, getRowTops(rowDivs));
		// console.timeEnd('t9');
		// console.time('t10');
		//t.syncRowsHeights();
		setTimeout(function(){
			t.syncRowsHeights();
			// delay it so it doesn't block render process, it's too heavy process
		}, 500);
		// console.timeEnd('t10');
		// console.timeEnd('total');
	}

	function renderTempDaySegs(segs, adjustRow, adjustTop) {
		var tempContainer = $("<div/>");
		var elements;
		var segmentContainer = getDaySegmentContainer();
		var i;
		var segCnt = segs.length;
		var element;

        buildSegElements(tempContainer, segs);

        elements = tempContainer.children();
		segmentContainer.append(elements);

        daySegElementResolve(segs, elements);
		daySegCalcHSides(segs);
		daySegSetWidths(segs);
		daySegCalcHeights(segs);
		daySegSetTops(segs, getRowTops(getRowDivs()));

        elements = [];
		for (i=0; i<segCnt; i++) {
			element = segs[i].element;
			if (element) {
				if (segs[i].row === adjustRow) {
					element.css('top', adjustTop);
				}
				elements.push(element[0]);
			}
		}
		return $(elements);
	}
	
	function daySegElementResolve(segs, elements) { // sets seg.element
		var i;
		var segCnt = segs.length;
		var seg;
		var event;
		var element;
		var triggerRes;
		for (i=0; i<segCnt; i++) {
			seg = segs[i];
			event = seg.event;
			element = $(elements[i]); // faster than .eq()
			triggerRes = trigger('eventRender', event, event, element);
			if (triggerRes === false) {
				element.remove();
			}else{
				if (triggerRes && triggerRes !== true) {
					triggerRes = $(triggerRes)
						.css({
							position: 'absolute',
							left: seg.left
						});
					element.replaceWith(triggerRes);
					element = triggerRes;
				}
				seg.element = element;
			}
		}
	}
	
	
	function daySegElementReport(segs) {
		var i;
		var segCnt = segs.length;
		var seg;
		var element;
		for (i=0; i<segCnt; i++) {
			seg = segs[i];
			element = seg.element;
			if (element) {
				reportEventElement(seg.event, element);
			}
		}
	}
	
	
	function daySegHandlers(segs, segmentContainer, modifiedEventId) {
		var i;
		var segCnt = segs.length;
		var seg;
		var element;
		var event;
		// retrieve elements, run through eventRender callback, bind handlers
		for (i=0; i<segCnt; i++) {
			seg = segs[i];
			element = seg.element;
			if (element) {
				event = seg.event;
				if (event._id === modifiedEventId) {
					bindDaySeg(event, element, seg, segs);
				}else{
					element[0]._fci = i; // for lazySegBind
				}
			}
		}
		lazySegBind(segmentContainer, segs, bindDaySeg);
	}
	
	
	function daySegCalcHSides(segs) { // also sets seg.key
		var i;
		var segCnt = segs.length;
		var seg;
		var element;
		var key, val;
		var hsideCache = {};
		// record event horizontal sides
		for (i=0; i<segCnt; i++) {
			seg = segs[i];
			element = seg.element;
			if (element) {
				key = seg.key = cssKey(element[0]);
				val = hsideCache[key];
				if (val === undefined) {
					val = hsideCache[key] = hsides(element[0], true);
				}
				seg.hsides = val;
			}
		}
	}
	
	
	function daySegSetWidths(segs) {
		var i;
		var segCnt = segs.length;
		var seg;
		var element;
		for (i=0; i<segCnt; i++) {
			seg = segs[i];
			element = seg.element;
			if (element) {
				element[0].style.width = Math.max(0, seg.outerWidth - seg.hsides) + 'px';
			}
		}
	}
	
	function daySegCalcHeights(segs) {
		var i;
		var segCnt = segs.length;
		var seg;
		var element;
		var key, val;
		var vmarginCache = {};
		// record event heights
		for (i=0; i<segCnt; i++) {
			seg = segs[i];
			element = seg.element;
			if (element) {
				key = seg.key; // created in daySegCalcHSides
				val = vmarginCache[key];
				if (val === undefined) {
					val = vmarginCache[key] = vmargins(element[0]);
				}
				seg.outerHeight = element[0].offsetHeight + val;
			}
		}
	}

	function getRowDivs() {
		var i;
		var rowCnt = getRowCnt();
		var rowDivs = [];
		for (i=0; i<rowCnt; i++) {
			rowDivs[i] = allDayTR(i)
				.find('td:first div.fc-day-content > div'); // optimal selector?
		}
		return rowDivs;
	}
	
	
	function getRowTops(rowDivs) {
		var i;
		var rowCnt = rowDivs.length;
		var tops = [];
		for (i=0; i<rowCnt; i++) {
			tops[i] = rowDivs[i][0].offsetTop;
		}
		return tops;
	}
	
	
	function daySegSetTops(segs, rowTops) { // also triggers eventAfterRender
		var i;
		var segCnt = segs.length;
		var seg;
		var element;
		var event;
		for (i=0; i<segCnt; i++) {
			seg = segs[i];
			element = seg.element;
			if (element) {
				element[0].style.top = rowTops[seg.row] + (seg.top||0) + 'px';
				event = seg.event;
				trigger('eventAfterRender', event, event, element);
			}
		}
	}
	
	
	
	/* Resizing
	-----------------------------------------------------------------------------------*/
	
	
	function resizableDayEvent(event, element, seg, segs) {
		if (opt('disableResizing') || !seg.isEnd)
			return;
		var rtl = opt('isRTL');
		var direction = rtl ? 'w' : 'e';
		var handle = element.find('div.ui-resizable-' + direction);

		handle.mousedown(function(ev) {
			if (ev.which != 1) {
				return; // needs to be left mouse button
			}
			var hoverListener = t.getHoverListener();
			var rowCnt = getRowCnt();
			var colCnt = getColCnt();
			var dis = rtl ? -1 : 1;
			var dit = rtl ? colCnt : 0;
			var elementTop = element.css('top');
			var dayDelta;
			var helpers;
			var eventCopy = $.extend({}, event);
			var segCopy = $.extend({}, seg);				
			segCopy.event = eventCopy;
				
			var minCell = dateCell(event.start);
			clearSelection();
			$('body')
				.css('cursor', direction + '-resize')
				.one('mouseup', mouseup);
			trigger('eventResizeStart', this, event, ev);
			hoverListener.start(function(cell, origCell) {
				if (cell) {
					var r = Math.max(minCell.row, cell.row);
					var c =  cell.col;
						
					if (rowCnt == 1) {
						r = 0; // hack for all-day area in agenda views
					}
					if (r == minCell.row) {
						if (rtl) {
							c = Math.min(minCell.col+1, c);
						}else{
							//c = Math.max(minCell.col, c);//Don't know if this is needed
						}
					}
					dayDelta = (/*r * colCnt + */c*dis+dit) - (/*origCell.row * colCnt +*/ origCell.col*dis+dit);
												
					var potentialNewEnd = addDays(eventEnd(event), dayDelta, true);
					eventCopy.end = potentialNewEnd;
												
					var collide = false;
					if(segs!==undefined)
					for(var i=0; i<segs.length && !collide; i++)
						if(segs[i].event !== seg.event)
							collide = eventsCollide(segs[i].event, eventCopy);
								
					if(!collide)
						newEnd = potentialNewEnd;
					newEnd = (Math.max(newEnd,event.start));

					if (dayDelta) {
						eventCopy.end = newEnd;
						var oldHelpers = helpers;
						helpers = renderTempDaySegs(compileDaySegs([eventCopy]), seg.row, elementTop);
						helpers.find('*').css('cursor', direction + '-resize');
						if (oldHelpers) {
							oldHelpers.remove();
						}
						hideEvents(event);
					}else{
						if (helpers) {
							showEvents(event);
							helpers.remove();
							helpers = null;
						}
					}
					clearOverlays();
					//renderDayOverlay(event.start, addDays(cloneDate(newEnd), 1)); // coordinate grid already rebuild at hoverListener.start
				}
			}, ev);
			function mouseup(ev) {
				var collide = false;
				trigger('eventResizeStop', this, event, ev);
				$('body').css('cursor', 'auto');
				hoverListener.stop();
				clearOverlays();
				if(segs!==undefined) {
					for(var i=0; i<segs.length && !collide; i++)
						if(segs[i].event !== seg.event)
							collide = eventsCollide(segs[i].event, eventCopy);
				}
				dayDelta = dayDiff(eventEnd(eventCopy), eventEnd(event));
				var potentialNewEnd = addDays(eventEnd(event), dayDelta, true);					
				if(potentialNewEnd < event.start)
					dayDelta = 0;
				if (!collide && dayDelta) {
					// event redraw will clear helpers
					eventResize(this, event, dayDelta, 0, ev);
				} else {
					// redo resizing
					eventResize(this, event, 0, 0, ev);
				}
			}
		});
	}

	/* Dragging
	----------------------------------------------------------------------------*/
	
	function draggableDayEvent(event, eventElement, seg, segs) {
		if (opt('disableDragging') || !eventElement.draggable)
			return;
		var hoverListener = getHoverListener();
		var dayDelta;
		var roomDelta;
		var helpers;
		var rtl = opt('isRTL');
		var direction = rtl ? 'w' : 'e';
		var newStart, newEnd;
		var eventCopy = $.extend({}, event);
		var segCopy = $.extend({}, seg);    
		segCopy.event = eventCopy;
		eventElement.draggable({
			zIndex: 9,
			delay: 50,
			opacity: opt('dragOpacity'),
			revertDuration: opt('dragRevertDuration'),
			start: dragingStarted,
			stop: draggingStopped
		});
		function dragingStarted(ev, ui) {
			trigger('eventDragStart', eventElement, event, event, ui);
			hideEvents(event, eventElement);
			hoverListener.start(function(cell, origCell, rowDelta, colDelta) {
				eventElement.draggable('option', 'revert', !cell || !rowDelta && !colDelta);
				clearOverlays();
				if (cell) {
					dayDelta = colDelta * (opt('isRTL') ? -1 : 1);
					roomDelta = rowDelta;
				}else{
					dayDelta = 0;
				}
				var collide = false;
				var oldStart = eventCopy.start;
				var oldEnd = eventCopy.end;
				var oldRoom = eventCopy.room;
				var potentialNewEnd = addDays(eventEnd(event), dayDelta, true);
				var potentialNewStart = addDays(cloneDate(event.start), dayDelta, true);
				eventCopy.start = potentialNewStart;
				eventCopy.end = potentialNewEnd;
				eventCopy.room = event.room+roomDelta;
				if(segs != null)
				{
					for(var i=0; i<segs.length && !collide; i++)
						if(segs[i].event !== seg.event)
							collide = eventsCollide(segs[i].event, eventCopy);
				}
				if (!cell || collide) {
					eventCopy.start = oldStart;
					eventCopy.end = oldEnd;
					eventCopy.room = oldRoom;
					dayDelta = 0;
					roomDelta = 0;
				}
				var oldHelpers = helpers;
				var elementTop = eventElement.css('top');
				helpers = renderTempDaySegs(compileDaySegs([eventCopy]), -1, elementTop)
				if (oldHelpers)
					oldHelpers.remove();
				hideEvents(event);
			}, event, 'drag');
		}

        function draggingStopped(ev, ui) {
			hoverListener.stop();
			clearOverlays();
			trigger('eventDragStop', eventElement, event, ev, ui);
			var needsRedo = true;
			 
			if (dayDelta !== 0 || roomDelta !== 0) {
				var collide = false;
				var eventCopy = $.extend({}, event);
				eventCopy.room += roomDelta;
				eventCopy.end=addDays(cloneDate(eventEnd(eventCopy)), dayDelta, true);
				eventCopy.start=addDays(cloneDate(eventCopy.start), dayDelta, true);
				if(segs!==undefined)
				for(var i=0; i<segs.length && !collide; i++) {
					if(segs[i].event !== event)
						collide = eventsCollide(segs[i].event, eventCopy);
				}
				eventElement.find('a').removeAttr('href'); // prevents safari from visiting the link
				needsRedo = false;
				eventDrop(this, event, dayDelta, 0, event.allDay, ev, ui, roomDelta);
			}else{
				eventElement.css('filter', ''); // clear IE opacity side-effects
				showEvents(event, eventElement);
			}
			if(needsRedo)
				eventDrop(this, event, 0, 0, event.allDay, ev, ui, 0);
		}
	}

    function buildSegElements($container, segs) { // also sets seg.left and seg.outerWidth
        var rtl = opt('isRTL');
        var i;
        var segCnt=segs.length;
        var seg;
        var event;
        var className;
        var bounds = allDayBounds();
        var minLeft = bounds.left;
        var maxLeft = bounds.right;
        var cols = []; // don't really like this system (but have to do this b/c RTL works differently in basic vs agenda)
        var left;
        var right;

        var html = "";

        // calculate desired position/dimensions, create html
        for (i=0; i<segCnt; i++) {
            seg = segs[i];
            event = seg.event;
            className = 'fc-event fc-event-hori ';
            if (rtl) {
                if (seg.isStart) {
                    className += 'fc-corner-right ';
                }
                if (seg.isEnd) {
                    className += 'fc-corner-left ';
                }
                cols[0] = seg.end.getDate()-1;
                cols[1] = seg.start.getDate();
                left = seg.isEnd ? colContentLeft(cols[0]) : minLeft;
                right = seg.isStart ? colContentRight(cols[1]) : maxLeft;
            }else{
                if (seg.isStart) {
                    className += 'fc-corner-left ';
                }
                if (seg.isEnd) {
                    className += 'fc-corner-right ';
                }

                cols[0] = dayDiff(seg.start, t.visStart);
                cols[1] = dayDiff(fc.addDays(cloneDate(seg.end),-1), t.visStart);

                //cols[0] = Math.max(cols[0],0);
                cols[1] = Math.max(cols[1],0);

                left = (seg.isStart ? colContentLeft(cols[0]) : minLeft);
                right = (seg.isEnd ? colContentRight(cols[1]) : maxLeft);
            }

            html += buildSegElement(seg, left, className, rtl)[0].outerHTML;

            seg.left = left;
            seg.outerWidth = right - left;
            cols.sort(cmp);
            seg.startCol = cols[0];
            seg.endCol = cols[1] + 1;
        }

        $container.html(html);
    }

    var buildSegElement = function (seg, left, className, rtl) {        
    	// set attributes for innGrid!
        var $element = $("<div>")
            .addClass(className +
                    seg.event.className.join(' ')
            )
            .attr('data-booking-id', seg.event.booking_id)
            .attr('data-room-id', seg.event.room_id)
            .css({
                position: "absolute",
                left: left + "px"
            });

        if (seg.event.warning_message)
        {
	        $element.popover({ 
	        	trigger: "hover",
	        	content: seg.event.warning_message,
	        	placement: 'bottom'
	        });
        }

        $element.append(buildSegEventElement(seg));

        if (seg.isEnd && (seg.event.editable || seg.event.editable === undefined && opt('editable')) && !opt('disableResizing'))
        {
            $element.append(buildSegResizableElement(seg, rtl));
        }

        return $element;
    };

    function buildSegEventElement (seg) {
        var $element = $("<a>");
        
        $element.css({
	                margin: "0.06rem"
	            });
        
		if (seg.event.color && seg.event.color != "transparent") {
        	if(innGrid.isDarkTheme){
        		$element.css({
	                backgroundColor: "#"+seg.event.color,
					borderColor: "#"+seg.event.color
	            });
        	} else {
        		$element.css({
	                backgroundColor: "#"+seg.event.color,
					// borderColor: "#"+seg.event.color
	            });
        	}
        }

        if (seg.event.url) {
            $element.attr("href", htmlEscape(seg.event.url));
        }

        if (!seg.event.allDay && seg.isStart) {
            $element.append(
                $("<span>")
                    .addClass("fc-event-time")
                    .text(formatDates(seg.event.start, seg.event.end, opt('timeFormat'))));
        }

        $element.append(
            $("<span>")
                .addClass("fc-event-title")
                .text(seg.event.title));

        return $element;
    };

    function buildSegResizableElement (seg, rtl)
    {
        var $element = $("<div>")
            .addClass("ui-resizable-handle ui-resizable-" + (rtl ? "w" : "e"));

        return $element;
    };

    function showPopup(mouseX, mouseY, $element)
    {
        var $popup = getPopup();

        $popup.html($element[0].outerHTML);
        $popup.children().show();
        $popup.show();

        $popup.css({
            left: calcPopupLeft($popup, mouseX),
            top: calcPopupTop($popup, mouseY)
        });
    }

    function hidePopup()
    {
        var $popup = $(".popup", getPopupContainer());

        $popup.html("");
        $popup.hide();
    }

    function getPopup()
    {
        var $popup = $(".popup", getPopupContainer());

        if ($popup.length == 0)
        {
            var $popup = $("<div>");
            $popup.addClass("popup");
            $popup.css({
                position: "relative"
            });

            getPopupContainer().append($popup);
        }

        return $popup;
    }

    function calcPopupLeft($popup, mouseX)
    {
        var pageLeft = mouseX + 10;

        if (mouseX > $(document).width() - $popup.width())
        {
            pageLeft = $(document).width() - $popup.width() - 15;
        }

        return pageLeft - getPopupContainer().offset().left;
    }

    function calcPopupTop($popup, mouseY)
    {
        var pageTop = mouseY + 20;

        if (mouseY > $(document).height() - $popup.height())
        {
            pageTop = $(document).height() - $popup.width() - 25;
        }

        return pageTop - getPopupContainer().offset().top;
    }

    function getPopupContainer()
    {
        return getDaySegmentContainer();
    }
}

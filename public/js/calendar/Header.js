
function Header(calendar, options) {
	var t = this;
	
	// exports
	t.render = render;
	t.destroy = destroy;
	t.updateTitle = updateTitle;
	t.activateButton = activateButton;
	t.deactivateButton = deactivateButton;
	t.disableButton = disableButton;
	t.enableButton = enableButton;
	
	
	// locals
	var element = $([]);
	var tm;

	function render() {
		tm = options.theme ? 'ui' : 'fc';
		var sections = options.header;
		if (sections) {
			element = $("<div/>",
						{
							class: "fc-header pad-btn form-inline col-md-12",
							// style:"margin-bottom: 1.0rem;",
						})
						.append(
							$("<div/>", {
								class: "form-group",
								// style: 'margin: 10px;'
							})
							.append(
								$("<div/>",
								{
									class: 'btn-group'
								})
								.append(
									$("<button/>", {
										href: '#',
										class: 'btn btn-success  create-new-booking',
										text: l('create_new_booking')
									}).on('click', function() {
										if (typeof $(this).openBookingModal !== 'undefined' && $.isFunction($(this).openBookingModal)) {
					                        $(this).openBookingModal();
					                    }
										mixpanel.track("create new booking button clicked");
									})
								)
							)
						)
						.append(
							$("<div/>", {
								class: "form-group hidden-xs"
							})
							.append(renderSection(sections.left))
						)
						.append(
							$("<form/>",
								{
									id: 'booking_search',
									method: 'GET',
									class: "form-group hidden-xs",
									action: getBaseURL()+"booking/show_bookings/",
                                    style: 'margin-left:10px'
								}
							)
							.append(
								$("<div/>", {
									class: "form-group"
								})
								.append("<input class='form-control' name='search_query' type='text' value=''>")
							)
							.append(
								$("<div/>", {
									class: "form-group"
								})
								.append("<input class='btn btn-light' type='submit' style='margin-left:10px;' value='"+l('search_bookings')+"'>")
							)
						)
						.append(
							$("<div/>", {
								class: "form-group"
							})
							.append(renderSection(sections.right))
						)
                        
                        .append(
									$("<button/>", {
										href: '#',
										class: 'btn btn-light filter-booking',
                                        style: 'margin-left:10px',
										text: l('More')
									}).on('click', function() {
										$('#filter-booking').slideToggle(); 
									})
                        )
						
                        /*.append(
                            $("<button/>", {
                                class: 'btn btn-light',
                                text: l('search_groups'),
                                style: 'padding: 6px 8px;margin-left: 4px;'
                            }).on('click', function(){
                                $(this).openSearchGroupModel();
                            })
                            
                        )*/
						
			return element;
		}
	}
	
	function destroy() {
		element.remove();
	}

	function renderSection(buttonStr) {
		if (buttonStr) {
			var tr = $("<div class='form-group' style='margin:10px;'>");
			$.each(buttonStr.split(' '), function(i) {
				if (i > 0) {
					tr.append("<td><span class='fc-header-space'/></td>");
				}
				var prevButton;

				// put in Relative & Month buttons
				$.each(this.split(','), function(j, buttonName) {
					if (buttonName == 'title') {
						tr.append("<div class='fc-header-title'>&nbsp;</div>");
						if (prevButton) {
							prevButton.addClass(tm + '-corner-right');
						}
						prevButton = null;
					}else{
						var buttonClick;
						if (calendar[buttonName]) {
							buttonClick = calendar[buttonName]; // calendar method
						}
						else if (fcViews[buttonName]) {
							buttonClick = function() {
								button.removeClass(tm + '-state-hover'); // forget why
								calendar.changeView(buttonName);
							};
						}
						if (buttonClick) {
							if (prevButton) {
								prevButton.addClass(tm + '-no-right');
							}
							var button;
							var icon = options.theme ? smartProperty(options.buttonIcons, buttonName) : null;
							var text = smartProperty(options.buttonText, buttonName);
							if (icon) {
								button = $("<div class='m-1 fc-button-" + buttonName + " ui-state-default btn btn-light'>" +
									"<a><span class='ui-icon ui-icon-" + icon + "'/></a></div>");
							}
							else if (text) {
							    if(text=='Today'){
                                    button = $("<div class='m-1 fc-button-" + buttonName + " " + tm + "-state-default btn btn-light'>" +
                                        "<a><span>" + l(buttonName) + "</span></a></div>");
                                }
                                else{
                                    button = $("<div class='m-1 fc-button-" + buttonName + " " + tm + "-state-default btn btn-light'>" +
                                        "<a><span>" + text + "</span></a></div>");
                                }
							}
							if (button) {
								button
									.click(function() {
										if (!button.hasClass(tm + '-state-disabled')) {
											buttonClick();
										}
									})
									.mousedown(function() {
										button
											.not('.' + tm + '-state-active')
											.not('.' + tm + '-state-disabled')
											.addClass(tm + '-state-down');
									})
									.mouseup(function() {
										button.removeClass(tm + '-state-down');
									})
									.hover(
										function() {
											button
												.not('.' + tm + '-state-active')
												.not('.' + tm + '-state-disabled')
												.addClass(tm + '-state-hover');
										},
										function() {
											button
												.removeClass(tm + '-state-hover')
												.removeClass(tm + '-state-down');
										}
									)
									.appendTo($("<td/>").appendTo(tr));
								if (prevButton) {
									prevButton.addClass(tm + '-no-right');
								}else{
									button.addClass(tm + '-corner-left');
								}
								prevButton = button;
							}
						}
					}
				});
				if (prevButton) {
					prevButton.addClass(tm + '-corner-right');
				}
			});
			return tr;
		}

		
	}

	
	function updateTitle(content) {
        element.find('div.fc-header-title')
            .empty()
            .append(content);
	}
	
	
	function activateButton(buttonName) {
		element.find('div.fc-button-' + buttonName)
			.addClass(tm + '-state-active');
	}
	
	
	function deactivateButton(buttonName) {
		element.find('div.fc-button-' + buttonName)
			.removeClass(tm + '-state-active');
	}
	
	
	function disableButton(buttonName) {
		element.find('div.fc-button-' + buttonName)
			.addClass(tm + '-state-disabled');
	}
	
	
	function enableButton(buttonName) {
		element.find('div.fc-button-' + buttonName)
			.removeClass(tm + '-state-disabled');
	}


}

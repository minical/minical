Array.prototype.addRepeatingValuesAsColumns = function(value, count)
{
    for(var i=0; i < Math.round(count); i++) this.push(value);
}
var color_class = '';

innGrid.allDaysBetween = function(start, end) {
    var d = new Date();
    var today_date = cur_date = d.getFullYear()+'-'+("0" + (d.getMonth() + 1)).slice(-2)+'-'+("0" + (d.getDate())).slice(-2);
    var days = [];
    var weekday = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    
    while (start < end)
    {
        var start_date = start.getFullYear()+'-'+("0" + (start.getMonth() + 1)).slice(-2)+'-'+("0" + (start.getDate())).slice(-2);
        if(today_date == start_date)
        {
            color_class = 'current_date_class'; 
        }
        else
        { 
            color_class = ''; 
        }
        days.push({date: start_date, day: weekday[start.getDay()], current_date:color_class});
        start.setDate(start.getDate() + 1);
    }
    
    return days;
};

innGrid.countDaysBetween = function(start, end) {
    return (new Date(end) - new Date(start)) / (1000*60*60*24);
};

innGrid.createInventoryRow = function(tableStartDate, tableEndDate, rowInventory) {
    var row = [];
    var inventoryAvail = [];
    var inventoryMaxAvail = [];
    var inventorySold = [];
    var inventoryStatus = [];
    
    var threshold = [];
    
    row['inventory_name'] = rowInventory.name + " (" + rowInventory.acronym+")"; 
     
    for(var i = 0; i<= rowInventory.room_count; i++ ){
        threshold.push(i);
    }
    row['threshold'] = threshold;

    // If the InnGrid API did not provide any availability data, it is not
    // available at all during the timeframe provided
    if(!('availability' in rowInventory))
    {
        inventoryAvail.addRepeatingValuesAsColumns(0, innGrid.countDaysBetween(tableStartDate, tableEndDate));
        inventoryMaxAvail.addRepeatingValuesAsColumns(0, innGrid.countDaysBetween(tableStartDate, tableEndDate));
        row['inventory_avail'] = inventoryAvail;
        row['inventory_max_avail'] = inventoryMaxAvail;
        row['inventory_sold'] = inventorySold;
        row['inventory_closeout_status'] = inventoryStatus;
        return row;
    }
    
    for(var i=0; i < rowInventory.availability.length; i++) {
        // add the availabilities provided in this chunk of time
        var avail = rowInventory.availability[i];
        var startAvail = avail.date_start;
        var endAvail = avail.date_end;
        startAvail = startAvail.split('-');
        startAvail =  startAvail[1]+'/'+startAvail[2]+'/'+startAvail[0];
        endAvail = endAvail.split('-');
        endAvail =  endAvail[1]+'/'+endAvail[2]+'/'+endAvail[0];
        var innerDiff = innGrid.countDaysBetween(startAvail, endAvail);
        
        inventoryAvail.addRepeatingValuesAsColumns(avail.availability, innerDiff);
        inventoryMaxAvail.addRepeatingValuesAsColumns(avail.max_availability, innerDiff);
        inventorySold.addRepeatingValuesAsColumns(avail.inventory_sold, innerDiff);
        inventoryStatus.addRepeatingValuesAsColumns(avail.closeout_status, innerDiff);

        avail1 = avail;
        rowInventory1 = rowInventory;
        
        // // add the availabilities "0" until the next chunk of time is provided
        // if(i < rowInventory.availability.length - 1) {
        //     var next_avail = rowInventory.availability[i + 1];
        //     console.log(next_avail)
        //     // convert date format from YYYY-mm-dd to mm/dd/YYYY
        //     if (next_avail)
        //     {
        //         nextAvail = next_avail.split('-');
        //         nextAvail =  nextAvail[1]+'/'+nextAvail[2]+'/'+nextAvail[0];
        //         var outerDiff = innGrid.countDaysBetween(startAvail, nextAvail);
        //         row.addRepeatingValuesAsColumns(0, outerDiff);
        //     }
            

        // }
    } 
    row['inventory_avail'] = inventoryAvail;
    row['inventory_max_avail'] = inventoryMaxAvail;
    row['inventory_sold'] = inventorySold;
    row['inventory_closeout_status'] = inventoryStatus;

    return row;
};

innGrid.createTable = function(dateStart, dateEnd) {
    var channel = $('.channels li.active')[0].dataset.id;
    var channel_key = $('.channels li.active')[0].dataset.key;
    var dateStart1 = '';
    // copy dates so we don't alter them
    dateStart = new Date(dateStart);
    
    dateStart1 = new Date(dateStart);
    dateEnd = new Date(dateEnd);
    var start_date = dateStart.getFullYear()+'-'+("0" + (dateStart.getMonth() + 1)).slice(-2)+'-'+("0" + (dateStart.getDate())).slice(-2);
    var end_date = dateEnd.getFullYear()+'-'+("0" + (dateEnd.getMonth() + 1)).slice(-2)+'-'+("0" + (dateEnd.getDate())).slice(-2);
    var query = {
        channel: channel,
        channel_key: channel_key,
        start_date: start_date,
        end_date: end_date,
        filter_can_be_sold_online: channel == -1 ? false : true
    };
    is_ota = true;
    if(channel == -1) // online booking engine
    { 
        is_ota = false;
    }
    
    //var page = 'get_room_type_availability_AJAX';

    //var page = $('.net-availability .check')[0].dataset.checked == 'true' ? 'get_room_type_availability_AJAX' : 'get_room_type_max_availability_AJAX'

    $.ajax({
            type: "GET",
            url: getBaseURL()+'room/get_room_type_availability_AJAX',
            data: query,
            beforeSend: function() {
                $("#loading_img").show();
            },
            success: function(res) {
                res = JSON.parse(res);
               $("#loading_img").hide();

                var otaThresholdVal = 0;
                var rows = [];
                var inventoryInfo = [];
                var inventoryAvail = [];
                var totalAvail = {'0':0,'1':0,'2':0,'3':0,'4':0,'5':0,'6':0};
                var totalSold = {'0':0,'1':0,'2':0,'3':0,'4':0,'5':0,'6':0};
                var totalMaxAvailable = {'0':0,'1':0,'2':0,'3':0,'4':0,'5':0,'6':0};
                for (var room_type_id in res)
                { 
                    if(res[room_type_id].ota_close_out_threshold != null)
                        otaThresholdVal = res[room_type_id].ota_close_out_threshold;

                    if(channel == -1){
                        is_ota = false;
                    }
                    else if(res[room_type_id].is_threshold_enabled){
                        is_ota = true;
                    } else {
                        is_ota = false;
                    }
                     
                    inventoryInfo = innGrid.createInventoryRow(dateStart, dateEnd, res[room_type_id]);

                     for(var i in inventoryInfo.inventory_avail){
                        totalAvail[i] = parseInt(totalAvail[i]) + parseInt(inventoryInfo.inventory_avail[i]);
                    }

                    for(var i in inventoryInfo.inventory_sold){
                        totalSold[i] = parseInt(totalSold[i]) + parseInt(inventoryInfo.inventory_sold[i]);
                    } 

                    for(var i in inventoryInfo.inventory_max_avail){
                        totalMaxAvailable[i] = parseInt(totalMaxAvailable[i]) + parseInt(inventoryInfo.inventory_max_avail[i]);
                    }
                    
                    rows.push({
                        id: room_type_id,
                        inventory: [{ota_threshold_val: otaThresholdVal, inventory_name: inventoryInfo.inventory_name, inventory_threshold: inventoryInfo.threshold, inventory_avail: inventoryInfo.inventory_avail, inventory_max_avail:inventoryInfo.inventory_max_avail, inventory_sold:inventoryInfo.inventory_sold, inventory_closeout_status:inventoryInfo.inventory_closeout_status, colortest_class: color_class, total_availibility: totalAvail, total_sold: totalSold, total_max_available: totalMaxAvailable}],
                        is_ota: is_ota,
                         total_avail: totalAvail,
                        total_sold: totalSold,
                        total_max_available: totalMaxAvailable
                    });
                }
                
                var dat = new Date();
                var color_cla = '';
                var cur_date = dat.getFullYear()+'-'+("0" + (dat.getMonth() + 1)).slice(-2)+'-'+("0" + (dat.getDate())).slice(-2);
                for(var i in rows){
                    if(rows.length >= i){
                        var len = rows[i]['inventory'][0]['inventory_avail'].length;
                        for(var j= 0; j < len; j++){
                            dateStart1 = dateStart1.getFullYear()+'-'+("0" + (dateStart1.getMonth() + 1)).slice(-2)+'-'+("0" + (dateStart1.getDate())).slice(-2);
                            rows[i]['inventory'][0]['inventory_avail'][j] = {
                                "inventory_avail": rows[i]['inventory'][0]['inventory_avail'][j],
                                "inventory_max_avail": rows[i]['inventory'][0]['inventory_max_avail'][j],
                                "inventory_sold": rows[i]['inventory'][0]['inventory_sold'][j],
                                "inventory_closeout_status": (rows[i]['inventory'][0]['inventory_closeout_status'][j] == '1') ? ({status: 'Open', open_selected: 'selected'}) : ({status: 'Close', close_selected: 'selected'}),
                                "colortest_class": (cur_date == dateStart1) ? ({color_cla: 'current_date_class'}) : ({color_cla: ''}),
                                "total_availibility": rows[i]['total_avail'][j],
                                "total_sold": rows[i]['total_sold'][j],
                                "total_max_available": rows[i]['total_max_available'][j]

                            };
                            dateStart1 = new Date(dateStart1 + " 00:00:00");
                            dateStart1.setDate(dateStart1.getDate() + 1);
                        }
                        if(i >= 0)
                        {
                            dateStart1 = new Date(dateStart);
                        }
                    }
                } 
               
                calendarTemp = $('#calendar-temp').html();

                var rendered = Mustache.render(calendarTemp, {
                    dates: innGrid.allDaysBetween(dateStart, dateEnd),
                    rows: rows,
                    is_ota: is_ota
                });
                
                $('.calendar')
                        .attr('data-channel', channel)
                        .html(rendered);
          
                $('.calendar').find('tr').each(function(){
                    var thresholdDropdown = $(this).find('select[name="ota-treshold"]');
                    var thresholdDropdownVal = thresholdDropdown.attr('value');
                    if(thresholdDropdownVal != null ){
                        thresholdDropdown.find('option[value="'+thresholdDropdownVal+'"]').attr('selected','selected');
                    }
                });
                
                // select threshold dropdown on change
                $('select[name="ota-treshold"]').on("change", function(){
                    var roomTypeId = $(this).parents('tr').attr('data-id');
                    var channel_id = $(this).parents('.calendar').attr('data-channel');
                    var thresholdVal = $(this).val();
                    if(thresholdVal == 0){
                       alert("Warning setting this to 0 may result in overbookings.");
                    }
                    $.ajax({
                       type: 'POST',
                       url: getBaseURL()+'room/set_room_type_ota_threshold',
                       data: {
                           room_type_id:  roomTypeId,
                           threshold_val: thresholdVal
                           //channel_id:    channelId
                        },
                        dataType: 'json',
                        success: function(response){
                            console.log(response);
                            innGrid.createTable(query.start_date, query.end_date);

                            innGrid.updateAvailabilities('', '', roomTypeId, channel_id);
                        }
                    });
                });
                innGrid.updateModifyButton();

            }
    });

    // $.get(getBaseURL()+'room/get_room_type_availability_AJAX', query, function(res) {
    //     res = JSON.parse(res);
        
        
    // });
};

innGrid.openEditAvailabilities = function() {



    $('#edit_availabilities_dialog').dialog('close');
    $('#edit_availabilities_dialog').dialog('open');
    
    var channel = $('.channels li.active')[0];
    $("#edit_availabilities_dialog").dialog('option', 'title', channel.textContent);

    var roomTypeIds = [];
    var roomTypeNames = [];
    
    $('.calendar tbody tr[data-selected="true"]').each(function(el) {
        roomTypeIds.push(this.dataset.id);
        roomTypeNames.push($(this).find('td')[1].innerHTML);
    });

    var baseURL = getBaseURL();
    var start_date = dateStart.getFullYear()+'-'+("0" + (dateStart.getMonth() + 1)).slice(-2)+'-'+("0" + (dateStart.getDate())).slice(-2);
    var query = $.param({
        dateStart: start_date,
        roomTypeIds: roomTypeIds,
        roomTypeNames: roomTypeNames,
        channelId: channel.dataset.id
    });
    var url = baseURL + 'room/modify_availabilities?' + query;
    
    // open the booking dialog once the content is loaded
    $('#edit-availability-dialog-iframe').attr("src", url);
};

innGrid.updateModifyButton = function() {
    if($('.calendar tbody tr[data-selected="true"').length) {
        $('.modify-availabilities').removeAttr('disabled');
    }
    else {
        $('.modify-availabilities').attr('disabled', 'true');
    }
};

var dateStart;
var dateEnd;

(function() {

    $("[name='start_date'], [name='end_date']").datepicker({ dateFormat:"yy-mm-dd"});
    dateStart = new Date();

    // Set today's date in start date
    filterStartDate = dateStart.getFullYear()+'-'+("0" + (dateStart.getMonth() + 1)).slice(-2)+'-'+("0" + (dateStart.getDate())).slice(-2);
    $("#dateStart").val(filterStartDate);

    $("#dateStart").datepicker({ dateFormat:"yy-mm-dd"});
    // set startdate as a Sunday of a current week
    // dateStart = new Date(new Date().getTime() - new Date().getDay()*24*60*60*1000);

    dateEnd = new Date(dateStart);
    dateEnd.setDate(dateStart.getDate() + 7);
    innGrid.createTable(dateStart, dateEnd);
    
    $('.change-dates').click(function() {
        var dateDiff = parseInt($(this)[0].dataset.dateDiff);
        dateStart.setDate(dateStart.getDate() + dateDiff);
        dateEnd.setDate(dateEnd.getDate() + dateDiff);
        innGrid.createTable(dateStart, dateEnd);
        var filterDate = dateStart.getFullYear()+'-'+("0" + (dateStart.getMonth() + 1)).slice(-2)+'-'+("0" + (dateStart.getDate())).slice(-2);            
        $('#dateStart').val(filterDate);
    });

    $('.net-availability .check').click(function() {
        this.dataset.checked = this.dataset.checked == 'false';
        $('.availability-type').html($('.availability-type').html() == 'Max' ? 'Net' : 'Max');
        innGrid.createTable(dateStart, dateEnd);
    });

    $('.channels a').click(function() {
        $('.channels li').removeClass('active');
        $(this).parent('li').addClass('active');
        innGrid.createTable(dateStart, dateEnd);
    });

    $('body').on('click', '.calendar tbody tr', function(event) {
        if ($(event.target).is("select")) { 
            return false;
        }
        this.dataset.selected = this.dataset.selected == 'false';
        $(this).find('input').prop('checked', this.dataset.selected == 'true');
        innGrid.updateModifyButton();
    });
    
    $('body').on('click', '.inventory', function() {
        
        var channel_id = $('.channels li.active')[0].dataset.id;
        if(channel_id == -1){
            return;
        }
        
        if($('.inv_hide:visible').length){
            return;
        }
        $('.inv_hide').hide();
        $(this).find('.max_avail_val').show();
        var key = $(this).find('.max_avail_val').attr('key');
        $(this).find('.inventory_input_'+key).eq(0).show().focus();
        $(this).find('.inventory_btn_'+key).eq(0).show();
        $(this).find('.max_avail_val_'+key).eq(0).hide();
    });
    
    $('body').on('focusout', 'input[name="inventory_max_avail"]', function(e) {
        if($('.inv_ok:hover').length == 0) {
            var key = $(this).attr('key');
            setTimeout(function(){
                $('.inventory_input_'+key).hide();
                $('.inventory_btn_'+key).hide();
                $('.max_avail_val_'+key).show();
            }, 250);
        }
    });
    
    //    for change status of inventory 
     $('body').on('click', '.inven_status_div', function(){
        
        var channel_id = $('.channels li.active')[0].dataset.id;
        if(channel_id == -1){
            return;
        }
        
        if($('.inv_hide:visible').length){
            return;
        }
        $(this).find('.inv_hide').hide();
        $(this).find('.inven_status').hide();
        var key = $(this).find('.inven_status').attr('key');
        $(this).find('.inv_status_select_'+key).eq(0).show().focus();
    });
    
    $('body').on('focusout', '.inv_status_select', function(e) {
       // if($('.inv_ok:hover').length == 0) {
            var key = $(this).attr('key');
            setTimeout(function(){
                $('.inv_status_select_'+key).hide();
                $('.inven_status_'+key).show();
            }, 250);
       // }
    });
    
  
    
    $('body').on('click', '.inv_ok', function() {
       var key = $(this).attr('key');
       var channel_id = $('.channels li.active')[0].dataset.id;
       var input_val = $(this).closest('td').find('.inventory_input_'+key).val();
       
       var index = $(this).closest('td').index();
       var date = null;
       if(!is_ota)
       {
           date = $('.date').eq(index-2).children('.date-value').html();
            console.log('if', date);
       }
       else
       {
           date = $('.date').eq(index-3).children('.date-value').html();
            console.log('else', date);
            console.log('index', index);
            console.log('is_ota', is_ota);
       }
       var obj = $(this);
       $.ajax({
                type: 'POST',
                url: getBaseURL()+'room/modify_availabilities_POST',
                data: {
                    room_type_ids: key,
                    channel_id: channel_id,
                    date_start: date,
                    date_end: date,
                    availability: input_val,
                    monday: true,
                    tuesday: true,
                    wednesday: true,
                    thursday: true,
                    friday: true,
                    saturday: true,
                    sunday: true
                },
                dataType: 'json',
                success: function(response){
                    if (response.status === 'error')
                    {
                        alert(response.message.replace(/<p>/g,'').replace(/<\/p>/g,''));
                    }
                    $('.inventory_input_'+key).hide();
                    $('.inventory_btn_'+key).hide();
                    innGrid.createTable(dateStart, dateEnd);
                    //obj.closest('td').find('.max_avail_val_'+key).html(input_val);
                    $('.max_avail_val_'+key).show();
                    // if(channel_id == 14)
                    // {
                        innGrid.updateAvailabilities(date, date, key, channel_id);
                    // }
                    // else
                    // {
                    //     innGrid.updateAvailabilities(date, date, '', '');
                    // }
                    
                }
            });
    });
    //    for change status of inventory 
     $('body').on('change', '.inv_status_select', function() {
       var key = $(this).attr('key');
       var channel_id = $('.channels li.active')[0].dataset.id;
       var input_val1 = $(this).closest('td').find('.inventory_input_'+key).val();
      
       var input_val = $(this).closest('td').find('.inv_status_select_'+key+' :selected').val();
       var index = $(this).closest('td').index();
       var date = null;
       if(channel_id == 1)
       {
           date = $('.date').eq(index-2).children('.date-value').html();
       }
       else
       {
           date = $('.date').eq(index-3).children('.date-value').html();
       }
       var obj = $(this);
       $.ajax({
                type: 'POST',
                url: getBaseURL()+'room/modify_availabilities_POST',
                data: {
                    update_status_only: true,
                    room_type_ids: key,
                    channel_id: channel_id,
                    date_start: date,
                    date_end: date,
                    status: input_val,
                    monday: true,
                    tuesday: true,
                    wednesday: true,
                    thursday: true,
                    friday: true,
                    saturday: true,
                    sunday: true
                },
                dataType: 'json',
                success: function(response){
                    if (response.status === 'error')
                    {
                        alert(response.message.replace(/<p>/g,'').replace(/<\/p>/g,''));
                    }
                    $('.inv_status_select_'+key).hide();
                    innGrid.createTable(dateStart, dateEnd);
                    //obj.closest('td').find('.max_avail_val_'+key).html(input_val);
                    $('.inven_status_'+key).show();
                    if(channel_id == 14)
                    {
                        innGrid.updateAvailabilities(date, date, key, channel_id);
                    }
                    else
                    {
                        innGrid.updateAvailabilities(date, date, '', '');
                    }
                }
            });
    });
    $('#edit_availabilities_dialog').html($('<iframe />', {
        'id': 'edit-availability-dialog-iframe',
        'scrolling': 'no',
        'frameborder': 0,
        'width': 450,
        'height': 450
    }));
    
    $("#edit_availabilities_dialog").dialog({
        autoOpen: false,
        width: 'auto',
        height: 'auto',
        autoResize: true,
        resizable: false,
        close : function(){
            $('#edit-availability-dialog-iframe').removeAttr('src');
            innGrid.createTable(dateStart, dateEnd);
        }
    });

    $('.modify-availabilities').click(innGrid.openEditAvailabilities);



    // Update Channel Manager Availabilities Manually

    $(".update-availabilities-button").on('click', function() {
        var modalDiv = $('#modal');
        modalDiv.modal({
                        keyboard: true
                    });
    });

    $("#update").on('click', function() {
        var start_date = $("[name='start_date']").val();
        var end_date = $("[name='end_date']").val();
        innGrid.updateAvailabilities(start_date, end_date, '', '');
        
    });
    
    $("#dateStart").on('change', function() {
        var date_start = $("#dateStart").val();
        var date_end = '';
        
        date_end = new Date(date_start);
        date_end.setDate(date_end.getDate() + 7);
        innGrid.createTable(date_start, date_end);
        // Setting Global Variables values
        dateStart = new Date(date_start);
        dateEnd = new Date(date_end);        
    });

})();

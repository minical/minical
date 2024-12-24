$(function () {
    $('#printReportButton').click(function () {
        window.print();
    });

    $(".sellingDate").datepicker({dateFormat: 'yy-mm-dd'});
    $(".sellingDate").on('change', function () {
        var date = $(this).val();
        window.location.href = base_url + "/reports/ledger/show_daily_report/" + date;
    });
    $('.show_payment_report').click(function () {
        $('.monthselectpicker').css('display', 'none');
        var dateRange = $('input[name=date_range]').val();
        dateRange = dateRange.toString();
        dateRange = dateRange.split(' - ');
        var startDate = dateRange[0].split('/');
        var endDate = dateRange[1].split('/');
        startDate = startDate[2]+'-'+startDate[0]+'-'+startDate[1];
        endDate = endDate[2]+'-'+endDate[0]+'-'+endDate[1];
        
        window.location.href = base_url + "/reports/ledger/show_monthly_payment_report/" + startDate+'--'+endDate;
    });
     $('.show_charge_report').click(function () {
        $('.monthselectpicker').css('display', 'none');
        var dateRange = $('input[name=date_range]').val();
        dateRange = dateRange.toString();
        dateRange = dateRange.split(' - ');
        var startDate = dateRange[0].split('/');
        var endDate = dateRange[1].split('/');
        startDate = startDate[2]+'-'+startDate[0]+'-'+startDate[1];
        endDate = endDate[2]+'-'+endDate[0]+'-'+endDate[1];
        
        window.location.href = base_url + "/reports/ledger/show_monthly_charge_report/" + startDate+'--'+endDate;
    });


     $('.currency_name').change(function(){ 

        var currency_code = $(this).val();

        if(currency_code != '') {
            window.location.assign(getBaseURL() +"reports/ledger/show_monthly_currency_payment_report/" + currency_code);
        } else {
            window.location.assign(getBaseURL() +"reports/ledger/show_monthly_payment_report");
        }
    });
});
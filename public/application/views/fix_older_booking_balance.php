<script type="text/javascript" src="<?php echo base_url();?>js/jquery-1.11.2.min.js"></script>
<script>
    var deferredFixCompany = '';
    var date = new Date();
    $(function(){
        deferredFixCompany = $.Deferred();
        
        date = new Date();
        re_declare();
        deferredFixCompany.resolve(date);
        
    });    
    function formatDate(dat) {
        var d = new Date(dat),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }
    function re_declare()
    {
        $.when(deferredFixCompany).done(function (new_date) {
            date.setDate(date.getDate() - 1);
            deferredFixCompany = $.Deferred();
            re_declare();
            
            $.ajax({
                type: "GET",
                url: location.protocol+"//"+location.host+"/booking/fix_older_booking_balance/"+formatDate(new_date),
                success: function (data) {
                    $('body').append("<div>"+data+"</div><br/>");
                    if(date > new Date('2015-01-01')) {
                        deferredFixCompany.resolve(date);
                    }
                },
                error: function () {
                    $('body').append("<div></div><br/>");
                    if(date > new Date('2015-01-01')) {
                        deferredFixCompany.resolve(date);
                    }
                }
            });
        });
    }
</script>

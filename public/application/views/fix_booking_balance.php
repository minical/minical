<script type="text/javascript" src="<?php echo base_url();?>js/jquery-1.11.2.min.js"></script>
<script>
    var companies = [];
    var deferredFixCompany = '';
    var count = 0;
    $(function(){
        companies = [];
        deferredFixCompany = $.Deferred();
        count = 0;

        $.getJSON(location.protocol+"//"+location.host+"/company/get_all_companies",
            function(c) {
                companies = c;
                if(typeof companies[count] !== "undefined") {
                    re_declare();
                    deferredFixCompany.resolve(companies[count].company_id);
                }
            }
        );
    });    
    
    function re_declare()
    {
        $.when(deferredFixCompany).done(function (company_id) {
            count++;
            deferredFixCompany = $.Deferred();
            re_declare();
            $.ajax({
                type: "GET",
                url: location.protocol+"//"+location.host+"/booking/fix_booking_balance/"+company_id,
                success: function (data) {
                    $('body').append("<div>"+data+"</div><br/>");
                    console.log(companies[count], count, companies[count].company_id);
                    if(typeof companies[count] !== "undefined") {
                        deferredFixCompany.resolve(companies[count].company_id);
                    }
                },
                error: function () {
                    $('body').append("<div></div><br/>");
                    console.log(companies[count], count, companies[count].company_id);
                    if(typeof companies[count] !== "undefined") {
                        deferredFixCompany.resolve(companies[count].company_id);
                    }
                }
            });
        });
    }
</script>

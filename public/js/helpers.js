//Global Helper Functions

//Create innGrid global
if (typeof(innGrid) === 'undefined') {
	var innGrid = {};
}

//Mixpanel integration
//var mpq=[];mpq.push(["init","38e4c7aabe404e5854716102c81bcbf5"]);(function(){var b,a,e,d,c;b=document.createElement("script");b.type="text/javascript";b.async=true;b.src=(document.location.protocol==="https:"?"https:":"http:")+"//api.mixpanel.com/site_media/js/api/mixpanel.js";a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(b,a);e=function(f){return function(){mpq.push([f].concat(Array.prototype.slice.call(arguments,0)))}};d=["init","track","track_links","track_forms","register","register_once","identify","name_tag","set_config"];for(c=0;c<d.length;c++){mpq[d[c]]=e(d[c])}})();

$(function () {
	//mpq.name_tag($('#user-email').text());
    
    
    $('#resend-verification-link').on('click', function(){
        $.ajax({
            url: getBaseURL()+'auth/resend_verification_email_AJAX',
            type: 'post',
            success: function(data){
                if(data != ''){
                    alert(l('We have sent the verification link to your email.'));
                }
            }
        });
    });
});

//http://www.mredkj.com/javascript/nfbasic.html
innGrid.addCommas = function(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

//Function getBaseURL
//returns baseURL
var getBaseURL = function () {
    var url = $('#project_url').val();
    url = url ? url : 'app.minical.io';
    if (url.substring(url.length - 1) !== "/") {
        url = url + '/';
    }
	return url;
}

// php number_format equivalent
//var number_format = function (number, decimals, decPoint, thousandsSep) 
//{
//    number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
//    var n = !isFinite(+number) ? 0 : +number
//    var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
//    var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
//    var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
//    var s = ''
//    var toFixedFix = function (n, prec) {
//        var k = Math.pow(10, prec)
//        return '' + (Math.round(n * k) / k)
//                .toFixed(prec)
//    }
//    // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
//    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.')
//    if (s[0].length > 3) {
//        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
//    }
//    if ((s[1] || '').length < prec) {
//        s[1] = s[1] || ''
//        s[1] += new Array(prec - s[1].length + 1).join('0')
//    }
//    return s.join(dec)
//}
var number_format = function (num, scale, decPoint, thousandsSep) {
    
    decPoint = decPoint ? decPoint : ".";
    thousandsSep = thousandsSep ? thousandsSep : "";
    
    if(!("" + num).includes("e")) {
        return (+(Math.round(num + "e+" + scale)  + "e-" + scale)).toFixed(scale);
    } else {
        var arr = ("" + num).split("e");
        var sig = ""
        if(+arr[1] + scale > 0) {
            sig = "+";
        }
        var response = (+(Math.round(+arr[0] + "e" + sig + (+arr[1] + scale)) + "e-" + scale)).toFixed(scale);
        return (response == "-0.00") ? "0.00" : response;
    }
}

innGrid._getLocalFormattedDate = function (date) {

    var companyDateFormat = $('#companyDateFormat').val() ? $('#companyDateFormat').val() : 'YY-MM-DD';
    innGrid.currentDateFormat = innGrid.currentDateFormat || companyDateFormat.toLowerCase();
    if (innGrid.currentDateFormat == 'yy-mm-dd') {
        return moment(date, 'YYYY-MM-DD').format('YYYY-MM-DD');
    }

    if (date.indexOf("-") === 4) {
        // date is YYYY-MM-DD
        var dateParts = date.split(/[ ]/)[0].split(/[-]/);
        if (innGrid.currentDateFormat == 'dd-mm-yy')
            return moment(date, "YYYY-MM-DD").format('DD-MM-YYYY');
        else if (innGrid.currentDateFormat == 'mm-dd-yy')
            return moment(date, "YYYY-MM-DD").format('MM-DD-YYYY');
    } else if (innGrid.currentDateFormat == 'dd-mm-yy') {
        // date is in local format already
        return moment(date, "DD-MM-YYYY").format('DD-MM-YYYY');
    } else if (innGrid.currentDateFormat == 'mm-dd-yy') {
        // date is in local format already
        return moment(date, "MM-DD-YYYY").format('MM-DD-YYYY');
    }
    return date;
};
innGrid._getBaseFormattedDate = function (date) {
    var companyDateFormat = $('#companyDateFormat').val() ? $('#companyDateFormat').val() : 'YY-MM-DD';
    innGrid.currentDateFormat = innGrid.currentDateFormat || companyDateFormat.toLowerCase();
    if (innGrid.currentDateFormat == 'yy-mm-dd') {
        return moment(date, 'YYYY-MM-DD').format('YYYY-MM-DD');
    }
    if (date !== undefined) {
        if (date.indexOf("-") === 4) {
            // date is YYYY-MM-DD already
            return moment(date, 'YYYY-MM-DD').format('YYYY-MM-DD');
        } else if (innGrid.currentDateFormat == 'dd-mm-yy') {
            return moment(date, 'DD-MM-YYYY').format('YYYY-MM-DD');
        } else if (innGrid.currentDateFormat == 'mm-dd-yy') {
            return moment(date, 'MM-DD-YYYY').format('YYYY-MM-DD');
        }
        return date;
    }
};
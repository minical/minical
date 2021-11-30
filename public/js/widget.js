window.onload = function () {
    if(!window.isRoomsyWidgetLoaded) {
        window.isRoomsyWidgetLoaded = true;
        var elements = [];
        var ele = document.getElementById("minical-booking-widget");
        if (ele) {
            elements.push(ele);
        }
        
        ele = document.getElementsByClassName("minical-booking-widget");
        if (ele) {
            for (var i = 0; i < ele.length; i++) {
                elements.push(ele[i]);
            }
        }

        if(elements.length > 0) {
            for(key in elements){
                if(elements.hasOwnProperty(key)) {

                    var div = elements[key];
                    // var id = div.getAttribute("name");
                    var id = window.miniCal.companyId ? window.miniCal.companyId : div.getAttribute("name");

                    // get today
                    var currentDate = new Date(new Date().getTime());
                    var dd = currentDate.getDate().toString();
                    var mm = (currentDate.getMonth() + 1).toString();
                    var yyyy = currentDate.getFullYear();
                    var today = yyyy + '-' + (mm[1]?mm:"0"+mm[0]) + '-' + (dd[1]?dd:"0"+dd[0]);

                    // get tomorrow
                    var currentDate = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
                    var dd = currentDate.getDate().toString();
                    var mm = (currentDate.getMonth() + 1).toString();
                    var yyyy = currentDate.getFullYear();
                    var tomorrow = yyyy + '-' + (mm[1]?mm:"0"+mm[0]) + '-' + (dd[1]?dd:"0"+dd[0]);

                    var params = {
                        "company_id":id,
                        "label_array": [
                            'Check-in Date', 'Check-out Date', 'Adults', 'Children', 'Find Rooms'
                        ],
                    }

                    var formUrl = window.miniCal.projectUrl ? window.miniCal.projectUrl : 'https://app.minical.io/';
                    
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", formUrl+"language_translation/get_translated_phrase");
                    xhr.send(JSON.stringify(params));
                    xhr.onload = function() {
                        console.log(xhr.responseText);
                        var resp = JSON.parse(xhr.responseText);
                        document.getElementById("check_in_date").innerHTML = resp.check_in_date;
                        document.getElementById("check_out_date").innerHTML = resp.check_out_date;
                        document.getElementById("adult_count").innerHTML = resp.adults;
                        document.getElementById("children_count").innerHTML = resp.children;
                        document.getElementById("find_rooms").value = resp.find_rooms;
                     };

                    div.innerHTML = "<form action='"+formUrl+"online_reservation/select_dates_and_rooms/"+id+"' method='post' target='_blank' id='booking-form'>"+
                            "\n <ul>"+
                                "\n <li>"+
                                    "\n <label for='check-in-date' id='check_in_date'>Check-in Date</label>"+
                                    "\n <input class='check-in-date' name='check-in-date' size='13' type='text' value='"+today+"' >"+
                                "\n </li>"+
                                "\n <li>"+
                                    "\n <label for='check-out-date' id='check_out_date'>Check-out Date</label>"+
                                    "\n <input class='check-out-date' name='check-out-date' size='13' type='text' value='"+tomorrow+"'> </li>   <li>\n          <label id='adult_count' for='adult_count'>Adults</label>"+
                                    " <select name='adult_count' style='display:inline;'>"+
                                    "\n <option value='1' selected='selected'>1</option>"+
                                    "\n <option value='2'>2</option>"+
                                    "\n <option value='3'>3</option>"+
                                    "\n <option value='4'>4</option>"+
                                    "\n <option value='5'>5</option>"+
                                    "\n <option value='6'>6</option>"+
                                    "\n <option value='7'>7</option>"+
                                    "\n <option value='8'>8</option>"+
                                    "\n <option value='9'>9</option>"+
                                    "\n <option value='10'>10</option>"+
                                    "\n </select>\n         \n          "+
                                    "<label for='children_count' id='children_count'>Children</label> "+
                                    "<select name='children_count'  style='display:inline;'> "+
                                    "<option value='0' selected='selected'>0</option>"+
                                    "\n <option value='1'>1</option>"+
                                    "\n <option value='2'>2</option>"+
                                    "\n <option value='3'>3</option>"+
                                    "\n <option value='4'>4</option>"+
                                    "\n <option value='5'>5</option>"+
                                    "\n <option value='6'>6</option>"+
                                    "\n <option value='7'>7</option>"+
                                    "\n <option value='8'>8</option>"+
                                    "\n <option value='9'>9</option>"+
                                    "\n <option value='10'>10</option>"+
                                    "\n </select>           \n      </li>\n      <li> <input name='number-of-rooms' value='1' hidden='hidden' /><input type='submit' name='submit' id='find_rooms' value='Find Rooms' > </li> </ul> </form>";
                }
            }
        }


        //! moment.js
        //! version : 2.6.0
        //! authors : Tim Wood, Iskren Chernev, Moment.js contributors
        //! license : MIT
        //! momentjs.com
        (function(a){function b(){return{empty:!1,unusedTokens:[],unusedInput:[],overflow:-2,charsLeftOver:0,nullInput:!1,invalidMonth:null,invalidFormat:!1,userInvalidated:!1,iso:!1}}function c(a,b){function c(){ib.suppressDeprecationWarnings===!1&&"undefined"!=typeof console&&console.warn&&console.warn("Deprecation warning: "+a)}var d=!0;return i(function(){return d&&(c(),d=!1),b.apply(this,arguments)},b)}function d(a,b){return function(c){return l(a.call(this,c),b)}}function e(a,b){return function(c){return this.lang().ordinal(a.call(this,c),b)}}function f(){}function g(a){y(a),i(this,a)}function h(a){var b=r(a),c=b.year||0,d=b.quarter||0,e=b.month||0,f=b.week||0,g=b.day||0,h=b.hour||0,i=b.minute||0,j=b.second||0,k=b.millisecond||0;this._milliseconds=+k+1e3*j+6e4*i+36e5*h,this._days=+g+7*f,this._months=+e+3*d+12*c,this._data={},this._bubble()}function i(a,b){for(var c in b)b.hasOwnProperty(c)&&(a[c]=b[c]);return b.hasOwnProperty("toString")&&(a.toString=b.toString),b.hasOwnProperty("valueOf")&&(a.valueOf=b.valueOf),a}function j(a){var b,c={};for(b in a)a.hasOwnProperty(b)&&wb.hasOwnProperty(b)&&(c[b]=a[b]);return c}function k(a){return 0>a?Math.ceil(a):Math.floor(a)}function l(a,b,c){for(var d=""+Math.abs(a),e=a>=0;d.length<b;)d="0"+d;return(e?c?"+":"":"-")+d}function m(a,b,c,d){var e=b._milliseconds,f=b._days,g=b._months;d=null==d?!0:d,e&&a._d.setTime(+a._d+e*c),f&&db(a,"Date",cb(a,"Date")+f*c),g&&bb(a,cb(a,"Month")+g*c),d&&ib.updateOffset(a,f||g)}function n(a){return"[object Array]"===Object.prototype.toString.call(a)}function o(a){return"[object Date]"===Object.prototype.toString.call(a)||a instanceof Date}function p(a,b,c){var d,e=Math.min(a.length,b.length),f=Math.abs(a.length-b.length),g=0;for(d=0;e>d;d++)(c&&a[d]!==b[d]||!c&&t(a[d])!==t(b[d]))&&g++;return g+f}function q(a){if(a){var b=a.toLowerCase().replace(/(.)s$/,"$1");a=Zb[a]||$b[b]||b}return a}function r(a){var b,c,d={};for(c in a)a.hasOwnProperty(c)&&(b=q(c),b&&(d[b]=a[c]));return d}function s(b){var c,d;if(0===b.indexOf("week"))c=7,d="day";else{if(0!==b.indexOf("month"))return;c=12,d="month"}ib[b]=function(e,f){var g,h,i=ib.fn._lang[b],j=[];if("number"==typeof e&&(f=e,e=a),h=function(a){var b=ib().utc().set(d,a);return i.call(ib.fn._lang,b,e||"")},null!=f)return h(f);for(g=0;c>g;g++)j.push(h(g));return j}}function t(a){var b=+a,c=0;return 0!==b&&isFinite(b)&&(c=b>=0?Math.floor(b):Math.ceil(b)),c}function u(a,b){return new Date(Date.UTC(a,b+1,0)).getUTCDate()}function v(a,b,c){return $(ib([a,11,31+b-c]),b,c).week}function w(a){return x(a)?366:365}function x(a){return a%4===0&&a%100!==0||a%400===0}function y(a){var b;a._a&&-2===a._pf.overflow&&(b=a._a[pb]<0||a._a[pb]>11?pb:a._a[qb]<1||a._a[qb]>u(a._a[ob],a._a[pb])?qb:a._a[rb]<0||a._a[rb]>23?rb:a._a[sb]<0||a._a[sb]>59?sb:a._a[tb]<0||a._a[tb]>59?tb:a._a[ub]<0||a._a[ub]>999?ub:-1,a._pf._overflowDayOfYear&&(ob>b||b>qb)&&(b=qb),a._pf.overflow=b)}function z(a){return null==a._isValid&&(a._isValid=!isNaN(a._d.getTime())&&a._pf.overflow<0&&!a._pf.empty&&!a._pf.invalidMonth&&!a._pf.nullInput&&!a._pf.invalidFormat&&!a._pf.userInvalidated,a._strict&&(a._isValid=a._isValid&&0===a._pf.charsLeftOver&&0===a._pf.unusedTokens.length)),a._isValid}function A(a){return a?a.toLowerCase().replace("_","-"):a}function B(a,b){return b._isUTC?ib(a).zone(b._offset||0):ib(a).local()}function C(a,b){return b.abbr=a,vb[a]||(vb[a]=new f),vb[a].set(b),vb[a]}function D(a){delete vb[a]}function E(a){var b,c,d,e,f=0,g=function(a){if(!vb[a]&&xb)try{require("./lang/"+a)}catch(b){}return vb[a]};if(!a)return ib.fn._lang;if(!n(a)){if(c=g(a))return c;a=[a]}for(;f<a.length;){for(e=A(a[f]).split("-"),b=e.length,d=A(a[f+1]),d=d?d.split("-"):null;b>0;){if(c=g(e.slice(0,b).join("-")))return c;if(d&&d.length>=b&&p(e,d,!0)>=b-1)break;b--}f++}return ib.fn._lang}function F(a){return a.match(/\[[\s\S]/)?a.replace(/^\[|\]$/g,""):a.replace(/\\/g,"")}function G(a){var b,c,d=a.match(Bb);for(b=0,c=d.length;c>b;b++)d[b]=cc[d[b]]?cc[d[b]]:F(d[b]);return function(e){var f="";for(b=0;c>b;b++)f+=d[b]instanceof Function?d[b].call(e,a):d[b];return f}}function H(a,b){return a.isValid()?(b=I(b,a.lang()),_b[b]||(_b[b]=G(b)),_b[b](a)):a.lang().invalidDate()}function I(a,b){function c(a){return b.longDateFormat(a)||a}var d=5;for(Cb.lastIndex=0;d>=0&&Cb.test(a);)a=a.replace(Cb,c),Cb.lastIndex=0,d-=1;return a}function J(a,b){var c,d=b._strict;switch(a){case"Q":return Nb;case"DDDD":return Pb;case"YYYY":case"GGGG":case"gggg":return d?Qb:Fb;case"Y":case"G":case"g":return Sb;case"YYYYYY":case"YYYYY":case"GGGGG":case"ggggg":return d?Rb:Gb;case"S":if(d)return Nb;case"SS":if(d)return Ob;case"SSS":if(d)return Pb;case"DDD":return Eb;case"MMM":case"MMMM":case"dd":case"ddd":case"dddd":return Ib;case"a":case"A":return E(b._l)._meridiemParse;case"X":return Lb;case"Z":case"ZZ":return Jb;case"T":return Kb;case"SSSS":return Hb;case"MM":case"DD":case"YY":case"GG":case"gg":case"HH":case"hh":case"mm":case"ss":case"ww":case"WW":return d?Ob:Db;case"M":case"D":case"d":case"H":case"h":case"m":case"s":case"w":case"W":case"e":case"E":return Db;case"Do":return Mb;default:return c=new RegExp(R(Q(a.replace("\\","")),"i"))}}function K(a){a=a||"";var b=a.match(Jb)||[],c=b[b.length-1]||[],d=(c+"").match(Xb)||["-",0,0],e=+(60*d[1])+t(d[2]);return"+"===d[0]?-e:e}function L(a,b,c){var d,e=c._a;switch(a){case"Q":null!=b&&(e[pb]=3*(t(b)-1));break;case"M":case"MM":null!=b&&(e[pb]=t(b)-1);break;case"MMM":case"MMMM":d=E(c._l).monthsParse(b),null!=d?e[pb]=d:c._pf.invalidMonth=b;break;case"D":case"DD":null!=b&&(e[qb]=t(b));break;case"Do":null!=b&&(e[qb]=t(parseInt(b,10)));break;case"DDD":case"DDDD":null!=b&&(c._dayOfYear=t(b));break;case"YY":e[ob]=ib.parseTwoDigitYear(b);break;case"YYYY":case"YYYYY":case"YYYYYY":e[ob]=t(b);break;case"a":case"A":c._isPm=E(c._l).isPM(b);break;case"H":case"HH":case"h":case"hh":e[rb]=t(b);break;case"m":case"mm":e[sb]=t(b);break;case"s":case"ss":e[tb]=t(b);break;case"S":case"SS":case"SSS":case"SSSS":e[ub]=t(1e3*("0."+b));break;case"X":c._d=new Date(1e3*parseFloat(b));break;case"Z":case"ZZ":c._useUTC=!0,c._tzm=K(b);break;case"w":case"ww":case"W":case"WW":case"d":case"dd":case"ddd":case"dddd":case"e":case"E":a=a.substr(0,1);case"gg":case"gggg":case"GG":case"GGGG":case"GGGGG":a=a.substr(0,2),b&&(c._w=c._w||{},c._w[a]=b)}}function M(a){var b,c,d,e,f,g,h,i,j,k,l=[];if(!a._d){for(d=O(a),a._w&&null==a._a[qb]&&null==a._a[pb]&&(f=function(b){var c=parseInt(b,10);return b?b.length<3?c>68?1900+c:2e3+c:c:null==a._a[ob]?ib().weekYear():a._a[ob]},g=a._w,null!=g.GG||null!=g.W||null!=g.E?h=_(f(g.GG),g.W||1,g.E,4,1):(i=E(a._l),j=null!=g.d?X(g.d,i):null!=g.e?parseInt(g.e,10)+i._week.dow:0,k=parseInt(g.w,10)||1,null!=g.d&&j<i._week.dow&&k++,h=_(f(g.gg),k,j,i._week.doy,i._week.dow)),a._a[ob]=h.year,a._dayOfYear=h.dayOfYear),a._dayOfYear&&(e=null==a._a[ob]?d[ob]:a._a[ob],a._dayOfYear>w(e)&&(a._pf._overflowDayOfYear=!0),c=W(e,0,a._dayOfYear),a._a[pb]=c.getUTCMonth(),a._a[qb]=c.getUTCDate()),b=0;3>b&&null==a._a[b];++b)a._a[b]=l[b]=d[b];for(;7>b;b++)a._a[b]=l[b]=null==a._a[b]?2===b?1:0:a._a[b];l[rb]+=t((a._tzm||0)/60),l[sb]+=t((a._tzm||0)%60),a._d=(a._useUTC?W:V).apply(null,l)}}function N(a){var b;a._d||(b=r(a._i),a._a=[b.year,b.month,b.day,b.hour,b.minute,b.second,b.millisecond],M(a))}function O(a){var b=new Date;return a._useUTC?[b.getUTCFullYear(),b.getUTCMonth(),b.getUTCDate()]:[b.getFullYear(),b.getMonth(),b.getDate()]}function P(a){a._a=[],a._pf.empty=!0;var b,c,d,e,f,g=E(a._l),h=""+a._i,i=h.length,j=0;for(d=I(a._f,g).match(Bb)||[],b=0;b<d.length;b++)e=d[b],c=(h.match(J(e,a))||[])[0],c&&(f=h.substr(0,h.indexOf(c)),f.length>0&&a._pf.unusedInput.push(f),h=h.slice(h.indexOf(c)+c.length),j+=c.length),cc[e]?(c?a._pf.empty=!1:a._pf.unusedTokens.push(e),L(e,c,a)):a._strict&&!c&&a._pf.unusedTokens.push(e);a._pf.charsLeftOver=i-j,h.length>0&&a._pf.unusedInput.push(h),a._isPm&&a._a[rb]<12&&(a._a[rb]+=12),a._isPm===!1&&12===a._a[rb]&&(a._a[rb]=0),M(a),y(a)}function Q(a){return a.replace(/\\(\[)|\\(\])|\[([^\]\[]*)\]|\\(.)/g,function(a,b,c,d,e){return b||c||d||e})}function R(a){return a.replace(/[-\/\\^$*+?.()|[\]{}]/g,"\\$&")}function S(a){var c,d,e,f,g;if(0===a._f.length)return a._pf.invalidFormat=!0,void(a._d=new Date(0/0));for(f=0;f<a._f.length;f++)g=0,c=i({},a),c._pf=b(),c._f=a._f[f],P(c),z(c)&&(g+=c._pf.charsLeftOver,g+=10*c._pf.unusedTokens.length,c._pf.score=g,(null==e||e>g)&&(e=g,d=c));i(a,d||c)}function T(a){var b,c,d=a._i,e=Tb.exec(d);if(e){for(a._pf.iso=!0,b=0,c=Vb.length;c>b;b++)if(Vb[b][1].exec(d)){a._f=Vb[b][0]+(e[6]||" ");break}for(b=0,c=Wb.length;c>b;b++)if(Wb[b][1].exec(d)){a._f+=Wb[b][0];break}d.match(Jb)&&(a._f+="Z"),P(a)}else ib.createFromInputFallback(a)}function U(b){var c=b._i,d=yb.exec(c);c===a?b._d=new Date:d?b._d=new Date(+d[1]):"string"==typeof c?T(b):n(c)?(b._a=c.slice(0),M(b)):o(c)?b._d=new Date(+c):"object"==typeof c?N(b):"number"==typeof c?b._d=new Date(c):ib.createFromInputFallback(b)}function V(a,b,c,d,e,f,g){var h=new Date(a,b,c,d,e,f,g);return 1970>a&&h.setFullYear(a),h}function W(a){var b=new Date(Date.UTC.apply(null,arguments));return 1970>a&&b.setUTCFullYear(a),b}function X(a,b){if("string"==typeof a)if(isNaN(a)){if(a=b.weekdaysParse(a),"number"!=typeof a)return null}else a=parseInt(a,10);return a}function Y(a,b,c,d,e){return e.relativeTime(b||1,!!c,a,d)}function Z(a,b,c){var d=nb(Math.abs(a)/1e3),e=nb(d/60),f=nb(e/60),g=nb(f/24),h=nb(g/365),i=45>d&&["s",d]||1===e&&["m"]||45>e&&["mm",e]||1===f&&["h"]||22>f&&["hh",f]||1===g&&["d"]||25>=g&&["dd",g]||45>=g&&["M"]||345>g&&["MM",nb(g/30)]||1===h&&["y"]||["yy",h];return i[2]=b,i[3]=a>0,i[4]=c,Y.apply({},i)}function $(a,b,c){var d,e=c-b,f=c-a.day();return f>e&&(f-=7),e-7>f&&(f+=7),d=ib(a).add("d",f),{week:Math.ceil(d.dayOfYear()/7),year:d.year()}}function _(a,b,c,d,e){var f,g,h=W(a,0,1).getUTCDay();return c=null!=c?c:e,f=e-h+(h>d?7:0)-(e>h?7:0),g=7*(b-1)+(c-e)+f+1,{year:g>0?a:a-1,dayOfYear:g>0?g:w(a-1)+g}}function ab(b){var c=b._i,d=b._f;return null===c||d===a&&""===c?ib.invalid({nullInput:!0}):("string"==typeof c&&(b._i=c=E().preparse(c)),ib.isMoment(c)?(b=j(c),b._d=new Date(+c._d)):d?n(d)?S(b):P(b):U(b),new g(b))}function bb(a,b){var c;return"string"==typeof b&&(b=a.lang().monthsParse(b),"number"!=typeof b)?a:(c=Math.min(a.date(),u(a.year(),b)),a._d["set"+(a._isUTC?"UTC":"")+"Month"](b,c),a)}function cb(a,b){return a._d["get"+(a._isUTC?"UTC":"")+b]()}function db(a,b,c){return"Month"===b?bb(a,c):a._d["set"+(a._isUTC?"UTC":"")+b](c)}function eb(a,b){return function(c){return null!=c?(db(this,a,c),ib.updateOffset(this,b),this):cb(this,a)}}function fb(a){ib.duration.fn[a]=function(){return this._data[a]}}function gb(a,b){ib.duration.fn["as"+a]=function(){return+this/b}}function hb(a){"undefined"==typeof ender&&(jb=mb.moment,mb.moment=a?c("Accessing Moment through the global scope is deprecated, and will be removed in an upcoming release.",ib):ib)}for(var ib,jb,kb,lb="2.6.0",mb="undefined"!=typeof global?global:this,nb=Math.round,ob=0,pb=1,qb=2,rb=3,sb=4,tb=5,ub=6,vb={},wb={_isAMomentObject:null,_i:null,_f:null,_l:null,_strict:null,_isUTC:null,_offset:null,_pf:null,_lang:null},xb="undefined"!=typeof module&&module.exports,yb=/^\/?Date\((\-?\d+)/i,zb=/(\-)?(?:(\d*)\.)?(\d+)\:(\d+)(?:\:(\d+)\.?(\d{3})?)?/,Ab=/^(-)?P(?:(?:([0-9,.]*)Y)?(?:([0-9,.]*)M)?(?:([0-9,.]*)D)?(?:T(?:([0-9,.]*)H)?(?:([0-9,.]*)M)?(?:([0-9,.]*)S)?)?|([0-9,.]*)W)$/,Bb=/(\[[^\[]*\])|(\\)?(Mo|MM?M?M?|Do|DDDo|DD?D?D?|ddd?d?|do?|w[o|w]?|W[o|W]?|Q|YYYYYY|YYYYY|YYYY|YY|gg(ggg?)?|GG(GGG?)?|e|E|a|A|hh?|HH?|mm?|ss?|S{1,4}|X|zz?|ZZ?|.)/g,Cb=/(\[[^\[]*\])|(\\)?(LT|LL?L?L?|l{1,4})/g,Db=/\d\d?/,Eb=/\d{1,3}/,Fb=/\d{1,4}/,Gb=/[+\-]?\d{1,6}/,Hb=/\d+/,Ib=/[0-9]*['a-z\u00A0-\u05FF\u0700-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+|[\u0600-\u06FF\/]+(\s*?[\u0600-\u06FF]+){1,2}/i,Jb=/Z|[\+\-]\d\d:?\d\d/gi,Kb=/T/i,Lb=/[\+\-]?\d+(\.\d{1,3})?/,Mb=/\d{1,2}/,Nb=/\d/,Ob=/\d\d/,Pb=/\d{3}/,Qb=/\d{4}/,Rb=/[+-]?\d{6}/,Sb=/[+-]?\d+/,Tb=/^\s*(?:[+-]\d{6}|\d{4})-(?:(\d\d-\d\d)|(W\d\d$)|(W\d\d-\d)|(\d\d\d))((T| )(\d\d(:\d\d(:\d\d(\.\d+)?)?)?)?([\+\-]\d\d(?::?\d\d)?|\s*Z)?)?$/,Ub="YYYY-MM-DDTHH:mm:ssZ",Vb=[["YYYYYY-MM-DD",/[+-]\d{6}-\d{2}-\d{2}/],["YYYY-MM-DD",/\d{4}-\d{2}-\d{2}/],["GGGG-[W]WW-E",/\d{4}-W\d{2}-\d/],["GGGG-[W]WW",/\d{4}-W\d{2}/],["YYYY-DDD",/\d{4}-\d{3}/]],Wb=[["HH:mm:ss.SSSS",/(T| )\d\d:\d\d:\d\d\.\d+/],["HH:mm:ss",/(T| )\d\d:\d\d:\d\d/],["HH:mm",/(T| )\d\d:\d\d/],["HH",/(T| )\d\d/]],Xb=/([\+\-]|\d\d)/gi,Yb=("Date|Hours|Minutes|Seconds|Milliseconds".split("|"),{Milliseconds:1,Seconds:1e3,Minutes:6e4,Hours:36e5,Days:864e5,Months:2592e6,Years:31536e6}),Zb={ms:"millisecond",s:"second",m:"minute",h:"hour",d:"day",D:"date",w:"week",W:"isoWeek",M:"month",Q:"quarter",y:"year",DDD:"dayOfYear",e:"weekday",E:"isoWeekday",gg:"weekYear",GG:"isoWeekYear"},$b={dayofyear:"dayOfYear",isoweekday:"isoWeekday",isoweek:"isoWeek",weekyear:"weekYear",isoweekyear:"isoWeekYear"},_b={},ac="DDD w W M D d".split(" "),bc="M D H h m s w W".split(" "),cc={M:function(){return this.month()+1},MMM:function(a){return this.lang().monthsShort(this,a)},MMMM:function(a){return this.lang().months(this,a)},D:function(){return this.date()},DDD:function(){return this.dayOfYear()},d:function(){return this.day()},dd:function(a){return this.lang().weekdaysMin(this,a)},ddd:function(a){return this.lang().weekdaysShort(this,a)},dddd:function(a){return this.lang().weekdays(this,a)},w:function(){return this.week()},W:function(){return this.isoWeek()},YY:function(){return l(this.year()%100,2)},YYYY:function(){return l(this.year(),4)},YYYYY:function(){return l(this.year(),5)},YYYYYY:function(){var a=this.year(),b=a>=0?"+":"-";return b+l(Math.abs(a),6)},gg:function(){return l(this.weekYear()%100,2)},gggg:function(){return l(this.weekYear(),4)},ggggg:function(){return l(this.weekYear(),5)},GG:function(){return l(this.isoWeekYear()%100,2)},GGGG:function(){return l(this.isoWeekYear(),4)},GGGGG:function(){return l(this.isoWeekYear(),5)},e:function(){return this.weekday()},E:function(){return this.isoWeekday()},a:function(){return this.lang().meridiem(this.hours(),this.minutes(),!0)},A:function(){return this.lang().meridiem(this.hours(),this.minutes(),!1)},H:function(){return this.hours()},h:function(){return this.hours()%12||12},m:function(){return this.minutes()},s:function(){return this.seconds()},S:function(){return t(this.milliseconds()/100)},SS:function(){return l(t(this.milliseconds()/10),2)},SSS:function(){return l(this.milliseconds(),3)},SSSS:function(){return l(this.milliseconds(),3)},Z:function(){var a=-this.zone(),b="+";return 0>a&&(a=-a,b="-"),b+l(t(a/60),2)+":"+l(t(a)%60,2)},ZZ:function(){var a=-this.zone(),b="+";return 0>a&&(a=-a,b="-"),b+l(t(a/60),2)+l(t(a)%60,2)},z:function(){return this.zoneAbbr()},zz:function(){return this.zoneName()},X:function(){return this.unix()},Q:function(){return this.quarter()}},dc=["months","monthsShort","weekdays","weekdaysShort","weekdaysMin"];ac.length;)kb=ac.pop(),cc[kb+"o"]=e(cc[kb],kb);for(;bc.length;)kb=bc.pop(),cc[kb+kb]=d(cc[kb],2);for(cc.DDDD=d(cc.DDD,3),i(f.prototype,{set:function(a){var b,c;for(c in a)b=a[c],"function"==typeof b?this[c]=b:this["_"+c]=b},_months:"January_February_March_April_May_June_July_August_September_October_November_December".split("_"),months:function(a){return this._months[a.month()]},_monthsShort:"Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec".split("_"),monthsShort:function(a){return this._monthsShort[a.month()]},monthsParse:function(a){var b,c,d;for(this._monthsParse||(this._monthsParse=[]),b=0;12>b;b++)if(this._monthsParse[b]||(c=ib.utc([2e3,b]),d="^"+this.months(c,"")+"|^"+this.monthsShort(c,""),this._monthsParse[b]=new RegExp(d.replace(".",""),"i")),this._monthsParse[b].test(a))return b},_weekdays:"Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday".split("_"),weekdays:function(a){return this._weekdays[a.day()]},_weekdaysShort:"Sun_Mon_Tue_Wed_Thu_Fri_Sat".split("_"),weekdaysShort:function(a){return this._weekdaysShort[a.day()]},_weekdaysMin:"Su_Mo_Tu_We_Th_Fr_Sa".split("_"),weekdaysMin:function(a){return this._weekdaysMin[a.day()]},weekdaysParse:function(a){var b,c,d;for(this._weekdaysParse||(this._weekdaysParse=[]),b=0;7>b;b++)if(this._weekdaysParse[b]||(c=ib([2e3,1]).day(b),d="^"+this.weekdays(c,"")+"|^"+this.weekdaysShort(c,"")+"|^"+this.weekdaysMin(c,""),this._weekdaysParse[b]=new RegExp(d.replace(".",""),"i")),this._weekdaysParse[b].test(a))return b},_longDateFormat:{LT:"h:mm A",L:"MM/DD/YYYY",LL:"MMMM D YYYY",LLL:"MMMM D YYYY LT",LLLL:"dddd, MMMM D YYYY LT"},longDateFormat:function(a){var b=this._longDateFormat[a];return!b&&this._longDateFormat[a.toUpperCase()]&&(b=this._longDateFormat[a.toUpperCase()].replace(/MMMM|MM|DD|dddd/g,function(a){return a.slice(1)}),this._longDateFormat[a]=b),b},isPM:function(a){return"p"===(a+"").toLowerCase().charAt(0)},_meridiemParse:/[ap]\.?m?\.?/i,meridiem:function(a,b,c){return a>11?c?"pm":"PM":c?"am":"AM"},_calendar:{sameDay:"[Today at] LT",nextDay:"[Tomorrow at] LT",nextWeek:"dddd [at] LT",lastDay:"[Yesterday at] LT",lastWeek:"[Last] dddd [at] LT",sameElse:"L"},calendar:function(a,b){var c=this._calendar[a];return"function"==typeof c?c.apply(b):c},_relativeTime:{future:"in %s",past:"%s ago",s:"a few seconds",m:"a minute",mm:"%d minutes",h:"an hour",hh:"%d hours",d:"a day",dd:"%d days",M:"a month",MM:"%d months",y:"a year",yy:"%d years"},relativeTime:function(a,b,c,d){var e=this._relativeTime[c];return"function"==typeof e?e(a,b,c,d):e.replace(/%d/i,a)},pastFuture:function(a,b){var c=this._relativeTime[a>0?"future":"past"];return"function"==typeof c?c(b):c.replace(/%s/i,b)},ordinal:function(a){return this._ordinal.replace("%d",a)},_ordinal:"%d",preparse:function(a){return a},postformat:function(a){return a},week:function(a){return $(a,this._week.dow,this._week.doy).week},_week:{dow:0,doy:6},_invalidDate:"Invalid date",invalidDate:function(){return this._invalidDate}}),ib=function(c,d,e,f){var g;return"boolean"==typeof e&&(f=e,e=a),g={},g._isAMomentObject=!0,g._i=c,g._f=d,g._l=e,g._strict=f,g._isUTC=!1,g._pf=b(),ab(g)},ib.suppressDeprecationWarnings=!1,ib.createFromInputFallback=c("moment construction falls back to js Date. This is discouraged and will be removed in upcoming major release. Please refer to https://github.com/moment/moment/issues/1407 for more info.",function(a){a._d=new Date(a._i)}),ib.utc=function(c,d,e,f){var g;return"boolean"==typeof e&&(f=e,e=a),g={},g._isAMomentObject=!0,g._useUTC=!0,g._isUTC=!0,g._l=e,g._i=c,g._f=d,g._strict=f,g._pf=b(),ab(g).utc()},ib.unix=function(a){return ib(1e3*a)},ib.duration=function(a,b){var c,d,e,f=a,g=null;return ib.isDuration(a)?f={ms:a._milliseconds,d:a._days,M:a._months}:"number"==typeof a?(f={},b?f[b]=a:f.milliseconds=a):(g=zb.exec(a))?(c="-"===g[1]?-1:1,f={y:0,d:t(g[qb])*c,h:t(g[rb])*c,m:t(g[sb])*c,s:t(g[tb])*c,ms:t(g[ub])*c}):(g=Ab.exec(a))&&(c="-"===g[1]?-1:1,e=function(a){var b=a&&parseFloat(a.replace(",","."));return(isNaN(b)?0:b)*c},f={y:e(g[2]),M:e(g[3]),d:e(g[4]),h:e(g[5]),m:e(g[6]),s:e(g[7]),w:e(g[8])}),d=new h(f),ib.isDuration(a)&&a.hasOwnProperty("_lang")&&(d._lang=a._lang),d},ib.version=lb,ib.defaultFormat=Ub,ib.momentProperties=wb,ib.updateOffset=function(){},ib.lang=function(a,b){var c;return a?(b?C(A(a),b):null===b?(D(a),a="en"):vb[a]||E(a),c=ib.duration.fn._lang=ib.fn._lang=E(a),c._abbr):ib.fn._lang._abbr},ib.langData=function(a){return a&&a._lang&&a._lang._abbr&&(a=a._lang._abbr),E(a)},ib.isMoment=function(a){return a instanceof g||null!=a&&a.hasOwnProperty("_isAMomentObject")},ib.isDuration=function(a){return a instanceof h},kb=dc.length-1;kb>=0;--kb)s(dc[kb]);ib.normalizeUnits=function(a){return q(a)},ib.invalid=function(a){var b=ib.utc(0/0);return null!=a?i(b._pf,a):b._pf.userInvalidated=!0,b},ib.parseZone=function(){return ib.apply(null,arguments).parseZone()},ib.parseTwoDigitYear=function(a){return t(a)+(t(a)>68?1900:2e3)},i(ib.fn=g.prototype,{clone:function(){return ib(this)},valueOf:function(){return+this._d+6e4*(this._offset||0)},unix:function(){return Math.floor(+this/1e3)},toString:function(){return this.clone().lang("en").format("ddd MMM DD YYYY HH:mm:ss [GMT]ZZ")},toDate:function(){return this._offset?new Date(+this):this._d},toISOString:function(){var a=ib(this).utc();return 0<a.year()&&a.year()<=9999?H(a,"YYYY-MM-DD[T]HH:mm:ss.SSS[Z]"):H(a,"YYYYYY-MM-DD[T]HH:mm:ss.SSS[Z]")},toArray:function(){var a=this;return[a.year(),a.month(),a.date(),a.hours(),a.minutes(),a.seconds(),a.milliseconds()]},isValid:function(){return z(this)},isDSTShifted:function(){return this._a?this.isValid()&&p(this._a,(this._isUTC?ib.utc(this._a):ib(this._a)).toArray())>0:!1},parsingFlags:function(){return i({},this._pf)},invalidAt:function(){return this._pf.overflow},utc:function(){return this.zone(0)},local:function(){return this.zone(0),this._isUTC=!1,this},format:function(a){var b=H(this,a||ib.defaultFormat);return this.lang().postformat(b)},add:function(a,b){var c;return c="string"==typeof a?ib.duration(+b,a):ib.duration(a,b),m(this,c,1),this},subtract:function(a,b){var c;return c="string"==typeof a?ib.duration(+b,a):ib.duration(a,b),m(this,c,-1),this},diff:function(a,b,c){var d,e,f=B(a,this),g=6e4*(this.zone()-f.zone());return b=q(b),"year"===b||"month"===b?(d=432e5*(this.daysInMonth()+f.daysInMonth()),e=12*(this.year()-f.year())+(this.month()-f.month()),e+=(this-ib(this).startOf("month")-(f-ib(f).startOf("month")))/d,e-=6e4*(this.zone()-ib(this).startOf("month").zone()-(f.zone()-ib(f).startOf("month").zone()))/d,"year"===b&&(e/=12)):(d=this-f,e="second"===b?d/1e3:"minute"===b?d/6e4:"hour"===b?d/36e5:"day"===b?(d-g)/864e5:"week"===b?(d-g)/6048e5:d),c?e:k(e)},from:function(a,b){return ib.duration(this.diff(a)).lang(this.lang()._abbr).humanize(!b)},fromNow:function(a){return this.from(ib(),a)},calendar:function(){var a=B(ib(),this).startOf("day"),b=this.diff(a,"days",!0),c=-6>b?"sameElse":-1>b?"lastWeek":0>b?"lastDay":1>b?"sameDay":2>b?"nextDay":7>b?"nextWeek":"sameElse";return this.format(this.lang().calendar(c,this))},isLeapYear:function(){return x(this.year())},isDST:function(){return this.zone()<this.clone().month(0).zone()||this.zone()<this.clone().month(5).zone()},day:function(a){var b=this._isUTC?this._d.getUTCDay():this._d.getDay();return null!=a?(a=X(a,this.lang()),this.add({d:a-b})):b},month:eb("Month",!0),startOf:function(a){switch(a=q(a)){case"year":this.month(0);case"quarter":case"month":this.date(1);case"week":case"isoWeek":case"day":this.hours(0);case"hour":this.minutes(0);case"minute":this.seconds(0);case"second":this.milliseconds(0)}return"week"===a?this.weekday(0):"isoWeek"===a&&this.isoWeekday(1),"quarter"===a&&this.month(3*Math.floor(this.month()/3)),this},endOf:function(a){return a=q(a),this.startOf(a).add("isoWeek"===a?"week":a,1).subtract("ms",1)},isAfter:function(a,b){return b="undefined"!=typeof b?b:"millisecond",+this.clone().startOf(b)>+ib(a).startOf(b)},isBefore:function(a,b){return b="undefined"!=typeof b?b:"millisecond",+this.clone().startOf(b)<+ib(a).startOf(b)},isSame:function(a,b){return b=b||"ms",+this.clone().startOf(b)===+B(a,this).startOf(b)},min:function(a){return a=ib.apply(null,arguments),this>a?this:a},max:function(a){return a=ib.apply(null,arguments),a>this?this:a},zone:function(a,b){var c=this._offset||0;return null==a?this._isUTC?c:this._d.getTimezoneOffset():("string"==typeof a&&(a=K(a)),Math.abs(a)<16&&(a=60*a),this._offset=a,this._isUTC=!0,c!==a&&(!b||this._changeInProgress?m(this,ib.duration(c-a,"m"),1,!1):this._changeInProgress||(this._changeInProgress=!0,ib.updateOffset(this,!0),this._changeInProgress=null)),this)},zoneAbbr:function(){return this._isUTC?"UTC":""},zoneName:function(){return this._isUTC?"Coordinated Universal Time":""},parseZone:function(){return this._tzm?this.zone(this._tzm):"string"==typeof this._i&&this.zone(this._i),this},hasAlignedHourOffset:function(a){return a=a?ib(a).zone():0,(this.zone()-a)%60===0},daysInMonth:function(){return u(this.year(),this.month())},dayOfYear:function(a){var b=nb((ib(this).startOf("day")-ib(this).startOf("year"))/864e5)+1;return null==a?b:this.add("d",a-b)},quarter:function(a){return null==a?Math.ceil((this.month()+1)/3):this.month(3*(a-1)+this.month()%3)},weekYear:function(a){var b=$(this,this.lang()._week.dow,this.lang()._week.doy).year;return null==a?b:this.add("y",a-b)},isoWeekYear:function(a){var b=$(this,1,4).year;return null==a?b:this.add("y",a-b)},week:function(a){var b=this.lang().week(this);return null==a?b:this.add("d",7*(a-b))},isoWeek:function(a){var b=$(this,1,4).week;return null==a?b:this.add("d",7*(a-b))},weekday:function(a){var b=(this.day()+7-this.lang()._week.dow)%7;return null==a?b:this.add("d",a-b)},isoWeekday:function(a){return null==a?this.day()||7:this.day(this.day()%7?a:a-7)},isoWeeksInYear:function(){return v(this.year(),1,4)},weeksInYear:function(){var a=this._lang._week;return v(this.year(),a.dow,a.doy)},get:function(a){return a=q(a),this[a]()},set:function(a,b){return a=q(a),"function"==typeof this[a]&&this[a](b),this},lang:function(b){return b===a?this._lang:(this._lang=E(b),this)}}),ib.fn.millisecond=ib.fn.milliseconds=eb("Milliseconds",!1),ib.fn.second=ib.fn.seconds=eb("Seconds",!1),ib.fn.minute=ib.fn.minutes=eb("Minutes",!1),ib.fn.hour=ib.fn.hours=eb("Hours",!0),ib.fn.date=eb("Date",!0),ib.fn.dates=c("dates accessor is deprecated. Use date instead.",eb("Date",!0)),ib.fn.year=eb("FullYear",!0),ib.fn.years=c("years accessor is deprecated. Use year instead.",eb("FullYear",!0)),ib.fn.days=ib.fn.day,ib.fn.months=ib.fn.month,ib.fn.weeks=ib.fn.week,ib.fn.isoWeeks=ib.fn.isoWeek,ib.fn.quarters=ib.fn.quarter,ib.fn.toJSON=ib.fn.toISOString,i(ib.duration.fn=h.prototype,{_bubble:function(){var a,b,c,d,e=this._milliseconds,f=this._days,g=this._months,h=this._data;h.milliseconds=e%1e3,a=k(e/1e3),h.seconds=a%60,b=k(a/60),h.minutes=b%60,c=k(b/60),h.hours=c%24,f+=k(c/24),h.days=f%30,g+=k(f/30),h.months=g%12,d=k(g/12),h.years=d},weeks:function(){return k(this.days()/7)},valueOf:function(){return this._milliseconds+864e5*this._days+this._months%12*2592e6+31536e6*t(this._months/12)},humanize:function(a){var b=+this,c=Z(b,!a,this.lang());return a&&(c=this.lang().pastFuture(b,c)),this.lang().postformat(c)},add:function(a,b){var c=ib.duration(a,b);return this._milliseconds+=c._milliseconds,this._days+=c._days,this._months+=c._months,this._bubble(),this},subtract:function(a,b){var c=ib.duration(a,b);return this._milliseconds-=c._milliseconds,this._days-=c._days,this._months-=c._months,this._bubble(),this},get:function(a){return a=q(a),this[a.toLowerCase()+"s"]()},as:function(a){return a=q(a),this["as"+a.charAt(0).toUpperCase()+a.slice(1)+"s"]()},lang:ib.fn.lang,toIsoString:function(){var a=Math.abs(this.years()),b=Math.abs(this.months()),c=Math.abs(this.days()),d=Math.abs(this.hours()),e=Math.abs(this.minutes()),f=Math.abs(this.seconds()+this.milliseconds()/1e3);return this.asSeconds()?(this.asSeconds()<0?"-":"")+"P"+(a?a+"Y":"")+(b?b+"M":"")+(c?c+"D":"")+(d||e||f?"T":"")+(d?d+"H":"")+(e?e+"M":"")+(f?f+"S":""):"P0D"}});for(kb in Yb)Yb.hasOwnProperty(kb)&&(gb(kb,Yb[kb]),fb(kb.toLowerCase()));gb("Weeks",6048e5),ib.duration.fn.asMonths=function(){return(+this-31536e6*this.years())/2592e6+12*this.years()},ib.lang("en",{ordinal:function(a){var b=a%10,c=1===t(a%100/10)?"th":1===b?"st":2===b?"nd":3===b?"rd":"th";return a+c}}),xb?module.exports=ib:"function"==typeof define&&define.amd?(define("moment",function(a,b,c){return c.config&&c.config()&&c.config().noGlobal===!0&&(mb.moment=jb),ib}),hb(!0)):hb()}).call(this);


        /*!
         * Pikaday
         *
         * Copyright © 2014 David Bushell | BSD & MIT license | https://github.com/dbushell/Pikaday
         */

        (function (root, factory)
        {
            'use strict';

            var moment;
            if (typeof exports === 'object') {
                // CommonJS module
                // Load moment.js as an optional dependency
                try { moment = require('moment'); } catch (e) {}
                module.exports = factory(moment);
            } else if (typeof define === 'function' && define.amd) {
                // AMD. Register as an anonymous module.
                define(function (req)
                {
                    // Load moment.js as an optional dependency
                    var id = 'moment';
                    moment = req.defined && req.defined(id) ? req(id) : undefined;
                    return factory(moment);
                });
            } else {
                root.Pikaday = factory(root.moment);
            }
        }(this, function (moment)
        {
            'use strict';

            /**
             * feature detection and helper functions
             */
            var hasMoment = typeof moment === 'function',

            hasEventListeners = !!window.addEventListener,

            document = window.document,

            sto = window.setTimeout,

            addEvent = function(el, e, callback, capture)
            {
                if (hasEventListeners) {
                    el.addEventListener(e, callback, !!capture);
                } else {
                    el.attachEvent('on' + e, callback);
                }
            },

            removeEvent = function(el, e, callback, capture)
            {
                if (hasEventListeners) {
                    el.removeEventListener(e, callback, !!capture);
                } else {
                    el.detachEvent('on' + e, callback);
                }
            },

            fireEvent = function(el, eventName, data)
            {
                var ev;

                if (document.createEvent) {
                    ev = document.createEvent('HTMLEvents');
                    ev.initEvent(eventName, true, false);
                    ev = extend(ev, data);
                    el.dispatchEvent(ev);
                } else if (document.createEventObject) {
                    ev = document.createEventObject();
                    ev = extend(ev, data);
                    el.fireEvent('on' + eventName, ev);
                }
            },

            trim = function(str)
            {
                return str.trim ? str.trim() : str.replace(/^\s+|\s+$/g,'');
            },

            hasClass = function(el, cn)
            {
                return (' ' + el.className + ' ').indexOf(' ' + cn + ' ') !== -1;
            },

            addClass = function(el, cn)
            {
                if (!hasClass(el, cn)) {
                    el.className = (el.className === '') ? cn : el.className + ' ' + cn;
                }
            },

            removeClass = function(el, cn)
            {
                el.className = trim((' ' + el.className + ' ').replace(' ' + cn + ' ', ' '));
            },

            isArray = function(obj)
            {
                return (/Array/).test(Object.prototype.toString.call(obj));
            },

            isDate = function(obj)
            {
                return (/Date/).test(Object.prototype.toString.call(obj)) && !isNaN(obj.getTime());
            },

            isLeapYear = function(year)
            {
                // solution by Matti Virkkunen: http://stackoverflow.com/a/4881951
                return year % 4 === 0 && year % 100 !== 0 || year % 400 === 0;
            },

            getDaysInMonth = function(year, month)
            {
                return [31, isLeapYear(year) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month];
            },

            setToStartOfDay = function(date)
            {
                if (isDate(date)) date.setHours(0,0,0,0);
            },

            compareDates = function(a,b)
            {
                // weak date comparison (use setToStartOfDay(date) to ensure correct result)
                return a.getTime() === b.getTime();
            },

            extend = function(to, from, overwrite)
            {
                var prop, hasProp;
                for (prop in from) {
                    hasProp = to[prop] !== undefined;
                    if (hasProp && typeof from[prop] === 'object' && from[prop].nodeName === undefined) {
                        if (isDate(from[prop])) {
                            if (overwrite) {
                                to[prop] = new Date(from[prop].getTime());
                            }
                        }
                        else if (isArray(from[prop])) {
                            if (overwrite) {
                                to[prop] = from[prop].slice(0);
                            }
                        } else {
                            to[prop] = extend({}, from[prop], overwrite);
                        }
                    } else if (overwrite || !hasProp) {
                        to[prop] = from[prop];
                    }
                }
                return to;
            },

            adjustCalendar = function(calendar) {
                if (calendar.month < 0) {
                    calendar.year -= Math.ceil(Math.abs(calendar.month)/12);
                    calendar.month += 12;
                }
                if (calendar.month > 11) {
                    calendar.year += Math.floor(Math.abs(calendar.month)/12);
                    calendar.month -= 12;
                }
                return calendar;
            },

            /**
             * defaults and localisation
             */
            defaults = {

                // bind the picker to a form field
                field: null,

                // automatically show/hide the picker on `field` focus (default `true` if `field` is set)
                bound: undefined,

                // position of the datepicker, relative to the field (default to bottom & left)
                // ('bottom' & 'left' keywords are not used, 'top' & 'right' are modifier on the bottom/left position)
                position: 'bottom left',

                // the default output format for `.toString()` and `field` value
                format: 'YYYY-MM-DD',

                // the initial date to view when first opened
                defaultDate: null,

                // make the `defaultDate` the initial selected value
                setDefaultDate: false,

                // first day of week (0: Sunday, 1: Monday etc)
                firstDay: 0,

                // the minimum/earliest date that can be selected
                minDate: today,
                // the maximum/latest date that can be selected
                maxDate: null,

                // number of years either side, or array of upper/lower range
                yearRange: 10,

                // used internally (don't config outside)
                minYear: 0,
                maxYear: 9999,
                minMonth: undefined,
                maxMonth: undefined,

                isRTL: false,

                // Additional text to append to the year in the calendar title
                yearSuffix: '',

                // Render the month after year in the calendar title
                showMonthAfterYear: false,

                // how many months are visible
                numberOfMonths: 1,

                // when numberOfMonths is used, this will help you to choose where the main calendar will be (default `left`, can be set to `right`)
                // only used for the first display or when a selected date is not visible
                mainCalendar: 'left',

                // internationalization
                i18n: {
                    previousMonth : 'Previous Month',
                    nextMonth     : 'Next Month',
                    months        : ['January','February','March','April','May','June','July','August','September','October','November','December'],
                    weekdays      : ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
                    weekdaysShort : ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']
                },

                // callback function
                onSelect: null,
                onOpen: null,
                onClose: null,
                onDraw: null
            },


            /**
             * templating functions to abstract HTML rendering
             */
            renderDayName = function(opts, day, abbr)
            {
                day += opts.firstDay;
                while (day >= 7) {
                    day -= 7;
                }
                return abbr ? opts.i18n.weekdaysShort[day] : opts.i18n.weekdays[day];
            },

            renderDay = function(d, m, y, isSelected, isToday, isDisabled, isEmpty)
            {
                if (isEmpty) {
                    return '<td class="is-empty"></td>';
                }
                var arr = [];
                if (isDisabled) {
                    arr.push('is-disabled');
                }
                if (isToday) {
                    arr.push('is-today');
                }
                if (isSelected) {
                    arr.push('is-selected');
                }
                return '<td data-day="' + d + '" class="' + arr.join(' ') + '">' +
                         '<button class="pika-button pika-day" type="button" ' +
                            'data-pika-year="' + y + '" data-pika-month="' + m + '" data-pika-day="' + d + '">' +
                                d +
                         '</button>' +
                       '</td>';
            },

            renderRow = function(days, isRTL)
            {
                return '<tr>' + (isRTL ? days.reverse() : days).join('') + '</tr>';
            },

            renderBody = function(rows)
            {
                return '<tbody>' + rows.join('') + '</tbody>';
            },

            renderHead = function(opts)
            {
                var i, arr = [];
                for (i = 0; i < 7; i++) {
                    arr.push('<th scope="col"><abbr title="' + renderDayName(opts, i) + '">' + renderDayName(opts, i, true) + '</abbr></th>');
                }
                return '<thead>' + (opts.isRTL ? arr.reverse() : arr).join('') + '</thead>';
            },

            renderTitle = function(instance, c, year, month, refYear)
            {
                var i, j, arr,
                    opts = instance._o,
                    isMinYear = year === opts.minYear,
                    isMaxYear = year === opts.maxYear,
                    html = '<div class="pika-title">',
                    monthHtml,
                    yearHtml,
                    prev = true,
                    next = true;

                for (arr = [], i = 0; i < 12; i++) {
                    arr.push('<option value="' + (year === refYear ? i - c : 12 + i - c) + '"' +
                        (i === month ? ' selected': '') +
                        ((isMinYear && i < opts.minMonth) || (isMaxYear && i > opts.maxMonth) ? 'disabled' : '') + '>' +
                        opts.i18n.months[i] + '</option>');
                }
                monthHtml = '<div class="pika-label">' + opts.i18n.months[month] + '<select class="pika-select pika-select-month">' + arr.join('') + '</select></div>';

                if (isArray(opts.yearRange)) {
                    i = opts.yearRange[0];
                    j = opts.yearRange[1] + 1;
                } else {
                    i = year - opts.yearRange;
                    j = 1 + year + opts.yearRange;
                }

                for (arr = []; i < j && i <= opts.maxYear; i++) {
                    if (i >= opts.minYear) {
                        arr.push('<option value="' + i + '"' + (i === year ? ' selected': '') + '>' + (i) + '</option>');
                    }
                }
                yearHtml = '<div class="pika-label">' + year + opts.yearSuffix + '<select class="pika-select pika-select-year">' + arr.join('') + '</select></div>';

                if (opts.showMonthAfterYear) {
                    html += yearHtml + monthHtml;
                } else {
                    html += monthHtml + yearHtml;
                }

                if (isMinYear && (month === 0 || opts.minMonth >= month)) {
                    prev = false;
                }

                if (isMaxYear && (month === 11 || opts.maxMonth <= month)) {
                    next = false;
                }

                if (c === 0) {
                    html += '<button class="pika-prev' + (prev ? '' : ' is-disabled') + '" type="button">' + opts.i18n.previousMonth + '</button>';
                }
                if (c === (instance._o.numberOfMonths - 1) ) {
                    html += '<button class="pika-next' + (next ? '' : ' is-disabled') + '" type="button">' + opts.i18n.nextMonth + '</button>';
                }

                return html += '</div>';
            },

            renderTable = function(opts, data)
            {
                return '<table cellpadding="0" cellspacing="0" class="pika-table">' + renderHead(opts) + renderBody(data) + '</table>';
            },


            /**
             * Pikaday constructor
             */
            Pikaday = function(options)
            {
                var self = this,
                    opts = self.config(options);

                self._onMouseDown = function(e)
                {
                    if (!self._v) {
                        return;
                    }
                    e = e || window.event;
                    var target = e.target || e.srcElement;
                    if (!target) {
                        return;
                    }

                    if (!hasClass(target, 'is-disabled')) {
                        if (hasClass(target, 'pika-button') && !hasClass(target, 'is-empty')) {
                            self.setDate(new Date(target.getAttribute('data-pika-year'), target.getAttribute('data-pika-month'), target.getAttribute('data-pika-day')));
                            if (opts.bound) {
                                sto(function() {
                                    self.hide();
                                    if (opts.field) {
                                        opts.field.blur();
                                    }
                                }, 100);
                            }
                            return;
                        }
                        else if (hasClass(target, 'pika-prev')) {
                            self.prevMonth();
                        }
                        else if (hasClass(target, 'pika-next')) {
                            self.nextMonth();
                        }
                    }
                    if (!hasClass(target, 'pika-select')) {
                        if (e.preventDefault) {
                            e.preventDefault();
                        } else {
                            e.returnValue = false;
                            return false;
                        }
                    } else {
                        self._c = true;
                    }
                };

                self._onChange = function(e)
                {
                    e = e || window.event;
                    var target = e.target || e.srcElement;
                    if (!target) {
                        return;
                    }
                    if (hasClass(target, 'pika-select-month')) {
                        self.gotoMonth(target.value);
                    }
                    else if (hasClass(target, 'pika-select-year')) {
                        self.gotoYear(target.value);
                    }
                };

                self._onInputChange = function(e)
                {
                    var date;

                    if (e.firedBy === self) {
                        return;
                    }
                    if (hasMoment) {
                        date = moment(opts.field.value, opts.format);
                        date = (date && date.isValid()) ? date.toDate() : null;
                    }
                    else {
                        date = new Date(Date.parse(opts.field.value));
                    }
                    self.setDate(isDate(date) ? date : null);
                    if (!self._v) {
                        self.show();
                    }
                };

                self._onInputFocus = function()
                {
                    self.show();
                };

                self._onInputClick = function()
                {
                    self.show();
                };

                self._onInputBlur = function()
                {
                    if (!self._c) {
                        self._b = sto(function() {
                            self.hide();
                        }, 50);
                    }
                    self._c = false;
                };

                self._onClick = function(e)
                {
                    e = e || window.event;
                    var target = e.target || e.srcElement,
                        pEl = target;
                    if (!target) {
                        return;
                    }
                    if (!hasEventListeners && hasClass(target, 'pika-select')) {
                        if (!target.onchange) {
                            target.setAttribute('onchange', 'return;');
                            addEvent(target, 'change', self._onChange);
                        }
                    }
                    do {
                        if (hasClass(pEl, 'pika-single')) {
                            return;
                        }
                    }
                    while ((pEl = pEl.parentNode));
                    if (self._v && target !== opts.trigger) {
                        self.hide();
                    }
                };

                self.el = document.createElement('div');
                self.el.className = 'pika-single' + (opts.isRTL ? ' is-rtl' : '');

                addEvent(self.el, 'mousedown', self._onMouseDown, true);
                addEvent(self.el, 'change', self._onChange);

                if (opts.field) {
                    if (opts.bound) {
                        document.body.appendChild(self.el);
                    } else {
                        opts.field.parentNode.insertBefore(self.el, opts.field.nextSibling);
                    }
                    addEvent(opts.field, 'change', self._onInputChange);

                    if (!opts.defaultDate) {
                        if (hasMoment && opts.field.value) {
                            opts.defaultDate = moment(opts.field.value, opts.format).toDate();
                        } else {
                            opts.defaultDate = new Date(Date.parse(opts.field.value));
                        }
                        opts.setDefaultDate = true;
                    }
                }

                var defDate = opts.defaultDate;

                if (isDate(defDate)) {
                    if (opts.setDefaultDate) {
                        self.setDate(defDate, true);
                    } else {
                        self.gotoDate(defDate);
                    }
                } else {
                    self.gotoDate(new Date());
                }

                if (opts.bound) {
                    this.hide();
                    self.el.className += ' is-bound';
                    addEvent(opts.trigger, 'click', self._onInputClick);
                    addEvent(opts.trigger, 'focus', self._onInputFocus);
                    addEvent(opts.trigger, 'blur', self._onInputBlur);
                } else {
                    this.show();
                }
            };


            /**
             * public Pikaday API
             */
            Pikaday.prototype = {


                /**
                 * configure functionality
                 */
                config: function(options)
                {
                    if (!this._o) {
                        this._o = extend({}, defaults, true);
                    }

                    var opts = extend(this._o, options, true);

                    opts.isRTL = !!opts.isRTL;

                    opts.field = (opts.field && opts.field.nodeName) ? opts.field : null;

                    opts.bound = !!(opts.bound !== undefined ? opts.field && opts.bound : opts.field);

                    opts.trigger = (opts.trigger && opts.trigger.nodeName) ? opts.trigger : opts.field;

                    var nom = parseInt(opts.numberOfMonths, 10) || 1;
                    opts.numberOfMonths = nom > 4 ? 4 : nom;

                    if (!isDate(opts.minDate)) {
                        opts.minDate = false;
                    }
                    if (!isDate(opts.maxDate)) {
                        opts.maxDate = false;
                    }
                    if ((opts.minDate && opts.maxDate) && opts.maxDate < opts.minDate) {
                        opts.maxDate = opts.minDate = false;
                    }
                    if (opts.minDate) {
                        setToStartOfDay(opts.minDate);
                        opts.minYear  = opts.minDate.getFullYear();
                        opts.minMonth = opts.minDate.getMonth();
                    }
                    if (opts.maxDate) {
                        setToStartOfDay(opts.maxDate);
                        opts.maxYear  = opts.maxDate.getFullYear();
                        opts.maxMonth = opts.maxDate.getMonth();
                    }

                    if (isArray(opts.yearRange)) {
                        var fallback = new Date().getFullYear() - 10;
                        opts.yearRange[0] = parseInt(opts.yearRange[0], 10) || fallback;
                        opts.yearRange[1] = parseInt(opts.yearRange[1], 10) || fallback;
                    } else {
                        opts.yearRange = Math.abs(parseInt(opts.yearRange, 10)) || defaults.yearRange;
                        if (opts.yearRange > 100) {
                            opts.yearRange = 100;
                        }
                    }

                    return opts;
                },

                /**
                 * return a formatted string of the current selection (using Moment.js if available)
                 */
                toString: function(format)
                {
                    return !isDate(this._d) ? '' : hasMoment ? moment(this._d).format(format || this._o.format) : this._d.toDateString();
                },

                /**
                 * return a Moment.js object of the current selection (if available)
                 */
                getMoment: function()
                {
                    return hasMoment ? moment(this._d) : null;
                },

                /**
                 * set the current selection from a Moment.js object (if available)
                 */
                setMoment: function(date, preventOnSelect)
                {
                    if (hasMoment && moment.isMoment(date)) {
                        this.setDate(date.toDate(), preventOnSelect);
                    }
                },

                /**
                 * return a Date object of the current selection
                 */
                getDate: function()
                {
                    return isDate(this._d) ? new Date(this._d.getTime()) : null;
                },

                /**
                 * set the current selection
                 */
                setDate: function(date, preventOnSelect)
                {
                    if (!date) {
                        this._d = null;
                        return this.draw();
                    }
                    if (typeof date === 'string') {
                        date = new Date(Date.parse(date));
                    }
                    if (!isDate(date)) {
                        return;
                    }

                    var min = this._o.minDate,
                        max = this._o.maxDate;

                    if (isDate(min) && date < min) {
                        date = min;
                    } else if (isDate(max) && date > max) {
                        date = max;
                    }

                    this._d = new Date(date.getTime());
                    setToStartOfDay(this._d);
                    this.gotoDate(this._d);

                    if (this._o.field) {
                        this._o.field.value = this.toString();
                        fireEvent(this._o.field, 'change', { firedBy: this });
                    }
                    if (!preventOnSelect && typeof this._o.onSelect === 'function') {
                        this._o.onSelect.call(this, this.getDate());
                    }
                },

                /**
                 * change view to a specific date
                 */
                gotoDate: function(date)
                {
                    var newCalendar = true;

                    if (!isDate(date)) {
                        return;
                    }

                    if (this.calendars) {
                        var firstVisibleDate = new Date(this.calendars[0].year, this.calendars[0].month, 1),
                            lastVisibleDate = new Date(this.calendars[this.calendars.length-1].year, this.calendars[this.calendars.length-1].month, 1),
                            visibleDate = date.getTime();
                        // get the end of the month
                        lastVisibleDate.setMonth(lastVisibleDate.getMonth()+1);
                        lastVisibleDate.setDate(lastVisibleDate.getDate()-1);
                        newCalendar = (visibleDate < firstVisibleDate.getTime() || lastVisibleDate.getTime() < visibleDate);
                    }

                    if (newCalendar) {
                        this.calendars = [{
                            month: date.getMonth(),
                            year: date.getFullYear()
                        }];
                        if (this._o.mainCalendar === 'right') {
                            this.calendars[0].month += 1 - this._o.numberOfMonths;
                        }
                    }

                    this.adjustCalendars();
                },

                adjustCalendars: function() {
                    this.calendars[0] = adjustCalendar(this.calendars[0]);
                    for (var c = 1; c < this._o.numberOfMonths; c++) {
                        this.calendars[c] = adjustCalendar({
                            month: this.calendars[0].month + c,
                            year: this.calendars[0].year
                        });
                    }
                    this.draw();
                },

                gotoToday: function()
                {
                    this.gotoDate(new Date());
                },

                /**
                 * change view to a specific month (zero-index, e.g. 0: January)
                 */
                gotoMonth: function(month)
                {
                    if (!isNaN(month)) {
                        this.calendars[0].month = parseInt(month, 10);
                        this.adjustCalendars();
                    }
                },

                nextMonth: function()
                {
                    this.calendars[0].month++;
                    this.adjustCalendars();
                },

                prevMonth: function()
                {
                    this.calendars[0].month--;
                    this.adjustCalendars();
                },

                /**
                 * change view to a specific full year (e.g. "2012")
                 */
                gotoYear: function(year)
                {
                    if (!isNaN(year)) {
                        this.calendars[0].year = parseInt(year, 10);
                        this.adjustCalendars();
                    }
                },

                /**
                 * change the minDate
                 */
                setMinDate: function(value)
                {
                    this._o.minDate = value;
                },

                /**
                 * change the maxDate
                 */
                setMaxDate: function(value)
                {
                    this._o.maxDate = value;
                },

                /**
                 * refresh the HTML
                 */
                draw: function(force)
                {
                    if (!this._v && !force) {
                        return;
                    }
                    var opts = this._o,
                        minYear = opts.minYear,
                        maxYear = opts.maxYear,
                        minMonth = opts.minMonth,
                        maxMonth = opts.maxMonth,
                        html = '';

                    if (this._y <= minYear) {
                        this._y = minYear;
                        if (!isNaN(minMonth) && this._m < minMonth) {
                            this._m = minMonth;
                        }
                    }
                    if (this._y >= maxYear) {
                        this._y = maxYear;
                        if (!isNaN(maxMonth) && this._m > maxMonth) {
                            this._m = maxMonth;
                        }
                    }

                    for (var c = 0; c < opts.numberOfMonths; c++) {
                        html += '<div class="pika-lendar">' + renderTitle(this, c, this.calendars[c].year, this.calendars[c].month, this.calendars[0].year) + this.render(this.calendars[c].year, this.calendars[c].month) + '</div>';
                    }

                    this.el.innerHTML = html;

                    if (opts.bound) {
                        if(opts.field.type !== 'hidden') {
                            sto(function() {
                                opts.trigger.focus();
                            }, 1);
                        }
                    }

                    if (typeof this._o.onDraw === 'function') {
                        var self = this;
                        sto(function() {
                            self._o.onDraw.call(self);
                        }, 0);
                    }
                },

                adjustPosition: function()
                {
                    var field = this._o.trigger, pEl = field,
                    width = this.el.offsetWidth, height = this.el.offsetHeight,
                    viewportWidth = window.innerWidth || document.documentElement.clientWidth,
                    viewportHeight = window.innerHeight || document.documentElement.clientHeight,
                    scrollTop = window.pageYOffset || document.body.scrollTop || document.documentElement.scrollTop,
                    left, top, clientRect;

                    if (typeof field.getBoundingClientRect === 'function') {
                        clientRect = field.getBoundingClientRect();
                        left = clientRect.left + window.pageXOffset;
                        top = clientRect.bottom + window.pageYOffset;
                    } else {
                        left = pEl.offsetLeft;
                        top  = pEl.offsetTop + pEl.offsetHeight;
                        while((pEl = pEl.offsetParent)) {
                            left += pEl.offsetLeft;
                            top  += pEl.offsetTop;
                        }
                    }

                    // default position is bottom & left
                    if (left + width > viewportWidth ||
                        (
                            this._o.position.indexOf('right') > -1 &&
                            left - width + field.offsetWidth > 0
                        )
                    ) {
                        left = left - width + field.offsetWidth;
                    }
                    if (top + height > viewportHeight + scrollTop ||
                        (
                            this._o.position.indexOf('top') > -1 &&
                            top - height - field.offsetHeight > 0
                        )
                    ) {
                        top = top - height - field.offsetHeight;
                    }
                    this.el.style.cssText = [
                        'position: absolute',
                        'left: ' + left + 'px',
                        'top: ' + top + 'px'
                    ].join(';');
                },

                /**
                 * render HTML for a particular month
                 */
                render: function(year, month)
                {
                    var opts   = this._o,
                        now    = new Date(),
                        days   = getDaysInMonth(year, month),
                        before = new Date(year, month, 1).getDay(),
                        data   = [],
                        row    = [];
                    setToStartOfDay(now);
                    if (opts.firstDay > 0) {
                        before -= opts.firstDay;
                        if (before < 0) {
                            before += 7;
                        }
                    }
                    var cells = days + before,
                        after = cells;
                    while(after > 7) {
                        after -= 7;
                    }
                    cells += 7 - after;
                    for (var i = 0, r = 0; i < cells; i++)
                    {
                        var day = new Date(year, month, 1 + (i - before)),
                            isDisabled = (opts.minDate && day < opts.minDate) || (opts.maxDate && day > opts.maxDate),
                            isSelected = isDate(this._d) ? compareDates(day, this._d) : false,
                            isToday = compareDates(day, now),
                            isEmpty = i < before || i >= (days + before);

                        row.push(renderDay(1 + (i - before), month, year, isSelected, isToday, isDisabled, isEmpty));

                        if (++r === 7) {
                            data.push(renderRow(row, opts.isRTL));
                            row = [];
                            r = 0;
                        }
                    }
                    return renderTable(opts, data);
                },

                isVisible: function()
                {
                    return this._v;
                },

                show: function()
                {
                    if (!this._v) {
                        removeClass(this.el, 'is-hidden');
                        this._v = true;
                        this.draw();
                        if (this._o.bound) {
                            addEvent(document, 'click', this._onClick);
                            this.adjustPosition();
                        }
                        if (typeof this._o.onOpen === 'function') {
                            this._o.onOpen.call(this);
                        }
                    }
                },

                hide: function()
                {
                    var v = this._v;
                    if (v !== false) {
                        if (this._o.bound) {
                            removeEvent(document, 'click', this._onClick);
                        }
                        this.el.style.cssText = '';
                        addClass(this.el, 'is-hidden');
                        this._v = false;
                        if (v !== undefined && typeof this._o.onClose === 'function') {
                            this._o.onClose.call(this);
                        }
                    }
                },

                /**
                 * GAME OVER
                 */
                destroy: function()
                {
                    this.hide();
                    removeEvent(this.el, 'mousedown', this._onMouseDown, true);
                    removeEvent(this.el, 'change', this._onChange);
                    if (this._o.field) {
                        removeEvent(this._o.field, 'change', this._onInputChange);
                        if (this._o.bound) {
                            removeEvent(this._o.trigger, 'click', this._onInputClick);
                            removeEvent(this._o.trigger, 'focus', this._onInputFocus);
                            removeEvent(this._o.trigger, 'blur', this._onInputBlur);
                        }
                    }
                    if (this.el.parentNode) {
                        this.el.parentNode.removeChild(this.el);
                    }
                }

            };

            return Pikaday;

        }));

        
        var picker;
        var check_in_dates = document.getElementsByClassName('check-in-date');
        for (var i = 0; i < check_in_dates.length; i++) {
            picker = new Pikaday({ 
                format: 'YYYY-MM-DD',
                    field: check_in_dates[i]
            });
        }
        var check_out_dates = document.getElementsByClassName('check-out-date');
        for (var i = 0; i < check_out_dates.length; i++) {
            picker = new Pikaday({ 
                format: 'YYYY-MM-DD',
                    field: check_out_dates[i]
            });
        }
        
        
        // Load CSS
        if(document.createStyleSheet) {
          document.createStyleSheet('https://v2.roomsy.com/css/widget.css');
        }
        else {
          var styles = "@import url('https://v2.roomsy.com/css/widget.css');";
          var newSS=document.createElement('link');
          newSS.rel='stylesheet';
          newSS.href='data:text/css,'+escape(styles);
          document.getElementsByTagName("head")[0].appendChild(newSS);
        }

    }
}

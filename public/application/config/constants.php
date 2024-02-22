<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

/*
|--------------------------------------------------------------------------
| Booking States
|--------------------------------------------------------------------------
|
|
*/

define('ALL', 				'-1'); // ALL
define('RESERVATION', 				'0'); // CONFIRMED RESERVATION
define('INHOUSE',						'1');
define('CHECKOUT', 					'2');
define('OUT_OF_ORDER', 				'3');
define('CANCELLED', 					'4'); // CANCELLED RESERVATION
define('NO_SHOW', 						'5'); // Reservation that did not show
define('DELETED', 						'6'); // Deleted Out of Order
define('UNCONFIRMED_RESERVATION',	'7'); // UNCONFIRMED RESERVATION
define('DEPARTURE',	'10'); // DEPARTURE RESERVATION
define('ARRIVALS',	'11'); // ARRIVALS RESERVATION

define('DELETED_RESERVATION', '9');
define('DELETED_INHOUSE', '10');
define('DELETED_CHECKOUT', '11');
define('DELETED_OUT_OF_ORDER', '12');

/*
|--------------------------------------------------------------------------
| Booking Source
|--------------------------------------------------------------------------
|*/

define('SOURCE_WALK_IN', '0');
define('SOURCE_ONLINE_WIDGET', '-1');
define('SOURCE_BOOKING_DOT_COM', '-2');
define('SOURCE_EXPEDIA', '-3');
define('SOURCE_AGODA', '-4');
define('SOURCE_TRIPCONNECT', '-5');
define('SOURCE_AIRBNB', '-6');
define('SOURCE_HOSTELWORLD', '-7');
define('SOURCE_MYALLOCATOR', '-8');
define('SOURCE_COMPANY', '-9');
define('SOURCE_GUEST_MEMBER', '-10');
define('SOURCE_OWNER', '-11');
define('SOURCE_RETURNING_GUEST', '-12');
define('SOURCE_APARTMENT', '-13');
define('SOURCE_SITEMINDER', '-14');
define('SOURCE_SEASONAL', '-15');
define('SOURCE_OTHER_TRAVEL_AGENCY', '-20');
define('SOURCE_CHANNEX', '-16');

// these booking sources are hardcoded and common for each user
define('COMMON_BOOKING_SOURCES', 
        json_encode(array(
            SOURCE_WALK_IN => 'Walk-in / Telephone',
            SOURCE_ONLINE_WIDGET => 'Online Booking Engine',
            SOURCE_BOOKING_DOT_COM => 'Booking.com',
            SOURCE_EXPEDIA => 'Expedia',
            SOURCE_AGODA => 'Agoda',
            SOURCE_TRIPCONNECT => 'Trip Connect',
            SOURCE_AIRBNB => 'AirBNB',
            SOURCE_HOSTELWORLD => 'Hostelworld',
            SOURCE_MYALLOCATOR => 'Myallocator',
            SOURCE_COMPANY => 'Company',
            SOURCE_GUEST_MEMBER => 'Guest Member',
            SOURCE_OWNER => 'Owner',
            SOURCE_RETURNING_GUEST => 'Returning Guest',
            SOURCE_APARTMENT => 'Apartment',
            SOURCE_OTHER_TRAVEL_AGENCY => 'Other Travel Agency',
            SOURCE_SITEMINDER => 'Siteminder',
            SOURCE_SEASONAL => 'seasonal.io',
            SOURCE_CHANNEX => 'Channex'
        ))
    );

// Customer Types
define('BLACKLIST', '-1');
define('VIP', '-2');

// these customer types are hardcoded and common for each user
define('COMMON_CUSTOMER_TYPES', 
        json_encode(array(
            BLACKLIST => 'Blacklist',
            VIP => 'VIP'
        ))
    );

/*
|--------------------------------------------------------------------------
| Booking
|--------------------------------------------------------------------------
|
|
*/
define('MAX_NUMBER_OF_ADULTS', 								'10');
define('MAX_NUMBER_OF_CHILDREN', 							'10');

/*
|--------------------------------------------------------------------------
| DAYS OF WEEK
|--------------------------------------------------------------------------
|
|
*/
define('SUNDAY',				 							'1');
define('MONDAY', 											'2');
define('TUESDAY', 											'3');
define('WEDNESDAY', 										'4');
define('THURSDAY', 											'5');
define('FRIDAY', 											'6');
define('SATURDAY', 											'7');



/*
|--------------------------------------------------------------------------
| Booking & invoice log
|--------------------------------------------------------------------------
|
|
*/
define('SYSTEM_LOG', 								'0');
define('USER_LOG', 							'1');
define('INVOICE_LOG', 							'2');

/*
|--------------------------------------------------------------------------
| Invoice logs
|--------------------------------------------------------------------------
|
|
*/
define('ADD_CHARGE', '1');
define('EDIT_CHARGE', '2');
define('DELETE_CHARGE', '3');
define('ADD_PAYMENT', '4');
define('REFUND_FULL_PAYMENT', '5');
define('DELETE_PAYMENT', '6');
define('REFUND_PARTIAL_PAYMENT', '7');
define('AUTHORIZED_PAYMENT', '8');
define('CAPTURED_PAYMENT', '9');
define('ADD_FOLIO', '10');
define('UPDATE_FOLIO', '11');
define('DELETE_FOLIO', '12');
define('VOID_PAYMENT', '13');

/*
|--------------------------------------------------------------------------
| AWS
|--------------------------------------------------------------------------
|
|
*/
define('AWS_ACCESS_KEY', isset($_SERVER['AWS_ACCESS_KEY']) ? $_SERVER['AWS_ACCESS_KEY'] : '');
define('AWS_SECRET_KEY', isset($_SERVER['AWS_SECRET_KEY']) ? $_SERVER['AWS_SECRET_KEY'] : '');

/*
|--------------------------------------------------------------------------
| Image linking
|--------------------------------------------------------------------------
|
|
*/

define('COMPANY_LOGO',				'0'); // CONFIRMED RESERVATION
define('RATE_PLAN_FULLSIZE',		'1');
define('RATE_PLAN_THUMBNAIL', 	'2');
define('SLIDE', '3');
define('GALLERY', '4');

/*
|--------------------------------------------------------------------------
| Image linking
|--------------------------------------------------------------------------
|
|
*/

define('LOGO_IMAGE_TYPE_ID',				'1');
define('GALLERY_IMAGE_TYPE_ID',				'2');
define('SLIDE_IMAGE_TYPE_ID', 				'3');
define('RATE_PLAN_IMAGE_TYPE_ID', 			'4');
define('ROOM_TYPE_IMAGE_TYPE_ID', 			'5');
define('VENDOR_LOGO_IMAGE_TYPE_ID', 	    '6');


/*
|--------------------------------------------------------------------------
| Pay Periods
|--------------------------------------------------------------------------
|
|
*/

define('DAILY',				'0');
define('WEEKLY',			'1');
define('MONTHLY',			'2');
define('ONE_TIME',			'3');


/*
|--------------------------------------------------------------------------
| Subscription Level
|--------------------------------------------------------------------------
|
|
*/
define('BASIC',				'0');
define('PREMIUM',			'1');
define('ELITE',             '2');
define('STARTER',			'3');


/*
|--------------------------------------------------------------------------
| TokenEx transaction types
|--------------------------------------------------------------------------
|
|
*/
define('TOKENEX_PAYMENT_AUTHORIZE',     	'1');
define('TOKENEX_PAYMENT_CAPTURE',			'2');
define('TOKENEX_PAYMENT_PURCHASE',			'3');
define('TOKENEX_PAYMENT_REFUND',			'4');
define('TOKENEX_PAYMENT_VOID',      		'5');
define('TOKENEX_PAYMENT_REVERSE',			'6');
 
/*
|-Tokenex Token Scheme
|--------------------------------------------------------------------------
|
|
*/
define('TOKEN_SCHEME_TOKEN', '22');	


/*
|--------------------------------------------------------------------------
| Date when the latest Terms of Service & Privacy Policy are published on
|--------------------------------------------------------------------------
|
|
*/

define('TOS_PUBLISH_DATE', 				'2014-02-14'); // CONFIRMED RESERVATION
/*
|--------------------------------------------------------------------------
| Support
|--------------------------------------------------------------------------
|
|
*/

define('SUPPORT_PHONE_NUMBER', 				'1 (403) 879-1166'); // Deleted Out of Order

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
|
*/

define('SUPER_ADMIN_USER_ID', 				'1');

define('SUPER_ADMIN', 'support@minical.io');

/*
|--------------------------------------------------------------------------
| Default Currency USD
|--------------------------------------------------------------------------
|
*/
define('DEFAULT_CURRENCY', 				'151');


define('SYSTEM_ROOM_NO_TAX',              '8693');


//Define the proper DOCUMENT_ROOT
if (!isset($_SERVER['SERVER_NAME']) or $_SERVER['SERVER_NAME'] == 'localhost') {
	define('DOC_ROOT', 'C:\xampp\htdocs\innGrid\\');
} else {
	define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
}

// Define Ajax Request (this is used to prevent ajax and codeigniter sess with database issues)
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
//  

// these customer fields are hardcoded and common for each user

define('FIELD_NAME', '-1');
define('FIELD_CUSTOMER_TYPE', '-2');
define('FIELD_EMAIL', '-3');
define('FIELD_PHONE', '-4');
define('FIELD_PHONE_TWO', '-5');
define('FIELD_FAX', '-6');
define('FIELD_ADDRESS', '-7');
define('FIELD_ADDRESS_TWO', '-8');
define('FIELD_CITY', '-9');
define('FIELD_REGION', '-10');
define('FIELD_COUNTRY', '-11');
define('FIELD_POSTAL_CODE', '-12');
define('FIELD_NOTES', '-13');

define('COMMON_CUSTOMER_FIELDS',
        json_encode(array(
            FIELD_NAME => 'Name',
            FIELD_CUSTOMER_TYPE => 'Customer Type',
            FIELD_EMAIL => 'Email',
            FIELD_PHONE => 'Phone',
            FIELD_PHONE_TWO => 'Phone 2',
            FIELD_FAX => 'Fax',
            FIELD_ADDRESS => 'Address',
            FIELD_ADDRESS_TWO => 'Address 2',
            FIELD_CITY => 'City',
            FIELD_REGION => 'Region',
            FIELD_COUNTRY => 'Country',
            FIELD_POSTAL_CODE => 'Postal Code',
            FIELD_NOTES => 'Notes',
        ))
    );

define('COMMON_CUSTOMER_DB_FIELDS',
        json_encode(array(
            FIELD_NAME => 'customer_name',
            FIELD_CUSTOMER_TYPE => 'customer_type',
            FIELD_EMAIL => 'email',
            FIELD_PHONE => 'phone',
            FIELD_PHONE_TWO => 'phone2',
            FIELD_FAX => 'fax',
            FIELD_ADDRESS => 'address',
            FIELD_ADDRESS_TWO => 'address2',
            FIELD_CITY => 'city',
            FIELD_REGION => 'region',
            FIELD_COUNTRY => 'country',
            FIELD_POSTAL_CODE => 'postal_code',
            FIELD_NOTES => 'customer_notes',
        ))
    );

// these booking engine fields are hardcoded and common for each user

define('BOOKING_FIELD_NAME', '-1');
define('BOOKING_FIELD_EMAIL', '-2');
define('BOOKING_FIELD_PHONE', '-3');
define('BOOKING_FIELD_ADDRESS', '-4');
define('BOOKING_FIELD_CITY', '-5');
define('BOOKING_FIELD_REGION', '-6');
define('BOOKING_FIELD_COUNTRY', '-7');
define('BOOKING_FIELD_POSTAL_CODE', '-8');
define('BOOKING_FIELD_SPECIAL_REQUEST', '-9');

define('COMMON_BOOKING_ENGINE_FIELDS',
        json_encode(array(
            BOOKING_FIELD_NAME => 'Full Name',
            BOOKING_FIELD_EMAIL => 'Email',
            BOOKING_FIELD_PHONE => 'Phone',
            BOOKING_FIELD_ADDRESS => 'Address',
            BOOKING_FIELD_CITY => 'City',
            BOOKING_FIELD_REGION => 'State Region/Province',
            BOOKING_FIELD_COUNTRY => 'Country',
            BOOKING_FIELD_POSTAL_CODE => 'Zip/Postal Code',
            BOOKING_FIELD_SPECIAL_REQUEST => 'Special Requests'
        ))
    );

/*
|--------------------------------------------------------------------------
| User Interface Themes
|--------------------------------------------------------------------------
|*/
define('THEME_DEFAULT', '0');
define('THEME_DARK', '1');




/* 
    XML REQUEST TYPE
*/
define('AVAILABILITY_UPDATE', '0');
define('RATE_UPDATE', '1');
define('BOOKING_RETRIEVAL', '2');
define('GET_ROOM_TYPES_AND_RATES', '3');
define('BOOKING_CONFIRMATION', '4');

/* 
    XML RESPONSE TYPE
*/
define('SUCCESS', '0');
define('ERROR', '1');
define('WARNING', '2');






//build json constant
define('BUILD_KEY_MAPPING', 
        json_encode(array(
            'automatic_email_confirmation'         => 'automatic_email_confirmation',
            'automatic_email_cancellation'         => 'automatic_email_cancellation',
            'send_booking_notes'                   => 'send_booking_notes',
            'additional_email_send_copy_to_additional_emails'       => 'send_copy_to_additional_emails',
            'additional_email_additional_company_emails'            => 'additional_company_emails',
            'send_invoice_email_automatically'     => 'send_invoice_email_automatically',
            'hide_room_name'                       => 'hide_room_name',
            'display_tooltip'                      => 'is_display_tooltip',
            'allow_guest_field_mandatory'           => 'make_guest_field_mandatory',
            'allow_booking_cancelled_with_balance' => 'booking_cancelled_with_balance',
            'include_cancelled_noshow_bookings'    => 'include_cancelled_noshow_bookings',
            'enable_hourly_booking'                => 'enable_hourly_booking',
            'allow_free_bookings'                  => 'allow_free_bookings',
            'restrict_booking_dates_modification'  => 'restrict_booking_dates_modification',
            'restrict_checkout_with_balance'       => 'restrict_checkout_with_balance',
            'restrict_cvc_not_mandatory'           => 'restrict_cvc_not_mandatory',
            'restrict_edit_after_checkout'         => 'restrict_edit_after_checkout',
            'allow_change_previous_booking_status' => 'allow_change_previous_booking_status',
            'auto_no_show'                         => 'auto_no_show',
            'force_room_selection'                 => 'force_room_selection',
            'hide_forecast_charges'                => 'hide_forecast_charges',
            'total_balance_include_forecast'    => 'is_total_balance_include_forecast',
            'show_guest_group_invoice'             => 'show_guest_group_invoice',
            'enable_new_calendar'                  => 'enable_new_calendar',
            'unit_name_singular'                    => 'default_room_singular',
            'unit_name_plural'                      => 'default_room_plural',
            'unit_type_name_singular'               => 'default_room_type'
        ))
    );

/*
|--------------------------------------------------------------------------
| Minor Unites for payu
|--------------------------------------------------------------------------
|*/
define('CURRENCY_JSON',
        '{"0":{"currency":"AFN","minor_unit":"2"},"1":{"currency":"EUR","minor_unit":"2"},"2":{"currency":"ALL","minor_unit":"2"},"3":{"currency":"DZD","minor_unit":"2"},"4":{"currency":"USD","minor_unit":"2"},"6":{"currency":"AOA","minor_unit":"2"},"7":{"currency":"XCD","minor_unit":"2"},"9":{"currency":"ARS","minor_unit":"2"},"10":{"currency":"AMD","minor_unit":"2"},"11":{"currency":"AWG","minor_unit":"2"},"12":{"currency":"AUD","minor_unit":"2"},"14":{"currency":"AZN","minor_unit":"2"},"15":{"currency":"BSD","minor_unit":"2"},"16":{"currency":"BHD","minor_unit":"3"},"17":{"currency":"BDT","minor_unit":"2"},"18":{"currency":"BBD","minor_unit":"2"},"19":{"currency":"BYN","minor_unit":"2"},"21":{"currency":"BZD","minor_unit":"2"},"22":{"currency":"XOF","minor_unit":"0"},"23":{"currency":"BMD","minor_unit":"2"},"24":{"currency":"INR","minor_unit":"2"},"25":{"currency":"BTN","minor_unit":"2"},"26":{"currency":"BOB","minor_unit":"2"},"27":{"currency":"BOV","minor_unit":"2"},"29":{"currency":"BAM","minor_unit":"2"},"30":{"currency":"BWP","minor_unit":"2"},"31":{"currency":"NOK","minor_unit":"2"},"32":{"currency":"BRL","minor_unit":"2"},"34":{"currency":"BND","minor_unit":"2"},"35":{"currency":"BGN","minor_unit":"2"},"37":{"currency":"BIF","minor_unit":"0"},"38":{"currency":"CVE","minor_unit":"2"},"39":{"currency":"KHR","minor_unit":"2"},"40":{"currency":"XAF","minor_unit":"0"},"41":{"currency":"CAD","minor_unit":"2"},"42":{"currency":"KYD","minor_unit":"2"},"45":{"currency":"CLP","minor_unit":"0"},"46":{"currency":"CLF","minor_unit":"4"},"47":{"currency":"CNY","minor_unit":"2"},"50":{"currency":"COP","minor_unit":"2"},"51":{"currency":"COU","minor_unit":"2"},"52":{"currency":"KMF","minor_unit":"0"},"53":{"currency":"CDF","minor_unit":"2"},"55":{"currency":"NZD","minor_unit":"2"},"56":{"currency":"CRC","minor_unit":"2"},"58":{"currency":"HRK","minor_unit":"2"},"59":{"currency":"CUP","minor_unit":"2"},"60":{"currency":"CUC","minor_unit":"2"},"61":{"currency":"ANG","minor_unit":"2"},"63":{"currency":"CZK","minor_unit":"2"},"64":{"currency":"DKK","minor_unit":"2"},"65":{"currency":"DJF","minor_unit":"0"},"67":{"currency":"DOP","minor_unit":"2"},"69":{"currency":"EGP","minor_unit":"2"},"70":{"currency":"SVC","minor_unit":"2"},"73":{"currency":"ERN","minor_unit":"2"},"75":{"currency":"ETB","minor_unit":"2"},"77":{"currency":"FKP","minor_unit":"2"},"79":{"currency":"FJD","minor_unit":"2"},"83":{"currency":"XPF","minor_unit":"0"},"86":{"currency":"GMD","minor_unit":"2"},"87":{"currency":"GEL","minor_unit":"2"},"89":{"currency":"GHS","minor_unit":"2"},"90":{"currency":"GIP","minor_unit":"2"},"96":{"currency":"GTQ","minor_unit":"2"},"97":{"currency":"GBP","minor_unit":"2"},"98":{"currency":"GNF","minor_unit":"0"},"100":{"currency":"GYD","minor_unit":"2"},"101":{"currency":"HTG","minor_unit":"2"},"105":{"currency":"HNL","minor_unit":"2"},"106":{"currency":"HKD","minor_unit":"2"},"107":{"currency":"HUF","minor_unit":"2"},"108":{"currency":"ISK","minor_unit":"0"},"110":{"currency":"IDR","minor_unit":"2"},"112":{"currency":"IRR","minor_unit":"2"},"113":{"currency":"IQD","minor_unit":"3"},"116":{"currency":"ILS","minor_unit":"2"},"118":{"currency":"JMD","minor_unit":"2"},"119":{"currency":"JPY","minor_unit":"0"},"121":{"currency":"JOD","minor_unit":"3"},"122":{"currency":"KZT","minor_unit":"2"},"123":{"currency":"KES","minor_unit":"2"},"125":{"currency":"KPW","minor_unit":"2"},"126":{"currency":"KRW","minor_unit":"0"},"127":{"currency":"KWD","minor_unit":"3"},"128":{"currency":"KGS","minor_unit":"2"},"129":{"currency":"LAK","minor_unit":"2"},"131":{"currency":"LBP","minor_unit":"2"},"132":{"currency":"LSL","minor_unit":"2"},"133":{"currency":"ZAR","minor_unit":"2"},"134":{"currency":"LRD","minor_unit":"2"},"135":{"currency":"LYD","minor_unit":"3"},"136":{"currency":"CHF","minor_unit":"2"},"139":{"currency":"MOP","minor_unit":"2"},"140":{"currency":"MKD","minor_unit":"2"},"141":{"currency":"MGA","minor_unit":"2"},"142":{"currency":"MWK","minor_unit":"2"},"143":{"currency":"MYR","minor_unit":"2"},"144":{"currency":"MVR","minor_unit":"2"},"149":{"currency":"MRU","minor_unit":"2"},"150":{"currency":"MUR","minor_unit":"2"},"153":{"currency":"MXN","minor_unit":"2"},"154":{"currency":"MXV","minor_unit":"2"},"156":{"currency":"MDL","minor_unit":"2"},"158":{"currency":"MNT","minor_unit":"2"},"161":{"currency":"MAD","minor_unit":"2"},"162":{"currency":"MZN","minor_unit":"2"},"163":{"currency":"MMK","minor_unit":"2"},"164":{"currency":"NAD","minor_unit":"2"},"167":{"currency":"NPR","minor_unit":"2"},"171":{"currency":"NIO","minor_unit":"2"},"173":{"currency":"NGN","minor_unit":"2"},"178":{"currency":"OMR","minor_unit":"3"},"179":{"currency":"PKR","minor_unit":"2"},"181":{"currency":"PAB","minor_unit":"2"},"183":{"currency":"PGK","minor_unit":"2"},"184":{"currency":"PYG","minor_unit":"0"},"185":{"currency":"PEN","minor_unit":"2"},"186":{"currency":"PHP","minor_unit":"2"},"188":{"currency":"PLN","minor_unit":"2"},"191":{"currency":"QAR","minor_unit":"2"},"193":{"currency":"RON","minor_unit":"2"},"194":{"currency":"RUB","minor_unit":"2"},"195":{"currency":"RWF","minor_unit":"0"},"197":{"currency":"SHP","minor_unit":"2"},"203":{"currency":"WST","minor_unit":"2"},"205":{"currency":"STN","minor_unit":"2"},"206":{"currency":"SAR","minor_unit":"2"},"208":{"currency":"RSD","minor_unit":"2"},"209":{"currency":"SCR","minor_unit":"2"},"210":{"currency":"SLL","minor_unit":"2"},"211":{"currency":"SGD","minor_unit":"2"},"216":{"currency":"SBD","minor_unit":"2"},"217":{"currency":"SOS","minor_unit":"2"},"219":{"currency":"SSP","minor_unit":"2"},"221":{"currency":"LKR","minor_unit":"2"},"222":{"currency":"SDG","minor_unit":"2"},"223":{"currency":"SRD","minor_unit":"2"},"225":{"currency":"SZL","minor_unit":"2"},"226":{"currency":"SEK","minor_unit":"2"},"228":{"currency":"CHE","minor_unit":"2"},"229":{"currency":"CHW","minor_unit":"2"},"230":{"currency":"SYP","minor_unit":"2"},"231":{"currency":"TWD","minor_unit":"2"},"232":{"currency":"TJS","minor_unit":"2"},"233":{"currency":"TZS","minor_unit":"2"},"234":{"currency":"THB","minor_unit":"2"},"238":{"currency":"TOP","minor_unit":"2"},"239":{"currency":"TTD","minor_unit":"2"},"240":{"currency":"TND","minor_unit":"3"},"241":{"currency":"TRY","minor_unit":"2"},"242":{"currency":"TMT","minor_unit":"2"},"245":{"currency":"UGX","minor_unit":"0"},"246":{"currency":"UAH","minor_unit":"2"},"247":{"currency":"AED","minor_unit":"2"},"251":{"currency":"USN","minor_unit":"2"},"252":{"currency":"UYU","minor_unit":"2"},"253":{"currency":"UYI","minor_unit":"0"},"254":{"currency":"UZS","minor_unit":"2"},"255":{"currency":"VUV","minor_unit":"0"},"256":{"currency":"VEF","minor_unit":"2"},"257":{"currency":"VND","minor_unit":"0"},"262":{"currency":"YER","minor_unit":"2"},"263":{"currency":"ZMW","minor_unit":"2"},"264":{"currency":"ZWL","minor_unit":"2"}}'
    );


/*
|- Application Insights INSTRUMENTATION KEY
|--------------------------------------------------------------------------
*/
// define('INSTRUMENTATION_KEY', $_SERVER['INSTRUMENTATION_KEY']);	
define('INSTRUMENTATION_KEY', isset($_SERVER['INSTRUMENTATION_KEY']) ? $_SERVER['INSTRUMENTATION_KEY'] : '');


define('PAYMENT_GATEWAYS',
        json_encode(array(
            // 'STRIPE' => 'stripe',
            'PAYFLOW' => 'PayflowGateway',
            'FIRST_DATA' => 'FirstdataE4Gateway',
            'CHASE_NET_CONNECT' => 'ChaseNetConnectGateway',
            'AUTHORIZE_NET' => 'AuthorizeNetGateway',
            'PAYU' => 'PayuGateway',
            'QUICKBOOKS' => 'QuickbooksGateway',
            'ELAVON' => 'ElavonGateway',
            'MONERIS' => 'MonerisGateway',
            'CIELO' => 'CieloGateway'
            // SQUARE => 'SquareGateway',
        ))
    );



define('COUNTRIES',
     json_encode(array( 'AF' => 'Afghanistan', 'AX' => 'Åland Islands', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa', 'AD' => 'AndorrA',  'AO' => 'Angola',  'AI' => 'Anguilla',  'AQ' => 'Antarctica',  'AG' => 'Antigua and Barbuda',  'AR' => 'Argentina',  'AM' => 'Armenia',  'AW' => 'Aruba',  'AU' => 'Australia',  'AT' => 'Austria',  'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BQ' => 'Bonaire, Saint Eustatius and Saba', 'BA' => 'Bosnia and Herzegovina', 'BW' => 'Botswana', 'BV' => 'Bouvet Island', 'BR' => 'Brazil', 'IO' => 'British Indian Ocean Territory', 'VG' => 'British Virgin Islands', 'BN' => 'Brunei', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada','CV' => 'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CX' => 'Christmas Island', 'CC' => 'Cocos Islands', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CK' => 'Cook Islands', 'CR' => 'Costa Rica', 'HR' => 'Croatia', 'CU' => 'Cuba', 'CW' => 'Curacao', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'CD' => 'Democratic Republic of the Congo', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'TL' => 'East Timor', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands', 'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'GF' => 'French Guiana', 'PF' => 'French Polynesia', 'TF' => 'French Southern Territories', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GR' => 'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' => 'Guam', 'GT' => 'Guatemala', 'GG' => 'Guernsey', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HM' => 'Heard Island and McDonald Islands', 'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IM' => 'Isle of Man', 'IL' => 'Israel', 'IT' => 'Italy', 'CI' => 'Ivory Coast', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JE' => 'Jersey', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'XK' => 'Kosovo', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Laos', 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macao', 'MK' => 'Macedonia', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MU' => 'Mauritius', 'YT' => 'Mayotte', 'MX' => 'Mexico', 'FM' => 'Micronesia', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'ME' => 'Montenegro', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria', 'NU' => 'Niue', 'NF' => 'Norfolk Island', 'KP' => 'North Korea', 'MP' => 'Northern Mariana Islands', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau', 'PS' => 'Palestinian Territory', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn', 'PL' => 'Poland', 'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'CG' => 'Republic of the Congo', 'RE' => 'Reunion', 'RO' => 'Romania', 'RU' => 'Russia', 'RW' => 'Rwanda', 'BL' => 'Saint Barthelemy', 'SH' => 'Saint Helena', 'KN' => 'Saint Kitts and Nevis', 'LC' => 'Saint Lucia', 'MF' => 'Saint Martin', 'PM' => 'Saint Pierre and Miquelon', 'VC' => 'Saint Vincent and the Grenadines', 'WS' => 'Samoa', 'SM' => 'San Marino', 'ST' => 'Sao Tome and Principe', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SX' => 'Sint Maarten', 'SK' => 'Slovakia', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'GS' => 'South Georgia and the South Sandwich Islands', 'KR' => 'South Korea', 'SS' => 'South Sudan', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard and Jan Mayen', 'SZ' => 'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syria', 'TW' => 'Taiwan', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TG' => 'Togo', 'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad and Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TC' => 'Turks and Caicos Islands', 'TV' => 'Tuvalu', 'VI' => 'U.S. Virgin Islands', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 'US' => 'United States', 'UM' => 'United States Minor Outlying Islands', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VA' => 'Vatican', 'VE' => 'Venezuela', 'VN' => 'Vietnam', 'WF' => 'Wallis and Futuna', 'EH' => 'Western Sahara', 'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe'))
);    

/* End of file constants.php */
/* Location: ./application/config/constants.php */



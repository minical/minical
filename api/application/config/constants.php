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


/* End of file constants.php */
/* Location: ./application/config/constants.php */

define('COMPANY_LOGO',				'0'); // CONFIRMED RESERVATION
define('RATE_PLAN_FULLSIZE',		'1');
define('RATE_PLAN_THUMBNAIL', 	'2');
define('SLIDE', '3');
define('GALLERY', '4');

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
| Booking States
|--------------------------------------------------------------------------
|
|
*/

define('RESERVATION', 				'0'); // CONFIRMED RESERVATION
define('INHOUSE',						'1');
define('CHECKOUT', 					'2');
define('OUT_OF_ORDER', 				'3');
define('CANCELLED', 					'4'); // CANCELLED RESERVATION
define('NO_SHOW', 						'5'); // Reservation that did not show
define('DELETED', 						'6'); // Deleted Out of Order
define('UNCONFIRMED_RESERVATION',	'7'); // UNCONFIRMED RESERVATION

define('DELETED_RESERVATION', '9');
define('DELETED_INHOUSE', '10');
define('DELETED_CHECKOUT', '11');
define('DELETED_OUT_OF_ORDER', '12');

	

/*
|--------------------------------------------------------------------------
| Pay Periods
|--------------------------------------------------------------------------
|
|
*/

define('DAILY', 				'0'); // CONFIRMED RESERVATION
define('WEEKLY',				'1');
define('MONTHLY', 				'2');
define('ONE_TIME', 				'3');	
 
/*
|-Tokenex Token Scheme
|--------------------------------------------------------------------------
|
|
*/
define('TOKEN_SCHEME_TOKEN', '22');	

/*
|--------------------------------------------------------------------------
| Invoice logs
|--------------------------------------------------------------------------
|
|
*/

define('DELETE_BOOKING', '8');


define('ELITE', '2');

define('DEFAULT_CURRENCY',              '151');

/*
|- Application Insights INSTRUMENTATION KEY
|--------------------------------------------------------------------------
*/
//define('INSTRUMENTATION_KEY', $_SERVER['INSTRUMENTATION_KEY']);
define('INSTRUMENTATION_KEY', isset($_SERVER['INSTRUMENTATION_KEY']) ? $_SERVER['INSTRUMENTATION_KEY'] : '');

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
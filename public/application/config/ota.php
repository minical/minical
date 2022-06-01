<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Booking.com credentials
|
| These details are used in authentication to connect to OTA
|--------------------------------------------------------------------------
*/

$config['booking_dot_com_username'] = ''; //getenv("BOOKING_DOT_COM_USERNAME");
$config['booking_dot_com_password'] = ''; //getenv("BOOKING_DOT_COM_PASSWORD");

/*
|--------------------------------------------------------------------------
| Expedia credentials
|
| These details are used in authentication to connect to OTA
|--------------------------------------------------------------------------
*/

$config['expedia_username'] = ''; //getenv("EXPEDIA_USERNAME");
$config['expedia_password'] = ''; //getenv("EXPEDIA_PASSWORD");
/*
|--------------------------------------------------------------------------
| Myallocator credentials
|
| These details are used in authentication to connect to OTA
|--------------------------------------------------------------------------
*/

$config['myallocator_vendor_id'] = ''; //getenv("MYALLOCATOR_VENDOR_ID");
$config['myallocator_vendor_password'] = '';// getenv("MYALLOCATOR_VENDOR_PASSWORD");

/*
|--------------------------------------------------------------------------
| Agoda credentials
|
| These details are used in authentication to connect to OTA
|--------------------------------------------------------------------------
*/

$config['agoda_api_key'] = ''; //getenv("AGODA_API_KEY");
$config['agoda_env'] = ''; //getenv("AGODA_ENV");

/*
|--------------------------------------------------------------------------
| Siteminder credentials
|
| These details are used in authentication to connect to OTA
|--------------------------------------------------------------------------
*/

$config['siteminder_username'] =  getenv("SITEMINDER_USERNAME");
$config['siteminder_password'] =  getenv("SITEMINDER_PASSWORD");
$config['siteminder_requestor_id'] =  ''; //getenv("SITEMINDER_REQUESTOR_ID");

/* End of file ota.php */
/* Location: ./application/config/ota.php */
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/
// $route['/minical/pages/gallery'] = 'Page/_gallery';

// $route['(:any)'] = 'Page/index';
// $route['/'] = 'Page/index';
// $route['default_controller'] = 'page/index';
// $route['index'] = 'page/index';
// $route['gallery'] = 'page/gallery';
// $route['room_types'] = 'page/room_types';
// $route['location'] = 'page/location';


// $route['index/(:any)'] = 'page/index/$1';
// // $route['main/(:any)'] = 'page/main/$1';
// $route['gallery/(:any)'] = 'page/gallery/$1';
// $route['room_types/(:any)'] = 'page/room_types/$1';
// $route['location/(:any)'] = 'page/location/$1';
$route['(:any)/index'] = 'page/index/$1';
$route['(:any)/gallery'] = 'page/gallery/$1';
$route['(:any)/room_types'] = 'page/room_types/$1';
$route['(:any)/location'] = 'page/location/$1';

// $route['send_email/(:any)'] = 'page/send_email/$1';
$route['send_email'] = 'page/send_email';


// $route['hotel_page'] = 'page/hotel_page';




// $route['404_override'] = 'Page';
// $route['default_controller'] = 'Page/index';
// $route['404_override'] = 'Page/index';

//$route['404_override'] = 'errors/page_missing';

/* End of file routes.php */
/* Location: ./application/config/routes.php */

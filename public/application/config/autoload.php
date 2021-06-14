<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| AUTO-LOADER
| -------------------------------------------------------------------
| This file specifies which systems should be loaded by default.
|
| In order to keep the framework as light-weight as possible only the
| absolute minimal resources are loaded by default. For example,
| the database is not connected to automatically since no assumption
| is made regarding whether you intend to use it.  This file lets
| you globally define which systems you would like loaded with every
| request.
|
| -------------------------------------------------------------------
| Instructions
| -------------------------------------------------------------------
|
| These are the things you can load automatically:
|
| 1. Packages
| 2. Libraries
| 3. Helper files
| 4. Custom config files
| 5. Language files
| 6. Models
|
*/

/*
| -------------------------------------------------------------------
|  Auto-load Packges
| -------------------------------------------------------------------
| Prototype:
|
|  $autoload['packages'] = array(APPPATH.'third_party', '/usr/local/shared');
|
*/

$autoload['packages'] = array(APPPATH.'third_party');


/*
| -------------------------------------------------------------------
|  Auto-load Libraries
| -------------------------------------------------------------------
| These are the classes located in the system/libraries folder
| or in your application/libraries folder.
|
| Prototype:
|
|	$autoload['libraries'] = array('database', 'session', 'xmlrpc');
*/

$autoload['libraries'] = array('database', 'session');

/*
| -------------------------------------------------------------------
|  Auto-load Helper Files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['helper'] = array('url', 'file');
*/


$autoload['helper'] = array( 'url', 'form', 'global_helper'); 

$modules = $active_extensions = array();
// load helpers for HMVC (modules)
$modules_path = APPPATH.'extensions/';

if(isset($_COOKIE['active_extensions']) && $_COOKIE['active_extensions']){
    $active_extensions = json_decode($_COOKIE['active_extensions'], true);
}

if($active_extensions){
    foreach ($active_extensions as $key => $extension) {
        $modules[] = $extension['extension_name'];
    }
}


if($modules && count($modules) > 0){
    foreach($modules as $module)
    {
        $extension_helper = array();
        if($module === '.' || $module === '..') continue;
        if(is_dir($modules_path) . '/' . $module)
        {
            $helpers_path = $modules_path . $module . '/config/autoload.php';
            if(file_exists($helpers_path))
            {
                require($helpers_path);

                if($extension_helper && is_array($extension_helper)){
    	            foreach($extension_helper as $key => $extension_helper_item) {
    	                $autoload['helper'][] = '../extensions/'.$module . '/helpers/' . $extension_helper_item;
    	            }
    	        }
            }
            else
            {
                continue;
            }
        }
    }
}

// echo '<pre>'; print_r($autoload); echo '</pre>';  die;

/*
| -------------------------------------------------------------------
|  Auto-load Config files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['config'] = array('config1', 'config2');
|
| NOTE: This item is intended for use ONLY if you have created custom
| config files.  Otherwise, leave it blank.
|
*/

$autoload['config'] = array();


/*
| -------------------------------------------------------------------
|  Auto-load Language files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['language'] = array('lang1', 'lang2');
|
| NOTE: Do not include the "_lang" part of your file.  For example
| "codeigniter_lang.php" would be referenced as array('codeigniter');
|
*/

$autoload['language'] = array();


/*
| -------------------------------------------------------------------
|  Auto-load Models
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['model'] = array('model1', 'model2');
|
*/

$autoload['model'] = array();


/* End of file autoload.php */
/* Location: ./application/config/autoload.php */



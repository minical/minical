<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Super-simple, minimum abstraction MailChimp API v2 wrapper
 * 
 * @author Drew McLellan <drew.mclellan@gmail.com> modified by Ben Bowler <ben.bowler@vice.com>
 * @version 1.0
 */

/**
 * api_key       
 * api_endpoint            
 */

$config['api_key'] = isset($_SERVER["MAILCHIMP_API_KEY"]) ? $_SERVER["MAILCHIMP_API_KEY"] : "";
$config['api_endpoint'] = 'https://us4.api.mailchimp.com/2.0/';
$config['list_id'] = isset($_SERVER["MAILCHIMP_LIST_ID"]) ? $_SERVER["MAILCHIMP_LIST_ID"] : "";

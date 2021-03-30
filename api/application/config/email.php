<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Email
| -------------------------------------------------------------------------
| This file lets you define parameters for sending emails.
| Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/libraries/email.html
|
*/

$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['newline']="\r\n";
$config['protocol'] = 'smtp';
$config['smtp_host']='smtp.sendgrid.com';  
$config['smtp_port']='587';
$config['smtp_timeout']='30';  
$config['smtp_user']=isset($_SERVER["SMTP_USER"]) ? $_SERVER["SMTP_USER"] : '';
$config['smtp_pass']=isset($_SERVER["SMTP_PASS"]) ? $_SERVER["SMTP_PASS"] : '';

/* End of file email.php */
/* Location: ./application/config/email.php */
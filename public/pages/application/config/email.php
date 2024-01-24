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
$config['smtp_user']=getenv('SMTP_USER');//$_SERVER["SMTP_USER"];
$config['smtp_pass']= getenv('SMTP_PASS');

//$config['smtp_user']=$_SERVER["SMTP_USER"];
//$config['smtp_pass']=$_SERVER["SMTP_PASS"];

/* End of file email.php */
/* Location: ./application/config/email.php */

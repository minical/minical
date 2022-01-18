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
$config['newline'] = "\r\n";
$config['protocol'] = 'smtp';
$config['smtp_host'] = getenv('SMTP_HOST') ?: 'smtp.sendgrid.com';
$config['smtp_port'] = getenv('SMTP_PORT') ?: '587';
$config['smtp_timeout'] = 30;
$config['smtp_crypto'] = getenv('SMTP_CRYPTO') ?: ''; //Default: Empty, can be 'ssl' or 'tls' for example
$config['smtp_user'] = getenv('SMTP_USER');
$config['smtp_pass'] = getenv('SMTP_PASS');

/* End of file email.php */
/* Location: ./application/config/email.php */
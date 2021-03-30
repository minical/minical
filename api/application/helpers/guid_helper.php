<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	if ( ! function_exists('generate_guid'))
	{
		// generate GUID
		// pray that it do not collide (very low %, but it can happen, but maybe not before you die).
		// http://en.wikipedia.org/wiki/Universally_unique_identifier#Random_UUID_probability_of_duplicates
		// http://php.net/manual/ru/function.com-create-guid.php
		// http://php.net/manual/en/function.uniqid.php
		function generate_guid()
		{
			return sprintf(
				'%04X%04X%04X%04X%04X%04X%04X%04X',
				mt_rand(0, 65535),
				mt_rand(0, 65535),
				mt_rand(0, 65535),
				mt_rand(16384, 20479),
				mt_rand(32768, 49151),
				mt_rand(0, 65535),
				mt_rand(0, 65535),
				mt_rand(0, 65535)
			);
		}
		
	}

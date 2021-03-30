<?php   
	if (!function_exists('convert_to_local_time'))
	{
		/*
			Converts UTC time to local time using timezoneDB (PECL extension)
			
			Args:
				$time: datetime object in UTC format
				
			Returns:
				$time: datetime object company's local time format
		*/
		
		function convert_to_local_time($time, $time_zone)
		{		
			//Convert time	
			$tz = new DateTimeZone($time_zone);
			$time->setTimezone($tz);					
			return $time;
		}
	}
	if (!function_exists('convert_to_UTC_time'))
	{
		/*
			Converts local time to UTC time using timezoneDB (PECL extension)
			
			Args:
				$time: datetime object in local time
				
			Returns:
				$time: datetime object in UTC format
		*/
		
		function convert_to_UTC_time($time)
		{
			//Convert time	
			$tz = new DateTimeZone('UTC');
			$time->setTimezone($tz);
					
			return $time;
		}
	}

	if (!function_exists('get_timezones'))
	{
	/**
	 * Return an array of timezones
	 * 
	 * @return array
	 */
		function get_timezones()
		{
		    $timezoneIdentifiers = DateTimeZone::listIdentifiers();
		    $utcTime = new DateTime('now', new DateTimeZone('UTC'));
		 
		    $tempTimezones = array();
		    foreach ($timezoneIdentifiers as $timezoneIdentifier) {
		        $currentTimezone = new DateTimeZone($timezoneIdentifier);
		 
		        $tempTimezones[] = array(
		            'offset' => (int)$currentTimezone->getOffset($utcTime),
		            'identifier' => $timezoneIdentifier
		        );
		    }
		 
		    // Sort the array by offset,identifier ascending
		    usort($tempTimezones, function($a, $b) {
				return ($a['offset'] == $b['offset'])
					? strcmp($a['identifier'], $b['identifier'])
					: $a['offset'] - $b['offset'];
		    });
		 
			$timezoneList = array();
		    foreach ($tempTimezones as $tz) {
				$sign = ($tz['offset'] > 0) ? '+' : '-';
				$offset = gmdate('H:i', abs($tz['offset']));
		        $timezoneList[$tz['identifier']] = '(UTC ' . $sign . $offset . ') - ' .
					 (new DateTime('now', new DateTimeZone($tz['identifier'])))->format("F j, Y, g:i a")." - ".
					 $tz['identifier'];
		    }
		 
		    return $timezoneList;
		}
	}

?>
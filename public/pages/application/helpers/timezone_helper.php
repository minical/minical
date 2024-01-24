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

?>
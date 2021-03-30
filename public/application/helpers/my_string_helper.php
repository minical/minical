<?php

/**
 * Repeater function
 *
 * @access	public
 * @param	string
 * @param	string and an integer of maximum allowed substring length
 * @return	substring of original string
 */
if ( ! function_exists('repeater'))
{
	function string_capper($string, $num = 100)
	{
		if (strlen($string) > $num) {		
			return substr($string, 0, $num)."...";
		}
		return $string;
		
	}
}

?>
<?php

	//returns <select> statement.
	//$value contains array of values that are associated with options. (e.g. rtid)
	//For example, option "NDH" would have roomTypeID value of "39"
	// (name, options (e.g. SDD), default(e.g. 3), values (e.g. 3))
	function form_dropdown($name = '', $options = array(), $selected = '',  $value = array())
	{
		if (!$options ) // if array is null, return ''
			return '';

		$form = '<select name="'.$name.'">\n';

		foreach ($options as $key => $val)
		{
			$key = (string) $key;
			$val = (string) $val;

			$sel = ($value[$key] == $selected)?' selected="selected"':'';

			$form .= '<option value="'.$value[$key].'"'.$sel.'>'.$val."</option>\n";
		}

		$form .= '</select>';
		return $form;
	}
	
?>

<?php 

function prx($array, $is_not_die = false)
{
	echo '<pre>';
	print_r($array);
	echo '</pre>';

	$is_not_die ? '' : die;
}

function lq($is_not_die = false)
{
	$ci = &get_instance();
	echo $ci->db->last_query();
	$is_not_die ? '' : die;
}

 ?>
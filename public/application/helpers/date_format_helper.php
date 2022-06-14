<?php

function get_date_div($date_str)
{
	$date = strtotime($date_str);
	$year = date("Y", $date); 
	$month = date("M", $date); 
	$day = date("d", $date);
    $CI =& get_instance();
	if ($CI->enable_hourly_booking) {
        $time = date("h:i A", $date);
        return "$month $day, $time";
    }
    return "$month $day";
}

function get_base_formatted_date($date) {
    $dateAr = explode(' ',$date);
    $dateParts = explode('-',$dateAr[0]);

	$CI =& get_instance();
    $companyDateFormat = $CI->company_date_format ? $CI->company_date_format : 'YY-MM-DD';
    $currentDateFormat = strtolower($companyDateFormat);
    if ($currentDateFormat == 'yy-mm-dd') {
        return $dateParts[0] . '-' . $dateParts[1] . '-' . $dateParts[2];
    }

    if (strpos($date, "-") === 4) {
        // date is YYYY-MM-DD already
        return $dateParts[0] . '-' . $dateParts[1] . '-' . $dateParts[2];
    } elseif ($currentDateFormat == 'dd-mm-yy') {
        return $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
    } elseif ($currentDateFormat == 'mm-dd-yy'){
        return $dateParts[2] . '-' . $dateParts[0] . '-' . $dateParts[1];
    }

    return $date;
}

function get_local_formatted_date($date, $company_date_format = null) {
    $dateAr = explode(' ',$date);
    $dateParts = explode('-',$dateAr[0]);

	$CI =& get_instance();

    if($company_date_format){
        $companyDateFormat = $company_date_format;
    } else {
        $companyDateFormat = isset($CI->company_date_format) && $CI->company_date_format ? $CI->company_date_format : 'YY-MM-DD';
    }
    
    $currentDateFormat = strtolower($companyDateFormat);
    
    if ($currentDateFormat == 'yy-mm-dd') {
        return $dateParts[0] . '-' . $dateParts[1] . '-' . $dateParts[2];
    }

    if (strpos($date, "-") === 4) {
        // date is YYYY-MM-DD already
        if($currentDateFormat == 'dd-mm-yy')
            $date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
        elseif($currentDateFormat == 'mm-dd-yy')
            $date = $dateParts[1] . '-' . $dateParts[2] . '-' . $dateParts[0];
    } elseif($currentDateFormat == 'dd-mm-yy') {
        return $dateParts[0] . '-' . $dateParts[1] . '-' . $dateParts[2];
    } elseif($currentDateFormat == 'mm-dd-yy') {
        return $dateParts[0] . '-' . $dateParts[1] . '-' . $dateParts[2];
    }

    return $date;
}

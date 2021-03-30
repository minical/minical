<?php
	//takes guest_names string which looks like "asdf#asdf#asdf" and guest_count
	//and convert it to something that looks like "asdf and 2 more guests"
	function get_neatly_formatted_customer_names($customer_name, $guest_name, $number_of_staying_guests) {
		
		if ($number_of_staying_guests < 1) 
		{
			return $customer_name;
		} elseif ($number_of_staying_guests == 1)
		{
			return $customer_name." with ".$guest_name;
		} elseif ($number_of_staying_guests > 1)
		{
			return $customer_name.", ".$guest_name." and ".($number_of_staying_guests-1)." other(s)";
		}
		
	}
        
        function get_customer_names($customer_name, $staying_customers, $number_of_staying_guests) {
		
		if ($number_of_staying_guests < 1) 
		{
			return $customer_name;
		} elseif ($number_of_staying_guests == 1)
		{
			return $customer_name." with ".$staying_customers;
		} elseif ($number_of_staying_guests > 1)
		{
			return $customer_name." with ".$staying_customers;
		}
		
	}
        
        function get_customer_fields($customer_name, $staying_customers, $number_of_staying_guests, $customer_field) {
		
		if ($number_of_staying_guests < 1) 
		{
			return $customer_field;
		} elseif ($number_of_staying_guests == 1)
		{
			return $customer_field." with ".$customer_field;
		} elseif ($number_of_staying_guests > 1)
		{
			return $customer_field." with ".$customer_field;
		}
		
	}
?>
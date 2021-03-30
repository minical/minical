<?php 

	/*
		SUMMARY: Checks session to see if user is logged in. If user is not logged in, redirects to login page	
	*/
	function check_login() {
		$CI =& get_instance();
			if ( ! $CI->session->userdata('is_logged_in')) { 
				redirect('/login');
		}
	}
?>
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//This is used to prevent issues between AJAX calls and codeigniter sess with database issues
class MY_Session extends CI_Session 
{

	/**
	* Update an existing session
	*
	* @access    public
	* @return    void
	*/
    function sess_update()
    {       
	   // skip the session update if this is an AJAX call! This is a bug in CI; see:
       // https://github.com/EllisLab/CodeIgniter/issues/154
       // http://codeigniter.com/forums/viewthread/102456/P15
       if ( !($this->CI->input->is_ajax_request()) ) {
           parent::sess_update();
       }
    } 
	
}

/* End of file MY_Session.php */
/* Location: ./application/libraries/MY_Session.php */ 
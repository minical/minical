<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_form_validation extends CI_form_validation {


    function valid_url($str){

           $pattern = "/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";
            if (!preg_match($pattern, $str))
            {
                return FALSE;
            }

            return TRUE;
    }

	/**
     * Error Count
     *
     * Returns the the number of errors
     *
     * @access    public
     * @return    int
     */    
    function error_count()
    {
        return count($this->_error_array); 
    }
} 
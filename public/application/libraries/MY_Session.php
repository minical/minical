<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//This is used to prevent issues between AJAX calls and codeigniter sess with database issues
class MY_Session extends CI_Session 
{

    /**
     * Session Constructor
     *
     * The constructor runs the session routines automatically
     * whenever the class is instantiated.
     */
    public function __construct($params = array())
    {
        log_message('debug', "Session Class Initialized");

        // Set the super object to a local variable for use throughout the class
        $this->CI =& get_instance();

        // Set all the session preferences, which can either be set
        // manually via the $params array above or via the config file
        foreach (array('sess_encrypt_cookie', 'sess_use_database', 'sess_table_name', 'sess_expiration', 'sess_expire_on_close', 'sess_match_ip', 'sess_match_useragent', 'sess_cookie_name', 'cookie_path', 'cookie_domain', 'cookie_secure', 'sess_time_to_update', 'time_reference', 'cookie_prefix', 'encryption_key') as $key)
        {
            $this->$key = (isset($params[$key])) ? $params[$key] : $this->CI->config->item($key);
        }

        if ($this->encryption_key == '')
        {
            show_error('In order to use the Session class you are required to set an encryption key in your config file.');
        }

        // Load the string helper so we can use the strip_slashes() function
        $this->CI->load->helper('string');

        // Do we need encryption? If so, load the encryption class
        if ($this->sess_encrypt_cookie == TRUE)
        {
            $this->CI->load->library('encrypt');
        }

        // Are we using a database?  If so, load it
        if ($this->sess_use_database === TRUE AND $this->sess_table_name != '')
        {
            $this->CI->load->database();
        }

        // Set the "now" time.  Can either be GMT or server time, based on the
        // config prefs.  We use this to set the "last activity" time
        $this->now = $this->_get_time();

        // Set the session length. If the session expiration is
        // set to zero we'll set the expiration two years from now.
        if ($this->sess_expiration == 0)
        {
            $this->sess_expiration = (60*60*24*365*2);
        }

        // Set the cookie name
        $this->sess_cookie_name = $this->cookie_prefix.$this->sess_cookie_name;

        // Run the Session routine. If a session doesn't exist we'll
        // create a new one.  If it does, we'll update it.
        if ( ! $this->sess_read())
        {
            $this->sess_create();
        }
        else
        {
            $this->sess_update();
        }

        // Delete 'old' flashdata (from last request)
        $this->_flashdata_sweep();

        // Mark all new flashdata as old (data will be deleted before next request)
        $this->_flashdata_mark();

        // Delete expired sessions if necessary
        $this->_sess_gc();

        log_message('debug', "Session routines successfully run");
    }


    /**
	* Update an existing session
	*
	* @access    public
	* @return    void
	*/
    function sess_update()
    {
        $db_debug = $this->CI->config->item('db_debug');
        //$this->CI->db->debug_off();

	   // skip the session update if this is an AJAX call! This is a bug in CI; see:
       // https://github.com/EllisLab/CodeIgniter/issues/154
       // http://codeigniter.com/forums/viewthread/102456/P15
       if ( !($this->CI->input->is_ajax_request()) ) {
           parent::sess_update();
       }

        if ($db_debug) {
            $this->CI->db->debug_on();
        }
    }

    /**
     * Create a new session
     *
     * @access	public
     * @return	void
     */
    function sess_create()
    {
        $sessid = '';
        while (strlen($sessid) < 32)
        {
            $sessid .= mt_rand(0, mt_getrandmax());
        }

        // To make the session ID even more secure we'll combine it with the user's IP
        $sessid .= $this->CI->input->ip_address();

        $this->userdata = array(
            'session_id'	=> md5(uniqid($sessid, TRUE)),
            'ip_address'	=> $this->CI->input->ip_address(),
            'user_agent'	=> substr($this->CI->input->user_agent(), 0, 120),
            'last_activity'	=> $this->now,
            'user_data'		=> ''
        );


        // Save the data to the DB if needed
        if ($this->sess_use_database === TRUE)
        {

            $db_debug = $this->CI->config->item('db_debug');
            $this->CI->db->debug_off();

            $this->CI->db->query($this->CI->db->insert_string($this->sess_table_name, $this->userdata));

            $error_message = $this->CI->db->_error_message();
            $error_number = $this->CI->db->_error_number();
            if ($error_number == 1146 && strpos($error_message, ".sessions' doesn't exist")) {
                // redirect to installation page
                if (!(isset($_GET['MIGRATION_REQUEST']) && $_GET['MIGRATION_REQUEST'])) {
                    redirect('/install/index.php');
                }
            }

            if ($db_debug) {
                $this->CI->db->debug_on();
            }
        }

        // Write the cookie
        $this->_set_cookie();
    }


    /**
     * Fetch the current session data if it exists
     *
     * @access	public
     * @return	bool
     */
    function sess_read()
    {
        // Fetch the cookie
        $session = $this->CI->input->cookie($this->sess_cookie_name);

        // No cookie?  Goodbye cruel world!...
        if ($session === FALSE)
        {
            log_message('debug', 'A session cookie was not found.');
            return FALSE;
        }

        // Decrypt the cookie data
        if ($this->sess_encrypt_cookie == TRUE)
        {
            $session = $this->CI->encrypt->decode($session);
        }
        else
        {
            // encryption was not used, so we need to check the md5 hash
            $hash	 = substr($session, strlen($session)-32); // get last 32 chars
            $session = substr($session, 0, strlen($session)-32);

            // Does the md5 hash match?  This is to prevent manipulation of session data in userspace
            if ($hash !==  md5($session.$this->encryption_key))
            {
                log_message('error', 'The session cookie data did not match what was expected. This could be a possible hacking attempt.');
                $this->sess_destroy();
                return FALSE;
            }
        }

        // Unserialize the session array
        $session = $this->_unserialize($session);

        // Is the session data we unserialized an array with the correct format?
        if ( ! is_array($session) OR ! isset($session['session_id']) OR ! isset($session['ip_address']) OR ! isset($session['user_agent']) OR ! isset($session['last_activity']))
        {
            $this->sess_destroy();
            return FALSE;
        }

        // Is the session current?
        if (($session['last_activity'] + $this->sess_expiration) < $this->now)
        {
            $this->sess_destroy();
            return FALSE;
        }

        // Does the IP Match?
        if ($this->sess_match_ip == TRUE AND $session['ip_address'] != $this->CI->input->ip_address())
        {
            $this->sess_destroy();
            return FALSE;
        }

        // Does the User Agent Match?
        if ($this->sess_match_useragent == TRUE AND trim($session['user_agent']) != trim(substr($this->CI->input->user_agent(), 0, 120)))
        {
            $this->sess_destroy();
            return FALSE;
        }

        // Is there a corresponding session in the DB?
        if ($this->sess_use_database === TRUE)
        {
            $this->CI->db->where('session_id', $session['session_id']);

            if ($this->sess_match_ip == TRUE)
            {
                $this->CI->db->where('ip_address', $session['ip_address']);
            }

            if ($this->sess_match_useragent == TRUE)
            {
                $this->CI->db->where('user_agent', $session['user_agent']);
            }

            $db_debug = $this->CI->config->item('db_debug');
            $this->CI->db->debug_off();

            $query = $this->CI->db->get($this->sess_table_name);

            // No result?  Kill it!
            if (!$query || $query->num_rows() == 0)
            {
                $this->sess_destroy();
                $error_message = $this->CI->db->_error_message();
                $error_number = $this->CI->db->_error_number();
                if ($error_number == 1146 && strpos($error_message, ".sessions' doesn't exist")) {
                    // redirect to installation page
                    if (!(isset($_GET['MIGRATION_REQUEST']) && $_GET['MIGRATION_REQUEST'])) {
                        redirect('/install/index.php');
                    }
                }
                return FALSE;
            }

            if ($db_debug) {
                $this->CI->db->debug_on();
            }

            // Is there custom data?  If so, add it to the main session array
            $row = $query->row();
            if (isset($row->user_data) AND $row->user_data != '')
            {
                $custom_data = $this->_unserialize($row->user_data);

                if (is_array($custom_data))
                {
                    foreach ($custom_data as $key => $val)
                    {
                        $session[$key] = $val;
                    }
                }
            }
        }

        // Session is valid!
        $this->userdata = $session;
        unset($session);

        return TRUE;
    }


}

/* End of file MY_Session.php */
/* Location: ./application/libraries/MY_Session.php */ 
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This file extends the core CI_URI class to support detection of route with or without a trailing slash. By
 * default CodeIgniter really only works with URL's such as
 * 
 * http://www.example.com/
 * http://www.example.com/page
 * http://www.example.com/directory
 * http://www.example.com/directory/page
 * http://www.example.com/pagewithextensions.html
 * 
 * I am particular about how my URLs are formed, and want regular pages or endpoints to function the same way as
 * CodeIgniter with no trailing slash. However, URLs that actually represent a directory or have additional URLs
 * under it I do want to have a final slash to indicate that its a parent of other resources. As an example I
 * would prefer the following:
 * 
 * http://www.example.com/
 * http://www.example.com/page
 * http://www.example.com/directory/
 * http://www.example.com/directory/page
 * http://www.example.com/pagewithextensions.html
 * 
 * While a subtle change, requires many changes to be able to have a website operate this way strictly. If a
 * directory is loaded without the trailing slash, the result will be a 404 page unless you provide routes to honor
 * both situations. The goal is to be able to have you specify in your routing configuration if the URL should or
 * should not have a final slash, that way you have the freedom to use to your preferences, and be strict at the
 * same time only allowing what you specify when it comes to trailing slashes.
 * 
 * @author		Brian Wozeniak
 * @copyright	Copyright (c) 1998-2013, Unmelted, LLC
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
/**
 * URI Class
 *
 * description
 *
 * @author Brian Wozeniak <brian.wozeniak@ozzu.com>
 */
class MX_URI extends CI_URI {
	
	/**
	 * Simply tracks if the URI had a trailing slash before it gets stripped by CodeIgniter
	 * 
	 * @var boolean
	 */
	private $has_trailing_slash = FALSE;
	
	/**
	 * Get the URI String
	 * 
	 * This function has to be re-declared to be able to extend _detect_uri because _detect_uri is a private method
	 * in the original URI class. Would have been easier if it was protected. Nothing has been changed in this
	 * method otherwise. Its possible that we will still need to extend this function as well since we only account
	 * for _detect_uri which works for most situations. May need to work PATH_INFO and QUERY_STRING if that is used
	 * to form the URI string.
	 *
	 * @access	private
	 * @return	string
	 */
	function _fetch_uri_string()
	{
		if (strtoupper($this->config->item('uri_protocol')) == 'AUTO')
		{
			// Is the request coming from the command line?
			if (php_sapi_name() == 'cli' or defined('STDIN'))
			{
				$this->_set_uri_string($this->_parse_cli_args());
				return;
			}
	
			// Let's try the REQUEST_URI first, this will work in most situations
			if ($uri = $this->_detect_uri())
			{
				$this->_set_uri_string($uri);
				return;
			}
	
			// Is there a PATH_INFO variable?
			// Note: some servers seem to have trouble with getenv() so we'll test it two ways
			$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
			if (trim($path, '/') != '' && $path != "/".SELF)
			{
				$this->_set_uri_string($path);
				return;
			}
	
			// No PATH_INFO?... What about QUERY_STRING?
			$path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
			if (trim($path, '/') != '')
			{
				$this->_set_uri_string($path);
				return;
			}
	
			// As a last ditch effort lets try using the $_GET array
			if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '')
			{
				$this->_set_uri_string(key($_GET));
				return;
			}
	
			// We've exhausted all our options...
			$this->uri_string = '';
			return;
		}
	
		$uri = strtoupper($this->config->item('uri_protocol'));
	
		if ($uri == 'REQUEST_URI')
		{
			$this->_set_uri_string($this->_detect_uri());
			return;
		}
		elseif ($uri == 'CLI')
		{
			$this->_set_uri_string($this->_parse_cli_args());
			return;
		}
	
		$path = (isset($_SERVER[$uri])) ? $_SERVER[$uri] : @getenv($uri);
		$this->_set_uri_string($path);
	}

	/**
	 * Detects the URI
	 *
	 * This function will detect the URI automatically and fix the query string if necessary. This extended version
	 * will also update whether or not a trailing slash was used in the URI.
	 *
	 * @access	private
	 * @return	string
	 */
	private function _detect_uri()
	{
		if ( ! isset($_SERVER['REQUEST_URI']) OR ! isset($_SERVER['SCRIPT_NAME']))
		{
			return '';
		}

		$uri = $_SERVER['REQUEST_URI'];
		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
		{
			$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		}
		elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
		{
			$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}

		// This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
		// URI is found, and also fixes the QUERY_STRING server var and $_GET array.
		if (strncmp($uri, '?/', 2) === 0)
		{
			$uri = substr($uri, 2);
		}
		$parts = preg_split('#\?#i', $uri, 2);
		$uri = $parts[0];
		if (isset($parts[1]))
		{
			$_SERVER['QUERY_STRING'] = $parts[1];
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		}
		else
		{
			$_SERVER['QUERY_STRING'] = '';
			$_GET = array();
		}

		if ($uri == '/' || empty($uri))
		{
			return '/';
		}

		$uri = parse_url($uri, PHP_URL_PATH);
		
		// Does a trailing slash exist?
		if(preg_match('/\/$/', $uri))
		{
			$this->has_trailing_slash = TRUE;
		}

		// Do some final cleaning of the URI and return it
		return str_replace(array('//', '../'), '/', trim($uri, '/'));
	}
	
	/**
	 * Parse cli arguments
	 *
	 * Take each command line argument and assume it is a URI segment.
	 * 
	 * Nothing changed here from the original CI _parse_cli_args. Unfortunately we have to add this because it is a
	 * private method, and CLI requests will not function properly without it.
	 *
	 * @access	private
	 * @return	string
	 */
	private function _parse_cli_args()
	{
		$args = array_slice($_SERVER['argv'], 1);
	
		return $args ? '/' . implode('/', $args) : '';
	}
	
	/**
	 * Returns whether or not the URI loaded with trailing slash
	 *
	 * @access	public
	 * @return	string
	 */
	public function has_trailing_slash()
	{
		return $this->has_trailing_slash;		
	}
	
	/**
	 * Returns the URI string exactly as it really is, with or without a trailing slash
	 * 
	 * @return string
	 */
	public function exact_uri_string() {
		return $this->uri_string() . ($this->has_trailing_slash() ? '/' : '');
	}
}


/* End of file $filename$ */
/* Location: $location$$filename$ */
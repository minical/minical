<?php

/**
 *  From the README file:
 *
 *  Project Name: PHP Xero
 *  Class Name: Xero
 *  Author: Ronan Quirke, Xero (dependent on the work of others, mainly David Pitman - see below)
 *  Date: May 2012
 *
 *  Description:
 *  A class for interacting with the xero (xero.com) private application API.  It could also be used for the public application API too, but it hasn't been tested with that.  More documentation for Xero can be found at http://blog.xero.com/developer/api-overview/  It is suggested you become familiar with the API before using this class, otherwise it may not make much sense to you - http://blog.xero.com/developer/api/
 *
 *  Thanks for the Oauth* classes provided by Andy Smith, find more about them at http://oauth.googlecode.com/.  The
 *  OAuthSignatureMethod_Xero class was written by me, as required by the Oauth classes.  The ArrayToXML classes were sourced from wwwzealdcom's work as shown on the comment dated August 30, 2009 on this page: http://snipplr.com/view/3491/convert-php-array-to-xml-or-simple-xml-object-if-you-wish/  I made a few minor changes to that code to overcome some bugs.
 *
 *  ---
 *
 *  License (applies to Xero and Oauth* classes):
 *  The MIT License
 *
 *  Copyright (c) 2007 Andy Smith (Oauth*)
 *  Copyright (c) 2010 David Pitman (Xero)
 *  Copyright (c) 2012 Ronan Quirke, Xero (Xero)
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

class Xero
{
    const ENDPOINT = 'https://api.xero.com/api.xro/2.0/';

    private $key;
    private $secret;
    private $public_cert;
    private $private_key;
    private $consumer;
    private $token;
    private $signature_method;
    private $format;

    public function __construct($config = array())
    {
        $this->initialize($config);
    }

    public function initialize(array $config)
    {
        $this->key          = isset($config['consumer']['key']) ? $config['consumer']['key'] : '';
        $this->secret       = isset($config['consumer']['secret']) ? $config['consumer']['secret'] : '';
        $this->public_cert  = isset($config['certs']['public']) ? $config['certs']['public'] : '';
        $this->private_key  = isset($config['certs']['private']) ? $config['certs']['private'] : '';

        if (!($this->key) || !($this->secret) || !($this->public_cert) || !($this->private_key))
        {
            error_log('Stuff missing ');
            return false;
        }
        if(!file_exists($this->public_cert))
        {
            throw new XeroException('Public cert does not exist: ' . $this->public_cert);
        }
        if(!file_exists($this->private_key))
        {
            throw new XeroException('Private key does not exist: ' . $this->private_key);
        }
        
        $this->consumer         = new OAuthConsumer($this->key, $this->secret);
        $this->token            = new OAuthToken($this->key, $this->secret);
        $this->signature_method = new OAuthSignatureMethod_Xero($this->public_cert, $this->private_key);
        $this->format           = (in_array($config['format'], array('xml','json'))) ? $config['format'] : 'json';
    }

    public function __call($name, $arguments)
    {
        $name               = strtolower($name);
        $valid_methods      = array(
            'accounts',
            'banktransactions',
            'brandingthemes',
            'contacts',
            'creditnotes',
            'currencies',
            'expenseclaims',
            'invoices',
            'items',
            'journals',
            'manualjournals',
            'organisation',
            'payments',
            'receipts',
            'taxrates',
            'trackingcategories',
            'users'
        );
        $valid_post_methods = array(
            'banktransactions',
            'contacts',
            'creditnotes',
            'expenseclaims',
            'invoices',
            'items',
            'manualjournals',
            'receipts'
        );
        $valid_put_methods  = array(
            'payments'
        );
        $valid_get_methods  = array(
            'accounts',
            'banktransactions',
            'brandingthemes',
            'contacts',
            'creditnotes',
            'currencies',
            'employees',
            'expenseclaims',
            'invoices',
            'items',
            'journals',
            'manualjournals',
            'organisation',
            'payments',
            'receipts',
            'taxrates',
            'trackingcategories',
            'users'
        );
        $methods_map        = array(
            'accounts'              => 'Accounts',
            'banktransactions'      => 'BankTransactions',
            'brandingthemes'        => 'BrandingThemes',
            'contacts'              => 'Contacts',
            'creditnotes'           => 'CreditNotes',
            'currencies'            => 'Currencies',
            'employees'             => 'Employees',
            'expenseclaims'         => 'ExpenseClaims',
            'invoices'              => 'Invoices',
            'items'                 => 'Items',
            'journals'              => 'Journals',
            'manualjournals'        => 'ManualJournals',
            'organisation'          => 'Organisation',
            'payments'              => 'Payments',
            'receipts'              => 'Receipts',
            'taxrates'              => 'TaxRates',
            'trackingcategories'    => 'TrackingCategories',
            'users'                 => 'Users'
        );
        if (!in_array($name,$valid_methods))
        {
            throw new XeroException('The selected method does not exist. Please use one of the following methods: '.implode(', ',$methods_map));
        }
        if ((count($arguments) == 0) || (is_string($arguments[0])) || (is_numeric($arguments[0])) || ($arguments[0] === false))
        {
            //it's a GET request
            if (!in_array($name, $valid_get_methods))
            {
                return false;
            }
            $filterid   = (count($arguments) > 0) ? strip_tags(strval($arguments[0])) : false;
            if(isset($arguments[1]) && ($arguments[1]!=false))
            {
                $modified_after = (count($arguments) > 1) ? str_replace( 'X','T', date( 'Y-m-dXH:i:s', strtotime($arguments[1])) ) : false;
            }
            if(isset($arguments[2]) && ($arguments[2]!=false))
            {
                $where  = (count($arguments) > 2) ? $arguments[2] : false;
            }
            else
            {
                $where  = false;
            }
            if (is_array($where) && (count($where) > 0))
            {
                $temp_where = '';
                foreach ($where as $wf => $wv)
                {
                    if (is_bool($wv))
                    {
                        $wv = ($wv) ? "%3d%3dtrue" : "%3d%3dfalse";
                    }
                    else if (is_array($wv))
                    {
                        if (is_bool($wv[1]))
                        {
                            $wv = ($wv[1]) ? rawurlencode($wv[0]) . "true" : rawurlencode($wv[0]) . "false" ;
                        }
                        else
                        {
                            $wv = rawurlencode($wv[0]) . "%22{$wv[1]}%22" ;
                        }
                    }
                    else
                    {
                        $wv = "%3d%3d%22$wv%22";
                    }
                    $temp_where .= "%26%26$wf$wv";
                }
                $where  = strip_tags(substr($temp_where, 6));
            }
            else
            {
                $where  = strip_tags(strval($where));
            }
            $order          = (count($arguments) > 3) ? strip_tags(strval($arguments[3])) : false;
            $acceptHeader   = (!empty($arguments[4])) ? $arguments[4] : '';
            $method         = $methods_map[$name];
            $xero_url       = self::ENDPOINT . $method;
            if ($filterid)
            {
                $xero_url   .= "/$filterid";
            }
            if (isset($where))
            {
                //$xero_url   .= "?where=$where";
            }
            if ($order)
            {
                $xero_url   .= "&order=$order";
            }
            $req    = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'GET', $xero_url);
            $req->sign_request($this->signature_method, $this->consumer, $this->token);
            $ch     = curl_init();
            if ($acceptHeader=='pdf')
            {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/".$acceptHeader));
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $req->to_url());
            if (isset($modified_after) && $modified_after != false)
            {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("If-Modified-Since: $modified_after"));
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $temp_xero_response = curl_exec($ch);
            curl_close($ch);
            if ($acceptHeader=='pdf')
            {
                return $temp_xero_response;
            }
            try {
                if (@simplexml_load_string($temp_xero_response)==false)
                {
                    throw new XeroException($temp_xero_response);
                    $xero_xml   = false;
                }
                else
                {
                    $xero_xml   = simplexml_load_string( $temp_xero_response );
                }
            }
            catch (XeroException $e)
            {
                return $e->getMessage() . "<br/>";
            }
            
            if ($this->format == 'xml' && isset($xero_xml))
            {
                return $xero_xml;
            }
            elseif(isset($xero_xml))
            {
                return ArrayToXML::toArray($xero_xml);
            }
        }
        elseif ((count($arguments) == 1) || (is_array($arguments[0])) || (is_a($arguments[0], 'SimpleXMLElement')))
        {
            //it's a POST or PUT request
            if (!(in_array($name, $valid_post_methods) || in_array($name, $valid_put_methods)))
            {
                return false;
            }
            $method = $methods_map[$name];
            if (is_a( $arguments[0], 'SimpleXMLElement'))
            {
                $post_body  = $arguments[0]->asXML();
            }
            elseif (is_array($arguments[0]))
            {
                $post_body  = ArrayToXML::toXML($arguments[0], $rootNodeName = $method);
            }
            $post_body  = trim(substr($post_body, (stripos($post_body, ">")+1)));
            if (in_array($name, $valid_post_methods))
            {
                $xero_url   = self::ENDPOINT . $method;
                $req        = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'POST', $xero_url, array('xml'=>$post_body));
                $req->sign_request($this->signature_method, $this->consumer, $this->token);
                $ch         = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_URL, $xero_url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $req->to_postdata());
                curl_setopt($ch, CURLOPT_HEADER, $req->to_header());
            }
            else
            {
                $xero_url   = self::ENDPOINT . $method;
                $req        = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, 'PUT', $xero_url);
                $req->sign_request($this->signature_method, $this->consumer, $this->token);
                $xml        = $post_body;
                $fh         = fopen('php://memory', 'w+');
                fwrite($fh, $xml);
                rewind($fh);
                $ch         = curl_init($req->to_url());
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_PUT, true);
                curl_setopt($ch, CURLOPT_INFILE, $fh);
                curl_setopt($ch, CURLOPT_INFILESIZE, strlen($xml));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $xero_response  = curl_exec($ch);
            if (isset($fh))
            {
                fclose($fh);
            }
            try {
                if (@simplexml_load_string($xero_response)==false)
                {
                    throw new XeroException($xero_response);
                }
                else
                {
                    $xero_xml   = simplexml_load_string($xero_response);
                }
            }
            catch (XeroException $e)
            {
                //display custom message
                return $e->getMessage() . "<br/>";
            }
            curl_close($ch);
            if (!isset($xero_xml))
            {
                return false;
            }
            if ($this->format == 'xml' && isset($xero_xml))
            {
                return $xero_xml;
            }
            elseif(isset($xero_xml))
            {
                return ArrayToXML::toArray($xero_xml);
            }
        }
        else
        {
            return false;
        }
    }

    public function __get($name)
    {
        return $this->$name();
    }
}

//END Xero class

/**
 * Generic exception class
 */

class OAuthException extends Exception
{
    // pass
}

class OAuthConsumer
{
    public $key;
    public $secret;

    function __construct($key, $secret, $callback_url=NULL)
    {
        $this->key          = $key;
        $this->secret       = $secret;
        $this->callback_url = $callback_url;
    }

    function __toString()
    {
        return "OAuthConsumer[key=$this->key,secret=$this->secret]";
    }
}

class OAuthToken
{
    // access tokens and request tokens
    public $key;
    public $secret;

    /**
     * key = the token
     * secret = the token secret
     */
    function __construct($key, $secret)
    {
        $this->key      = $key;
        $this->secret   = $secret;
    }

    /**
     * generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     */
    function to_string()
    {
        return "oauth_token=" . OAuthUtil::urlencode_rfc3986($this->key) . "&oauth_token_secret=" . OAuthUtil::urlencode_rfc3986($this->secret);
    }

    function __toString()
    {
        return $this->to_string();
    }
}

/**
 * A class for implementing a Signature Method
 * See section 9 ("Signing Requests") in the spec
 */
abstract class OAuthSignatureMethod
{
    /**
     * Needs to return the name of the Signature Method (ie HMAC-SHA1)
     * @return string
     */
    abstract public function get_name();

    /**
     * Build up the signature
     * NOTE: The output of this function MUST NOT be urlencoded.
     * the encoding is handled in OAuthRequest when the final
     * request is serialized
     * @param OAuthRequest $request
     * @param OAuthConsumer $consumer
     * @param OAuthToken $token
     * @return string
     */
    abstract public function build_signature($request, $consumer, $token);

    /**
     * Verifies that a given signature is correct
     * @param OAuthRequest $request
     * @param OAuthConsumer $consumer
     * @param OAuthToken $token
     * @param string $signature
     * @return bool
     */
    public function check_signature($request, $consumer, $token, $signature)
    {
        $built  = $this->build_signature($request, $consumer, $token);
        return $built == $signature;
    }
}

/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104]
 * where the Signature Base String is the text and the key is the concatenated values (each first
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&'
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1")
 */
class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod
{
    function get_name()
    {
        return "HMAC-SHA1";
    }

    public function build_signature($request, $consumer, $token)
    {
        $base_string            = $request->get_signature_base_string();
        $request->base_string   = $base_string;

        $key_parts  = array(
            $consumer->secret,
            ($token) ? $token->secret : ""
        );

        $key_parts  = OAuthUtil::urlencode_rfc3986($key_parts);
        $key        = implode('&', $key_parts);

        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }
}

/**
 * The PLAINTEXT method does not provide any security protection and SHOULD only be used
 * over a secure channel such as HTTPS. It does not use the Signature Base String.
 *   - Chapter 9.4 ("PLAINTEXT")
 */
class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod
{
    public function get_name()
    {
        return "PLAINTEXT";
    }

    /**
     * oauth_signature is set to the concatenated encoded values of the Consumer Secret and
     * Token Secret, separated by a '&' character (ASCII code 38), even if either secret is
     * empty. The result MUST be encoded again.
     *   - Chapter 9.4.1 ("Generating Signatures")
     *
     * Please note that the second encoding MUST NOT happen in the SignatureMethod, as
     * OAuthRequest handles this!
     */
    public function build_signature($request, $consumer, $token)
    {
        $key_parts  = array(
            $consumer->secret,
            ($token) ? $token->secret : ""
        );

        $key_parts              = OAuthUtil::urlencode_rfc3986($key_parts);
        $key                    = implode('&', $key_parts);
        $request->base_string   = $key;

        return $key;
    }
}

/**
 * The RSA-SHA1 signature method uses the RSASSA-PKCS1-v1_5 signature algorithm as defined in
 * [RFC3447] section 8.2 (more simply known as PKCS#1), using SHA-1 as the hash function for
 * EMSA-PKCS1-v1_5. It is assumed that the Consumer has provided its RSA public key in a
 * verified way to the Service Provider, in a manner which is beyond the scope of this
 * specification.
 *   - Chapter 9.3 ("RSA-SHA1")
 */
abstract class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod
{
    public function get_name()
    {
        return "RSA-SHA1";
    }

    // Up to the SP to implement this lookup of keys. Possible ideas are:
    // (1) do a lookup in a table of trusted certs keyed off of consumer
    // (2) fetch via http using a url provided by the requester
    // (3) some sort of specific discovery code based on request
    //
    // Either way should return a string representation of the certificate
    protected abstract function fetch_public_cert(&$request);

    // Up to the SP to implement this lookup of keys. Possible ideas are:
    // (1) do a lookup in a table of trusted certs keyed off of consumer
    //
    // Either way should return a string representation of the certificate
    protected abstract function fetch_private_cert(&$request);

    public function build_signature($request, $consumer, $token)
    {
        $base_string            = $request->get_signature_base_string();
        $request->base_string   = $base_string;

        // Fetch the private key cert based on the request
        $cert                   = $this->fetch_private_cert($request);

        // Pull the private key ID from the certificate
        $privatekeyid           = openssl_get_privatekey($cert);

        // Sign using the key
        $ok                     = openssl_sign($base_string, $signature, $privatekeyid);

        // Release the key resource
        openssl_free_key($privatekeyid);

        return base64_encode($signature);
    }

    public function check_signature($request, $consumer, $token, $signature)
    {
        $decoded_sig    = base64_decode($signature);

        $base_string    = $request->get_signature_base_string();

        // Fetch the public key cert based on the request
        $cert           = $this->fetch_public_cert($request);

        // Pull the public key ID from the certificate
        $publickeyid    = openssl_get_publickey($cert);

        // Check the computed signature against the one passed in the query
        $ok             = openssl_verify($base_string, $decoded_sig, $publickeyid);

        // Release the key resource
        openssl_free_key($publickeyid);

        return $ok == 1;
    }
}

class OAuthRequest
{
    private $parameters;
    private $http_method;
    private $http_url;
    // for debug purposes
    public $base_string;
    public static $version = '1.0';
    public static $POST_INPUT = 'php://input';

    function __construct($http_method, $http_url, $parameters=NULL)
    {
        @$parameters or $parameters = array();
        $parameters         = array_merge( OAuthUtil::parse_parameters(parse_url($http_url, PHP_URL_QUERY)), $parameters);
        $this->parameters   = $parameters;
        $this->http_method  = $http_method;
        $this->http_url     = $http_url;
    }

    /**
     * attempt to build up a request from what was passed to the server
     */
    public static function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL)
    {
        $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") ? 'http' : 'https';
        @$http_url or $http_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        @$http_method or $http_method   = $_SERVER['REQUEST_METHOD'];

        // We weren't handed any parameters, so let's find the ones relevant to
        // this request.
        // If you run XML-RPC or similar you should use this to provide your own
        // parsed parameter-list
        if (!$parameters)
        {
            // Find request headers
            $request_headers    = OAuthUtil::get_headers();

            // Parse the query-string to find GET parameters
            $parameters         = OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);

            // It's a POST request of the proper content-type, so parse POST
            // parameters and add those overriding any duplicates from GET
            if ($http_method == "POST" && @strstr($request_headers["Content-Type"],"application/x-www-form-urlencoded"))
            {
                $post_data  = OAuthUtil::parse_parameters(file_get_contents(self::$POST_INPUT));
                $parameters = array_merge($parameters, $post_data);
            }

            // We have a Authorization-header with OAuth data. Parse the header
            // and add those overriding any duplicates from GET or POST
            if (@substr($request_headers['Authorization'], 0, 6) == "OAuth ")
            {
                $header_parameters  = OAuthUtil::split_header($request_headers['Authorization']);
                $parameters         = array_merge($parameters, $header_parameters);
            }
        }

        return new OAuthRequest($http_method, $http_url, $parameters);
    }

    /**
     * pretty much a helper function to set up the request
     */
    public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL)
    {
        @$parameters or $parameters = array();
        $defaults   = array(
            "oauth_version"         => OAuthRequest::$version,
            "oauth_nonce"           => OAuthRequest::generate_nonce(),
            "oauth_timestamp"       => OAuthRequest::generate_timestamp(),
            "oauth_consumer_key"    => $consumer->key
        );
        if ($token)
        {
                $defaults['oauth_token'] = $token->key;
            }

        $parameters = array_merge($defaults, $parameters);

        return new OAuthRequest($http_method, $http_url, $parameters);
    }

    public function set_parameter($name, $value, $allow_duplicates = true)
    {
        if ($allow_duplicates && isset($this->parameters[$name]))
        {
            // We have already added parameter(s) with this name, so add to the list
            if (is_scalar($this->parameters[$name]))
            {
                // This is the first duplicate, so transform scalar (string)
                // into an array so we can add the duplicates
                $this->parameters[$name]    = array($this->parameters[$name]);
            }
            $this->parameters[$name][]  = $value;
        }
        else
        {
            $this->parameters[$name]    = $value;
        }
    }

    public function get_parameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    public function get_parameters()
    {
        return $this->parameters;
    }

    public function unset_parameter($name)
    {
        unset($this->parameters[$name]);
    }

    /**
     * The request parameters, sorted and concatenated into a normalized string.
     * @return string
     */
    public function get_signable_parameters()
    {
        // Grab all parameters
        $params = $this->parameters;

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if (isset($params['oauth_signature']))
        {
            unset($params['oauth_signature']);
        }

        return OAuthUtil::build_http_query($params);
    }

    /**
     * Returns the base string of this request
     *
     * The base string defined as the method, the url
     * and the parameters (normalized), each urlencoded
     * and the concated with &.
     */
    public function get_signature_base_string()
    {
        $parts  = array(
            $this->get_normalized_http_method(),
            $this->get_normalized_http_url(),
            $this->get_signable_parameters()
        );

        $parts  = OAuthUtil::urlencode_rfc3986($parts);

        return implode('&', $parts);
    }

    /**
     * just uppercases the http method
     */
    public function get_normalized_http_method()
    {
        return strtoupper($this->http_method);
    }

    /**
     * parses the url and rebuilds it to be
     * scheme://host/path
     */
    public function get_normalized_http_url()
    {
        $parts  = parse_url($this->http_url);

        $port   = @$parts['port'];
        $scheme = $parts['scheme'];
        $host   = $parts['host'];
        $path   = @$parts['path'];

        $port or $port  = ($scheme == 'https') ? '443' : '80';

        if (($scheme == 'https' && $port != '443') || ($scheme == 'http' && $port != '80'))
        {
            $host   = "{$host}:{$port}";
        }
        return "{$scheme}://{$host}{$path}";
    }

    /**
     * builds a url usable for a GET request
     */
    public function to_url()
    {
        $post_data  = $this->to_postdata();
        $out        = $this->get_normalized_http_url();
        if ($post_data)
        {
            $out    .= '?'.$post_data;
        }
        return $out;
    }

    /**
     * builds the data one would send in a POST request
     */
    public function to_postdata()
    {
        return OAuthUtil::build_http_query($this->parameters);
    }

    /**
     * builds the Authorization: header
     */
    public function to_header($realm=null)
    {
        $first  = true;
        if ($realm)
        {
            $out    = 'Authorization: OAuth realm="' . OAuthUtil::urlencode_rfc3986($realm) . '"';
            $first  = false;
        }
        else
        {
            $out    = 'Authorization: OAuth';
        }

        $total  = array();
        foreach ($this->parameters as $k => $v)
        {
            if (substr($k, 0, 5) != "oauth")
            {
                continue;
            }
            if (is_array($v))
            {
                throw new OAuthException('Arrays not supported in headers');
            }
            $out    .= ($first) ? ' ' : ',';
            $out    .= OAuthUtil::urlencode_rfc3986($k) . '="' . OAuthUtil::urlencode_rfc3986($v) . '"';
            $first  = false;
        }
        return $out;
    }

    public function __toString()
    {
        return $this->to_url();
    }


    public function sign_request($signature_method, $consumer, $token)
    {
        $this->set_parameter(
            "oauth_signature_method",
            $signature_method->get_name(),
            false
        );
        $signature  = $this->build_signature($signature_method, $consumer, $token);
        $this->set_parameter("oauth_signature", $signature, false);
    }

    public function build_signature($signature_method, $consumer, $token)
    {
        $signature  = $signature_method->build_signature($this, $consumer, $token);
        return $signature;
    }

    /**
     * util function: current timestamp
     */
    private static function generate_timestamp()
    {
        return time();
    }

    /**
     * util function: current nonce
     */
    private static function generate_nonce()
    {
        $mt     = microtime();
        $rand   = mt_rand();

        return md5($mt . $rand); // md5s look nicer than numbers
    }
}

class OAuthServer
{
    protected $timestamp_threshold = 300; // in seconds, five minutes
    protected $version = '1.0';             // hi blaine
    protected $signature_methods = array();

    protected $data_store;

    function __construct($data_store)
    {
        $this->data_store = $data_store;
    }

    public function add_signature_method($signature_method)
    {
        $this->signature_methods[$signature_method->get_name()] = $signature_method;
    }

    // high level functions

    /**
     * process a request_token request
     * returns the request token on success
     */
    public function fetch_request_token(&$request)
    {
        $this->get_version($request);

        $consumer   = $this->get_consumer($request);

        // no token required for the initial token request
        $token      = NULL;

        $this->check_signature($request, $consumer, $token);

        // Rev A change
        $callback   = $request->get_parameter('oauth_callback');
        $new_token  = $this->data_store->new_request_token($consumer, $callback);

        return $new_token;
    }

    /**
     * process an access_token request
     * returns the access token on success
     */
    public function fetch_access_token(&$request)
    {
        $this->get_version($request);

        $consumer   = $this->get_consumer($request);

        // requires authorized request token
        $token      = $this->get_token($request, $consumer, "request");

        $this->check_signature($request, $consumer, $token);

        // Rev A change
        $verifier   = $request->get_parameter('oauth_verifier');
        $new_token  = $this->data_store->new_access_token($token, $consumer, $verifier);

        return $new_token;
    }

    /**
     * verify an api call, checks all the parameters
     */
    public function verify_request(&$request)
    {
        $this->get_version($request);
        $consumer   = $this->get_consumer($request);
        $token      = $this->get_token($request, $consumer, "access");
        $this->check_signature($request, $consumer, $token);
        return array($consumer, $token);
    }

    // Internals from here
    /**
     * version 1
     */
    private function get_version(&$request)
    {
        $version = $request->get_parameter("oauth_version");
        if (!$version)
        {
            // Service Providers MUST assume the protocol version to be 1.0 if this parameter is not present.
            // Chapter 7.0 ("Accessing Protected Ressources")
            $version = '1.0';
        }
        if ($version !== $this->version)
        {
            throw new OAuthException("OAuth version '$version' not supported");
        }
        return $version;
    }

    /**
     * figure out the signature with some defaults
     */
    private function get_signature_method(&$request)
    {
        $signature_method   = @$request->get_parameter("oauth_signature_method");

        if (!$signature_method)
        {
            // According to chapter 7 ("Accessing Protected Ressources") the signature-method
            // parameter is required, and we can't just fallback to PLAINTEXT
            throw new OAuthException('No signature method parameter. This parameter is required');
        }

        if (!in_array($signature_method, array_keys($this->signature_methods)))
        {
            throw new OAuthException(
                "Signature method '$signature_method' not supported " .
                "try one of the following: " .
                implode(", ", array_keys($this->signature_methods))
            );
        }
        return $this->signature_methods[$signature_method];
    }

    /**
     * try to find the consumer for the provided request's consumer key
     */
    private function get_consumer(&$request)
    {
        $consumer_key   = @$request->get_parameter("oauth_consumer_key");
        if (!$consumer_key)
        {
            throw new OAuthException("Invalid consumer key");
        }

        $consumer   = $this->data_store->lookup_consumer($consumer_key);
        if (!$consumer)
        {
            throw new OAuthException("Invalid consumer");
        }
        return $consumer;
    }

    /**
     * try to find the token for the provided request's token key
     */
    private function get_token(&$request, $consumer, $token_type="access")
    {
        $token_field    = @$request->get_parameter('oauth_token');
        $token          = $this->data_store->lookup_token($consumer, $token_type, $token_field);
        if (!$token)
        {
            throw new OAuthException("Invalid $token_type token: $token_field");
        }
        return $token;
    }

    /**
     * all-in-one function to check the signature on a request
     * should guess the signature method appropriately
     */
    private function check_signature(&$request, $consumer, $token)
    {
        // this should probably be in a different method
        $timestamp  = @$request->get_parameter('oauth_timestamp');
        $nonce      = @$request->get_parameter('oauth_nonce');

        $this->check_timestamp($timestamp);
        $this->check_nonce($consumer, $token, $nonce, $timestamp);

        $signature_method   = $this->get_signature_method($request);

        $signature  = $request->get_parameter('oauth_signature');
        $valid_sig  = $signature_method->check_signature($request, $consumer, $token, $signature);

        if (!$valid_sig)
        {
            throw new OAuthException("Invalid signature");
        }
    }

    /**
     * check that the timestamp is new enough
     */
    private function check_timestamp($timestamp)
    {
        if (!$timestamp)
        {
            throw new OAuthException('Missing timestamp parameter. The parameter is required');
        }

        // verify that timestamp is recentish
        $now    = time();
        if (abs($now - $timestamp) > $this->timestamp_threshold)
        {
            throw new OAuthException("Expired timestamp, yours $timestamp, ours $now");
        }
    }

    /**
     * check that the nonce is not repeated
     */
    private function check_nonce($consumer, $token, $nonce, $timestamp)
    {
        if (!$nonce)
        {
            throw new OAuthException('Missing nonce parameter. The parameter is required');
        }

        // verify that the nonce is uniqueish
        $found  = $this->data_store->lookup_nonce($consumer, $token, $nonce, $timestamp);
        if ($found)
        {
            throw new OAuthException("Nonce already used: $nonce");
        }
    }
}

class OAuthDataStore
{
    function lookup_consumer($consumer_key)
    {
        // implement me
    }

    function lookup_token($consumer, $token_type, $token)
    {
        // implement me
    }

    function lookup_nonce($consumer, $token, $nonce, $timestamp)
    {
        // implement me
    }

    function new_request_token($consumer, $callback = null)
    {
        // return a new token attached to this consumer
    }

    function new_access_token($token, $consumer, $verifier = null)
    {
        // return a new access token attached to this consumer
        // for the user associated with this token if the request token
        // is authorized
        // should also invalidate the request token
    }
}

class OAuthUtil
{
    public static function urlencode_rfc3986($input)
    {
        if (is_array($input))
        {
            return array_map(array('OAuthUtil', 'urlencode_rfc3986'), $input);
        }
        else if (is_scalar($input))
        {
            return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode($input)));
        }
        else
        {
            return '';
        }
    }

    // This decode function isn't taking into consideration the above
    // modifications to the encoding process. However, this method doesn't
    // seem to be used anywhere so leaving it as is.
    public static function urldecode_rfc3986($string)
    {
        return urldecode($string);
    }

    // Utility function for turning the Authorization: header into
    // parameters, has to do some unescaping
    // Can filter out any non-oauth parameters if needed (default behaviour)
    public static function split_header($header, $only_allow_oauth_parameters = true)
    {
        $pattern    = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/';
        $offset     = 0;
        $params     = array();
        while (preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0)
        {
            $match          = $matches[0];
            $header_name    = $matches[2][0];
            $header_content = (isset($matches[5])) ? $matches[5][0] : $matches[4][0];
            if (preg_match('/^oauth_/', $header_name) || !$only_allow_oauth_parameters)
            {
                $params[$header_name]   = OAuthUtil::urldecode_rfc3986($header_content);
            }
            $offset = $match[1] + strlen($match[0]);
        }

        if (isset($params['realm']))
        {
            unset($params['realm']);
        }
        return $params;
    }

    // helper to try to sort out headers for people who aren't running apache
    public static function get_headers()
    {
        if (function_exists('apache_request_headers'))
        {
            // we need this to get the actual Authorization: header
            // because apache tends to tell us it doesn't exist
            $headers    = apache_request_headers();

            // sanitize the output of apache_request_headers because
            // we always want the keys to be Cased-Like-This and arh()
            // returns the headers in the same case as they are in the
            // request
            $out    = array();
            foreach ($headers as $key => $value)
            {
                $key        = str_replace(" ", "-", ucwords(strtolower(str_replace("-", " ", $key))));
                $out[$key]  = $value;
            }
        }
        else
        {
            // otherwise we don't have apache and are just going to have to hope
            // that $_SERVER actually contains what we need
            $out    = array();
            if (isset($_SERVER['CONTENT_TYPE']))
            {
                $out['Content-Type']    = $_SERVER['CONTENT_TYPE'];
            }
            if (isset($_ENV['CONTENT_TYPE']))
            {
                $out['Content-Type']    = $_ENV['CONTENT_TYPE'];
            }

            foreach ($_SERVER as $key => $value)
            {
                if (substr($key, 0, 5) == "HTTP_")
                {
                    // this is chaos, basically it is just there to capitalize the first
                    // letter of every word that is not an initial HTTP and strip HTTP
                    // code from przemek
                    $key        = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                    $out[$key]  = $value;
                }
            }
        }
        return $out;
    }

    // This function takes a input like a=b&a=c&d=e and returns the parsed
    // parameters like this
    // array('a' => array('b','c'), 'd' => 'e')
    public static function parse_parameters( $input )
    {
        if (!isset($input) || !$input)
        {
            return array();
        }

        $pairs  = explode('&', $input);

        $parsed_parameters  = array();
        foreach ($pairs as $pair)
        {
            $split      = explode('=', $pair, 2);
            $parameter  = OAuthUtil::urldecode_rfc3986($split[0]);
            $value      = isset($split[1]) ? OAuthUtil::urldecode_rfc3986($split[1]) : '';

            if (isset($parsed_parameters[$parameter]))
            {
                // We have already recieved parameter(s) with this name, so add to the list
                // of parameters with this name
                if (is_scalar($parsed_parameters[$parameter]))
                {
                    // This is the first duplicate, so transform scalar (string) into an array
                    // so we can add the duplicates
                    $parsed_parameters[$parameter]  = array($parsed_parameters[$parameter]);
                }
                $parsed_parameters[$parameter][]    = $value;
            }
            else
            {
                $parsed_parameters[$parameter]  = $value;
            }
        }
        return $parsed_parameters;
    }

    public static function build_http_query($params)
    {
        if (!$params)
        {
            return '';
        }

        // Urlencode both keys and values
        $keys   = OAuthUtil::urlencode_rfc3986(array_keys($params));
        $values = OAuthUtil::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs  = array();
        foreach ($params as $parameter => $value)
        {
            if (is_array($value))
            {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                natsort($value);
                foreach ($value as $duplicate_value)
                {
                    $pairs[]    = $parameter . '=' . $duplicate_value;
                }
            }
            else
            {
                $pairs[]    = $parameter . '=' . $value;
            }
        }
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        return implode('&', $pairs);
    }
}

//Xero specific signature class
class OAuthSignatureMethod_Xero extends OAuthSignatureMethod_RSA_SHA1
{
    protected $public_cert;
    protected $private_key;

    public function __construct($public_cert, $private_key)
    {
        $this->public_cert  = $public_cert;
        $this->private_key  = $private_key;
    }

    protected function fetch_public_cert(&$request)
    {
        return file_get_contents($this->public_cert);
    }

    protected function fetch_private_cert(&$request)
    {
        return file_get_contents($this->private_key);
    }
}

//END Oauth classes

class ArrayToXML
{
    /**
     * The main function for converting to an XML document.
     * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
     *
     * @param array $data
     * @param string $rootNodeName - what you want the root node to be - defaultsto data.
     * @param SimpleXMLElement $xml - should only be used recursively
     * @return string XML
     */
    public static function toXML($data, $rootNodeName = 'ResultSet', &$xml=null)
    {
        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        if (ini_get('zend.ze1_compatibility_mode') == 1)
        {
            ini_set('zend.ze1_compatibility_mode', 0);
        }
        if (is_null($xml))
        {
            $xml            = simplexml_load_string("<$rootNodeName />");
            $rootNodeName   = rtrim($rootNodeName, 's');
        }
        // loop through the data passed in.
        foreach ($data as $key => $value)
        {
            // no numeric keys in our xml please!
            $numeric = 0;
            if (is_numeric($key))
            {
                $numeric    = 1;
                $key        = $rootNodeName;
            }

            // delete any char not allowed in XML element names
            $key    = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);

            // if there is another array found recursively call this function
            if (is_array($value))
            {
                $node = (ArrayToXML::isAssoc($value) || $numeric) ? $xml->addChild($key) : $xml;
                // recursive call.
                if ($numeric)
                {
                    $key    = 'anon';
                }
                ArrayToXML::toXml($value, $key, $node);
            }
            else
            {
                // add single node.
                $xml->$key  = $value;
            }
        }
        // pass back as XML
        return $xml->asXML();

        // if you want the XML to be formatted, use the below instead to return the XML
        // $doc = new DOMDocument('1.0');
        // $doc->preserveWhiteSpace = false;
        // $doc->loadXML($xml->asXML());
        // $doc->formatOutput = true;
        // return $doc->saveXML();
    }

    /**
     * Convert an XML document to a multi dimensional array
     * Pass in an XML document (or SimpleXMLElement object) and this recrusively loops through and builds a representative array
     *
     * @param string $xml - XML document - can optionally be a SimpleXMLElement object
     * @return array ARRAY
     */
    public static function toArray($xml)
    {
        if (is_string($xml))
        {
            $xml    = new SimpleXMLElement($xml);
        }
        $children   = $xml->children();
        if (!$children)
        {
            return (string) $xml;
        }
        $arr    = array();
        foreach ($children as $key => $node)
        {
            $node   = ArrayToXML::toArray($node);

            // support for 'anon' non-associative arrays
            if ($key == 'anon')
            {
                $key    = count($arr);
            }

            // if the node is already set, put it into an array
            if (array_key_exists($key, $arr) &&  isset($arr[$key]))
            {
                if (!is_array($arr[$key]) || !array_key_exists(0,$arr[$key]) ||  (array_key_exists(0,$arr[$key]) && ($arr[$key][0] == null)))
                {
                    $arr[$key]  = array($arr[$key]);
                }
                $arr[$key][] = $node;
            }
            else
            {
                $arr[$key]  = $node;
            }
        }
        return $arr;
    }

    // determine if a variable is an associative array
    public static function isAssoc($array)
    {
        return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
    }
}

class XeroException extends Exception
{
    //
}

class XeroApiException extends XeroException
{
    private $xml;

    public function __construct($xml_exception)
    {
        $this->xml  = $xml_exception;
        $xml        = new SimpleXMLElement($xml_exception);

        list($message)      = $xml->xpath('/ApiException/Message');
        list($errorNumber)  = $xml->xpath('/ApiException/ErrorNumber');
        list($type)         = $xml->xpath('/ApiException/Type');

        parent::__construct((string)$type . ': ' . (string)$message, (int)$errorNumber);

        $this->type = (string)$type;
    }

    public function getXML()
    {
        return $this->xml;
    }

    public static function isException($xml)
    {
        return preg_match('/^<ApiException.*>/', $xml);
    }
}

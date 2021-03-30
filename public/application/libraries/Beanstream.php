<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Super-simple, minimum abstraction MailChimp API v2 wrapper
 * 
 * Requires curl (I know, right?)
 * This probably has more comments than code.
 * 
 * @author Drew McLellan <drew.mclellan@gmail.com> modified by Ben Bowler <ben.bowler@vice.com>
 * @version 1.0
 */
class Beanstream
{
	private $merchant_id;
	private $authentication_api_access_passcode;
	private $profile_api_access_passcode;
	
	/**
	 * Create a new instance
	 */
	function __construct()
	{
        $this->ci =& get_instance();
        $this->merchant_id = '300201068';
		$this->api_access_passcode = '56a6DC612733454F87728F936F3534b7';
		$this->profile_api_access_passcode = '8E264D72B0654C159F20F6BFC2303C0A';

	}

	/**
	 * Call an API method. Every request needs the API key, so that is added automatically -- you don't need to pass it in.
	 * @param  string $method The API method to call, e.g. 'lists/list'
	 * @param  array  $args   An array of arguments to pass to the method. Will be json-encoded for you.
	 * @return array          Associative array of json decoded API response.
	 */
	public function call($method, $args=array())
	{
		return $this->_raw_request($method, $args);
	}

	// Tokenize CC info
	// returns token id
	public function create_profile()
	{
		echo "tokenizing";
		$req = curl_init('https://www.beanstream.com/api/v1/profiles');

		$auth = base64_encode( $this->merchant_id.":".$this->profile_api_access_passcode );

		$headers = array(
			'Content-Type: application/json',
			'Authorization: Passcode '.$auth
		);

		curl_setopt($req,CURLOPT_HTTPHEADER, $headers);
		curl_setopt($req,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($req,CURLOPT_HEADER, 0);

		$post = array(
			'card' => array(
				'name' => 'John Doe',
				'number' => '5100000010001004',
				'expiry_month' => '02',
				'expiry_year' => '17',
				'cvd' => '123'
			)
		);        

		curl_setopt($req,CURLOPT_POSTFIELDS, json_encode($post));
		$result = curl_exec($req);
		if (strpos($result,"approved"))
			print("Tokenization Successful!");
		else
			print_r($result);
		curl_close($req);
	}

	// process payment on token data
	// return response
	public function process_payment($amount)
	{

	}

	// test
	public function test()
	{
		$req = curl_init('https://www.beanstream.com/api/v1/payments');

		$auth = base64_encode( $this->merchant_id.":".$this->api_access_passcode );

		$headers = array(
			'Content-Type: application/json',
			'Authorization: Passcode '.$auth
		);

		curl_setopt($req,CURLOPT_POST, true);
		curl_setopt($req,CURLOPT_HTTPHEADER, $headers);
		curl_setopt($req,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($req,CURLOPT_FAILONERROR, true);
		curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);

		$post = array(
			'merchant_id' => $merchantId,
			'order_number' => '100001234',
			'amount' => 100.00,
			'payment_method' => 'card',
			'card' => array(
				'name' => 'John Doe',
				'number' => '5100000010001004',
				'expiry_month' => '02',
				'expiry_year' => '17',
				'cvd' => '123'
			)
		);        

		curl_setopt($req,CURLOPT_POSTFIELDS, json_encode($post));
		$result = curl_exec($req);
		if (strpos($result,"approved"))
			print("Payment Successful!");
		else
			print_r($result);
		curl_close($req);
	}

}
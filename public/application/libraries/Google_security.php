<?php  
    use Sonata\GoogleAuthenticator\GoogleAuthenticator;
    use Sonata\GoogleAuthenticator\GoogleQrUrl;

    if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Google_security {

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    function create_secret($email = null, $host_name = null) {

        $g = new GoogleAuthenticator();

        // Generate a secret key for the user
        $secret = $g->generateSecret();

        // Generate a QR code URL
        $user = $email; // Replace with the actual username
        $hostname = $host_name; // Replace with your domain
        $qrCodeUrl = GoogleQrUrl::generate($user, $secret, $hostname);

        return array('secret_code' => $secret, 'qr_code_url' => $qrCodeUrl);
    }

    function check_secret_with_otp($secret, $otp){

        $g = new GoogleAuthenticator();

        // Verify the OTP
        if ($g->checkCode($secret, $otp)) {
            return true;
            // Proceed with the login process
        } else {
            return false;
        }
    }

}

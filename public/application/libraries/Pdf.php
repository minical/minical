<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// require_once APPPATH . '../vendor/autoload.php'; // Correct path to Composer autoload

use Mpdf\Mpdf;

class Pdf extends Mpdf
{
    public function __construct()
    {
        parent::__construct([
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);
    }
}

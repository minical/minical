<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'third_party/tcpdf/tcpdf.php';

class Pdf extends TCPDF
{
    public function __construct()
    {
        parent::__construct(
            'P',        // Portrait
            'mm',
            'A4',
            true,
            'UTF-8',
            false
        );

        // Page margins (closer to mPDF)
        $this->SetMargins(15, 15, 15);
        $this->SetHeaderMargin(0);
        $this->SetFooterMargin(0);

        // Auto page break
        $this->SetAutoPageBreak(TRUE, 15);

        // Font
        $this->SetFont('dejavusans', '', 10);

        // Cell padding (THIS MAKES A BIG DIFFERENCE)
        $this->setCellPaddings(2, 2, 2, 2);
        $this->setCellMargins(0, 0, 0, 0);

        // Disable default header/footer
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);

        $this->AddPage();
    }
}


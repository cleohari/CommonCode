<?php
namespace PDF;

require_once('libs/mpdf/mpdf.php');

class PDF
{
    private $mpdf;

    function __construct()
    {
        $this->mpdf = new \mPDF();
    }

    public function setPDFFromHTML($html)
    {
        $this->mpdf->WriteHTML($html);
    }

    public function toPDFBuffer()
    {
        return $this->mpdf->Output('', 'S');
    }

    public function toPDFFile($filename)
    {
        return $this->mpdf->Output($filename);
    }
}

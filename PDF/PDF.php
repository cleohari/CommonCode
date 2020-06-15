<?php
namespace Flipside\PDF;

require(dirname(__FILE__).'/../vendor/autoload.php');

class PDF
{
    private $mpdf;

    function __construct()
    {
        $this->mpdf = new \Mpdf\Mpdf();
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

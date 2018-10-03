<?php
require_once('Autoload.php');
class PDFTest extends PHPUnit\Framework\TestCase
{
    public function testPDF()
    {
        if(version_compare(PHP_VERSION, '7.0.0', '<'))
        {
            $pdf = new \PDF\PDF();
            $pdf->setPDFFromHTML('<html><body>Test</body></html>');
            $pdfStr = $pdf->toPDFBuffer();
            $this->assertEquals(14567, strlen($pdfStr));
            $name = tempnam('/tmp', 'PDF');
            $pdf->toPDFFile($name);
            $this->assertEquals($pdfStr, file_get_contents($name));
            unlink($name);
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

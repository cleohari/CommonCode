<?php
require_once('Autoload.php');
class PDFTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $pdf = new \Flipside\PDF\PDF();
        $this->assertNotFalse($pdf);
    }

    public function testRender()
    {
        $pdf = new \Flipside\PDF\PDF();
        $pdf->setPDFFromHTML('<html><body><h1>Test</h1></body></html>');
        $str = $pdf->toPDFBuffer();
	$this->assertIsString($str);

	$pdf = new \Flipside\PDF\PDF();
	$pdf->setPDFFromHTML('<html><body><h1>Test</h1></body></html>');
	$pdf->toPDFFile('/tmp/tmp.pdf');
	$this->assertFileExists('/tmp/tmp.pdf');
	@unlink('/tmp/tmp.pdf');
    }
}

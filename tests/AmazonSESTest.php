<?php
require_once('Autoload.php');
class AmazonSESTest extends PHPUnit\Framework\TestCase
{
    public function testBadInit()
    {
        try
        {
            $amazon = new Email\AmazonSES(false);
            $this->assertFalse(true);
        }
        catch(\Exception $e)
        {
            $this->assertFalse(false);
        }

        try
        {
            $amazon = new Email\AmazonSES(array());
            $this->assertFalse(true);
        }
        catch(\Exception $e)
        {
            $this->assertFalse(false);
        }
    }

    public function testGoodInit()
    {
        $amazon = new Email\AmazonSES(array('ini'=>dirname(__FILE__).'/helpers/aws.ini'));
        $this->assertFalse(false);
    }
}

<?php
require_once('Autoload.php');
class SingletonTest extends PHPUnit\Framework\TestCase
{
    public function testNew()
    {
        $tmp = Flipside\Singleton::getInstance();
        $this->assertNotNull($tmp);
        $this->assertInstanceOf('Flipside\Singleton', $tmp);
    }

    public function testExisting()
    {
        $orig = Flipside\Singleton::getInstance();
        $this->assertNotNull($orig);
        $new  = Flipside\Singleton::getInstance();
        $this->assertNotNull($new);
        $this->assertEquals($orig, $new);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

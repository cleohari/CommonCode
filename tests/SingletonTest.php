<?php
require_once('Autoload.php');
class SingletonTest extends PHPUnit_Framework_TestCase
{
    public function testNew()
    {
        $tmp = Singleton::getInstance();
        $this->assertNotNull($tmp);
        $this->assertInstanceOf('Singleton', $tmp);
    }

    public function testExisting()
    {
        $orig = Singleton::getInstance();
        $this->assertNotNull($orig);
        $new  = Singleton::getInstance();
        $this->assertNotNull($new);
        $this->assertEquals($orig, $new);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

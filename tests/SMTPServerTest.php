<?php
require_once('Autoload.php');
class SMTPServerTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $serv = new \Flipside\Email\SMTPServer(array('host' => 'example.org'));
        $this->assertNotNull($serv);

        $serv = new \Flipside\Email\SMTPServer(array('host' => 'example.org', 'port' => 25));
        $this->assertNotNull($serv);

        $serv = new \Flipside\Email\SMTPServer(array('host' => 'example.org', 'port' => 25, 'encryption' => true));
        $this->assertNotNull($serv);

        $serv = new \Flipside\Email\SMTPServer(array('host' => 'example.org', 'port' => 25, 'username' => 'user', 'password' => 'pass'));
        $this->assertNotNull($serv);
    }

    public function canSend()
    {
        $serv = new \Flipside\Email\SMTPServer(array('host' => 'example.org'));
        $this->assertNotNull($serv);
        $this->assertTrue($serv->canSend());
    }

    public function testDecode()
    {
        $serv = new \Flipside\Email\SMTPServer(array('host' => 'example.org'));
        $this->assertNotNull($serv);
        $ret = $serv->decodeAddress('test@example.org');
        $this->assertEquals(array('test@example.org'), $ret);

        $ret = $serv->decodeAddress('test@example.org <Example Person>');
        $this->assertEquals(array('Example Person', 'test@example.org'), $ret);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

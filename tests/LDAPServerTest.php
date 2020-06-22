<?php
require_once('Autoload.php');
class LDAPServerTest extends PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testLdapEscape()
    {
        $this->assertEquals('test', \Flipside\LDAP\ldap_escape('test'));
        $this->assertEquals('test\28\29', \Flipside\LDAP\ldap_escape('test()'));
        $this->assertEquals('test()', \Flipside\LDAP\ldap_escape('test()', false, array('(', ')')));
        $this->assertEquals('test()', \Flipside\LDAP\ldap_escape('test()', true));
        $this->assertEquals('test\23', \Flipside\LDAP\ldap_escape('test#', true));
    }

    public function testCleanupDN()
    {
        $this->assertEquals('test', \Flipside\LDAP\cleanupDN('test'));
        $this->assertEquals('\\20test', \Flipside\LDAP\cleanupDN(' test'));
        $this->assertEquals('test\\20', \Flipside\LDAP\cleanupDN('test '));
        $this->assertEquals('\\20test\\20', \Flipside\LDAP\cleanupDN(' test '));
    }

    public function testConstructor()
    {
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->assertNotNull($server);
    }

    public function testDestructor()
    {
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->__destruct();
        $this->assertNotNull($server);
    }

    public function testFailedConnect()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->once())->willReturn(false);

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->assertNotNull($server);
        $this->assertFalse($server->connect('test'));
    }

    public function testConnect()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(2))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(2))->willReturn(true);

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->assertNotNull($server);
        $this->assertTrue($server->connect('test'));

        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);

        $this->assertTrue($server->connect('test', 'ldaps'));
    }

    public function testDisconnect()
    {
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->disconnect();
        $server->disconnect();
    }

    public function testNoConnectBind()
    {
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->expectException(Exception::class);
        $server->bind();
    }

    public function testBind()
    {
        $i = 0;
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(2))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(2))->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->exactly(2))->willReturnCallback(function($link, $user=null, $pass=null){
            if($user === null)
            {
                $this->assertNull($pass);
            }
            else
            {
                if($i === 0)
                {
                    $i++;
                    throw new Exception('Failed!');
                }
                $this->assertNotNull($pass);
            }
        });

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('ldap://test');
        $server->bind();
        $server->bind('test', 'test1');
    }

    public function testUnbind()
    {
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);
        $ldap_unbind = $this->getFunctionMock('Flipside\LDAP', "ldap_unbind");
        $ldap_unbind->expects($this->once())->willReturn(true);
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->assertTrue($server->unbind());
        $server->disconnect();
        $this->assertTrue($server->unbind());
    }

    public function testBadCreate()
    {
        $ldap_add = $this->getFunctionMock('Flipside\LDAP', "ldap_add");
        $ldap_add->expects($this->exactly(1))->willReturn(false);

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->expectException(Exception::class);
        $server->create(array('dn'=>'test'));
    }

    public function testGoodCreate()
    {
        $ldap_add = $this->getFunctionMock('Flipside\LDAP', "ldap_add");
        $ldap_add->expects($this->exactly(1))->willReturn(true);
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->assertTrue($server->create(array('dn'=>'test')));
    }

    public function testDisonnectedRead()
    {
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->expectException(Exception::class);
        $server->read('test');
    }

    public function testBadSingleRead()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(1))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(1))->willReturn(true);
        $ldap_read = $this->getFunctionMock('Flipside\LDAP', "ldap_read");
        $ldap_read->expects($this->exactly(1))->willReturn(false);

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('ldap://test');
        $this->assertFalse($server->read('test', '(objectclass=*)', true));
    }

    public function testGoodSingleRead()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(1))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(1))->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->exactly(2))->willReturn(true);
        $ldap_read = $this->getFunctionMock('Flipside\LDAP', "ldap_read");
        $ldap_read->expects($this->exactly(1))->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->exactly(1))->willReturn(array('count' => 1, 0 => array('dn'=>'test')));

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('ldap://test');
        $this->assertNotFalse($server->read('test', '(objectclass=*)', true));
        $server->disconnect();
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

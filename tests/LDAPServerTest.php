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
        //Handle state from other tests...
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->assertNotNull($server);
        $this->assertFalse($server->connect('test'));
    }

    public function testConnect()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(3))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(3))->willReturn(true);

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->assertNotNull($server);
        $this->assertTrue($server->connect('test'));

        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->exactly(2))->willReturn(true);

        $this->assertTrue($server->connect('test', 'ldaps'));

        $this->assertTrue($server->connect('test', 'ldap'));
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
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(3))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(3))->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->exactly(3))->willReturnCallback(function($link, $user=null, $pass=null){
            static $i = 0;
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
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);
        $ldap_unbind = $this->getFunctionMock('Flipside\LDAP', "ldap_unbind");
        $ldap_unbind->expects($this->once())->willReturn(true);
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('ldap://test');
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
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->disconnect();
        $this->expectException(Exception::class);
        $server->read('test');
    }

    public function testBadSingleRead()
    {
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);
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

    public function testGoodMultiRead()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(1))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(1))->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->exactly(1))->willReturn(true);
        $ldap_read = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_read->expects($this->exactly(1))->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->exactly(1))->willReturn(array('count' => 1, 0 => array('dn'=>'test')));

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('test', 'ldapi');
        $this->assertNotFalse($server->read('test', '(objectclass=*)', false));
        $server->disconnect();
    }

    public function testGoodMultiReadWithAttrbitues()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(1))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(1))->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->exactly(1))->willReturn(true);
        $ldap_read = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_read->expects($this->exactly(1))->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->exactly(1))->willReturn(array('count' => 1, 0 => array('dn'=>'test')));

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('test', 'ldapi');
        $this->assertNotFalse($server->read('test', '(objectclass=*)', false, array()));
        $server->disconnect();
    }

    public function testBadMultiRead()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(1))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(1))->willReturn(true);
        //$ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        //$ldap_close->expects($this->exactly(1))->willReturn(true);
        $ldap_read = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_read->expects($this->exactly(1))->will($this->throwException(new Exception('bad', 0)));

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('test', 'ldapi');
        $this->expectException(Exception::class);
        $server->read('test', '(objectclass=*)', false);
        $server->disconnect();
    }

    public function testWakeup()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(2))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(3))->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->exactly(1))->willReturn(true);

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('ldap://test');
        $x = serialize($server);
        $tmp = unserialize($x);
    }

    public function testNotConnectedCount()
    {
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->exactly(1))->willReturn(true);

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->disconnect();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Not connected');
        $server->count('test');
    }

    public function testLdapListFails()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(1))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(1))->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->will($this->throwException(new Exception('bad', 0)));

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('test', 'ldap');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('bad (objectclass=*)');
        $server->count('test');
    }

    public function testLdapListReturnsFalse()
    {
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->exactly(1))->willReturn(true);
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(1))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(1))->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(false);

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('test', 'ldap');
        $this->assertEquals(0, $server->count('test'));
    }

    public function testLdapCount()
    {
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->exactly(1))->willReturn(true);
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->exactly(1))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(1))->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(true);
        $ldap_count = $this->getFunctionMock('Flipside\LDAP', "ldap_count_entries");
        $ldap_count->expects($this->exactly(1))->willReturn(10);

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('test', 'ldap');
        $this->assertEquals(10, $server->count('test'));
    }

    public function testLdapUpdateEmpty()
    {
        $test = array('dn'=> 'test');
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->assertEquals(false, $server->update($test));
    }

    public function testLdapSetOnly()
    {
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_mod_replace");
        $ldap_close->expects($this->exactly(1))->willReturn(true);

        $test = array('dn'=> 'test', 'test'=>'test1', 'a'=>array(1, 2, 3));
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->assertEquals(true, $server->update($test));
    }

    public function testLdapSetFails()
    {
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_mod_replace");
        $ldap_close->expects($this->exactly(1))->willReturn(false);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_errno");
        $ldap_close->expects($this->exactly(1))->willReturn(0);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_error");
        $ldap_close->expects($this->exactly(1))->willReturn('x');

        $test = array('dn'=> 'test', 'test'=>'test1');
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to update object with dn=test (0:x) Array
(
    [test] => test1
)');
        $server->update($test);
    }

    public function testLdapDeleteKey()
    {
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_mod_del");
        $ldap_close->expects($this->exactly(1))->willReturn(true);

        $test = array('dn'=> 'test', 'test'=>null);
        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->assertEquals(true, $server->update($test));
    }

    public function testLdapDelete()
    {
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->exactly(1))->willReturn(true);
        $ldap_del = $this->getFunctionMock('Flipside\LDAP', "ldap_delete");
        $ldap_del->expects($this->exactly(1))->willReturn('bob');

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->assertEquals('bob', $server->delete('test'));
        $server->disconnect();
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

<?php
require_once('Autoload.php');
class LDAPAuthenticatorTest extends PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testSortArray()
    {
        $array = array();
        $array[] = array('mail' => array('test@example.org'), 'b'=>9);
        $array[] = array('mail' => array('xyz@example.org', 'bob@example.org'), 'b'=>8);
        \Flipside\Auth\sort_array($array, array('mail'=>1));
        $this->assertEquals(array(array('mail'=>array('test@example.org'), 'b'=>9), array('mail'=>array('xyz@example.org', 'bob@example.org'), 'b'=>8)), $array);
    }

    public function testConstrutor()
    {
        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false));
        $this->assertNotFalse($auth);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'host'=>'testhost'));
        $this->assertNotFalse($auth);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'host'=>'testhost', 'bind_dn'=>'testdn'));
        $this->assertNotFalse($auth);
    }

    public function testGetAndBindServerNotConnected()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->once())->willReturn(false);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false));
        $this->assertNotFalse($auth);

        $this->expectException('\Exception');
        $auth->getAndBindServer();
    }

    public function testGetAndBindReadOnly()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->once())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(2))->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'ro_bind_dn'=> 'readonly', 'ro_bind_pass' => 'readonly'));
        $this->assertNotFalse($auth);

        $server = $auth->getAndBindServer();
        $this->assertNotFalse($server);
        $server->disconnect();
    }

    public function testBindFail()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->once())->willReturn(false);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(2))->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'ro_bind_dn'=> 'readonly', 'ro_bind_pass' => 'readonly'));
        $this->assertNotFalse($auth);

        $server = $auth->getAndBindServer();
        $this->assertFalse($server);
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testBindRW()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->once())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(2))->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);

        $server = $auth->getAndBindServer(true);
        $this->assertNotFalse($server);
        $server->disconnect();
    }

    public function testLoginNoBind()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->exactly(1))->willReturn(false);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(1))->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertFalse($auth->login('nope', 'dontcare'));
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testLoginUserNotPresent()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->exactly(1))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(1))->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(false);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertFalse($auth->login('nope', 'dontcare'));
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testLoginBadPass()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->exactly(2))->willReturnCallback(function($conn, $user=false, $pass=false) {
            if($user === false)
            {
                return true;
            }
            return false;
        });
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->exactly(1))->willReturn(array('count' => 1, 0 => array('dn'=>'test')));
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);
        $ldap_unbind = $this->getFunctionMock('Flipside\LDAP', "ldap_unbind");
        $ldap_unbind->expects($this->once())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertFalse($auth->login('nope', 'badpass'));
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testLoginGoodPass()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->exactly(2))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->exactly(1))->willReturn(array('count' => 1, 0 => array('dn'=>'test')));
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);
        $ldap_unbind = $this->getFunctionMock('Flipside\LDAP', "ldap_unbind");
        $ldap_unbind->expects($this->once())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $res = $auth->login('nope', 'goodpass');
        $this->assertNotFalse($res);
        $this->assertArrayHasKey('res', $res);
        $this->assertTrue($res['res']);
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testLoggedIn()
    {
        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertFalse($auth->isLoggedIn(array('res'=>false)));
        $this->assertTrue($auth->isLoggedIn(array('res'=>true)));
        $this->assertFalse($auth->isLoggedIn(false));
    }

    public function testGetUser()
    {
        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $user = $auth->getUser(array('extended'=>array()));
        $this->assertNotFalse($user);
    }

    public function testGetGroupNoBind()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->exactly(1))->willReturn(false);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->exactly(1))->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertNull($auth->getGroupByName('nope'));
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testGetGroupNotThere()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->exactly(1))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(false);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertNull($auth->getGroupByName('nope'));
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testGetGroup()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->exactly(1))->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->exactly(1))->willReturn(array('count' => 1, 0 => array('dn'=>'test')));
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->once())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertNotNull($auth->getGroupByName('yup'));
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testGetGroupFilterNoBind()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(false);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertFalse($auth->getGroupsByFilter(false));
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testGetGroupFilterNoGroups()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(false);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertFalse($auth->getGroupsByFilter(false));
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testGetGroupFilter()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->exactly(1))->willReturn(array('count' => 1, 0 => array('dn'=>'test')));
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $group = $auth->getGroupsByFilter(false);
        $this->assertNotFalse($group);
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testGetGroupFilterSelect()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->any())->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->any())->willReturn(array('count' => 2, 0 => array('dn'=>'test', 'cn'=>'test', 'description'=>array('Bob'), 'member'=>array('count' => 0)), 1 => array('dn'=>'test1', 'cn'=>'abc', 'description'=>array('Bob1'), 'member'=>array('count' => 0))));
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $group = $auth->getGroupsByFilter(false);
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertEquals('Bob', $group[0]->getDescription());

        $group = $auth->getGroupsByFilter(false, array('mail', 'members'));
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertArrayNotHasKey('description', $group[0]);
        
        $group = $auth->getGroupsByFilter(false, false, 1);
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertArrayNotHasKey(1, $group);

        $group = $auth->getGroupsByFilter(false, false, false, 1);
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertArrayNotHasKey(1, $group);
        $this->assertEquals('Bob1', $group[0]->getDescription());

        $group = $auth->getGroupsByFilter(false, false, 1, 1);
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertArrayNotHasKey(1, $group);
        $this->assertEquals('Bob1', $group[0]->getDescription());

        $group = $auth->getGroupsByFilter(false, false, false, false, array('cn' => 1));
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertArrayHasKey(1, $group);
        $this->assertEquals('Bob1', $group[0]->getDescription());
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testActiveUserCountNoBind()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(false);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertEquals(0, $auth->getActiveUserCount());
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testActiveUserCount()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(true);
        $ldap_count = $this->getFunctionMock('Flipside\LDAP', "ldap_count_entries");
        $ldap_count->expects($this->exactly(1))->willReturn(50);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertEquals(50, $auth->getActiveUserCount());
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testGetUserFilterNoBind()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(false);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertFalse($auth->getUsersByFilter(false));
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testGetUserFilterNoUsers()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(false);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $this->assertFalse($auth->getUsersByFilter(false));
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testGetUserFilter()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->exactly(1))->willReturn(array('count' => 1, 0 => array('dn'=>'test')));
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $user = $auth->getUsersByFilter(false);
        $this->assertNotFalse($user);
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testGetUserFilterSelect()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->any())->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->any())->willReturn(array('count' => 2, 0 => array('dn'=>'test', 'cn'=>'test', 'givenname'=>array('Bob'), 'member'=>array('count' => 0)), 1 => array('dn'=>'test1', 'cn'=>'abc', 'givenname'=>array('Bob1'), 'member'=>array('count' => 0))));
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);
        $group = $auth->getUsersByFilter(false);
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertEquals('Bob', $group[0]->givenName);

        $group = $auth->getUsersByFilter(false, array('mail', 'members'));
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertArrayNotHasKey('givenname', $group[0]);

        $group = $auth->getUsersByFilter(false, false, 1);
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertArrayNotHasKey(1, $group);

        $group = $auth->getUsersByFilter(false, false, false, 1);
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertArrayNotHasKey(1, $group);
        $this->assertEquals('Bob1', $group[0]->givenName);

        $group = $auth->getUsersByFilter(false, false, 1, 1);
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertArrayNotHasKey(1, $group);
        $this->assertEquals('Bob1', $group[0]->givenName);

        $group = $auth->getUsersByFilter(false, false, false, false, array('cn' => 1));
        $this->assertNotEmpty($group);
        $this->assertArrayHasKey(0, $group);
        $this->assertArrayHasKey(1, $group);
        $this->assertEquals('Bob1', $group[0]->givenName);
        \Flipside\LDAP\LDAPServer::getInstance()->disconnect();
    }

    public function testActivatePendingAddFail()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_add = $this->getFunctionMock('Flipside\LDAP', "ldap_add");
        $ldap_add->expects($this->any())->willReturn(false);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $user = new \BareMinimumTestUser();
        $user->uid = 'test';
        $user->mail = 'test@example.org';

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);

        $this->expectException('\Exception');
        $this->expectExceptionMessage('Failed to create object with dn=uid=test,');
        $auth->activatePendingUser($user);
    }

    public function testActivatePendingQueryFail()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_add = $this->getFunctionMock('Flipside\LDAP', "ldap_add");
        $ldap_add->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(false);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $user = new \SlightlyLessBareMinimumTestUser();
        $user->uid = 'test';
        $user->mail = 'test@example.org';
        $user->sn = 'Smith';
        $user->givenName = 'Bob';

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);

        $this->expectException('\Exception');
        $this->expectExceptionMessage('Error creating user!');
        $auth->activatePendingUser($user);
    }

    public function testActivatePending()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_add = $this->getFunctionMock('Flipside\LDAP', "ldap_add");
        $ldap_add->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->any())->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->any())->willReturn(array('count' => 1, 0 => array('dn'=>'test', 'cn'=>'test', 'givenname'=>array('Bob'), 'member'=>array('count' => 0))));
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $user = new \SlightlyLessBareMinimumTestUser();
        $user->uid = 'test';
        $user->mail = 'test@example.org';
        $user->sn = 'Smith';
        $user->host = 'test.org';
        $user->givenName = 'Bob';

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);

        $x = $auth->activatePendingUser($user);
        $this->assertNotFalse($x);
        $this->assertTrue($user->wasDeleted);
    }

    public function testUserByResetHashQueryFail()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->exactly(1))->willReturn(false);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);

        $this->assertFalse($auth->getUserByResetHash('hash'));
    }

    public function testUserByResetHash()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_add = $this->getFunctionMock('Flipside\LDAP', "ldap_add");
        $ldap_add->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->any())->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->any())->willReturn(array('count' => 1, 0 => array('dn'=>'test', 'cn'=>'test', 'givenname'=>array('Bob'), 'member'=>array('count' => 0))));
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);

        $auth = new \Flipside\Auth\LDAPAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'bind_dn'=> 'readwrite', 'bind_pass' => 'readwrite'));
        $this->assertNotFalse($auth);

        $this->assertNotFalse($auth->getUserByResetHash('hash'));
    }
}

class BareMinimumTestUser
{
    public function getPassword()
    {
        return false;
    }
}

class SlightlyLessBareMinimumTestUser
{
    public $wasDeleted = false;

    public function getPassword()
    {
        return 'fakePass';
    }

    public function delete()
    {
        $this->wasDeleted = true;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

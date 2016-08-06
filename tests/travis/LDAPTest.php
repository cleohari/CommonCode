<?php
require_once('Autoload.php');
class LDAPTest extends PHPUnit_Framework_TestCase
{
    private $LDAPSERVER = 'ldap://localhost:3389';

    public function testConnect()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $this->assertInstanceOf('LDAP\LDAPServer', $server);
        $res = $server->connect($this->LDAPSERVER);
        $this->assertTrue($res);
    }

    public function testDisconnect()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $res = $server->connect($this->LDAPSERVER);
        $this->assertTrue($res);
        $server->disconnect();
        $this->assertTrue(true);
    }

    public function testAnonymousBind()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $res = $server->connect($this->LDAPSERVER);
        $this->assertTrue($res);
        $res = $server->bind();
        $this->assertTrue($res);
    }

    public function testBind()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $res = $server->connect($this->LDAPSERVER);
        $this->assertTrue($res);
        $res = $server->bind('cn=admin,dc=example,dc=com','test');
        $this->assertTrue($res);
    }

    public function testRead()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $res = $server->connect($this->LDAPSERVER);
        $this->assertTrue($res);
        $res = $server->bind('cn=admin,dc=example,dc=com','test');
        $this->assertTrue($res);
      
        $data = $server->read('dc=example,dc=com');
        $this->assertNotFalse($data);
        $this->assertContainsOnlyInstancesOf('LDAP\LDAPObject', $data);

        $data = $server->read('dc=example,dc=com', '(mail=test.entry@example.com)');
        $this->assertNotFalse($data);
        $this->assertContainsOnlyInstancesOf('LDAP\LDAPObject', $data);
        $this->assertCount(1, $data);

        $data = $server->read('dc=example,dc=com', '(mail=test.entry@example.com)', false, array('givenName'));
        $this->assertNotFalse($data);
        $this->assertContainsOnlyInstancesOf('LDAP\LDAPObject', $data);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('givenname', $data[0]);

        $data = $server->read('cn=existing,dc=example,dc=com', false, true);
        $this->assertNotFalse($data);
        $this->assertContainsOnlyInstancesOf('LDAP\LDAPObject', $data);
        $this->assertCount(1, $data);
    }

    public function testCount()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $res = $server->connect($this->LDAPSERVER);
        $this->assertTrue($res);
        $res = $server->bind('cn=admin,dc=example,dc=com','test');
        $this->assertTrue($res);

        $count = $server->count('dc=example,dc=com');
        $this->assertGreaterThan(0, $count);
    }

    public function testLDAPAuthenticator()
    {
        $params = array();
        $params['current'] = true;
        $params['pending'] = true;
        $params['supplement'] = false;
        $params['host'] = $this->LDAPSERVER;
        $params['user_base'] = 'dc=users,dc=example,dc=com';
        $params['group_base'] = 'dc=example,dc=com';
        $params['bind_dn'] = 'cn=admin,dc=example,dc=com';
        $params['bind_pass'] = 'test';
        $auth = new \Auth\LDAPAuthenticator($params);
        $this->assertNotFalse($auth->getAndBindServer());
        $this->assertNotFalse($auth->getAndBindServer(true));

        $pendingUser = new \Auth\PendingUser();
        $pendingUser->uid = 'test1';
        $pendingUser->mail = 'test@test.com';
        $pendingUser->sn = 'User';
        $pendingUser->givenName = 'Test';
        $pendingUser->host = 'test.com';

        $this->assertNotFalse($auth->activatePendingUser($pendingUser));

        $params['bind_pass'] = 'test1';
        $auth = new \Auth\LDAPAuthenticator($params);
        $this->assertNotFalse($auth->getAndBindServer());
        $this->assertFalse($auth->getAndBindServer(true));
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

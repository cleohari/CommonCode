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
        $this->assertArrayHasKey('givenName', $data[0]);

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
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

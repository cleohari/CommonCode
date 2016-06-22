<?php
require_once('Autoload.php');
class LDAPTest extends PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $this->assertInstanceOf('LDAP\LDAPServer', $server);
        $res = $server->connect('ldap://directory.verisign.com');
        $this->assertTrue($res);
    }

    public function testDisconnect()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $res = $server->connect('ldap://directory.verisign.com');
        $this->assertTrue($res);
        $server->disconnect();
        $this->assertTrue(true);
    }

    public function testAnonymousBind()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $res = $server->connect('ldap://directory.verisign.com');
        $this->assertTrue($res);
        $res = $server->bind();
        $this->assertTrue($res);
    }

    public function testBind()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $res = $server->connect('ldap://ldap.forumsys.com');
        $this->assertTrue($res);
        $res = $server->bind('cn=read-only-admin,dc=example,dc=com','password');
        $this->assertTrue($res);
    }

    public function testRead()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $res = $server->connect('ldap://directory.verisign.com');
        $this->assertTrue($res);
        $res = $server->bind('cn=read-only-admin,dc=example,dc=com','password');
        $this->assertTrue($res);
      
        $data = $server->read('dc=example,dc=com');
        $this->assertNotFalse($data);
        $this->assertContainsOnlyInstancesOf('LDAP\LDAPObject', $data);

        $data = $server->read('dc=example,dc=com', '(mail=pasteur@ldap.forumsys.com)');
        $this->assertNotFalse($data);
        $this->assertContainsOnlyInstancesOf('LDAP\LDAPObject', $data);
        $this->assertCount(1, $data);

        $data = $server->read('dc=example,dc=com', '(mail=pasteur@ldap.forumsys.com)', false, array('telephonenumber'));
        $this->assertNotFalse($data);
        $this->assertContainsOnlyInstancesOf('LDAP\LDAPObject', $data);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('telephonenumber', $data[0]);

        $data = $server->read('uid=pasteur,dc=example,dc=com', false, true);
        $this->assertNotFalse($data);
        $this->assertContainsOnlyInstancesOf('LDAP\LDAPObject', $data);
        $this->assertCount(1, $data);
    }

    public function testCount()
    {
        $server = \LDAP\LDAPServer::getInstance();
        $res = $server->connect('ldap://ldap.forumsys.com');
        $this->assertTrue($res);
        $res = $server->bind('cn=read-only-admin,dc=example,dc=com','password');
        $this->assertTrue($res);

        $count = $server->count('dc=example,dc=com');
        $this->assertGreaterThan(0, $count);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

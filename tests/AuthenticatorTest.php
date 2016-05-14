<?php
require_once('Autoload.php');
class AuthenticatorTest extends PHPUnit_Framework_TestCase
{
    public function testAuthenticator()
    {
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false);
        $auth = new \Auth\Authenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\Authenticator', $auth);
        $this->assertTrue($auth->current);
        $this->assertFalse($auth->pending);
        $this->assertFalse($auth->supplement);

        $this->assertFalse($auth->login('test', 'test'));
        $this->assertFalse($auth->isLoggedIn(false));
        $this->assertNull($auth->getUser(false));
        $this->assertNull($auth->getGroupByName(false));
        $this->assertNull($auth->getUserByName(false));
        $this->assertFalse($auth->getGroupsByFilter(false));
        $this->assertFalse($auth->getUsersByFilter(false));
        $this->assertFalse($auth->getPendingUsersByFilter(false));
        $this->assertEquals(0, $auth->getActiveUserCount(false));
        $this->assertEquals(0, $auth->getPendingUserCount(false));
        $this->assertEquals(0, $auth->getGroupCount(false));
        $this->assertFalse($auth->getSupplementLink());
        $this->assertFalse($auth->createPendingUser(false));
        $this->assertFalse($auth->activatePendingUser(false));
        $this->assertFalse($auth->getUserByResetHash(false));
        $this->assertFalse($auth->getTempUserByHash(false));
        $this->assertFalse($auth->getHostName(false));

        $params = array('current'=>false, 'pending'=>true, 'supplement'=>false);
        $auth = new \Auth\Authenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\Authenticator', $auth);
        $this->assertFalse($auth->current);
        $this->assertTrue($auth->pending);
        $this->assertFalse($auth->supplement);

        $this->assertFalse($auth->getPendingUsersByFilter(false));
        $this->assertEquals(0, $auth->getActiveUserCount(false));
        $this->assertEquals(0, $auth->getPendingUserCount(false));

        $params = array('current'=>false, 'pending'=>false, 'supplement'=>true);
        $auth = new \Auth\Authenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\Authenticator', $auth);
        $this->assertFalse($auth->current);
        $this->assertFalse($auth->pending);
        $this->assertTrue($auth->supplement);

        $this->assertFalse($auth->getSupplementLink());
        $this->assertFalse($auth->getHostName(false));
    }

    public function testNullAuthenticator()
    {
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false);
        $auth = new \Auth\NullAuthenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\NullAuthenticator', $auth);
        $res = $auth->login('test', 'test');
        $this->assertNotFalse($res);
        $this->assertFalse($auth->isLoggedIn(false));
        $this->assertTrue($auth->isLoggedIn($res));
        $this->assertNull($auth->getUser(false));
    }

    public function testLDAPAuthenticator()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false, 'host'=>'ldap://ldap.forumsys.com', 'user_base'=>'dc=example,dc=com', 'group_base'=>'dc=example,dc=com', 
                        'bind_dn'=>'cn=read-only-admin,dc=example,dc=com', 'bind_pass'=>'password');
        $auth = new \Auth\LDAPAuthenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\LDAPAuthenticator', $auth);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

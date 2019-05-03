<?php
require_once('Autoload.php');
class AuthProviderTest extends PHPUnit\Framework\TestCase
{
    public function testSingleton()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth1 = \AuthProvider::getInstance();
        $auth2 = \AuthProvider::getInstance();
        $this->assertEquals($auth1, $auth2);
    }

    public function testGetUserByLogin()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \AuthProvider::getInstance();

        $dataSet = \DataSetFactory::getDataSetByName('authentication');
        $dataSet->raw_query('CREATE TABLE user (uid varchar(255), pass varchar(255));');

        $user = $auth->getUserByLogin('baduser', 'badpass');
        $this->assertFalse($user);

        $dataTable = \DataSetFactory::getDataTableByNames('authentication', 'user');
        $dataTable->create(array('uid'=>'gooduser', 'pass'=>password_hash('goodpass', PASSWORD_DEFAULT)));

        $user = $auth->getUserByLogin('gooduser', 'goodpass');
        $this->assertNotFalse($user);
        $this->assertInstanceOf('Auth\User', $user);
    }

    public function testLogin()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \AuthProvider::getInstance();
 
        $res = $auth->login('baduser', 'badpass');
        $this->assertFalse($res);

        $res = $auth->login('gooduser', 'goodpass');
        $this->assertNotFalse($res);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('res', $res);
        $this->assertTrue($res['res']);
    }

    public function testIsLoggedIn()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \AuthProvider::getInstance();

        $data = FlipSession::getVar('AuthData');
        $method = FlipSession::getVar('AuthMethod');
        $res = $auth->isLoggedIn($data, $method);
        $this->assertTrue($res);

        $res = $auth->isLoggedIn(false, $method);
        $this->assertFalse($res);

        $res = $auth->isLoggedIn(array(), $method);
        $this->assertFalse($res);
    }

    public function testGetUser()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \AuthProvider::getInstance();

        $data = FlipSession::getVar('AuthData');
        $method = FlipSession::getVar('AuthMethod');
        $user = $auth->getUser($data, $method);
        $this->assertNotFalse($user);
        $this->assertInstanceOf('Auth\User', $user);
    }

    public static function tearDownAfterClass(): void
    {
        unlink('/tmp/auth.sq3');
        unlink('/tmp/pending.sq3');
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

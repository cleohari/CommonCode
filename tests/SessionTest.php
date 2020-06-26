<?php
require_once('Autoload.php');
class SessionTest extends PHPUnit\Framework\TestCase
{
    public function testIsLoggedIn()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';

        unset($_SESSION['flipside_user']);
        unset($_SESSION['AuthMethod']);
        $this->assertFalse(\Flipside\FlipSession::isLoggedIn());

        $_SESSION['flipside_user'] = true;
        $this->assertTrue(\Flipside\FlipSession::isLoggedIn());

        unset($_SESSION['flipside_user']);
        $_SESSION['AuthMethod'] = 'Flipside\\Auth\\SQLAuthenticator';
        $_SESSION['AuthData'] = array('res' => true);
        $this->assertTrue(\Flipside\FlipSession::isLoggedIn());
        unset($_SESSION['flipside_user']);
        unset($_SESSION['AuthMethod']);
    }

    public function testGetUser()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';

        unset($_SESSION['flipside_user']);
        unset($_SESSION['AuthMethod']);
        unset($_SESSION['AuthData']);
        $this->assertEquals(null, \Flipside\FlipSession::getUser());
        $_SESSION['flipside_user'] = 'test';
        $this->assertEquals('test', \Flipside\FlipSession::getUser());
        unset($_SESSION['flipside_user']);
        $_SESSION['AuthMethod'] = 'Flipside\Auth\SQLAuthenticator';
        $_SESSION['AuthData'] = array('uid'=>'test');
        $this->assertNotEquals(null, \Flipside\FlipSession::getUser());
        unset($_SESSION['flipside_user']);
        unset($_SESSION['AuthMethod']);
        unset($_SESSION['AuthData']);
    }

    public function testSetUser()
    {
        unset($_SESSION['flipside_user']);
        $obj = new stdClass();
        \Flipside\FlipSession::setUser($obj);
        $this->assertEquals($obj, $_SESSION['flipside_user']);
        unset($_SESSION['flipside_user']);
    }

    public function testGetEmail()
    {
        $_SESSION['flipside_email'] = 'test@example.org';
        $this->assertEquals('test@example.org', \Flipside\FlipSession::getUserEmail());
        unset($_SESSION['flipside_email']);

        $this->assertFalse(\Flipside\FlipSession::getUserEmail());

        $obj = new stdClass();
        $obj->mail = array('test1@example.org');
        $_SESSION['flipside_user'] = $obj;
        $this->assertEquals('test1@example.org', \Flipside\FlipSession::getUserEmail());
        unset($_SESSION['flipside_email']);

        $obj = new stdClass();
        $_SESSION['flipside_user'] = $obj;
        $this->assertFalse(\Flipside\FlipSession::getUserEmail());
        unset($_SESSION['flipside_email']);
        unset($_SESSION['flipside_user']);
    }

    public function testEnd()
    {
        $_SESSION['flipside_email'] = 'test@example.org';
        $this->expectError();
        \Flipside\FlipSession::end();
        assertArrayNotHasKey('flipside_email', $_SESSION);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

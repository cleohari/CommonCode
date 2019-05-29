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
        $dataSet->raw_query('CREATE TABLE tbluser (uid varchar(255), pass varchar(255));');

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

    public function testGetGroupByName()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \AuthProvider::getInstance();

        $group = $auth->getGroupByName('BadGroup');
        $this->assertNull($group);

        $dataSet = \DataSetFactory::getDataSetByName('authentication');
        $dataSet->raw_query('CREATE TABLE tblgroup (gid varchar(255), description varchar(255));');

        $dataTable = \DataSetFactory::getDataTableByNames('authentication', 'group');
        $dataTable->create(array('gid'=>'goodgroup', 'description'=>'Good Group'));

        $group = $auth->getGroupByName('goodgroup');
        $this->assertNotNull($group);
        $this->assertInstanceOf('Auth\Group', $group);

        $group = $auth->getGroupByName('goodgroup', 'Auth\SQLAuthenticator');
        $this->assertNotNull($group);
        $this->assertInstanceOf('Auth\Group', $group);
    }

    public function testUsersByFilter()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \AuthProvider::getInstance();

        $dataTable = \DataSetFactory::getDataTableByNames('authentication', 'user');
        $res = $dataTable->create(array('uid'=>'gooduser2', 'pass'=>password_hash('goodpass', PASSWORD_DEFAULT)));

        $users = $auth->getUsersByFilter(false);
        $this->assertNotNull($users);
        $this->assertCount(2, $users);

        $users = $auth->getUsersByFilter(new \Data\Filter('uid eq "gooduser2"'));
        $this->assertNotNull($users);
        $this->assertCount(1, $users);
    }

    public function testGroupsByFilter()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \AuthProvider::getInstance();

        $dataTable = \DataSetFactory::getDataTableByNames('authentication', 'group');
        $res = $dataTable->create(array('gid'=>'goodgroup2', 'description'=>'Good Group'));

        $groups = $auth->getGroupsByFilter(false);
        $this->assertNotNull($groups);
        $this->assertCount(2, $groups);

        $groups = $auth->getGroupsByFilter(new \Data\Filter('gid eq "goodgroup2"'));
        $this->assertNotNull($groups);
        $this->assertCount(1, $groups);
    }

    public function testActiveUserCount()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \AuthProvider::getInstance();

        $count = $auth->getActiveUserCount();
        $this->assertEquals(2, $count);

        $count = $auth->getActiveUserCount('Auth\SQLAuthenticator');
        $this->assertEquals(2, $count);
    }

    public function testGroupCount()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \AuthProvider::getInstance();

        $count = $auth->getGroupCount();
        $this->assertEquals(2, $count);

        $count = $auth->getActiveUserCount('Auth\SQLAuthenticator');
        $this->assertEquals(2, $count);
    }

    public function testMergeResult()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \AuthProvider::getInstance();

        $full = array();
        $auth->mergeResult($full, false);
        $this->assertCount(0, $full);
    }

    public static function tearDownAfterClass(): void
    {
        unlink('/tmp/auth.sq3');
        unlink('/tmp/pending.sq3');
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

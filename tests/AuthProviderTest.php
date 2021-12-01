<?php
require_once('Autoload.php');
class AuthProviderTest extends PHPUnit\Framework\TestCase
{
    public function testSingleton()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth1 = \Flipside\AuthProvider::getInstance();
        $auth2 = \Flipside\AuthProvider::getInstance();
        $this->assertEquals($auth1, $auth2);
    }

    public function testGetUserByLogin()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('authentication');
        $dataSet->raw_query('CREATE TABLE tbluser (uid varchar(255), pass varchar(255), mail varchar(255), jpegphoto varchar(255));');

        $user = $auth->getUserByLogin('baduser', 'badpass');
        $this->assertFalse($user);

        $dataTable = \Flipside\DataSetFactory::getDataTableByNames('authentication', 'user');
        $dataTable->create(array('uid'=>'gooduser', 'mail'=>'good@example.com', 'pass'=>password_hash('goodpass', PASSWORD_DEFAULT)));

        $user = $auth->getUserByLogin('gooduser', 'goodpass');
        $this->assertNotFalse($user);
        $this->assertInstanceOf('Flipside\Auth\User', $user);

        $dataSet->raw_query('DROP TABLE tbluser;');
    }

    public function testLogin()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('authentication');
        $dataSet->raw_query('CREATE TABLE tbluser (uid varchar(255), pass varchar(255), mail varchar(255), jpegphoto varchar(255));');
        $dt = $dataSet['user'];

        $dt->create(array('uid'=>'gooduser', 'pass'=>password_hash('goodpass', PASSWORD_DEFAULT)));
 
        $res = $auth->login('baduser', 'badpass');
        $this->assertFalse($res);

        $res = $auth->login('gooduser', 'goodpass');
        $this->assertNotFalse($res);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('res', $res);
        $this->assertTrue($res['res']);

        $dt->create(array('uid'=>'jpeguser', 'pass'=>password_hash('jpegpass', PASSWORD_DEFAULT), 'jpegphoto'=>'photo'));
        $res = $auth->login('jpeguser', 'jpegpass');
        $this->assertNotFalse($res);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('res', $res);
        $this->assertTrue($res['res']);
        $this->assertArrayHasKey('extended', $res);
        $this->assertArrayHasKey('jpegphoto', $res['extended']);
        $this->assertTrue($res['extended']['jpegphoto']);

        $dataSet->raw_query('DROP TABLE tbluser;');
    }

    public function testIsLoggedIn()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $data = \Flipside\FlipSession::getVar('AuthData');
        $method = \Flipside\FlipSession::getVar('AuthMethod');
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
        $auth = \Flipside\AuthProvider::getInstance();

        $data = \Flipside\FlipSession::getVar('AuthData');
        $method = \Flipside\FlipSession::getVar('AuthMethod');
        $user = $auth->getUser($data, $method);
        $this->assertNotFalse($user);
        $this->assertInstanceOf('Flipside\Auth\User', $user);
    }

    public function testGetGroupByName()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('authentication');
        $this->assertNotFalse($dataSet->raw_query('CREATE TABLE tblgroup (cn varchar(50), description varchar(255));'), 'SQL Error: '.print_r($dataSet->getLastError(), true));

        $group = $auth->getGroupByName('BadGroup');
        $this->assertNull($group);

        $dataTable = \Flipside\DataSetFactory::getDataTableByNames('authentication', 'group');
        $this->assertTrue($dataTable->create(array('cn'=>'goodgroup', 'description'=>'Good Group')));

        $group = $auth->getGroupByName('goodgroup');
        $this->assertNotNull($group);
        $this->assertInstanceOf('Flipside\Auth\Group', $group);

        $group = $auth->getGroupByName('goodgroup', 'Flipside\Auth\SQLAuthenticator');
        $this->assertNotNull($group);
        $this->assertInstanceOf('Flipside\Auth\Group', $group);

        $dataSet->raw_query('DROP TABLE tblgroup;');
    }

    public function testUsersByFilter()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('authentication');
        $dataSet->raw_query('CREATE TABLE tbluser (uid varchar(255), pass varchar(255), mail varchar(255), jpegphoto varchar(255));');

        $dt = $dataSet['user'];
        $dt->create(array('uid'=>'gooduser', 'pass'=>password_hash('goodpass', PASSWORD_DEFAULT)));
        $dt->create(array('uid'=>'gooduser2', 'pass'=>password_hash('goodpass', PASSWORD_DEFAULT)));

        $users = $auth->getUsersByFilter(false);
        $this->assertNotNull($users);
        $this->assertCount(2, $users);

        $users = $auth->getUsersByFilter(new \Flipside\Data\Filter('uid eq "gooduser2"'));
        $this->assertNotNull($users);
        $this->assertCount(1, $users);

        $dataSet->raw_query('DROP TABLE tbluser;');
    }

    public function testGroupsByFilter()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('authentication');
        $this->assertNotFalse($dataSet->raw_query('CREATE TABLE tblgroup (cn varchar(50), description varchar(255));'), 'SQL Error: '.print_r($dataSet->getLastError(), true));

        $dataTable = \Flipside\DataSetFactory::getDataTableByNames('authentication', 'group');
        $this->assertTrue($dataTable->create(array('cn'=>'goodgroup', 'description'=>'Good Group')));
        $this->assertTrue($dataTable->create(array('cn'=>'goodgroup2', 'description'=>'Good Group 2')));

        $groups = $auth->getGroupsByFilter(false);
        $this->assertNotNull($groups);
        $this->assertCount(2, $groups);

        $groups = $auth->getGroupsByFilter(new \Flipside\Data\Filter('gid eq "goodgroup2"'));
        $this->assertNotNull($groups);
        $this->assertCount(1, $groups);

        $dataSet->raw_query('DROP TABLE tblgroup;');
    }

    public function testActiveUserCount()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $count = $auth->getActiveUserCount();
        $this->assertEquals(0, $count);

        $count = $auth->getActiveUserCount('Flipside\Auth\SQLAuthenticator');
        $this->assertEquals(0, $count);

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('authentication');
        $dataSet->raw_query('CREATE TABLE tbluser (uid varchar(255), pass varchar(255), mail varchar(255), jpegphoto varchar(255));');
        $dt = $dataSet['user'];
        $dt->create(array('uid'=>'gooduser', 'pass'=>password_hash('goodpass', PASSWORD_DEFAULT)));
        $dt->create(array('uid'=>'gooduser2', 'pass'=>password_hash('goodpass', PASSWORD_DEFAULT)));

        $count = $auth->getActiveUserCount();
        $this->assertEquals(2, $count);

        $count = $auth->getActiveUserCount('Flipside\Auth\SQLAuthenticator');
        $this->assertEquals(2, $count);

        $dataSet->raw_query('DROP TABLE tbluser;');
    }

    public function testPendingUserCount()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('pending_authentication');
        $dataSet->raw_query('CREATE TABLE tblusers (hash varchar(255), data varchar(255), time varchar(255));');

        $count = $auth->getPendingUserCount();
        $this->assertEquals(0, $count);

        $dt = \Flipside\DataSetFactory::getDataTableByNames('pending_authentication', 'users');
        $dt->create(array('hash'=>'1', 'data'=>'{}'));

        $count = $auth->getPendingUserCount('Flipside\Auth\SQLAuthenticator');
        $this->assertEquals(1, $count);

        $dataSet->raw_query('DROP TABLE tblusers;');
    }

    public function testGroupCount()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('authentication');
        $this->assertNotFalse($dataSet->raw_query('CREATE TABLE tblgroup (cn varchar(50), description varchar(255));'), 'SQL Error: '.print_r($dataSet->getLastError(), true));

        $count = $auth->getGroupCount();
        $this->assertEquals(0, $count);

        $count = $auth->getGroupCount('Flipside\Auth\SQLAuthenticator');
        $this->assertEquals(0, $count);

        $dataTable = \Flipside\DataSetFactory::getDataTableByNames('authentication', 'group');
        $this->assertTrue($dataTable->create(array('cn'=>'goodgroup', 'description'=>'Good Group')));
        $this->assertTrue($dataTable->create(array('cn'=>'goodgroup2', 'description'=>'Good Group 2')));

        $count = $auth->getGroupCount();
        $this->assertEquals(2, $count);

        $count = $auth->getGroupCount('Flipside\Auth\SQLAuthenticator');
        $this->assertEquals(2, $count);

        $dataSet->raw_query('DROP TABLE tblgroup;');
    }

    public function testMergeResult()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $full = array();
        $auth->mergeResult($full, false);
        $this->assertCount(0, $full);

        $tmp = new \MyMergeClass($this);
        $auth->mergeResult($tmp, $full);
    }

    public function testPendingUsers()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('pending_authentication');
        $dataSet->raw_query('CREATE TABLE tblusers (hash varchar(255), data varchar(255), time varchar(255));');

        $auth = \Flipside\AuthProvider::getInstance();

        $users = $auth->getPendingUsersByFilter(false);
        $this->assertEmpty($users);
        $this->assertEquals(0, $auth->getPendingUserCount());

        $dataSet->raw_query('DROP TABLE tblusers;');
    }

    public function testSuplmentLinksEmpty()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';

        $auth = \Flipside\AuthProvider::getInstance();
        $this->assertEmpty($auth->getSupplementaryLinks());

        $reflection = new ReflectionClass($auth);
        $mock = $reflection->newInstanceWithoutConstructor();

        $reflection_property = $reflection->getProperty('methods');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($mock, array(new \TestSupplmentProv()));

        $sups = $mock->getSupplementaryLinks();
        $this->assertNotEmpty($sups);
        $this->assertContains('testlink', $sups);
    }

    public function testImpersonate()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';

        $auth = \Flipside\AuthProvider::getInstance();
        $obj = new \stdClass();
        $auth->impersonateUser($obj);
        $this->assertEquals($obj, $_SESSION['flipside_user']);

        $arr = array('class'=>'\stdClass');
        $auth->impersonateUser($arr);
        $this->assertIsObject($_SESSION['flipside_user']);

        unset($_SESSION['flipside_user']);
    }

    public function testPendingUsersByHash()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';

        $auth = \Flipside\AuthProvider::getInstance();
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('pending_authentication');
        $dataSet->raw_query('CREATE TABLE tblusers (hash varchar(255), data varchar(255), time varchar(255));');
        $dt = $dataSet['users'];
        $dt->create(array('hash'=>'good', 'data'=>json_encode(array('uid'=>'test'))));

        $this->assertFalse($auth->getTempUserByHash('bad'));
        $this->assertFalse($auth->getTempUserByHash('bad', 'Flipside\Auth\SQLAuthenticator'));

        $this->assertNotFalse($auth->getTempUserByHash('good'));
        $this->assertNotFalse($auth->getTempUserByHash('good', 'Flipside\Auth\SQLAuthenticator'));

        $dataSet->raw_query('DROP TABLE tblusers;');
    }

    public function testCreatePendingUser()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('pending_authentication');
        $dataSet->raw_query('CREATE TABLE tblusers (hash varchar(255), data varchar(255), time varchar(255));');
        $dt = $dataSet['users'];

        $this->assertTrue($auth->createPendingUser(array('uid'=>'test', 'mail'=>'test@example.org')));
        $this->assertTrue($auth->createPendingUser(array('uid'=>'test1', 'mail'=>'test1@example.org'), 'Flipside\Auth\SQLAuthenticator'));

        $reflection = new ReflectionClass($auth);
        $mock = $reflection->newInstanceWithoutConstructor();

        $reflection_property = $reflection->getProperty('methods');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($mock, array(new \TestSupplmentProv()));

        $this->assertFalse($mock->createPendingUser(array('uid'=>'test', 'mail'=>'test@example.org')));

        $dataSet->raw_query('DROP TABLE tblusers;');
    }

    public function testActivatePending()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();
        $dataSet2 = \Flipside\DataSetFactory::getDataSetByName('authentication');
        $dataSet2->raw_query('CREATE TABLE tbluser (uid varchar(255), pass varchar(255), mail varchar(255), jpegphoto varchar(255));');
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('pending_authentication');
        $dataSet->raw_query('CREATE TABLE tblusers (hash varchar(255), data varchar(255), time varchar(255));');
        $dt = $dataSet['users'];

        $user = new \Flipside\Auth\PendingUser();
        $this->assertFalse($auth->activatePendingUser($user));
        $this->assertFalse($auth->activatePendingUser($user, 'Flipside\Auth\SQLAuthenticator'));

        $reflection = new ReflectionClass($auth);
        $mock = $reflection->newInstanceWithoutConstructor();

        $reflection_property = $reflection->getProperty('methods');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($mock, array(new \TestSupplmentProv()));

        $this->assertFalse($mock->activatePendingUser($user));

        $reflection_property->setValue($mock, array(new \TestCurrentProv()));

        $this->assertTrue($mock->activatePendingUser($user));

        $dataSet->raw_query('DROP TABLE tblusers;');
        $dataSet2->raw_query('DROP TABLE tbluser;');
    }

    public function testUserByResetHash()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $dataSet2 = \Flipside\DataSetFactory::getDataSetByName('authentication');
        $dataSet2->raw_query('CREATE TABLE tbluser (uid varchar(255), pass varchar(255), mail varchar(255), resetHash varchar(255), jpegphoto varchar(255));');

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('pending_authentication');
        $dataSet->raw_query('CREATE TABLE tblusers (hash varchar(255), data varchar(255), time varchar(255));');
        $dt = $dataSet['users'];

        $this->assertFalse($auth->getUserByResetHash('bad'));
        $this->assertFalse($auth->getUserByResetHash('bad', 'Flipside\Auth\SQLAuthenticator'));
        $this->assertFalse($auth->getUserByResetHash('bad', 'bad'));

        $dataSet->raw_query('DROP TABLE tblusers;');
        $dataSet2->raw_query('DROP TABLE tbluser;');
    }

    public function testSuplementalProviderHost()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $this->assertFalse($auth->getSuplementalProviderByHost('bad'));

        $reflection = new ReflectionClass($auth);
        $mock = $reflection->newInstanceWithoutConstructor();

        $reflection_property = $reflection->getProperty('methods');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($mock, array(new \TestSupplmentProv()));

        $this->assertFalse($mock->getSuplementalProviderByHost('bad'));
        $this->assertNotFalse($mock->getSuplementalProviderByHost('good'));
    }

    public function testDeletePending()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('pending_authentication');
        $dataSet->raw_query('CREATE TABLE tblusers (hash varchar(255), data varchar(255), time varchar(255));');
        $dt = $dataSet['users'];

        $this->assertTrue($dt->create(array('hash'=>'good', 'data'=>'{}', 'time'=>'')));

        $this->assertFalse($auth->deletePendingUsersByFilter(new \Flipside\Data\Filter('hash eq "bad"')));
        $this->assertTrue($auth->deletePendingUsersByFilter(new \Flipside\Data\Filter('hash eq "good"')));

        $dataSet->raw_query('DROP TABLE tblusers;');
    }
/*
    public function testAccessCode()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $auth = \Flipside\AuthProvider::getInstance();

        $this->assertFalse($auth->getUserByAccessCode('key'));
        $this->assertFalse($auth->getUserByAccessCode('key', 'Flipside\Auth\SQLAuthenticator'));
    }*/

    public static function tearDownAfterClass(): void
    {
        //unlink('/tmp/auth.sq3');
    }
}

class MyMergeClass
{
    public function __construct($test)
    {
        $this->test = $test;
    }

    public function merge($res)
    {
        $this->test->assertIsArray($res);
    }
}

class TestSupplmentProv
{
    public $current = false;
    public $pending = false;
    public $supplement = true;

    public function getSupplementLink()
    {
        return 'testlink';
    }

    public function getHostName()
    {
        return 'good';
    }
}

class TestCurrentProv
{
    public $current = true;
    public $pending = false;
    public $supplement = false;

    public function activatePendingUser($user)
    {
        return $user;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */


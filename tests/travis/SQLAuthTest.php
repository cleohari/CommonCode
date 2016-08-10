<?php
require_once('Autoload.php');
class SQLAuthTest extends PHPUnit_Framework_TestCase
{
    public function testSQLAuthenticator()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        if(!isset(FlipsideSettings::$dataset['auth']))
        {
            $params = array('dsn'=>'mysql:host=localhost;dbname=auth', 'host'=>'localhost', 'user'=>'root', 'pass'=>'');
            FlipsideSettings::$dataset['auth'] = array('type'=>'SQLDataSet', 'params'=>$params);
        }

        $dataSet = \DataSetFactory::getDataSetByName('auth');
        $dataSet->raw_query('CREATE TABLE user (uid VARCHAR(255), pass VARCHAR(255));');
        $dataSet->raw_query('CREATE TABLE tblgroup (gid VARCHAR(255), uid VARCHAR(255), description VARCHAR(255));');

        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false, 'current_data_set'=>'auth');
        $auth = new \Auth\SQLAuthenticator($params);

        $this->assertFalse($auth->login('test', 'test'));

        $dataSet->raw_query('INSERT INTO user VALUES (\'test\', \'$2y$10$bBzajdH12NSC9MOmMldfxOlozTKSS7Dyl3apWhyO53/KobKtHkoES\');');

        $res = $auth->login('test', 'test');
        $this->assertNotFalse($res);

        $this->assertFalse($auth->login('test', 'test1'));

        $this->assertTrue($auth->isLoggedIn($res));
        $this->assertFalse($auth->isLoggedIn(false));

        $user = $auth->getUser($res);
        $this->assertInstanceOf('Auth\SQLUser', $user);
        $this->assertEquals('test', $user->uid);
 
        $user = $auth->getUserByName('test');
        $this->assertInstanceOf('Auth\SQLUser', $user);
        $this->assertEquals('test', $user->uid);

        $user = $auth->getUserByName('test1');
        $this->assertNull($user);

        $group = $auth->getGroupByName('test');
        $this->assertNull($group);

        $dataSet->raw_query('INSERT INTO tblgroup VALUES (\'test\', \'test\', \'Test Group\');');

        $group = $auth->getGroupByName('test');
        $this->assertNotFalse($group);
        $this->assertInstanceOf('Auth\SQLGroup', $group);

        $user = $auth->getUserByName('test');
        $this->assertTrue($user->isInGroupNamed('test'));
        $this->assertFalse($user->isInGroupNamed('test1'));
        $user->mail = 'test@test.com';
    }

    /**
     * @depends testSQLAuthenticator
     */
    public function testFunctionsNonCurrent()
    {
        $params = array();
        $params['current'] = false;
        $params['pending'] = false;
        $params['supplement'] = false;
        $params['current_data_set'] = 'auth';
        $auth = new \Auth\SQLAuthenticator($params);
        $this->assertFalse($auth->login('test', 'test')); 
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

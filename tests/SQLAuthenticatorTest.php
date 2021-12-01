<?php
require_once('Autoload.php');
/**
* @runTestsInSeparateProcesses
* @preserveGlobalState disabled
 */
class SQLAuthenticatorTest extends PHPUnit\Framework\TestCase
{
    public function testConstrutor()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';

        $auth = new \Flipside\Auth\SQLAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false));
        $this->assertNotFalse($auth);

        $auth = new \Flipside\Auth\SQLAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'current_data_set'=>'memory'));
        $this->assertNotFalse($auth);

        $auth = new \Flipside\Auth\SQLAuthenticator(array('current'=>false, 'pending'=>true, 'supplement'=>false, 'pending_data_set'=>'memory'));
        $this->assertNotFalse($auth);
    }

    public function testChangeType()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';

        $auth = new \Flipside\Auth\SQLAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'pending_data_set'=>'bad'));
        $this->assertNotFalse($auth);

        $auth->pending = true;
        $this->expectException('\Exception');
        $this->expectExceptionMessage('Unable to obtain dataset for SQL Authentication!');

        $auth->getPendingUserCount();
    }

    public function testGeneration()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';

        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('DROP TABLE tbluser;');
        $dataSet->raw_query('DROP TABLE user;');
        $dataSet->raw_query('DROP TABLE tbltest;');
        $dataSet->raw_query('DROP TABLE test;');

        $auth = new \Flipside\Auth\SQLAuthenticator(array('current'=>true, 'pending'=>true, 'supplement'=>false, 'current_data_set'=>'memory', 'pending_data_set'=>'memory', 'pending_user_table'=>'test'));
        $this->assertNotFalse($auth);

        $this->assertFalse($auth->getUsersByFilter(false));

        $dt = $dataSet['user'];
        $dt->create(array('uid'=>'test'));

        $this->assertNotFalse($auth->getUsersByFilter(false));

        $this->assertFalse($auth->getPendingUsersByFilter(false));

        $dt = $dataSet['test'];
        $dt->create(array('hash'=>'test'));
        $this->assertNotFalse($auth->getPendingUsersByFilter(false));

        $dataSet->raw_query('DROP TABLE tbluser;');
        $dataSet->raw_query('DROP TABLE tbltest;');
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

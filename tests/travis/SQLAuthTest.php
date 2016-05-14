<?php
require_once('Autoload.php');
class SQLAuthTest extends PHPUnit_Framework_TestCase
{
    public function testSQLAuthenticator()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/travis/helpers';
        if(!isset(FlipsideSettings::$dataset['auth']))
        {
            $params = array('dsn'=>'mysql:host=localhost;dbname=auth', 'host'=>'localhost', 'user'=>'root', 'pass'=>'');
            FlipsideSettings::$dataset['auth'] = array('type'=>'SQLDataSet', 'params'=>$params);
        }

        $dataSet = \DataSetFactory::getDataSetByName('auth');
        $dataSet->raw_query('CREATE TABLE tbluser (uid VARCHAR(255), pass VARCHAR(255));');

        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false, 'current_data_set'=>'auth');
        $auth = new \Auth\SQLAuthenticator($params);

        $this->assertFalse($auth->login('test', 'test'));

        $dataSet->raw_query('INSERT INTO tbluser (\'test\', \'$2y$10$bBzajdH12NSC9MOmMldfxOlozTKSS7Dyl3apWhyO53/KobKtHkoES\');');

        $res = $auth->login('test', 'test');
        $this->assertNotFalse($res);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

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
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false, 'current_data_set'=>'auth');
        $auth = new \Auth\SQLAuthenticator($params);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

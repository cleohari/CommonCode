<?php
require_once('Autoload.php');
class DataTableSessionHandlerTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $handler = new \Flipside\Data\DataTableSessionHandler('memory', 'test');
        $this->assertFalse(false);
    }

    //public function testBadOpen()
    //{
    //    $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
    //    $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');

    //    $handler = new \Flipside\Data\DataTableSessionHandler('memory', 'badtable');
    //    $this->assertFalse($handler->open('', ''));
    //}

    public function testGoodOpen()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('CREATE TABLE session (sessionId varchar(255), sessionData varchar(255), sessionLastAccess varchar(255));');

        $handler = new \Flipside\Data\DataTableSessionHandler('memory', 'session');
        $this->assertTrue($handler->open('', ''));
        $this->assertTrue($handler->close());

        $dataSet->raw_query('DROP TABLE session;');
    }

    public function testRead()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('CREATE TABLE tblsession (sessionId varchar(255), sessionData varchar(255), sessionLastAccess varchar(255));');
        $dt = $dataSet->getTable('session');
        $dt->create(array('sessionId' => 'test', 'sessionData' => 'ABC'));

        $handler = new \Flipside\Data\DataTableSessionHandler('memory', 'session');
        $this->assertTrue($handler->open('', ''));
        $this->assertEquals('ABC', $handler->read('test'));
        $this->assertEquals('', $handler->read('badid'));
        $this->assertTrue($handler->close());

        $dataSet->raw_query('DROP TABLE tblsession;');
    }

    public function testWrite()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('CREATE TABLE tblsession (sessionId varchar(255), sessionData varchar(255), sessionLastAccess varchar(255));');
        $dt = $dataSet->getTable('session');

        $handler = new \Flipside\Data\DataTableSessionHandler('memory', 'session');
        $this->assertTrue($handler->open('', ''));
        $this->assertTrue($handler->write('test', 'ABC'));
        $this->assertTrue($handler->close());

        $dataSet->raw_query('DROP TABLE tblsession;');
    }

    public function testDestroy()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('CREATE TABLE tblsession (sessionId varchar(255), sessionData varchar(255), sessionLastAccess varchar(255));');
        $dt = $dataSet->getTable('session');
        $dt->create(array('sessionId' => 'test', 'sessionData' => 'ABC'));

        $handler = new \Flipside\Data\DataTableSessionHandler('memory', 'session');
        $this->assertTrue($handler->open('', ''));
        $this->assertTrue($handler->destroy('test'));
        $this->assertTrue($handler->close());
    }
}

/* vim: set tabstop=4 shiftwidth=4 expandtab: */

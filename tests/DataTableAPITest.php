<?php
require_once('Autoload.php');
class DataTableTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $api = new \Flipside\Http\Rest\DataTableAPI('test', 'test');
        $this->assertFalse(false);
    }

    public function testSetup()
    {
        $api = new \Flipside\Http\Rest\DataTableAPI('test', 'test');
        $test = new \TestNoKey($this, $api);
        $api->setup($test);
        $this->assertArrayHasKey('get', $test->counts);
        $this->assertEquals(1, $test->counts['get']);
        $this->assertArrayHasKey('post', $test->counts);
        $this->assertEquals(1, $test->counts['post']);

        $api = new \Flipside\Http\Rest\DataTableAPI('test', 'test', 'test');
        $test = new \TestWithKey($this, $api);
        $api->setup($test);
        $this->assertArrayHasKey('get', $test->counts);
        $this->assertEquals(2, $test->counts['get']);
        $this->assertArrayHasKey('post', $test->counts);
        $this->assertEquals(1, $test->counts['post']);
        $this->assertArrayHasKey('patch', $test->counts);
        $this->assertEquals(1, $test->counts['patch']);
        $this->assertArrayHasKey('delete', $test->counts);
        $this->assertEquals(1, $test->counts['delete']);
    }

    public function testNotLoggedIn()
    {
        $api = new \Flipside\Http\Rest\DataTableAPI('authentication', 'test');
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=text/html');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        try
        {
            $response = $api->readEntries($request, $response, null);
            $this->assertFalse(true);
        }
        catch(\Exception $e)
        {
            $this->assertFalse(false);
        }
    }

    public function testRead()
    {
        //Setup the table...
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('CREATE TABLE tbltest (a varchar(255), b varchar(255));');
        $dataSet->raw_query('INSERT INTO tbltest (a, b) VALUES ("Test", "Test");');
        $dataSet->raw_query('INSERT INTO tbltest (a, b) VALUES ("Test1", "Test1");');

        $api = new \AlwaysReadAPI('memory', 'test');

        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        $response = $api->readEntries($request, $response, null);
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('[{"a":"Test","b":"Test"},{"a":"Test1","b":"Test1"}]', $body->getContents());

        $api = new \AlwaysReadAPI('memory', 'test', 'a');
        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        $response = $api->readEntry($request, $response, array('name'=>'Test1'));
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('{"a":"Test1","b":"Test1"}', $body->getContents());

        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        $response = $api->readEntry($request, $response, array('name'=>'Test2'));
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('', $body->getContents());
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCreate()
    {
        //Setup the table...
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('CREATE TABLE tblcreate (a varchar(255), b varchar(255));');

        //Test not authorized to create...
        $api = new \AlwaysReadAPI('memory', 'create');

        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write('{"a": "Test", "b": "Test123"}');
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        $response = $api->createEntry($request, $response, null);
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('', $body->getContents());
        $this->assertEquals(401, $response->getStatusCode());

        //Test authorized to create...
        $api = new \AlwaysCreateAPI('memory', 'create');

        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $headers->set('Content-Type', 'application/json');
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write('{"a": "Test", "b": "Test123"}');
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        $response = $api->createEntry($request, $response, null);
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('true', $body->getContents());
        $this->assertEquals(200, $response->getStatusCode());

        $dt = $dataSet->getTable('create');
        $entries = $dt->read();
        $this->assertNotEmpty($entries);
        $this->assertCount(1, $entries);
        $this->assertEquals(array('a' => 'Test', 'b' => 'Test123'), $entries[0]);
    }

    public function testUpdate()
    {
        //Setup the table...
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('CREATE TABLE tblup (a varchar(255), b varchar(255));');
        $dataSet->raw_query('INSERT INTO tblup (a, b) VALUES ("Test", "Test");');
        $dataSet->raw_query('INSERT INTO tblup (a, b) VALUES ("Test1", "Test1");');

        //Test not authorized to update...
        $api = new \AlwaysReadAPI('memory', 'up', 'a');

        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write('{"b": "Test123"}');
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        $response = $api->updateEntry($request, $response, array('name' => 'Test'));
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('', $body->getContents());
        $this->assertEquals(401, $response->getStatusCode());

        //Test authorized to update...
        $api = new \AlwaysUpdateAPI('memory', 'up', 'a');

        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write('{"b": "Test123"}');
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        $response = $api->updateEntry($request, $response, array('name' => 'Test'));
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('true', $body->getContents());
        $this->assertEquals(200, $response->getStatusCode());

        $dt = $dataSet->getTable('up');
        $entry = $dt->read(new \Flipside\Data\Filter('a eq "Test"'));
        $this->assertNotEmpty($entry);
        $this->assertArrayHasKey('b', $entry[0]);
        $this->assertEquals('Test123', $entry[0]['b']);

        //Test not found...
        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write('{"b": "Test123"}');
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        $response = $api->updateEntry($request, $response, array('name' => 'Nope'));
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('', $body->getContents());
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDelete()
    {
        //Setup the table...
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('CREATE TABLE tbldelete (a varchar(255), b varchar(255));');
        $dataSet->raw_query('INSERT INTO tbldelete (a, b) VALUES ("Test", "Test");');

        //First try not authorized to delete...
        $api = new \AlwaysReadAPI('memory', 'delete', 'a');

        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        $response = $api->deleteEntry($request, $response, array('name'=>'Test'));
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('', $body->getContents());
        $this->assertEquals(401, $response->getStatusCode());

        //Now try on an API we can delete on
        $api = new \AlwaysDeleteAPI('memory', 'delete', 'a');

        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        $response = $api->deleteEntry($request, $response, array('name'=>'Test'));
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('true', $body->getContents());
        $this->assertEquals(200, $response->getStatusCode());

        //Now it should return 404...
        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();

        $response = $api->deleteEntry($request, $response, array('name'=>'Test'));
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('', $body->getContents());
        $this->assertEquals(404, $response->getStatusCode());
    }
}

class TestNoKey
{
    public function __construct($test, $api)
    {
        $this->test = $test;
        $this->api = $api;
        $this->counts = array('get' => 0, 'post' => 0);
    }

    public function get($str, $arr)
    {
        $this->counts['get']++;
        $this->test->assertEquals('[/]', $str);
        $this->test->assertEquals(array($this->api, 'readEntries'), $arr);
    }

    public function post($str, $arr)
    {
        $this->counts['post']++;
        $this->test->assertEquals('[/]', $str);
        $this->test->assertEquals(array($this->api, 'createEntry'), $arr);
    }
}

class TestWithKey
{
    public function __construct($test, $api)
    {
        $this->test = $test;
        $this->api = $api;
        $this->counts = array('get' => 0, 'post' => 0, 'patch' => 0, 'delete' => 0);
    }

    public function get($str, $arr)
    {
        $this->counts['get']++;
        if(strcmp($str, '[/]') === 0)
        {
            $this->test->assertEquals(array($this->api, 'readEntries'), $arr);
        }
        else if(strcmp($str, '/{name}[/]') === 0)
        {
            $this->test->assertEquals(array($this->api, 'readEntry'), $arr);
        }
    }

    public function post($str, $arr)
    {
        $this->counts['post']++;
        $this->test->assertEquals('[/]', $str);
        $this->test->assertEquals(array($this->api, 'createEntry'), $arr);
    }

    public function patch($str, $arr)
    {
        $this->counts['patch']++;
        $this->test->assertEquals('/{name}[/]', $str);
        $this->test->assertEquals(array($this->api, 'updateEntry'), $arr);
    }

    public function delete($str, $arr)
    {
        $this->counts['delete']++;
        $this->test->assertEquals('/{name}[/]', $str);
        $this->test->assertEquals(array($this->api, 'deleteEntry'), $arr);
    }
}

class AlwaysReadAPI extends \Flipside\Http\Rest\DataTableAPI
{
    protected function canRead($request)
    {
        return true;
    }
}

class AlwaysDeleteAPI extends \AlwaysReadAPI
{
    protected function canDelete($request, $entry)
    {
        return true;
    }
}

class AlwaysUpdateAPI extends \AlwaysReadAPI
{
    protected function canUpdate($request, $entry)
    {
        return true;
    }
}

class AlwaysCreateAPI extends \AlwaysReadAPI
{
    protected function canCreate($request)
    {
        return true;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

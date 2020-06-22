<?php
require_once('Autoload.php');
class RestAPITest extends PHPUnit\Framework\TestCase
{
    public function testNotLoggedIn()
    {
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=csv;odata.streaming=true');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $api = new \Flipside\Http\Rest\RestAPI();
        $this->expectException(Exception::class);
        $this->expectExceptionCode(\Flipside\Http\Rest\ACCESS_DENIED);
        $api->validateLoggedIn($request);
    }

    public function testLoggedIn()
    {
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=csv;odata.streaming=true');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $request = $request->withAttribute('user', true);
        $api = new \Flipside\Http\Rest\RestAPI();
        $api->validateLoggedIn($request);
        $this->assertFalse(false);
    }

    public function testGetParsedBody()
    {
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=csv;odata.streaming=true');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write('{"test": true}');
        $headers->set('Content-Type', 'application/json');
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $api = new MyTestRestAPI();

        $this->assertEquals(array('test'=> true), $api->doParseBody($request)); 

        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=csv;odata.streaming=true');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write('{"test1": true}');
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $api = new MyTestRestAPI();

        $this->assertEquals(array('test1'=> true), $api->doParseBody($request));
    }
}

class MyTestRestAPI extends \Flipside\Http\Rest\RestAPI
{
    public function doParseBody($request)
    {
        return $this->getParsedBody($request);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

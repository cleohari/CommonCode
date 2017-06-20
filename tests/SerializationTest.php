<?php
require_once('Autoload.php');
class SerializationTest extends PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $middleware = new \Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
    }

    public function testODataStreaming()
    {
        $middleware = new \Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=csv;odata.streaming=true');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals(406, $response->getStatusCode());
    }

    public function __invoke($request, $response)
    {
        return $response;
    }
}

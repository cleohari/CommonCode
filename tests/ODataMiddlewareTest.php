<?php
require_once('Autoload.php');
class ODataMiddlewareTest extends PHPUnit\Framework\TestCase
{
    public function testBasic()
    {
        $middleware = new \Http\ODataMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
    }

    public function __invoke($request, $response)
    {
        $this->assertNotNull($response);
        $odata = $request->getAttribute('odata', null);
        $this->assertNotNull($odata);
        $this->assertInstanceOf(\ODataParams::class, $odata);
        return $response;
    } 
}

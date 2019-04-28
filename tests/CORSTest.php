<?php
require_once('Autoload.php');
class CORSTest extends PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $middleware = new \Http\Rest\CORSMiddleware(null);
        $this->assertNotNull($middleware);
    }

    public function testInvoke()
    {
        $middleware = new \Http\Rest\CORSMiddleware($this);
        $this->assertNotNull($middleware);

        $uri = \Slim\Http\Uri::createFromString('https://www.burningflipside.com');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
    }

    public function __invoke($request, $response)
    {
        return $response;
    }
}

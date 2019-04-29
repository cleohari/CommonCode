<?php
require_once('Autoload.php');
class AuthMiddlewareTest extends PHPUnit\Framework\TestCase
{
    protected $prev;

    public function testNoAuth()
    {
        $middleware = new \Http\AuthMiddleware();
        $this->assertNotNull($middleware);

        $uri = \Slim\Http\Uri::createFromString('https://www.burningflipside.com');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertFalse($this->prev->getAttribute('user'));
    }

    public function testBasicAuthBad()
    {
        $middleware = new \Http\AuthMiddleware();
        $this->assertNotNull($middleware);

        $uri = \Slim\Http\Uri::createFromString('https://www.burningflipside.com');
        $headers = new \Slim\Http\Headers();
        $headers->set('Authorization', 'Basic dGVzdDpiYWQ='); //user test, pw bad
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        //$response = $middleware($request, $response, $this);
        //$this->assertFalse($this->prev->getAttribute('user'));
    }

    public function __invoke($request, $response)
    {
        $this->prev = $request;
        return $response;
    }
}

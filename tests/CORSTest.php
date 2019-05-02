<?php
require_once('Autoload.php');
class CORSTest extends PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $middleware = new \Http\Rest\CORSMiddleware(null);
        $this->assertNotNull($middleware);
    }

    public function testNoCORS()
    {
        $middleware = new \Http\Rest\CORSMiddleware($this);
        $this->assertNotNull($middleware);

        $uri = \Slim\Http\Uri::createFromString('https://www.burningflipside.com');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
    }

    public function testBasicWithBadOrigin()
    {
        $middleware = new \Http\Rest\CORSMiddleware($this);
        $this->assertNotNull($middleware);

        $uri = \Slim\Http\Uri::createFromString('https://www.burningflipside.com');
        $headers = new \Slim\Http\Headers();
        $headers->set('origin', 'http://example.com');
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals($response->getHeaderLine('Access-Control-Allow-Methods'), 'GET');
        $this->assertEquals($response->getHeaderLine('Access-Control-Allow-Headers'), 'Authorization,Cookie,apikey');
        $this->assertEquals($response->getHeaderLine('Access-Control-Allow-Origin'), '');
    }

    public function testBasicWithGoodOrigin()
    {
        $middleware = new \Http\Rest\CORSMiddleware($this);
        $this->assertNotNull($middleware);

        $uri = \Slim\Http\Uri::createFromString('https://www.burningflipside.com');
        $headers = new \Slim\Http\Headers();
        $headers->set('origin', 'https://www.burningflipside.com');
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals($response->getHeaderLine('Access-Control-Allow-Methods'), 'GET');
        $this->assertEquals($response->getHeaderLine('Access-Control-Allow-Headers'), 'Authorization,Cookie,apikey');
        $this->assertEquals($response->getHeaderLine('Access-Control-Allow-Origin'), 'https://www.burningflipside.com');
        $this->assertEquals($response->getHeaderLine('Access-Control-Allow-Credentials'), 'true');
    }

    public function testBasicWithFullRoute()
    {
        $middleware = new \Http\Rest\CORSMiddleware($this);
        $this->assertNotNull($middleware);
        $this->router = $this;

        $uri = \Slim\Http\Uri::createFromString('https://www.burningflipside.com');
        $headers = new \Slim\Http\Headers();
        $headers->set('origin', 'https://www.burningflipside.com');
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $request = $request->withAttribute('route', $this);
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals($response->getHeaderLine('Access-Control-Allow-Methods'), 'POST,PATCH');
        $this->assertEquals($response->getHeaderLine('Access-Control-Allow-Headers'), 'Authorization,Cookie,apikey');
        $this->assertEquals($response->getHeaderLine('Access-Control-Allow-Origin'), 'https://www.burningflipside.com');
        $this->assertEquals($response->getHeaderLine('Access-Control-Allow-Credentials'), 'true');
    }

    public function __invoke($request, $response)
    {
        return $response;
    }

    public function getPattern()
    {
        return 'pattern';
    }

    public function getRoutes()
    {
        return array($this);
    }

    public function getMethods()
    {
        return array('POST', 'PATCH');
    }
}

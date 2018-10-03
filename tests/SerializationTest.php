<?php
require_once('Autoload.php');
class SerializationTest extends PHPUnit\Framework\TestCase
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

    public function testAcceptHeader()
    {
        $middleware = new \Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $headers->set('Accept', 'text/csv');
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('text/csv', $response->getHeaderLine('Content-Type'));

        $headers->set('Accept', 'text/x-vCard');
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('text/x-vCard', $response->getHeaderLine('Content-Type'));

        $headers->set('Accept', '*/*');
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('application/json;charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testDataTable()
    {
        $middleware = new \Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=data-table');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('application/json;charset=utf-8', $response->getHeaderLine('Content-Type'));
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('{"data":{"test":"a"}}', $body->getContents());
    }

    public function testXML()
    {
        $middleware = new \Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=xml');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('text/xml', $response->getHeaderLine('Content-Type'));
    }

    public function testYAML()
    {
        $middleware = new \Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=yaml');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('text/x-yaml', $response->getHeaderLine('Content-Type'));
    }

    public function testExcel()
    {
        $middleware = new \Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=xls');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('application/vnd.ms-excel', $response->getHeaderLine('Content-Type'));

        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=xlsx');
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->getHeaderLine('Content-Type'));
    }

    public function __invoke($request, $response)
    {
        if($request->getAttribute('format') === 'vcard')
        {
            return $response->withHeader('Content-Type', 'text/x-vCard');
        }
        return $response->withJson(array('test'=>'a'));
    }
}

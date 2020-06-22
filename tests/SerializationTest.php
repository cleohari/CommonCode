<?php
require_once('Autoload.php');
class SerializationTest extends PHPUnit\Framework\TestCase
{
    public function testBasic()
    {
        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
    }

    public function testDeserialization()
    {
        $obj = \Flipside\SerializableObject::jsonDeserialize('{"test1": "a", "test2": 3}');
        $this->assertNotNull($obj);
        $this->assertArrayHasKey('test1', $obj);
        $this->assertEquals('a', $obj->test1);
        $this->assertArrayHasKey('test2', $obj);
        $this->assertEquals(3, $obj->test2);
    }

    public function testSerializtion()
    {
        $obj = new \Flipside\SerializableObject(array('a'=>1, 'b'=>2));
        $this->assertEquals('{"a":1,"b":2}', $obj->serializeObject());
        $this->assertEquals('{"a":1}', $obj->serializeObject('json', array('a')));

        $this->expectException(Exception::class);
        $this->assertEquals('{"a":1}', $obj->serializeObject('bad', array('a')));
    }

    public function testUnset()
    {
        $obj = new \Flipside\SerializableObject(array('a'=>1, 'b'=>2));
        $this->assertEquals('{"a":1,"b":2}', $obj->serializeObject());
        $this->assertEquals(2, $obj['b']);
        unset($obj['b']);
        $this->assertEquals('{"a":1}', $obj->serializeObject());
    }

    public function testODataStreaming()
    {
        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=csv;odata.streaming=true');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals(406, $response->getStatusCode());
    }

    public function testODataJson()
    {
        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=json');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write(json_encode(array('test', 'x')));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $body = $response->getBody();
        $body->rewind();
        $array = json_decode($body->getContents(), true);
        $this->assertEquals(array('value'=>array('test'=>'a')), $array);
    }

    public function testJsonSS()
    {
        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=json-ss');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write(json_encode(array('test', 'x')));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $body = $response->getBody();
        $body->rewind();
        $array = json_decode($body->getContents(), true);
        $this->assertEquals(array(array('test'=>'a')), $array);

        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=json-ss-dt');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write(json_encode(array('test', 'x')));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $body = $response->getBody();
        $body->rewind();
        $array = json_decode($body->getContents(), true);
        $this->assertEquals(array('data'=>array(array('test'=>'a'))), $array);

        $serializer = new \Flipside\Serialize\JsonSpreadSheet();
        $type = 'badtype';
        $this->assertNull($serializer->serializeData($type, array('test'=>0)));
        $type = 'json-ss';
        $this->assertEquals('[]', $serializer->serializeData($type, array()));
    }

    public function testAcceptHeader()
    {
        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
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
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testDataTable()
    {
        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=data-table');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $response->withHeader('content-type', 'application/json;charset=utf-8');
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('{"data":{"test":"a"}}', $body->getContents());
    }

    public function testXML()
    {
        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=xml');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $response->withHeader('content-type', 'application/json;charset=utf-8');
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('text/xml', $response->getHeaderLine('Content-Type'));
    }

    public function testYAML()
    {
        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=yaml');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $response->withHeader('content-type', 'application/json;charset=utf-8');
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('text/x-yaml', $response->getHeaderLine('Content-Type'));

        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=application/x-yaml');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $response->withHeader('content-type', 'application/json;charset=utf-8');
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('text/x-yaml', $response->getHeaderLine('Content-Type'));

        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=text/x-yaml');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $response->withHeader('content-type', 'application/json;charset=utf-8');
        $response = $middleware($request, $response, $this);
        $this->assertNotNull($response);
        $this->assertEquals('text/x-yaml', $response->getHeaderLine('Content-Type'));
    }

    public function testExcel()
    {
        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
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

    public function testOverride()
    {
        $middleware = new \Flipside\Http\Rest\SerializationMiddleware();
        $uri = \Slim\Http\Uri::createFromString('http://example.org?$format=text/html');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response();
        $response = $middleware($request, $response, new \TestSerializer());
        $this->assertNotNull($response);
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $body = $response->getBody();
        $body->rewind();
        $this->assertEquals('abc', $body->getContents());
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

class TestSerializer extends \Flipside\Serialize\Serializer
{
    public function __invoke($request, $response)
    {
        $overrides = $request->getAttribute('serializeOverrides');
        $overrides['text/html'] = '\TestSerializer';
        return $response->withJson(array('test'=>'a'));
    }

    public function serializeData(&$type, $array)
    {
        return "abc";
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

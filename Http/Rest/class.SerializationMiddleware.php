<?php
namespace Http\Rest;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

class SerializationMiddleware
{
    protected $format = null;

    private function getFormatFromHeader($request)
    {
        $mimeType = $request->getHeaderLine('Accept');
        switch($mimeType)
        {
            case 'text/csv':
                return 'csv';
            case 'text/x-vCard':
                return 'vcard';
            default:
                return 'json';
        }
    }

    private function getParamFromArrayIfSet($array, $param, $default = null)
    {
        if(isset($array[$param]))
        {
            return $array[$param];
        }
        return $default;
    }

    private function getFormat($request, $response)
    {
        $params = $request->getQueryParams();
        $this->format = $this->getParamFromArrayIfSet($params, 'fmt');
        if($this->format === null)
        {
            $this->format = $this->getParamFromArrayIfSet($params, '$format');
            if($this->format === null)
            {
                $this->format = $this->getFormatFromHeader($request);
            }
            else if($this->format === 'json')
            {
                //OData json is different from notmal json
                $this->format = 'odata-json';
            }
        }
        if(strstr($this->format, 'odata.streaming=true'))
        {
            return $response->withStatus(406);
        }
        return $response;
    }

    protected function reserializeBody($response, $serializer)
    {
        $body = $response->getBody();
        $body->rewind();
        $data = json_decode($body->getContents());
        $serializer = new $serializer();
        $res = $serializer->serializeData($this->format, $data);
        $response = $response->withBody(new \Slim\Http\Body(fopen('php://temp', 'r+')));
        $response->getBody()->write($res);
        return $response->withHeader('Content-Type', $this->format);
    }

    public function __invoke($request, $response, $next)
    {
        $response = $this->getFormat($request, $response);
        if($response->getStatusCode() !== 200)
        {
            return $response;
        }
        $request = $request->withAttribute('format', $this->format);
        $response = $next($request, $response);
        if($response->getHeaderLine('Content-Type') !== 'application/json;charset=utf-8')
        {
            //The underlying API call gave us back a different content type. Just pass that on...
            return $response;
        }
        switch($this->format)
        {
            case 'application/json':
            case 'application/x-javascript':
            case 'text/javascript':
            case 'text/x-javascript':
            case 'text/x-json':
            case 'json':
                return $response;
            case 'json-ss':
            case 'json-ss-dt':
                return $this->reserializeBody($response, '\Serialize\JsonSpreadSheet');
            case 'data-table':
                //This is a special case for json...
                $body = $response->getBody();
                $body->rewind();
                $data = json_decode($body->getContents());
                return $response->withJson(array('data'=>$data));
            case 'odata-json':
                //This is a special case for json...
                $body = $response->getBody();
                $body->rewind();
                $data = json_decode($body->getContents());
                return $response->withJson(array('value'=>$data));
            case 'xml':
            case 'application/xml':
            case 'text/xml':
                return $this->reserializeBody($response, '\Serialize\XMLSerializer');
            case 'csv':
            case 'text/csv':
                return $this->reserializeBody($response, '\Serialize\CSVSerializer');
            case 'xlsx':
            case 'xls':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            case 'application/vnd.ms-excel':
                return $this->reserializeBody($response, '\Serialize\ExcelSerializer');
            case 'yaml':
            case 'application/x-yaml':
            case 'text/x-yaml':
                return $this->reserializeBody($response, '\Serialize\YAMLSerializer');
            default:
                print_r($this->format); die();
                break;
        }
        return $response;
    }
}

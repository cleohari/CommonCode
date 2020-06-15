<?php
namespace Flipside\Http;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

class ODataMiddleware
{
    public function __invoke($request, $response, $next)
    {
        $params = $request->getQueryParams();
        $odata = new \Flipside\ODataParams($params);
        $request = $request->withAttribute('odata', $odata);
        $response = $next($request, $response);
        return $response;
    }
}

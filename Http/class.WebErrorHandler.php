<?php
namespace Http;

class WebErrorHandler
{
    public function __invoke($request, $response, $exception)
    {
        if($exception->getCode() === \Http\Rest\ACCESS_DENIED)
        {
            return $response->withStatus(401);
        }
        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write(print_r($exception, true));
   }
}

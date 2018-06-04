<?php
namespace Http;

class WebErrorHandler
{
    public function __invoke($request, $response, $exception)
    {
        if($exception->getCode() === \Http\Rest\ACCESS_DENIED)
        {
            $response->getBody()->write('You are not authorized to view this page. The most common cause of this is that you are not logged in to the website. Please log in then try again');
            return $response->withStatus(401);
        }
        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write($exception->__toString());
   }
}

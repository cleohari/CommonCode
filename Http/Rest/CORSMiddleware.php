<?php
namespace Flipside\Http\Rest;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CORSMiddleware
{
    protected $container;
    protected $allowedOrigins;

    public function __construct($c)
    {
        $settings = \Flipside\Settings::getInstance();
        $this->container = $c;
        $this->allowedOrigins = array(
            $settings->getGlobalSetting('www_url', 'https://www.burningflipside.com'),
            $settings->getGlobalSetting('wiki_url', 'https://wiki.burningflipside.com'),
            $settings->getGlobalSetting('profiles_url', 'https://profiles.burningflipside.com'),
            $settings->getGlobalSetting('secure_url', 'https://secure.burningflipside.com')
        );
    }

    public function __invoke($request, $response, $next)
    {
        $route = $request->getAttribute("route");
        $methods = [];

        if(!empty($route))
        {
            $pattern = $route->getPattern();
            foreach($this->container->router->getRoutes() as $route)
            {
                if($pattern === $route->getPattern())
                {
                    $methods = array_merge_recursive($methods, $route->getMethods());
                }
            }
        }
        else
        {
            array_push($methods, $request->getMethod());
        }
        $response = $next($request, $response);
        $origin = $request->getHeaderLine('origin');
        if($origin === '')
        {
            return $response;
        }
        if(in_array($origin, $this->allowedOrigins))
        {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }
        $response = $response->withHeader('Access-Control-Allow-Headers', 'Authorization,Cookie,apikey');
        return $response->withHeader("Access-Control-Allow-Methods", implode(",", $methods));
    }
}

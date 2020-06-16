<?php
namespace Flipside\Http;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

class WebSite extends \Slim\App
{
    public function __construct()
    {
        $settings = array("settings"=>["determineRouteBeforeAppMiddleware"=>true]);
        parent::__construct($settings);
        $c = $this->getContainer();
        $c['errorHandler'] = function($c) { return new WebErrorHandler();};
        $this->add(new AuthMiddleware());
        $this->add(new ODataMiddleware());
    }

    public function registerPage($uri, $page)
    {
        $this->get($uri, array($page, 'handleRequest'));
    }

    public function registerAPI($uri, $api)
    {
        $group = $this->group($uri, function() use($api){$api->setup($this);});
        $group->add(new \Flipside\Http\Rest\SerializationMiddleware());
        $group->add(new \Flipside\Http\Rest\CORSMiddleware($this->getContainer()));
    }
}

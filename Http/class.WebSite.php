<?php
namespace Http;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

class WebSite extends \Slim\App
{
    public function registerPage($uri, $page)
    {
        $this->get($uri, array($page, 'handleRequest'));
    }

    public function registerAPI($baseUri, $api)
    {
        $this->any($baseUri, $api);
    }
}

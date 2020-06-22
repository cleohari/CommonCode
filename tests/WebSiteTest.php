<?php
require_once('Autoload.php');
class WebSiteTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $site = new \Flipside\Http\WebSite();
        $this->assertFalse(false);
    }

    public function testPage()
    {
        $site = new \Flipside\Http\WebSite();
        $page = new \Flipside\Http\WebPage('Test');
        $site->registerPage('/', $page);
        $c = $site->getContainer();
        $this->assertNotNull($c);
        $routes = $c->get('router')->getRoutes();
        $this->assertCount(1, $routes);
        $route = array_shift($routes); 
        $this->assertEquals(array($page, 'handleRequest'), $route->getCallable());
    }

    public function testAPI()
    {
        $site = new \Flipside\Http\WebSite();
        $site->registerAPI('/', new \TestAPI($this));
    }
}

class TestAPI extends \Flipside\Http\Rest\RestAPI
{
    public function __construct($test)
    {
        $this->test = $test;
    }

    public function setup($app)
    {
        $this->test->assertInstanceOf('Flipside\Http\WebSite', $app);
        parent::setup($app);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

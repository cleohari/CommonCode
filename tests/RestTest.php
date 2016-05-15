<?php
require_once('Autoload.php');
class RestTest extends PHPUnit_Framework_TestCase
{
    public function testFlipRest()
    {
        $app = new \FlipREST();
        $this->assertInstanceOf('FlipREST', $app);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_NAME'] = 'localhost';

        ob_start();
        $app->run();
        $html = ob_get_contents();
        ob_end_clean();
        $this->assertTrue(true);

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        $elements = $doc->getElementsByTagName('title');
        $this->assertEquals(1, $elements->length);
        $node = $elements->item(0);
        $this->assertStringStartsWith('404', $node->nodeValue);

        $data = array('uid'=>'test');
        \FlipSession::setUser(new \Auth\SQLUser($data));
        ob_start();
        $app->run();
        $html = ob_get_contents();
        ob_end_clean();

        $this->assertNotFalse($app->user);
    }

    public function call()
    {
    }

    public function testBasicAuth()
    {
        $headers = array('Authorization'=>'Basic aHR0cHdhdGNoOmY=');
        $_SERVER['PHP_AUTH_USER'] = 'test';
        $_SERVER['PHP_AUTH_PW'] = 'test';
        $plugin = new \OAuth2Auth($headers);
        $app = new \stdClass();
        $plugin->setApplication($app);
        $plugin->setNextMiddleware($this);
        $plugin->call();
        $this->assertNotFalse($app->user);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

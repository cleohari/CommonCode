<?php
require_once('Autoload.php');
class WebPageTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $page = new \Flipside\Http\WebPage('Test');
        $this->assertNotNull($page);
        $this->assertNotNull($page->body);
        $this->assertNotNull($page->content);
    }

    public function testAddTemplate()
    {
        $page = new \Flipside\Http\WebPage('Test');
        try
        {
            $page->addTemplateDir('.', 'Test');
            $this->assertFalse(false);
        }
        catch(\Exception $e)
        {
            $this->assertFalse(true);
        }
    }

    public function testTemplateName()
    {
        $page = new \Flipside\Http\WebPage('Test');
        try
        {
            $page->setTemplateName('Test.html');
            $this->assertFalse(false);
        }
        catch(\Exception $e)
        {
            $this->assertFalse(true);
        }
    }

    public function testAddCSS()
    {
        $page = new \Flipside\Http\WebPage('Test');
        $page->addCSS('http://example.org/test.css');
        $this->assertNotNull($page->content);
        $this->assertArrayHasKey('css', $page->content);
        $this->assertIsarray($page->content['css']);
        $this->assertContains('http://example.org/test.css', $page->content['css']);

        $page = new \Flipside\Http\WebPage('Test');
        $page->addWellKnownCSS(CSS_BOOTSTRAP_FH);
        $this->assertNotNull($page->content);
        $this->assertArrayHasKey('css', $page->content);
        $this->assertIsarray($page->content['css']);
        $this->assertContains('//cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/css/bootstrap-formhelpers.min.css', $page->content['css']);
    }

    public function testAddJS()
    {
        $page = new \Flipside\Http\WebPage('Test');
        $page->addJS('http://example.org/test.js');
        $this->assertNotNull($page->content);
        $this->assertArrayHasKey('js', $page->content);
        $this->assertIsarray($page->content['js']);
        $this->assertContains('http://example.org/test.js', $page->content['js']);

        $page = new \Flipside\Http\WebPage('Test');
        $page->addWellKnownJS(JQUERY_VALIDATE);
        $this->assertNotNull($page->content);
        $this->assertArrayHasKey('js', $page->content);
        $this->assertIsarray($page->content['js']);
        $this->assertContains('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js', $page->content['js']);

        $page = new \Flipside\Http\WebPage('Test');
        $page->addWellKnownJS(JS_BOOTBOX);
        $this->assertNotNull($page->content);
        $this->assertArrayHasKey('securejs', $page->content);
        $this->assertIsarray($page->content['securejs']);
    }

    public function testAddLink()
    {
        $page = new \Flipside\Http\WebPage('Test');
        $page->addLink('Test');
        $this->assertNotNull($page->content);
        $this->assertArrayHasKey('header', $page->content);
        $this->assertArrayHasKey('right', $page->content['header']);
        $this->assertArrayHasKey('Test', $page->content['header']['right']);
        $this->assertArrayHasKey('url', $page->content['header']['right']['Test']);
        $this->assertFalse($page->content['header']['right']['Test']['url']);

        $page = new \Flipside\Http\WebPage('Test');
        $page->addLink('Test 123', 'test.php');
        $this->assertNotNull($page->content);
        $this->assertArrayHasKey('header', $page->content);
        $this->assertArrayHasKey('right', $page->content['header']);
        $this->assertArrayHasKey('Test 123', $page->content['header']['right']);
        $this->assertArrayHasKey('url', $page->content['header']['right']['Test 123']);
        $this->assertEquals('test.php', $page->content['header']['right']['Test 123']['url']);

        $page = new \Flipside\Http\WebPage('Test');
        $sub = array('Test'=>'test.php');
        $page->addLink('Test 123', false, $sub);
        $this->assertNotNull($page->content);
        $this->assertArrayHasKey('header', $page->content);
        $this->assertArrayHasKey('right', $page->content['header']);
        $this->assertArrayHasKey('Test 123', $page->content['header']['right']);
        $this->assertArrayHasKey('url', $page->content['header']['right']['Test 123']);
        $this->assertFalse($page->content['header']['right']['Test 123']['url']);
        $this->assertArrayHasKey('menu', $page->content['header']['right']['Test 123']);
        $this->assertEquals($sub, $page->content['header']['right']['Test 123']['menu']);
    }

    public function testAddNotification()
    {
        $page = new \Flipside\Http\WebPage('Test');
        $page->addNotification('Test');
        $this->assertNotNull($page->content);
        $this->assertArrayHasKey('notifications', $page->content);
        $this->assertArrayHasKey('msg', $page->content['notifications'][0]);
        $this->assertEquals('Test', $page->content['notifications'][0]['msg']);

        $page = new \Flipside\Http\WebPage('Test');
        $page->addNotification('Test', \Flipside\Http\WebPage::NOTIFICATION_SUCCESS);
        $this->assertNotNull($page->content);
        $this->assertArrayHasKey('notifications', $page->content);
        $this->assertArrayHasKey('msg', $page->content['notifications'][0]);
        $this->assertEquals('Test', $page->content['notifications'][0]['msg']);
        $this->assertArrayHasKey('sev', $page->content['notifications'][0]);
        $this->assertEquals('alert-success', $page->content['notifications'][0]['sev']);
    }

    public function testPrinting()
    {
        $page = new \Flipside\Http\WebPage('Test');
        ob_start();
        $page->printPage();
        $html = ob_get_clean();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $this->assertTrue($doc->loadHTML($html));
        libxml_clear_errors();

        $page = new \Flipside\Http\WebPage('Test');
        $uri = \Slim\Http\Uri::createFromString('http://example.org/test.php');
        $headers = new \Slim\Http\Headers();
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $request = new \Slim\Http\Request('GET', $uri, $headers, array(), array(), $body);
        $response = new \Slim\Http\Response(); 
        $response = $page->handleRequest($request, $response, null);
        $this->assertNotNull($response);
        $body = $response->getBody();
        $body->rewind();
        $html = $body->getContents();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $this->assertTrue($doc->loadHTML($html));
        libxml_clear_errors();
    }

    public function testCurrentUrl()
    {
        $page = new \Flipside\Http\WebPage('Test');
        $this->assertEquals('', $page->currentURL());

        $_SERVER['REQUEST_URI'] = '/test.php';
        $this->assertEquals('http://example.com/test.php', $page->currentURL());

        $_SERVER['HTTPS'] = true;
        $this->assertEquals('https://example.com/test.php', $page->currentURL());
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

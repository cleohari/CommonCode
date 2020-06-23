<?php
require_once('Autoload.php');
class LoginRequiredPageTest extends PHPUnit\Framework\TestCase
{
    public function testPrinting()
    {
        $page = new \MyLoginRequiredPage(false);
        ob_start();
        $page->printPage();
        $html = ob_get_clean();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $this->assertTrue($doc->loadHTML($html));
        $this->assertStringNotContainsString('Protected content', $html);
        libxml_clear_errors();

        $page = new \MyLoginRequiredPage(true);
        ob_start();
        $page->printPage();
        $html = ob_get_clean();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $this->assertTrue($doc->loadHTML($html));
        $this->assertStringContainsString('Protected content', $html);
        libxml_clear_errors();
    }
}

class MyLoginRequiredPage extends \Flipside\Http\LoginRequiredPage
{
    public function __construct($user)
    {
        parent::__construct('Test');
        $this->user = $user;
        $this->content['body'] = 'Protected content';
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

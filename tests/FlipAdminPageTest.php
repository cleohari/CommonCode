<?php
require_once('Autoload.php');
class FlipAdminPageTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $page = new \Flipside\Http\FlipAdminPage('Test');
        $this->assertNotNull($page);
        $this->assertNotNull($page->body);
        $this->assertNotNull($page->content);
        $this->assertFalse($page->is_admin);

        $page = new \Flipside\Http\FlipAdminPage('Test', 'Group');
        $this->assertNotNull($page);
        $this->assertNotNull($page->body);
        $this->assertNotNull($page->content);
        $this->assertFalse($page->is_admin);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

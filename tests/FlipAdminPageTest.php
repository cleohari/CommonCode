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
        $this->assertFalse($page->isAdmin());
    }

    public function testUserIsAdmin()
    {
        $page = new \FlipAdminUserPage(false);
        $this->assertFalse($page->getAdmin());

        $page = new \FlipAdminUserPage(null);
        $this->assertFalse($page->getAdmin());

        $page = new \FlipAdminUserPage(new \FlipAdminGoodUser());
        $this->assertTrue($page->getAdmin());
    }

    public function testAddCard()
    {
        $page = new \Flipside\Http\FlipAdminPage('Test', 'Group');
        $this->assertNotNull($page);
        $page->addCard('test', 'text', 'link.php');
        $this->assertArrayHasKey('cards', $page->content);
        $this->assertEquals(array('icon' => 'test', 'text' => 'text', 'link' => 'link.php'), $page->content['cards'][0]);
        $page->addCard('test1', 'text1', 'link1.php', \Flipside\Http\FlipAdminPage::CARD_BLUE);
        $this->assertArrayHasKey('cards', $page->content);
        $this->assertEquals(array('icon' => 'test1', 'text' => 'text1', 'link' => 'link1.php', 'color' => 'blue'), $page->content['cards'][1]);
    }

    public function testGetContent()
    {
        $page = new \Flipside\Http\FlipAdminPage('Test', 'Group');
        $this->assertNotNull($page);

        ob_start();
        $page->printPage();
        $html = ob_get_clean();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $this->assertTrue($doc->loadHTML($html));
        libxml_clear_errors();
        $this->assertStringContainsString('You must be an administrator to access the Test system!', $html);

        $page = new \FlipAdminUserPage(null);
        ob_start();
        $page->printPage();
        $html = ob_get_clean();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $this->assertTrue($doc->loadHTML($html));
        libxml_clear_errors();
        $this->assertStringContainsString('You must <a href=', $html);
    }
}

class FlipAdminUserPage extends \Flipside\Http\FlipAdminPage
{
    public function __construct($user)
    {
        parent::__construct('test', 'group');
        $this->user = $user;
    }

    public function getAdmin()
    {
        return $this->userIsAdmin('group');
    }
}

class FlipAdminGoodUser extends \Flipside\Auth\User
{
    public function isInGroupNamed($name)
    {
        return true;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

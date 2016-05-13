<?php
require_once('Autoload.php');
require_once('./tests/helpers/HTML5Validate.php');
class PageValidTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyPage()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $GLOBALS['BROWSCAP_CACHE']        = './tests/helpers';
        $page = new FlipPage('Test', false);
        ob_start();
        $page->printPage();
        $html = ob_get_contents();
        ob_end_clean();


        $validator = new HTML5Validate();
        $result = $validator->Assert($html);
        $this->assertTrue($result, $validator->message);
    }

    public function testEmptyPageWHeader()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $GLOBALS['BROWSCAP_CACHE']        = './tests/helpers';
        $page = new FlipPage('Test');
        ob_start();
        $page->printPage();
        $html = ob_get_contents();
        ob_end_clean();


        $validator = new HTML5Validate();
        $result = $validator->Assert($html);
        $this->assertTrue($result, $validator->message);
    }
}
?>

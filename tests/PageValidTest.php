<?php
require_once('Autoload.php');

use KubaWerlos\HtmlValidator\Validator;

class PageValidTest extends PHPUnit_Framework_TestCase
{
/* Disable until I can find a good HTML 5 validator
    public function testEmptyPage()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $GLOBALS['BROWSCAP_CACHE']        = './tests/helpers/browscap';
        $page = new FlipPage('Test', false);
        ob_start();
        $page->printPage();
        $html = ob_get_contents();
        ob_end_clean();

        $errors = Validator::validate($html);
        $this->assertEmpty($errors);
    }

    public function testEmptyPageWHeader()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $GLOBALS['BROWSCAP_CACHE']        = './tests/helpers/browscap';
        $page = new FlipPage('Test');
        ob_start();
        $page->printPage();
        $html = ob_get_contents();
        ob_end_clean();

        $errors = Validator::validate($html);
        $this->assertEmpty($errors);
    }
*/
}

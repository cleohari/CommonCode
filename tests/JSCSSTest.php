<?php
require('static.js_css.php');
class JSSCSSTest extends PHPUnit\Framework\TestCase
{
    public function testJS()
    {
        global $jsArray;
        foreach($jsArray as $js)
        {
            $this->assertArrayHasKey('cdn', $js);
            $this->assertArrayHasKey('no', $js);
            $this->assertArrayHasKey('min', $js['cdn']);
            $this->assertArrayHasKey('no', $js['cdn']);
            $this->assertArrayHasKey('min', $js['no']);
            $this->assertArrayHasKey('no', $js['no']);
        }
    }

    public function testCSS()
    {
        global $cssArray;
        foreach($cssArray as $css)
        {
            $this->assertArrayHasKey('cdn', $css);
            $this->assertArrayHasKey('no', $css);
            $this->assertArrayHasKey('min', $css['cdn']);
            $this->assertArrayHasKey('no', $css['cdn']);
            $this->assertArrayHasKey('min', $css['no']);
            $this->assertArrayHasKey('no', $css['no']);
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

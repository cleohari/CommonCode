<?php
require_once('Autoload.php');
class ODataParamsTest extends PHPUnit\Framework\TestCase
{
    public function testOldStyle()
    {
        $params = array();
        $params['filter'] = 'year eq 2020';
        $params['select'] = 'd,e,f';
        $odata = new \ODataParams($params);
        $this->assertNotFalse($odata->filter);
        $this->assertNotFalse($odata->select);
        $this->assertNotFalse($odata->filter->contains('year eq 2020'));
        $this->assertCount(3, $odata->select); 
        $this->assertContains('d', $odata->select); 
        $this->assertContains('e', $odata->select);
        $this->assertContains('f', $odata->select);
    }

    public function testNewStyleFilter()
    {
        $params = array();
        $params['$filter'] = 'sold eq 1';
        $odata = new \ODataParams($params);
        $this->assertNotFalse($odata->filter);
        $this->assertTrue($odata->filter->contains('sold eq 1'));
    }

    public function testNewStyleSelect()
    {
        $params = array();
        $params['$select'] = 'test1,a,1,Zz';
        $odata = new \ODataParams($params);
        $this->assertNotFalse($odata->select);
        $this->assertCount(4, $odata->select);
        $this->assertContains('test1', $odata->select);
        $this->assertContains('a', $odata->select);
        $this->assertContains('1', $odata->select);
        $this->assertContains('Zz', $odata->select);
    }

    public function testExpand()
    {
        $params = array();
        $params['$expand'] = 'tickets,donations';
        $odata = new \ODataParams($params);
        $this->assertNotFalse($odata->expand);
        $this->assertCount(2, $odata->expand);
        $this->assertContains('tickets', $odata->expand);
        $this->assertContains('donations', $odata->expand);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

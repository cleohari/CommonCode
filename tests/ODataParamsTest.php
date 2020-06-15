<?php
require_once('Autoload.php');
class ODataParamsTest extends PHPUnit\Framework\TestCase
{
    public function testOldStyle()
    {
        $params = array();
        $params['filter'] = 'year eq 2020';
        $params['select'] = 'd,e,f';
        $odata = new \Flipside\ODataParams($params);
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
        $odata = new \Flipside\ODataParams($params);
        $this->assertNotFalse($odata->filter);
        $this->assertTrue($odata->filter->contains('sold eq 1'));
    }

    public function testNewStyleSelect()
    {
        $params = array();
        $params['$select'] = 'test1,a,1,Zz';
        $odata = new \Flipside\ODataParams($params);
        $this->assertNotFalse($odata->select);
        $this->assertCount(4, $odata->select);
        $this->assertContains('test1', $odata->select);
        $this->assertContains('a', $odata->select);
        $this->assertContains('1', $odata->select);
        $this->assertContains('Zz', $odata->select);

        $array = array(array('test1'=>1, 'a'=>1, '1'=>1, 'Zz'=>1, 'T'=>1));
        $filtered = $odata->filterArrayPerSelect($array);
        $this->assertEquals(array(array('test1'=>1, 'a'=>1, '1'=>1, 'Zz'=>1)), $filtered);

        $array = array(new \Flipside\SerializableObject(array('test1'=>1, 'a'=>1, '1'=>1, 'Zz'=>1, 'T'=>1)));
        $filtered = $odata->filterArrayPerSelect($array);
        $this->assertEquals(array(array('test1'=>1, 'a'=>1, '1'=>1, 'Zz'=>1)), $filtered);

        $params = array();
        $odata = new \Flipside\ODataParams($params);
        $this->assertFalse($odata->select);
        $array = array(array('test1'=>1, 'a'=>1, '1'=>1, 'Zz'=>1, 'T'=>1));
        $filtered = $odata->filterArrayPerSelect($array);
        $this->assertEquals(array(array('test1'=>1, 'a'=>1, '1'=>1, 'Zz'=>1, 'T'=>1)), $filtered);
    }

    public function testExpand()
    {
        $params = array();
        $params['$expand'] = 'tickets,donations';
        $odata = new \Flipside\ODataParams($params);
        $this->assertNotFalse($odata->expand);
        $this->assertCount(2, $odata->expand);
        $this->assertContains('tickets', $odata->expand);
        $this->assertContains('donations', $odata->expand);
    }

    public function testTop()
    {
        $params = array();
        $params['$top'] = 1;
        $odata = new \Flipside\ODataParams($params);
        $this->assertNotFalse($odata->top);
        $this->assertEquals(1, $odata->top);

        $params = array();
        $params['$top'] = '1';
        $odata = new \Flipside\ODataParams($params);
        $this->assertNotFalse($odata->top);
        $this->assertEquals(1, $odata->top);
    }

    public function testSkip()
    {
        $params = array();
        $params['$skip'] = 1;
        $odata = new \Flipside\ODataParams($params);
        $this->assertNotFalse($odata->skip);
        $this->assertEquals(1, $odata->skip);

        $params = array();
        $params['$skip'] = '1';
        $odata = new \Flipside\ODataParams($params);
        $this->assertNotFalse($odata->skip);
        $this->assertEquals(1, $odata->skip);
    }

    public function testCount()
    {
        $params = array();
        $params['$count'] = 'true';
        $odata = new \Flipside\ODataParams($params);
        $this->assertNotFalse($odata->count);

        $params = array();
        $odata = new \Flipside\ODataParams($params);
        $this->assertFalse($odata->count);
    }

    public function testOrderBy()
    {
        $params = array();
        $params['$orderby'] = 'test';
        $odata = new \Flipside\ODataParams($params);
        $this->assertNotFalse($odata->orderby);
        $this->assertEquals(array('test'=>1), $odata->orderby);

        $params = array();
        $params['$orderby'] = 'test asc';
        $odata = new \Flipside\ODataParams($params);
        $this->assertNotFalse($odata->orderby);
        $this->assertEquals(array('test'=>1), $odata->orderby);

        $params = array();
        $params['$orderby'] = 'test desc';
        $odata = new \Flipside\ODataParams($params);
        $this->assertNotFalse($odata->orderby);
        $this->assertEquals(array('test'=>-1), $odata->orderby);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

<?php
require_once('Autoload.php');
class CSVDTTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        try
        {
            $dt = new \Flipside\Data\CSVDataTable(__DIR__ . '/helpers/bad.csv');
            $this->assertFalse(true);
        }
        catch(\Exception $e)
        {
            $this->assertFalse(false);
        }
        $dt = new \Flipside\Data\CSVDataTable(__DIR__ . '/helpers/good.csv');
        $this->assertFalse(false);
    }

    public function testCount()
    {
        $dt = new \Flipside\Data\CSVDataTable(__DIR__ . '/helpers/good.csv');
        $this->assertEquals(2, $dt->count());

        $this->assertEquals(1, $dt->count(new \Flipside\Data\Filter('Test1 eq a')));

        $this->assertEquals(0, $dt->count(new \Flipside\Data\Filter('Testz eq q')));
    }

    public function testRead()
    {
        $dt = new \Flipside\Data\CSVDataTable(__DIR__ . '/helpers/good.csv');
        $data = $dt->read();
        $this->assertNotEmpty($data);
        $this->assertIsArray($data);
        $this->assertCount(2, $data);

        $data = $dt->read(new \Flipside\Data\Filter('Test1 eq 1'));
        $this->assertNotEmpty($data);
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('1', $data[0]['Test1']);
    }

    public function testCreateUpdateDelete()
    {
        $dt = new \Flipside\Data\CSVDataTable(__DIR__ . '/helpers/good.csv');
        try
        {
            $dt->create(array('Test1'=>'q'));
            $this->assertFalse(true);
        }
        catch(\Exception $e)
        {
            $this->assertFalse(false);
        }
        try
        {
            $dt->update(new \Flipside\Data\Filter('Test1 eq a'), array('Test1'=>'q'));
            $this->assertFalse(true);
        }
        catch(\Exception $e)
        {
            $this->assertFalse(false);
        }
        try
        {
            $dt->delete(new \Flipside\Data\Filter('Test1 eq a'));
            $this->assertFalse(true);
        }
        catch(\Exception $e)
        {
            $this->assertFalse(false);
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

<?php
require_once('Autoload.php');
class DataSetTest extends PHPUnit_Framework_TestCase
{
    public function testDataSet()
    {
        $dataSet = new \Data\DataSet();
        $dataSet->offsetSet(0, 'test');
        $this->assertTrue(true);
        $dataSet->offsetSet('a', 'test');
        $this->assertTrue(true);
        $dataSet[0] = 'test';
        $this->assertTrue(true);
        $dataSet['a'] = 'test';
        $this->assertTrue(true);
        try
        {
            isset($dataSet['a']);
            $this->assertTrue(false);
        }
        catch(\Exception $ex)
        {
            $this->assertTrue(true);
        }
        try
        {
            isset($dataSet[0]);
            $this->assertTrue(false);
        }
        catch(\Exception $ex)
        {
            $this->assertTrue(true);
        }
        unset($dataSet['a']);
        unset($dataSet[0]);
        try
        {
            $data = $dataSet['a'];
            $this->assertTrue(false);
        }
        catch(\Exception $ex)
        {
            $this->assertTrue(true);
        }
        try
        {
            $data = $dataSet[0];
            $this->assertTrue(false);
        }
        catch(\Exception $ex)
        {
            $this->assertTrue(true);
        }
        try
        {
            $dataSet->raw_query('SELECT * from tblUsers WHERE 1');
            $this->assertTrue(false);
        }
        catch(\Exception $ex)
        {
            $this->assertTrue(true);
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

<?php
require_once('Autoload.php');
class MongoDataTableTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $table = new \Flipside\Data\MongoDataTable(new \MyMongoMockCollection());
        $this->assertNotNull($table);

        $table = new \Flipside\Data\MongoDataTable(new \MyMongoMockCollection(), 'Test');
        $this->assertNotNull($table);
    }

    public function testCount()
    {
        $table = new \Flipside\Data\MongoDataTable(new \MyMongoMockCollection(), 'Count0');
        $this->assertNotNull($table);

        $this->assertEquals(0, $table->count());

        $table = new \Flipside\Data\MongoDataTable(new \MyMongoMockCollection(), 'Count1');
        $this->assertNotNull($table);

        $this->assertEquals(1, $table->count());

        $table = new \Flipside\Data\MongoDataTable(new \MyMongoMockCollection($this), 'FilterTest');
        $this->assertNotNull($table);

        $this->assertEquals(0, $table->count(array()));
        $this->assertEquals(0, $table->count(new \Flipside\Data\Filter('test eq 1')));
    }

    public function testCreate()
    {
        $table = new \Flipside\Data\MongoDataTable(new \MyMongoMockCollection(), 'Create0');
        $this->assertNotNull($table);

        $this->assertFalse($table->create(array('test' => 1)));

        $table = new \Flipside\Data\MongoDataTable(new \MyMongoMockCollection(), 'Create1');
        $this->assertNotNull($table);

        $this->assertEquals('Test passed', $table->create(array('test' => 1)));
    }

    public function testUpdate()
    {
        $table = new \Flipside\Data\MongoDataTable(new \MyMongoMockCollection(), 'Update0');
        $this->assertNotNull($table);

        $data = array('_id'  => 'test');
        $this->assertFalse($table->update(false, $data));
    }

    public function testDelete()
    {
        $table = new \Flipside\Data\MongoDataTable(new \MyMongoMockCollection(), 'Delete0');
        $this->assertNotNull($table);

        $this->assertFalse($table->delete(array()));
    }
}

class MyMongoMockCollection
{
    public function __construct($test = false)
    {
        $this->test = $test;
    }

    public function count($crit, $dummy, $name)
    {
        switch($name)
        {
        case 'Count0':
            return 0;
        case 'Count1':
            return 1;
        case 'FilterTest':
            $this->test->assertIsArray($crit);
            return 0;
        }
    }

    public function insert(&$data, $dummy, $name)
    {
        switch($name)
        {
        case 'Create0':
            return false;
        case 'Create1':
            $data['_id'] = 'Test passed';
            return array('err' => null);
        }
    }

    public function update($crit, $data, $dummy, $name)
    {
        switch($name)
        {
        case 'Update0':
            if(isset($data['_id']))
            {
                return true;
            }
            return false;
        }
    }

    public function remove($crit, $dummy,  $name)
    {
        switch($name)
        {
        case 'Delete0':
            return false;
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

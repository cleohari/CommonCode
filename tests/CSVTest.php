<?php
require_once('Autoload.php');
class CSVTest extends PHPUnit\Framework\TestCase
{
    public function testBasic()
    {
        $serializer = new \Flipside\Serialize\CSVSerializer();
        $array = array(array('Test1'=>1,'Test2'=>'a','ABC'=>'1'));
        $type = 'text/csv';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("Test1,Test2,ABC\n1,a,1\n", $data);
    }

    public function testObject()
    {
        $serializer = new \Flipside\Serialize\CSVSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $array = array($obj);
        $type = 'text/csv';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("Test1,Test2,ABC\n1,a,1\n", $data);

        $serializer = new \Flipside\Serialize\CSVSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $type = 'text/csv';
        $data = $serializer->serializeData($type, $obj);
        $this->assertEquals("Test1,Test2,ABC\n1,a,1\n", $data);
    }

    public function testComma()
    {
        $serializer = new \Flipside\Serialize\CSVSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $type = 'text/csv';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("Test1,\"Test2,3\",ABC\n1,a,\"1,0\"\n", $data);

        $serializer = new \Flipside\Serialize\CSVSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>array(1,0)));
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("Test1,\"Test2,3\",ABC\n1,a,\"1,0\"\n", $data);
    }

    public function testUnevenArrays()
    {
        $serializer = new \Flipside\Serialize\CSVSerializer();
        $row1 = array('A'=>1,'B'=>'2','C'=>'3');
        $row2 = array('A'=>1,'C'=>2);
        $array = array($row1, $row2);
        $type = 'text/csv';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("A,B,C\n1,2,3\n1,,2\n", $data);
    }

    public function testObjectContents()
    {
        //May need the Mongo Polyfill
        $tmp = new \Flipside\Data\MongoDataSet(false);

	$serializer = new \Flipside\Serialize\CSVSerializer();
	$id = null;
	if(class_exists('MongoId'))
        {
            $id = new \MongoId('4af9f23d8ead0e1d32000000');
        }
        else
        {
            $id = new \MongoDB\BSON\ObjectId('4af9f23d8ead0e1d32000000');
        }
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $row1 = array('A'=>$obj,'B'=>'2','C'=>'3','_id'=>$id);
        $array = array($row1);
        $type = 'text/csv';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("A.Test1,A.Test2,A.ABC,B,C,_id\n1,a,1,2,3,4af9f23d8ead0e1d32000000\n", $data);
    }

    public function testBadType()
    {
        $serializer = new \Flipside\Serialize\CSVSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $type = 'text/json';
        $data = $serializer->serializeData($type, $array);
        $this->assertNull($data);
    }

    public function testEmpty()
    {
        $serializer = new \Flipside\Serialize\CSVSerializer();
        $array = array();
        $type = 'text/csv';
        $data = $serializer->serializeData($type, $array);
        $this->assertNotNull($data);
    }
}

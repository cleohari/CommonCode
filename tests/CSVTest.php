<?php
require_once('Autoload.php');
class CSVTest extends PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $serializer = new \Serialize\CSVSerializer();
        $array = array(array('Test1'=>1,'Test2'=>'a','ABC'=>'1'));
        $data = $serializer->serializeData('text/csv', $array);
        $this->assertEquals("Test1,Test2,ABC\n1,a,1\n", $data);
    }

    public function testObject()
    {
        $serializer = new \Serialize\CSVSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $array = array($obj);
        $data = $serializer->serializeData('text/csv', $array);
        $this->assertEquals("Test1,Test2,ABC\n1,a,1\n", $data);

        $serializer = new \Serialize\CSVSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $data = $serializer->serializeData('text/csv', $obj);
        $this->assertEquals("Test1,Test2,ABC\n1,a,1\n", $data);
    }

    public function testComma()
    {
        $serializer = new \Serialize\CSVSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $data = $serializer->serializeData('text/csv', $array);
        $this->assertEquals("Test1,\"Test2,3\",ABC\n1,a,\"1,0\"\n", $data);

        $serializer = new \Serialize\CSVSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>array(1,0)));
        $data = $serializer->serializeData('text/csv', $array);
        $this->assertEquals("Test1,\"Test2,3\",ABC\n1,a,\"1,0\"\n", $data);
    }

    public function testUnevenArrays()
    {
        $serializer = new \Serialize\CSVSerializer();
        $row1 = array('A'=>1,'B'=>'2','C'=>'3');
        $row2 = array('A'=>1,'C'=>2);
        $array = array($row1, $row2);
        $data = $serializer->serializeData('text/csv', $array);
        $this->assertEquals("A,B,C\n1,2,3\n1,,2\n", $data);
    }

    public function testObjectContents()
    {
        //May need the Mongo Polyfill
        $tmp = new \Data\MongoDataSet(false);

        $serializer = new \Serialize\CSVSerializer();
        $id = new \MongoId('4af9f23d8ead0e1d32000000');
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $row1 = array('A'=>$obj,'B'=>'2','C'=>'3','_id'=>$id);
        $array = array($row1);
        $data = $serializer->serializeData('text/csv', $array);
        $this->assertEquals("A,B,C,_id\n\"{\"\"Test1\"\":1,\"\"Test2\"\":\"\"a\"\",\"\"ABC\"\":\"\"1\"\"}\",2,3,4af9f23d8ead0e1d32000000\n", $data);
    }

    public function testSimpleType()
    {
        $serializer = new \Serialize\CSVSerializer();
        $data = $serializer->serializeData('text/csv', array('Test', 'Test1'));
        $this->assertEquals("Test\nTest1\n", $data);
    }

    public function testBadType()
    {
        $serializer = new \Serialize\CSVSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $data = $serializer->serializeData('text/json', $array);
        $this->assertNull($data);
    }

    public function testEmpty()
    {
        $serializer = new \Serialize\CSVSerializer();
        $array = array();
        $data = $serializer->serializeData('text/csv', $array);
        $this->assertNull($data);
    }
}
?>

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

<?php
require_once('Autoload.php');
class YAMLTest extends PHPUnit\Framework\TestCase
{
    public function testBasic()
    {
        $serializer = new \Serialize\YAMLSerializer();
        $array = array(array('Test1'=>1,'Test2'=>'a','ABC'=>'1'));
        $type = 'text/x-yaml';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("-\n    Test1: 1\n    Test2: a\n    ABC: '1'\n", $data);
    }

    public function testComma()
    {
        $serializer = new \Serialize\YAMLSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $type = 'text/x-yaml';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("-\n    Test1: 1\n    'Test2,3': a\n    ABC: '1,0'\n", $data);

        $serializer = new \Serialize\YAMLSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>array(1,0)));
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("-\n    Test1: 1\n    'Test2,3': a\n    ABC: [1, 0]\n", $data);
    }

    public function testUnevenArrays()
    {
        $serializer = new \Serialize\YAMLSerializer();
        $row1 = array('A'=>1,'B'=>'2','C'=>'3');
        $row2 = array('A'=>1,'C'=>2);
        $array = array($row1, $row2);
        $type = 'text/x-yaml';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("-\n    A: 1\n    B: '2'\n    C: '3'\n-\n    A: 1\n    C: 2\n", $data);
    }

    public function testObjectContents()
    {
        //May need the Mongo Polyfill
        $tmp = new \Data\MongoDataSet(false);

        $serializer = new \Serialize\YAMLSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $row1 = array('A'=>$obj,'B'=>'2','C'=>'3');
        $array = array($row1);
        $type = 'text/x-yaml';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("-\n    A: { Test1: 1, Test2: a, ABC: '1' }\n    B: '2'\n    C: '3'\n", $data);
    }

    public function testSimpleType()
    {
        $serializer = new \Serialize\YAMLSerializer();
        $type = 'text/x-yaml';
        $data = $serializer->serializeData($type, array('Test', 'Test1'));
        $this->assertEquals("- Test\n- Test1\n", $data);
    }

    public function testBadType()
    {
        $serializer = new \Serialize\YAMLSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $type = 'text/json';
        $data = $serializer->serializeData($type, $array);
        $this->assertNull($data);
    }

    public function testEmpty()
    {
        $serializer = new \Serialize\YAMLSerializer();
        $array = array();
        $type = 'text/x-yaml';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals('{  }', $data);
    }
}

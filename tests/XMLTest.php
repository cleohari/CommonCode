<?php
require_once('Autoload.php');
class XMLTest extends PHPUnit\Framework\TestCase
{
    public function testBasic()
    {
        $serializer = new \Flipside\Serialize\XMLSerializer();
        $array = array(array('Test1'=>1,'Test2'=>'a','ABC'=>'1'));
        $type = 'application/xml';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals('<?xml version="1.0"?>
<Array><Entity><Test1>1</Test1><Test2>a</Test2><ABC>1</ABC></Entity></Array>', $data);
    }

    public function testObject()
    {
        $serializer = new \Flipside\Serialize\XMLSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $array = array($obj);
        $type = 'text/xml';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<Array><Entity><Test1>1</Test1><Test2>a</Test2><ABC>1</ABC></Entity></Array>", $data);

        $serializer = new \Flipside\Serialize\XMLSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $data = $serializer->serializeData($type, $obj);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<Test1>1</Test1><Test2>a</Test2><ABC>1</ABC>", $data);

        $serializer = new \Flipside\Serialize\XMLSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = new stdClass();
        $obj->ABC->x = 'abc';
        $data = $serializer->serializeData($type, $obj);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<Test1>1</Test1><Test2>a</Test2><ABC><x>abc</x></ABC>", $data);

        $serializer = new \Flipside\Serialize\XMLSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = new stdClass();
        $obj->ABC->{'$x'} = 'abc';
        $data = $serializer->serializeData($type, $obj);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<Test1>1</Test1><Test2>a</Test2><ABC><x>abc</x></ABC>", $data);
    }

    public function testComma()
    {
        $serializer = new \Flipside\Serialize\XMLSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $type = 'text/xml';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<Array><Entity><Test1>1</Test1><Test23>a</Test23><ABC>1,0</ABC></Entity></Array>", $data);

        $serializer = new \Flipside\Serialize\XMLSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>array(1,0)));
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<Array><Entity><Test1>1</Test1><Test23>a</Test23><ABC>1</ABC><ABC>0</ABC></Entity></Array>", $data);
    }

    public function testUnevenArrays()
    {
        $serializer = new \Flipside\Serialize\XMLSerializer();
        $row1 = array('A'=>1,'B'=>'2','C'=>'3');
        $row2 = array('A'=>1,'C'=>2);
        $array = array($row1, $row2);
        $type = 'text/xml';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<Array><Entity><A>1</A><B>2</B><C>3</C></Entity><Entity><A>1</A><C>2</C></Entity></Array>", $data);
    }

    public function testUnkeyedArrays()
    {
        $serializer = new \Flipside\Serialize\XMLSerializer();
        $row1 = array(1,2,3);
        $row2 = array('A','B');
        $array = array($row1, $row2);
        $type = 'text/xml';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("<?xml version=\"1.0\"?>\n<Array><Entity><Child>1</Child><Child>2</Child><Child>3</Child></Entity><Entity><Child>A</Child><Child>B</Child></Entity></Array>", $data);
    }

    public function testBadType()
    {
        $serializer = new \Flipside\Serialize\XMLSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $type = 'text/csv';
        $data = $serializer->serializeData($type, $array);
        $this->assertNull($data);
    }

    public function testEmpty()
    {
        $serializer = new \Flipside\Serialize\XMLSerializer();
        $array = array();
        $type = 'text/xml';
        $data = $serializer->serializeData($type, $array);
        $this->assertEquals("<?xml version=\"1.0\"?>\n", $data);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

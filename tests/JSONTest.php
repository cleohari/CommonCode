<?php
require_once('Autoload.php');
class JSONTest extends PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $serializer = new \Serialize\JSONSerializer();
        $array = array(array('Test1'=>1,'Test2'=>'a','ABC'=>'1'));
        $data = $serializer->serializeData('application/json', $array);
        $this->assertEquals('[{"Test1":1,"Test2":"a","ABC":"1"}]', $data);
    }

    public function testBadType()
    {
        $serializer = new \Serialize\JSONSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $data = $serializer->serializeData('text/csv', $array);
        $this->assertNull($data);
    }

    public function testEmpty()
    {
        $serializer = new \Serialize\JSONSerializer();
        $array = array();
        $data = $serializer->serializeData('application/json', $array);
        $this->assertEquals('[]', $data);
    }
}
?>

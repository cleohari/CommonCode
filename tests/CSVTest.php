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

    public function testComma()
    {
        $serializer = new \Serialize\CSVSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $data = $serializer->serializeData('text/csv', $array);
        $this->assertEquals("Test1,\"Test2,3\",ABC\n1,a,\"1,0\"\n", $data);
    }
}
?>

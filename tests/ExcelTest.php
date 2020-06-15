<?php
require_once('Autoload.php');
require_once dirname(__FILE__) . '/../vendor/autoload.php';
class ExcelTest extends PHPUnit\Framework\TestCase
{
    private function stringToExcel($data)
    {
         $name = tempnam("/tmp", "Excel");
         file_put_contents($name, $data);
         $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($name);
         $excel = $reader->load($name);
         unlink($name);
         return $excel;
    }

    public function testBasic()
    {
        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $array = array(array('Test1'=>1,'Test2'=>'a','ABC'=>'1'));
        $type = 'xls';
        $data = $serializer->serializeData($type, $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
        $test = $sheet->rangeToArray('A1:C2', NULL, true, true, false);
        $this->assertEquals(array(array('Test1', 'Test2', 'ABC'), array('1', 'a', '1')), $test);

        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $array = array(array('Test1'=>1,'Test2'=>'a','ABC'=>'1'));
        $type = 'xlsx';
        $data = $serializer->serializeData($type, $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
        $test = $sheet->rangeToArray('A1:C2', NULL, true, true, false);
        $this->assertEquals(array(array('Test1', 'Test2', 'ABC'), array('1', 'a', '1')), $test);
    }

    public function testObject()
    {
        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $array = array($obj);
        $type = 'xls';
        $data = $serializer->serializeData($type, $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
        $test = $sheet->rangeToArray('A1:C2', NULL, true, true, false);
        $this->assertEquals(array(array('Test1', 'Test2', 'ABC'), array('1', 'a', '1')), $test);

        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $array = array($obj);
        $type = 'xlsx';
        $data = $serializer->serializeData($type, $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
        $test = $sheet->rangeToArray('A1:C2', NULL, true, true, false);
        $this->assertEquals(array(array('Test1', 'Test2', 'ABC'), array('1', 'a', '1')), $test);

        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $type = 'xls';
        $data = $serializer->serializeData($type, $obj);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
        $test = $sheet->rangeToArray('A1:C2', NULL, true, true, false);
        $this->assertEquals(array(array('Test1', 'Test2', 'ABC'), array('1', 'a', '1')), $test);

        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $type = 'xlsx';
        $data = $serializer->serializeData($type, $obj);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
        $test = $sheet->rangeToArray('A1:C2', NULL, true, true, false);
        $this->assertEquals(array(array('Test1', 'Test2', 'ABC'), array('1', 'a', '1')), $test);
    }

    public function testComma()
    {
        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $type = 'xls';
        $data = $serializer->serializeData($type, $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
        $test = $sheet->rangeToArray('A1:C2', NULL, true, true, false);
        $this->assertEquals(array(array('Test1', 'Test2,3', 'ABC'), array('1', 'a', '1,0')), $test);

        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $type = 'xlsx';
        $data = $serializer->serializeData($type, $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
        $test = $sheet->rangeToArray('A1:C2', NULL, true, true, false);
        $this->assertEquals(array(array('Test1', 'Test2,3', 'ABC'), array('1', 'a', '1,0')), $test);
    }

    public function testUnevenArrays()
    {
        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $row1 = array('A'=>1,'B'=>'2','C'=>'3');
        $row2 = array('A'=>1,'C'=>2);
        $array = array($row1, $row2);
        $type = 'xls';
        $data = $serializer->serializeData($type, $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
        $test = $sheet->rangeToArray('A1:C3', NULL, true, true, false);
        $this->assertEquals(array(array('A', 'B', 'C'), array('1', '2', '3'), array('1', NULL, '2')), $test);

        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $row1 = array('A'=>1,'B'=>'2','C'=>'3');
        $row2 = array('A'=>1,'C'=>2);
        $array = array($row1, $row2);
        $type = 'xlsx';
        $data = $serializer->serializeData($type, $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
        $test = $sheet->rangeToArray('A1:C3', NULL, true, true, false);
        $this->assertEquals(array(array('A', 'B', 'C'), array('1', '2', '3'), array('1', NULL, '2')), $test);
    }

    public function testObjectContents()
    {
        //May need the Mongo Polyfill
        $tmp = new \Flipside\Data\MongoDataSet(false);

	$serializer = new \Flipside\Serialize\ExcelSerializer();
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
        $type = 'xls';
        $data = $serializer->serializeData($type, $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
	$test = $sheet->rangeToArray('A1:F2', NULL, true, true, false);
        $this->assertEquals(array(array('A.Test1', 'A.Test2', 'A.ABC', 'B', 'C', '_id'), array('1', 'a', '1', '2', '3', '4af9f23d8ead0e1d32000000')), $test);

        $serializer = new \Flipside\Serialize\ExcelSerializer();
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
        $type = 'xlsx';
        $data = $serializer->serializeData($type, $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet(0);
        $this->assertNotNull($sheet);
        $test = $sheet->rangeToArray('A1:F2', NULL, true, true, false);
        $this->assertEquals(array(array('A.Test1', 'A.Test2', 'A.ABC', 'B', 'C', '_id'), array('1', 'a', '1', '2', '3', '4af9f23d8ead0e1d32000000')), $test);
    }

    public function testBadType()
    {
        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $type = 'text/json';
        $data = $serializer->serializeData($type, $array);
        $this->assertNull($data);
    }

    public function testEmpty()
    {
        $serializer = new \Flipside\Serialize\ExcelSerializer();
        $array = array();
        $type = 'xls';
        $data = $serializer->serializeData($type, $array);
        $this->assertNotNull($data);
    }
}

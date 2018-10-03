<?php
require_once('Autoload.php');
require_once('Auth/class.LDAPAuthenticator.php');
class SortArrayTest extends PHPUnit\Framework\TestCase
{
    public function testEmpty()
    {
        $array = array();
        \Auth\sort_array($array, array());
        $this->assertEquals($array, array());
    }

    public function testNoOp()
    {
        $array = array();
        $array[] = array('a'=>1, 'b'=>9);
        $array[] = array('a'=>2, 'b'=>8);
        $array[] = array('a'=>3, 'b'=>7);
        $array[] = array('a'=>4, 'b'=>6);
        $array[] = array('a'=>5, 'b'=>5);
        $array[] = array('a'=>6, 'b'=>4);
        $tmp = $array;
        \Auth\sort_array($array, array('a'=>1));
        $this->assertEquals($array, $tmp);
        \Auth\sort_array($array, array('b'=>0));
        $this->assertEquals($array, $tmp);
    }

    public function testSwap()
    {
        $array = array();
        $array[] = array('a'=>'1', 'b'=>'9');
        $array[] = array('a'=>'2', 'b'=>'8');
        $array[] = array('a'=>'3', 'b'=>'7');
        $array[] = array('a'=>'4', 'b'=>'6');
        $array[] = array('a'=>'5', 'b'=>'5');
        $array[] = array('a'=>'6', 'b'=>'4');
        $rev = array();
        $rev[] = array('a'=>'6', 'b'=>'4');
        $rev[] = array('a'=>'5', 'b'=>'5');
        $rev[] = array('a'=>'4', 'b'=>'6');
        $rev[] = array('a'=>'3', 'b'=>'7');
        $rev[] = array('a'=>'2', 'b'=>'8');
        $rev[] = array('a'=>'1', 'b'=>'9');
        \Auth\sort_array($array, array('a'=>0));
        $this->assertEquals($array, $rev);
        $rev = array();
        $rev[] = array('a'=>'1', 'b'=>'9');
        $rev[] = array('a'=>'2', 'b'=>'8');
        $rev[] = array('a'=>'3', 'b'=>'7');
        $rev[] = array('a'=>'4', 'b'=>'6');
        $rev[] = array('a'=>'5', 'b'=>'5');
        $rev[] = array('a'=>'6', 'b'=>'4');
        \Auth\sort_array($array, array('a'=>1));
        $this->assertEquals($array, $rev);
    }
}

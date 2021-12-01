<?php
require_once('Autoload.php');
class LDAPCacheableObjectTest extends PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testInit()
    {
        $obj = new \CacheableObjectTest();
        $obj->initFalse($this);
        $obj->initObject($this);
    }

    public function testInitReadFails()
    {
        $obj = new \CacheableObjectTest();
        $obj->server = new \FailServer();
        $obj->initString($this);
    }

    public function testInitArrayMailFails()
    {
        $obj = new \CacheableObjectTest();
        $obj->server = new \FailServer();
        $obj->initArray($this, array('mail'=>'bad'), false);
    }

    public function testInitArrayMail()
    {
        $obj = new \CacheableObjectTest();
        $obj->server = new \PassServer();
        $obj->initArray($this, array('mail'=>'good'), 'pass');
    }

    public function testInitArrayBad()
    {
        $obj = new \CacheableObjectTest();
        $obj->server = new \PassServer();
        $obj->initArray($this, array('mailx'=>'good'), null);
    }

    public function testLocalFieldNoInit()
    {
        $obj = new \CacheableObjectTest();
        $obj->checkFieldLocal($this, false, 'bad', false);
    }

    public function testLocalSingleFieldNoInit()
    {
        $obj = new \CacheableObjectTest();
        $obj->checkFieldSingleLocal($this, false, 'bad', false);
    }

    public function testLocalSingleFieldArray()
    {
        $obj = new \CacheableObjectTest();
        $obj->checkFieldSingleLocal($this, array('test'=>array(1, 2)), 'test', 1);
    }

    public function testServerSingleFieldArray()
    {
        $obj = new \CacheableObjectTest();
        $initObj = new \stdClass();
        $initObj->test = array();
        $obj->checkFieldSingleServer($this, $initObj, 'test', false);
    }

    public function testSetField()
    {
        $obj = new \CacheableObjectTest();
        $obj->checkSetField($this);
    }

    public function testAppendField()
    {
        $obj = new \CacheableObjectTest();
        $obj->checkAppendField($this);
    }
}

class CacheableObjectTest
{
    use \Flipside\Auth\LDAPCachableObject;

    private $ldapObj;

    public function initFalse($test)
    {
        $this->ldapObj = false;
        $this->initialize(false);
        $test->assertFalse($this->ldapObj);
    }

    public function initObject($test)
    {
        $obj = new \Flipside\LDAP\LDAPObject();
        $this->initialize($obj);
        $test->assertEquals($obj, $this->ldapObj);
    }

    public function initString($test)
    {
        $this->ldapObj = true;
        $this->initialize('test');
        $test->assertFalse($this->ldapObj);
    }

    public function initArray($test, $arr, $exp)
    {
        $this->ldapObj = true;
        $this->initialize($arr);
        $test->assertEquals($exp, $this->ldapObj);
    }

    public function checkFieldLocal($test, $init, $name, $exp)
    {
        $this->ldapObj = $init;
        $test->assertEquals($exp, $this->getFieldLocal($name));
    }

    public function checkFieldSingleLocal($test, $init, $name, $exp)
    {
        $this->ldapObj = $init;
        $test->assertEquals($exp, $this->getFieldLocalSingleValue($name));
    }

    public function checkFieldSingleServer($test, $init, $name, $exp)
    {
        $this->ldapObj = $init;
        $test->assertEquals($exp, $this->getFieldServerSingleValue($name));
    }

    public function checkSetField($test)
    {
        $this->ldapObj = false;
        $test->assertTrue($this->setFieldLocal('test', 1));
        $test->assertEquals(array('test'=>1), $this->ldapObj);

        $test->assertTrue($this->setFieldLocal('test', 2));
        $test->assertEquals(array('test'=>2), $this->ldapObj);

        $test->assertTrue($this->setFieldLocal('test', null));
        $test->assertEquals(array(), $this->ldapObj);

        $this->ldapObj = new \stdClass();
        $this->ldapObj->dn = 'test';
        $this->test = $test;
        $ret = $this->setFieldServer('test', 'a');
        $test->assertEquals(array('dn'=>'test', 'test'=>'a'), $ret);

        $this->ldapObj = new \stdClass();
        $this->ldapObj->dn = 'test';
        $this->test = $test;
        $ret = $this->setFieldServer('test', null);
        $test->assertEquals(array('dn'=>'test', 'test'=>null), $ret);

        $this->ldapObj = new \stdClass();
        $this->ldapObj->dn = 'test';
        $this->test = $test;
        $ret = $this->setFieldServer('test', array(1));
        $test->assertEquals(array('dn'=>'test', 'test'=>array(1)), $ret);
    }

    public function checkAppendField($test)
    {
        $this->ldapObj = false;
        $test->assertTrue($this->appendFieldLocal('test', 1));
        $test->assertEquals(array('test'=>array(1)), $this->ldapObj);

        $test->assertTrue($this->appendFieldLocal('test', 2));
        $test->assertEquals(array('test'=>array(1, 2)), $this->ldapObj);

        $this->ldapObj = array();
        $test->assertTrue($this->appendField('test', 1));
        $test->assertEquals(array('test'=>array(1)), $this->ldapObj);

        $this->ldapObj = new \stdClass();
        $this->ldapObj->dn = 'test';
        $this->test = $test;
        $ret = $this->appendFieldServer('test', 1);
        $test->assertEquals(array('dn'=>'test', 'test'=>1), $ret);

        $this->ldapObj = new \stdClass();
        $this->ldapObj->dn = 'test';
        $this->ldapObj->test = array('count'=>0);
        $this->test = $test;
        $ret = $this->appendFieldServer('test', 1);
        $test->assertEquals(array('dn'=>'test', 'test'=>array('count'=>1, 0 => 1)), $ret);
    }

    protected function update($obj)
    {
        return $obj;
    }
}

class FailServer
{
    public $user_base = '';

    public function read($baseDN, $filter = false, $single = false, $attributes = false)
    {
        return false;
    }

    public function update($obj)
    {
        throw new \Exception();
    }
}

class PassServer
{
    public $user_base = '';

    public function read($baseDN, $filter = false, $single = false, $attributes = false)
    {
        return array('pass');
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

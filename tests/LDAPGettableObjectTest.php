<?php
require_once('Autoload.php');
class LDAPGettableObjectTest extends PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testDefaults()
    {
        $obj = new \GettableObjectTest();
        $obj->checkValueWithDefault($this);
    }

    public function testMultiValueProp()
    {
        $obj = new \GettableObjectTest();
        $obj->checkMultiValue($this);
    }

    public function testIsSet()
    {
        $obj = new \GettableObjectTest();
        $obj->chcekIsSet($this);
    }

    public function testMultiEmail()
    {
        $obj = new \GettableObjectTest();
        $obj->checkMultiEmail($this);
    }
}

class GettableObjectTest
{
    use \Flipside\Auth\LDAPGettableObject;

    private $valueDefaults;
    private $multiValueProps;
    private $fields;
    private $ldapObj;

    public function getFieldSingleValue($fieldname)
    {
        if(isset($this->fields[$fieldname]))
        {
            return $this->fields[$fieldname];
        }
        return false;
    }

    public function getField($fieldname)
    {
        return $this->getFieldSingleValue($fieldname);
    }

    public function checkValueWithDefault($test)
    {
        $this->fields = array();
        $this->valueDefaults = array();
        $test->assertFalse($this->getValueWithDefault('bad'));

        $this->valueDefaults = array('good'=>1);
        $test->assertFalse($this->getValueWithDefault('bad'));

        $test->assertEquals(1, $this->getValueWithDefault('good'));

        $this->fields = array('good' => 2);
        $test->assertEquals(2, $this->getValueWithDefault('good'));

        $this->fields = array();
        $this->valueDefaults = array();
    }

    public function checkMultiValue($test)
    {
        $this->fields = array();
        $this->valueDefaults = array();
        $this->multiValueProps = array();
        $test->assertFalse($this->getMultiValueProp('bad'));

        $this->multiValueProps = array('good');
        $test->assertFalse($this->getMultiValueProp('bad'));
        $test->assertFalse($this->getMultiValueProp('good'));

        $this->fields = array('good' => array(2));
        $test->assertEquals(array(2), $this->getMultiValueProp('good'));

        $this->fields = array('good' => array('count'=>1, 0=>2));
        $test->assertEquals(array(2), $this->getMultiValueProp('good'));

        $this->fields = array();
        $this->valueDefaults = array();
        $this->multiValueProps = array();
    }

    public function chcekIsSet($test)
    {
        $this->fields = array();
        $this->valueDefaults = array();
        $this->multiValueProps = array();

        $test->assertFalse(isset($this->bad));
        $test->assertFalse(isset($this->good));

        $this->valueDefaults = array('good'=>1);
        $test->assertFalse(isset($this->bad));
        $test->assertTrue(isset($this->good));

        $this->valueDefaults = array();
        $this->multiValueProps = array('goodmulti');
        $test->assertFalse(isset($this->bad));
        $test->assertFalse(isset($this->good));
        $test->assertTrue(isset($this->goodmulti));

        $this->multiValueProps = array();
        $this->ldapObj = array();
        $test->assertFalse(isset($this->bad));
        $test->assertFalse(isset($this->good));

        $this->ldapObj = array('good'=>1);
        $test->assertFalse(isset($this->bad));
        $test->assertTrue(isset($this->good));

        $this->ldapObj = array('labeleduri'=>1);
        $test->assertFalse(isset($this->bad));
        $test->assertFalse(isset($this->good));
        $test->assertTrue(isset($this->allMail));

        $this->ldapObj = new \stdClass();
        $test->assertFalse(isset($this->bad));
        $test->assertFalse(isset($this->good));

        $this->ldapObj->good = 1;
        $test->assertFalse(isset($this->bad));
        $test->assertTrue(isset($this->good));

        unset($this->ldapObj->good);
        $this->ldapObj->labeleduri = 1;
        $test->assertFalse(isset($this->bad));
        $test->assertFalse(isset($this->good));
        $test->assertTrue(isset($this->allMail));
    }

    public function checkMultiEmail($test)
    {
        $this->fields = array();
        $this->valueDefaults = array();
        $this->multiValueProps = array();
        $this->ldapObj = array();
        $test->assertFalse($this->bad);

        $this->fields['mail'] = 'test@example.org';
        $test->assertEquals('test@example.org', $this->mail);

        $this->ldapObj['labeleduri'] = 1;
        $this->fields['mail'] = array('count'=>2, 0=>'test@example.org', 1=>'bob@example.org');
        $this->fields['labeleduri'] = 'bob@example.org';
        $test->assertEquals('bob@example.org', $this->mail);
        $test->assertEquals(array('test@example.org','bob@example.org'), $this->allMail);
        $this->fields = array();
        $this->valueDefaults = array();
        $this->multiValueProps = array();
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

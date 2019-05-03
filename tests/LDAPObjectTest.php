<?php
require_once('Autoload.php');
class LDAPObjectTest extends PHPUnit\Framework\TestCase
{
    public function testLDAPObject()
    {
        $data = array(0 => 'Bob', 'jpegphoto'=>'Test', 'host'=>array(0 => 'google.com', 'count'=>1), 'uid'=>'cn=test', 'titles'=>array('Title1', 'Title2', 'count'=>2));

        $ldap = new \LDAP\LDAPObject($data);
        $res = $ldap->jsonSerialize();
        $this->assertNotEquals($data, $res);
        $serialized = array('jpegphoto'=>'VA==', 'host'=>'google.com', 'uid'=>'cn=test', 'titles'=>array('Title1', 'Title2', 'count'=>2));
        $this->assertEquals($serialized, $res);
    }
}

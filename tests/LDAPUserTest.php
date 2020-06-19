<?php
require_once('Autoload.php');
class LDAPUserTest extends PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $user = new \Flipside\Auth\LDAPUser(array('extended' => array('mail' => 'test@example.org')));
        $this->assertFalse(false);
    }

    public function testIsInGroup()
    {
        $ext = new \stdClass();
        $ext->dn = 'test';
        $ext->uid = array('test1');
        $user = new \MyLDAPUser(array('extended' => $ext));
        $this->assertFalse(false);

        $this->assertFalse($user->isInGroupNamed('Nope'));
        $this->assertFalse($user->isInGroupNamed('Nope1'));
        $this->assertTrue($user->isInGroupNamed('Yup'));
        $this->assertTrue($user->isInGroupNamed('Yup1'));
        $this->assertTrue($user->isInGroupNamed('Yup2'));
        $this->assertTrue($user->isInGroupNamed('Yup3'));
    }

    public function testGetGroups()
    {
        $ext = new \stdClass();
        $ext->dn = 'test';
        $ext->uid = array('test1');
        $user = new \MyLDAPUser(array('extended' => $ext));
        $this->assertFalse(false);

        $groups = $user->getGroups();
        $this->assertEmpty($groups);
    }

    public function testAddLogin()
    {
        $ext = new \stdClass();
        $ext->dn = 'test';
        $ext->uid = array('test1');
        $user = new \MyLDAPUser(array('extended' => $ext));
        $this->assertFalse(false);

        $this->assertTrue($user->addLoginProvider('example.org'));
    }

    public function testSetPass()
    {
        $ext = new \stdClass();
        $ext->dn = 'test';
        $ext->uid = array('test1');
        $user = new \MyLDAPUser(array('extended' => $ext));
        $this->assertFalse(false);

        $this->assertTrue($user->setPass('test'));

        $ext = new \stdClass();
        $ext->dn = 'test';
        $ext->uid = array('test1');
        $ext->uniqueidentifier = 'a';
        $user = new \MyLDAPUser(array('extended' => $ext));
        $this->assertFalse(false);

        $this->assertTrue($user->setPass('test'));
        $this->assertTrue($user->validate_password('test'));
    }

    public function testValidateHash()
    {
        $ext = new \stdClass();
        $ext->dn = 'test';
        $ext->uid = array('test1');
        $ext->uniqueidentifier = array('a');
        $user = new \MyLDAPUser(array('extended' => $ext));
        $this->assertFalse(false);

        $this->assertTrue($user->validate_reset_hash('a'));
        $this->assertFalse($user->validate_reset_hash('b'));
    }
}

class MyLDAPUser extends \Flipside\Auth\LDAPUser
{
    public function __construct($data, $test = false)
    {
        parent::__construct($data);
        $this->server = new MyLDAPServerUserMockup($test);
    }
}

class MyLDAPServerUserMockup
{
    public $group_base = 'test_base';

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function read($base, $filter = false)
    {
        if($filter === false && $base === 'test_base')
        {
            return array();
        }
        $data = $filter->to_mongo_filter();
        switch($data['cn'])
        {
        case 'Nope':
            return false;
        case 'Nope1':
            return array();
        case 'Yup':
            return array(array('member' => array('test')));
        case 'Yup1':
            return array(array('uniquemember' => array('test')));
        case 'Yup2':
            return array(array('member' => array('count'=> 1 , 0 => 'cn=Yup,test_base')));
        case 'Yup3':
            return array(array('memberUid' => array('test1')));
        }
    }

    public function update($obj)
    {
        return true;
    }

    public function bind($pass)
    {
        return true;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

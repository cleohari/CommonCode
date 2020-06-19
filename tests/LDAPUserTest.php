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
    public $group_base = 'test';

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function read($base, $filter)
    {
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
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

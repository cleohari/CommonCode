<?php
require_once('Autoload.php');
class GroupTest extends PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testGroup()
    {
        $user = new \Flipside\Auth\Group();
        $this->assertFalse($user->getGroupName());
        $this->assertFalse($user->getDescription());
        $this->assertFalse($user->setGroupName('AAR'));
        $this->assertFalse($user->setDescription('Test'));
        $this->assertEmpty($user->getMemberUids());
        $this->assertEmpty($user->getMemberUids(true));
        $this->assertEmpty($user->members());
        $this->assertEmpty($user->members(true));
        $this->assertEmpty($user->members(false, false));
        $this->assertEmpty($user->members(true, false));
        $this->assertEmpty($user->members(false, false, false));
        $this->assertEmpty($user->members(true, false, false));
        $this->assertEmpty($user->members(true, true, false));
        $this->assertEquals(0, $user->member_count());
        $this->assertFalse($user->clearMembers());
        $this->assertEmpty($user->getNonMembers());
        $this->assertFalse($user->addMember('test'));
        $this->assertFalse($user->addMember('test', true));
        $this->assertFalse($user->addMember('test', false, false));
        $this->assertFalse($user->addMember('test', true, false));
        $json = json_encode($user);
        $this->assertEquals($json, '{"cn":false,"description":false,"member":[]}');
        $this->assertFalse(\Flipside\Auth\Group::from_name('test'));

        $user->editGroup(array('description' => 'Testing'));
    }

    public function testLDAPGroup()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        try
        {
            $group = new \Flipside\Auth\LDAPGroup(false);
            $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertFalse(false);
        }
        $array = array('dn'=>array('cn=Test,dc=example,dc=com'), 'cn'=>array('Test'), 'description'=>array('Test Group'), 'member'=>array('uid=test,dc=example,dc=com', 'cn=tg,dc=example,dc=com'));
        $ldapGroup = new \Flipside\LDAP\LDAPObject($array);
        $group = new \Flipside\Auth\LDAPGroup($ldapGroup);
        $this->assertEquals('Test', $group->getGroupName());
        $this->assertEquals('Test Group', $group->getDescription());
        $group->setDescription('New Group');
        $this->assertEquals('New Group', $group->getDescription());
        $ids = $group->getMemberUids(false);
        $this->assertNotFalse($ids);
        $this->assertCount(2, $ids);

        $this->assertTrue($group->addMember('test2', false, false));
        $ids = $group->getMemberUids(false);
        $this->assertNotFalse($ids);
        $this->assertCount(3, $ids);

        $this->assertTrue($group->addMember('test3', true, false));
        $ids = $group->getMemberUids(false);
        $this->assertNotFalse($ids);
        $this->assertCount(4, $ids);

        $group->clearMembers();
        $ids = $group->getMemberUids();
        $this->assertNotFalse($ids);
        $this->assertCount(0, $ids);

        $array = array('dn'=>array('cn=Test,dc=example,dc=com'), 'cn'=>array('Test'), 'description'=>array('Test Group'), 'memberuid'=>array('test'));
        $ldapGroup = new \Flipside\LDAP\LDAPObject($array);
        $group = new \Flipside\Auth\LDAPGroup($ldapGroup);
        $ids = $group->getMemberUids();
        $this->assertNotFalse($ids);
        $this->assertCount(1, $ids);

        $group->clearMembers();
        $ids = $group->getMemberUids();
        $this->assertNotFalse($ids);
        $this->assertCount(0, $ids);

        $array = array('dn'=>array('cn=Test,dc=example,dc=com'), 'cn'=>array('Test'), 'description'=>array('Test Group'), 'uniquemember'=>array('uid=test'));
        $ldapGroup = new \Flipside\LDAP\LDAPObject($array);
        $group = new \Flipside\Auth\LDAPGroup($ldapGroup);
        $ids = $group->getMemberUids();
        $this->assertNotFalse($ids);
        $this->assertCount(1, $ids);

        $group->clearMembers();
        $ids = $group->getMemberUids();
        $this->assertNotFalse($ids);
        $this->assertCount(0, $ids);
    }

    public function testLDAPGroupRecursiveMembers()
    {
        $ldap_connect = $this->getFunctionMock('Flipside\LDAP', "ldap_connect");
        $ldap_connect->expects($this->any())->willReturn(true);
        $ldap_bind = $this->getFunctionMock('Flipside\LDAP', "ldap_bind");
        $ldap_bind->expects($this->any())->willReturn(true);
        $ldap_set_option = $this->getFunctionMock('Flipside\LDAP', "ldap_set_option");
        $ldap_set_option->expects($this->any())->willReturn(true);
        $ldap_close = $this->getFunctionMock('Flipside\LDAP', "ldap_close");
        $ldap_close->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_read");
        $ldap_list->expects($this->any())->willReturn(true);
        $ldap_list = $this->getFunctionMock('Flipside\LDAP', "ldap_list");
        $ldap_list->expects($this->any())->willReturn(true);
        $ldap_get_entries = $this->getFunctionMock('Flipside\LDAP', "ldap_get_entries");
        $ldap_get_entries->expects($this->any())->willReturn(array('count' => 1, 0 => array('dn'=>'test', 'cn'=>'test', 'description'=>array('Bob'), 'member'=>array('count' => 1, 0 => 'uid=bob'))));

        $server = \Flipside\LDAP\LDAPServer::getInstance();
        $server->connect('ldap');
        $array = array('dn'=>array('cn=Test,dc=example,dc=com'), 'cn'=>array('Test'), 'description'=>array('Test Group'), 'uniquemember'=>array('count' => 2, 0=> 'uid=test', 1 => 'cn=x'));
        $ldapGroup = new \Flipside\LDAP\LDAPObject($array);
        $group = new \Flipside\Auth\LDAPGroup($ldapGroup);
        $ids = $group->getMemberUids(true);
        $this->assertNotFalse($ids);
        $this->assertCount(2, $ids);

        $ids = $group->members();
        $this->assertNotFalse($ids);
        $this->assertCount(2, $ids);

        $array = array('dn'=>array('cn=Test,dc=example,dc=com'), 'cn'=>array('Test'), 'description'=>array('Test Group'), 'uniquemember'=>array('count' => 2, 0=> 'uid=test', 1 => 'cn=x'));
        $ldapGroup = new \Flipside\LDAP\LDAPObject($array);
        $group = new \Flipside\Auth\LDAPGroup($ldapGroup);
        $ids = $group->members(true, false);
        $this->assertNotFalse($ids);
        $this->assertCount(2, $ids);

        $server->disconnect();
    }

    public function testSQLGroup()
    {
        $group = new \Flipside\Auth\SQLGroup(array(), false);
        $this->assertFalse($group->getGroupName());
        $this->assertFalse($group->getDescription());
        $this->assertEmpty($group->members());
        $this->assertEmpty($group->getMemberUids());

        $group = new \Flipside\Auth\SQLGroup(array('gid'=>'testGid', 'description'=>'Test Group'), false);
        $this->assertEquals($group->getGroupName(), 'testGid');
        $this->assertEquals($group->getDescription(), 'Test Group');

        $group = new \Flipside\Auth\SQLGroup(array(), false);
        $group->addMember('test');
        $group->editGroup(array('member'=>array('test1')));
        $this->assertEquals(array(), $group->getMemberUids());

        $group->editGroup(array('member'=>array(array('type'=>'Group', 'cn'=>'test1'))));
        $this->assertEquals(array(), $group->getMemberUids());

        $group->editGroup(array('member'=>array(array('type'=>'User', 'uid'=>'test1'))));
        $this->assertEquals(array(), $group->getMemberUids());
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

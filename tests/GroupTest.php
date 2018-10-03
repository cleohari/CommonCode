<?php
require_once('Autoload.php');
class GroupTest extends PHPUnit\Framework\TestCase
{
    public function testGroup()
    {
        $user = new \Auth\Group();
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
        $this->assertFalse(\Auth\Group::from_name('test'));
    }

    public function testLDAPGroup()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        try
        {
            $group = new \Auth\LDAPGroup(false);
            $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertFalse(false);
        }
        $array = array('dn'=>array('cn=Test,dc=example,dc=com'), 'cn'=>array('Test'), 'description'=>array('Test Group'), 'member'=>array('uid=test,dc=example,dc=com', 'cn=tg,dc=example,dc=com'));
        $ldapGroup = new \LDAP\LDAPObject($array);
        $group = new \Auth\LDAPGroup($ldapGroup);
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
        $ldapGroup = new \LDAP\LDAPObject($array);
        $group = new \Auth\LDAPGroup($ldapGroup);
        $ids = $group->getMemberUids();
        $this->assertNotFalse($ids);
        $this->assertCount(1, $ids);

        $group->clearMembers();
        $ids = $group->getMemberUids();
        $this->assertNotFalse($ids);
        $this->assertCount(0, $ids);

        $array = array('dn'=>array('cn=Test,dc=example,dc=com'), 'cn'=>array('Test'), 'description'=>array('Test Group'), 'uniquemember'=>array('uid=test'));
        $ldapGroup = new \LDAP\LDAPObject($array);
        $group = new \Auth\LDAPGroup($ldapGroup);
        $ids = $group->getMemberUids();
        $this->assertNotFalse($ids);
        $this->assertCount(1, $ids);

        $group->clearMembers();
        $ids = $group->getMemberUids();
        $this->assertNotFalse($ids);
        $this->assertCount(0, $ids);
    }

    public function testSQLGroup()
    {
        $group = new \Auth\SQLGroup(array(), false);
        $this->assertFalse($group->getGroupName());
        $this->assertFalse($group->getDescription());
        $this->assertEmpty($group->members());
        $this->assertEmpty($group->getMemberUids());

        $group = new \Auth\SQLGroup(array('gid'=>'testGid', 'description'=>'Test Group'), false);
        $this->assertEquals($group->getGroupName(), 'testGid');
        $this->assertEquals($group->getDescription(), 'Test Group');
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

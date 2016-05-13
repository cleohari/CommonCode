<?php
require_once('Autoload.php');
class GroupTest extends PHPUnit_Framework_TestCase
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
        $array = array('dn'=>array('cn=Test,dc=example,dc=com'), 'cn'=>array('Test'), 'description'=>array('Test Group'), 'member'=>array('uid=test'));
        $ldapGroup = new \LDAP\LDAPObject($array);
        $group = new \Auth\LDAPGroup($ldapGroup);
        $this->assertEquals('Test', $group->getGroupName());
        $this->assertEquals('Test Group', $group->getDescription());
        $group->setDescription('New Group');
        $this->assertEquals('New Group', $group->getDescription());
        $ids = $group->getMemberUids();
        $this->assertNotFalse($ids);
        $this->assertCount(1, $ids);

        $group->clearMembers();
        $ids = $group->getMemberUids();
        $this->assertNotFalse($ids);
        $this->assertCount(0, $ids);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

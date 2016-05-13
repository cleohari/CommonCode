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
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

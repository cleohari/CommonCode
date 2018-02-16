<?php
namespace Auth;

class Group extends \SerializableObject
{
    /**
     * Get the Group's Name
     *
     * @return boolean|string The name of the group or false on error
     */
    public function getGroupName()
    {
        return false;
    }

    /**
     * Get the Group's Description
     *
     * @return boolean|string The description of the group or false on error
     */
    public function getDescription()
    {
        return false;
    }

    /**
     * Set the Group's Name
     *
     * @param string $name The name for the group
     *
     * @return boolean true if the name was successfully updated, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setGroupName($name)
    {
        return false;
    }

    /**
     * Set the Group's Description
     *
     * @param string $desc The description for the group
     *
     * @return boolean true if the description was successfully updated, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setDescription($desc)
    {
        return false;
    }

    /**
     * Get the UID's of the Group Members
     *
     * @param boolean $recursive Include members of child groups
     *
     * @return array Array of UIDs
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function getMemberUids($recursive = true)
    {
        return array();
    }

    public function members($details = false, $recursive = true, $includeGroups = true)
    {
        return array();
    }

    public function member_count()
    {
        return count($this->members(false, false, false));
    }

    public function clearMembers()
    {
        return false;
    }

    public function jsonSerialize()
    {
        $group = array();
        $group['cn'] = $this->getGroupName();
        $group['description'] = $this->getDescription();
        $group['member'] = $this->getMemberUids();
        return $group;
    }

    /**
     * Get all users that aren't in this group
     *
     * @param array|boolean $select The fields to include
     *
     * @return array An array of all users not in this group
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function getNonMembers($select = false)
    {
        return array();
    }

    public function addMember($name, $isGroup = false, $flush = true)
    {
        return false;
    }

    public function editGroup($group)
    {
        //Make sure we are bound in write mode
        $auth = \AuthProvider::getInstance();
        $ldap = $auth->getMethodByName('Auth\LDAPAuthenticator');
        $ldap->getAndBindServer(true);
        if(is_array($group))
        {
            $group = json_decode(json_encode($group), false);
        }
        if(isset($group->description))
        {
            $this->setDescription($group->description);
            unset($group->description);
        }
        if(isset($group->member))
        {
            $this->clearMembers();
            $count = count($group->member);
            for($i = 0; $i < $count; $i++)
            {
                $isLast = false;
                if($i === $count - 1)
                {
                    $isLast = true;
                }
                if(!isset($group->member[$i]->type))
                {
                    continue;
                }
                if($group->member[$i]->type === 'Group')
                {
                    $this->addMember($group->member[$i]->cn, true, $isLast);
                }
                else
                {
                    $this->addMember($group->member[$i]->uid, false, $isLast);
                }
            }
            unset($group->member);
        }
        return true;
    }

    /**
     * Get the Group's By it's name
     *
     * @param string $name The name of the group to get
     * @param mixed $data The data used to help get the Group
     *
     * @return boolean|\Auth\Group The group with the specified name or false otherwise
     */
    public static function from_name($name, $data = false)
    {
        return false;
    }
}

<?php
namespace Auth;

class LDAPGroup extends Group
{
    use LDAPCachableObject;

    private $ldapObj;
    private $server;

    /**
     * Initialize a LDAPGroup object
     *
     * @SuppressWarnings("StaticAccess")
     */
    public function __construct($data)
    {
        $this->server = \LDAP\LDAPServer::getInstance();
        $this->initialize($data);
    }

    public function getGroupName()
    {
        return $this->getFieldSingleValue('cn');
    }

    public function getDescription()
    {
        return $this->getFieldSingleValue('description');
    }

    public function setDescription($name)
    {
        return $this->setField('description', $name);
    }

    private function getMembersField(&$fieldName = false)
    {
        $rawMembers = $this->getField('member');
        $fieldName  = 'member';
        if($rawMembers === false)
        {
            $rawMembers = $this->getField('uniqueMember');
            $fieldName  = 'uniqueMember';
        }
        if($rawMembers === false)
        {
            $rawMembers = $this->getField('memberUid');
            $fieldName  = 'memberUid';
        }
        if(!isset($rawMembers['count']))
        {
            $rawMembers['count'] = count($rawMembers);
        }
        return $rawMembers;
    }

    private function getIDFromDN($distinguishedName)
    {
        $split = explode(',', $distinguishedName);
        if(strncmp('cn=', $split[0], 3) === 0)
        {
            return substr($split[0], 3);
        }
        return substr($split[0], 4);
    }

    public function getMemberUids($recursive = true)
    {
        $members = array();
        $rawMembers = $this->getMembersField();
        for($i = 0; $i < $rawMembers['count']; $i++)
        {
            if($recursive && strncmp($rawMembers[$i], 'cn=', 3) === 0)
            {
                $child = new LDAPGroup($rawMembers[$i]);
                if($child !== false)
                {
                    $members = array_merge($members, $child->members());
                }
            }
            else
            {
                array_push($members, $rawMembers[$i]);
            }
        }
        $count = count($members);
        for($i = 0; $i < $count; $i++)
        {
            $members[$i] = $this->getIDFromDN($members[$i]);
        }
        return $members;
    }

    private function getObjectFromDN($distinguishedName)
    {
        $split = explode(',', $distinguishedName);
        if(strncmp('cn=', $distinguishedName, 3) === 0)
        {
            if(count($split) === 1)
            {
                return LDAPGroup::from_name($distinguishedName, $this->server);
            }
            return LDAPGroup::from_name(substr($split[0], 3), $this->server);
        }
        if(count($split) === 1)
        {
            return LDAPUser::from_name($distinguishedName, $this->server);
        }
        return LDAPUser::from_name(substr($split[0], 4), $this->server);
    }

    private function getMemberDetail($members)
    {
        $details = array();
        $count = count($members);
        for($i = 0; $i < $count; $i++)
        {
            $details[$i] = $this->getObjectFromDN($members[$i]);
        }
        return $details;
    }

    public function members($details = false, $recursive = true, $includeGroups = true)
    {
        $members = array();
        $rawMembers = $this->getMembersField();
        for($i = 0; $i < $rawMembers['count']; $i++)
        {
            if($recursive && strncmp($rawMembers[$i], 'cn=', 3) === 0)
            {
                $child = new LDAPGroup($rawMembers[$i]);
                if($child !== false)
                {
                    $members = array_merge($members, $child->members());
                }
            }
            else if($includeGroups !== false || strncmp($rawMembers[$i], 'cn=', 3) !== 0)
            {
                array_push($members, $rawMembers[$i]);
            }
        }
        if($details === true)
        {
            $members = $this->getMemberDetail($members);
        }
        return $members;
    }

    public function getNonMemebers($select = false)
    {
        $data = array();
        $groupFilter = '(&(cn=*)(!(cn='.$this->getGroupName().'))';
        $userFilter = '(&(cn=*)';
        $members = $this->members();
        $count = count($members);
        for($i = 0; $i < $count; $i++)
        {
            $dnComps = explode(',', $members[$i]);
            if(strncmp($members[$i], "uid=", 4) == 0)
            {
                $userFilter .= '(!('.$dnComps[0].'))';
            }
            else
            {
                $groupFilter .= '(!('.$dnComps[0].'))';
            }
        }
        $userFilter .= ')';
        $groupFilter .= ')';
        $groups = $this->server->read($this->server->group_base, $groupFilter);
        $count = count($groups);
        for($i = 0; $i < $count; $i++)
        {
            if($groups[$i] === false || $groups[$i] === null)
            {
                continue;
            }
            array_push($data, new LDAPGroup($groups[$i]));
        }
        $users = $this->server->read($this->server->user_base, $userFilter, false, $select);
        $count = count($users);
        for($i = 0; $i < $count; $i++)
        {
            array_push($data, new LDAPUser($users[$i]));
        } 
        return $data;
    }

    public function clearMembers()
    {
        if(isset($this->ldapObj['member']))
        {
            $this->ldapObj['member'] = array();
        }
        else if(isset($this->ldapObj['uniquemember']))
        {
            $this->ldapObj['uniquemember'] = array();
        }
        else if(isset($this->ldapObj['memberuid']))
        {
            $this->ldapObj['memberuid'] = array();
        }
    }

    public function addMember($name, $isGroup = false, $flush = true)
    {
        $distinguishedName = false;
        if($isGroup)
        {
            $distinguishedName = 'cn='.$name.','.$this->server->group_base;
        }
        else
        {
            $distinguishedName = 'uid='.$name.','.$this->server->user_base;
        }
        $propName   = false;
        $rawMembers = $this->getMembersField($propName);
        if(isset($rawMembers['count']))
        {
            unset($rawMembers['count']);
        }
        if(in_array($distinguishedName, $rawMembers) || in_array($name, $rawMembers))
        {
            return true;
        }
        if($propName === 'memberUid')
        {
            if($isGroup)
            {
                throw new \Exception('Unable to add a group as a child of this group type');
            }
            array_push($rawMembers, $name);
        }
        else
        {
            array_push($rawMembers, $distinguishedName);
        }
        $tmp = strtolower($propName);
        $this->ldapObj->$tmp = $rawMembers;
        if($flush === true)
        {
            $obj = array('dn'=>$this->ldapObj->dn);
            $obj[$propName] = $rawMembers;
            return $this->server->update($obj);
        }
        else
        {
            return true;
        }
    }

    public static function from_name($name, $data = false)
    {
        if($data === false)
        {
            throw new \Exception('data must be set for LDAPGroup');
        }
        $filter = new \Data\Filter("cn eq $name");
        $group = $data->read($data->group_base, $filter);
        if($group === false || !isset($group[0]))
        {
            return false;
        }
        return new static($group[0]);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

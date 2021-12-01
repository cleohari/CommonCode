<?php
namespace Flipside\Auth;

class SQLGroup extends Group
{
    private $data;
    private $auth;

    public function __construct($data, $auth = false)
    {
        $this->data = $data;
        $this->auth = $auth;
    }

    public function getGroupName()
    {
        if(isset($this->data['gid']))
        {
            return $this->data['gid'];
        }
        if(isset($this->data['cn']))
        {
            return $this->data['cn'];
        }
        return false;
    }

    public function getDescription()
    {
        if(isset($this->data['description']))
        {
            return $this->data['description'];
        }
        return false;
    }

    public function getMemberUids($recursive = true)
    {
        return $this->members(false, $recursive, true);
    }

    private function getMemberDetail($members)
    {
        $details = array();
        $count = count($members);
        for($i = 0; $i < $count; $i++)
        {
            $details[$i] = $this->auth->getUserByName($members[$i]);
            if($details[$i] === null)
            {
                $details[$i] = $this->auth->getGroupByName($members[$i]);
            }
        }
        return $details;
    }

    public function members($details = false, $recursive = true, $includeGroups = true)
    {
        $members = array();
        $gid = $this->getGroupName();
        $dt = $this->auth->dataSet['groupUserMap'];
        $sqlMemberData = $dt->read(new \Flipside\Data\Filter("groupCN eq \"$gid\""));
        if($sqlMemberData === false)
        {
            return $members;
        }
        $count = count($sqlMemberData);
        for($i = 0; $i < $count; $i++)
        {
            if($sqlMemberData[$i]['uid'] !== null)
            {
                array_push($members, $sqlMemberData[$i]['uid']);
            }
            else if($recursive && $sqlMemberData[$i]['gid'] !== null)
            {
                $child = $this->auth->getGroupByName($sqlMemberData[$i]['gid']);
                if($child !== null)
                {
                    $members = array_merge($members, $child->members(false, $recursive, $includeGroups));
                }
            }
            else if($includeGroups !== false)
            {
                array_push($members, $sqlMemberData[$i]['gid']);
            }
        }
        if($details)
        {
            $members = $this->getMemberDetail($members);
        }
        return $members;
    }

    public function getNonMembers($select = false)
    {
        $members = $this->getMemberUids(false);
        $fields = '*';
        if($select !== false)
        {
            $fields = implode(',', $select);
        }
        $array = '';
        $count = count($members);
        //if($count === 0)
        {
            //Just get all users and groups
            $users = $this->auth->getUsersByFilter(false, $select);
            $groups = $this->auth->getGroupsByFilter(new \Flipside\Data\Filter('cn ne "'.$this->getGroupName().'"'));
            $data = array_merge($users, $groups);
            return $data;
        }
        $sql = 'SELECT '.$fields.' FROM Users WHERE uid NOT IN ('.$array.')';
        var_dump($sql);
        return array();
    }

    public function hasMemberUID($uid)
    {
        $dt = $this->auth->dataSet['groupUserMap'];
        //Is this user in any groups?
        $data = $dt->read(new \Flipside\Data\Filter("uid eq \"$uid\""));
        if($data === false)
        {
            //Not in any groups, return
            return false;
        }
        //Ok do the more expensive check...
        return in_array($uid, $this->getMemberUids());
    }

    public function clearMembers()
    {
        return $this->tmpMembers = array();
    }

    public function addMember($name, $isGroup = false, $flush = true)
    {
        if($isGroup)
        {
        	array_push($this->tmpMembers, array('gid' => $name));
        }
        else
        {
        	array_push($this->tmpMembers, array('uid' => $name));
        }
        if($flush)
        {
                $gid = $this->getGroupName();
        	$memberDT = $this->auth->getDataTable('groupUserMap');
                //Get all cu rrent direct members
                $existing = $memberDT->read(new \Flipside\Data\Filter('groupCN eq "'.$gid.'"'));
                $exCount = count($existing);
                $newCount = count($this->tmpMembers);
                for($i = 0; $i < $exCount; $i++)
                {
                    $isUser = isset($existing[$i]['uid']);
                    for($j = 0; $j < $newCount; $j++)
                    {
                        if($this->tmpMembers[$j] === false)
                        {
                            continue;
                        }
                        if(($isUser && !isset($this->tmpMembers[$j]['uid'])) || (!$isUser && isset($this->tmpMembers[$j]['uid'])))
                        {
                            //Not same type, skip
                            continue;
                        }
                        if($isUser && strcmp($this->tmpMembers[$j]['uid'], $existing[$i]['uid']) === 0)
                        {
                            $existing[$i] = false;
                            $this->tmpMembers[$j] = false;
                        }
                        else if(!$isUser && strcmp($this->tmpMembers[$j]['gid'], $existing[$i]['gid']) === 0)
                        {
                            $existing[$i] = false;
                            $this->tmpMembers[$j] = false;
                        }
                    }
                }
                $existing = array_values(array_filter($existing));
                $this->tmpMembers = array_values(array_filter($this->tmpMembers));
                $newCount = count($this->tmpMembers);
                for($i = 0; $i < $newCount; $i++)
                {
                    $this->tmpMembers[$i]['groupCN'] = $gid;
                    $res = $memberDT->create($this->tmpMembers[$i]);
                    $res = true;
                    if($res === false)
                    {
                        return false;
                    }
                }
                $remCount = count($existing);
                for($i = 0; $i < $remCount; $i++)
                {
                    $memberDT->delete(new \Flipside\Data\Filter('idgroupUserMap eq '.$existing[$i]['idgroupUserMap']));
                }
        }
        return true;
    }
}

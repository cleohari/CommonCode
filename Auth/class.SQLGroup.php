<?php
namespace Auth;

class SQLGroup extends Group
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getGroupName()
    {
        if(isset($data['gid']))
        {
            return $data['gid'];
        }
        return false;
    }

    public function getDescription()
    {
        if(isset($data['description']))
        {
            return $data['description'];
        }
        return false;
    }

    public function getMemberUids($recursive = true)
    {
        return $this->members(false, $recursive, true);
    }

    public function members($details = false, $recursive = true, $includeGroups = true)
    {
        //TODO
        return array();
    }
}

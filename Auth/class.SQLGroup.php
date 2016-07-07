<?php
namespace Auth;

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

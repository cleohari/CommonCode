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
        if(isset($this->data['gid']))
        {
            return $this->data['gid'];
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

    public function members($details = false, $recursive = true, $includeGroups = true)
    {
        //TODO
        return array();
    }
}

<?php
namespace Auth;

class SQLUser extends User
{
    private $data;
    private $auth;

    /**
     * Initialize a SQLUser object
     *
     * @param boolean|array $data The data to initialize the SQLUser with or false for an empty User
     * @param boolean|\Auth\SQLAuthenticator The SQLAuthenticator instance that produced this user
     */
    public function __construct($data = false, $auth = false)
    {
        $this->data = array();
        $this->auth = $auth;
        if($data !== false)
        {
            $this->data = $data;
            if(isset($data['extended']))
            {
                $this->data = $data['extended'];
            }
        }
    }

    public function isInGroupNamed($name)
    {
        if($this->auth === false)
        {
            return false;
        }
        $auth_data_set = $this->auth->dataSet;
        $group_data_table = $auth_data_set['group'];
        $uid = $this->uid;
        $filter = new \Data\Filter("uid eq '$uid' and gid eq '$name'");
        $groups = $group_data_table->read($filter);
        if($groups === false || !isset($groups[0]))
        {
            return false;
        }
        return true;
    }

    public function __get($propName)
    {
        if(isset($this->data[$propName]))
        {
            return $this->data[$propName];
        }
        return false;
    }

    public function __set($propName, $value)
    {
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

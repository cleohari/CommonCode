<?php
namespace Auth;

class SQLUser extends User
{
    private $data;

    function __construct($data=false)
    {
        $this->data = array();
        if($data !== false)
        {
            $this->data = $data;
            if(isset($data['extended']))
            {
                $this->data = $data['extended'];
            }
        }
    }

    function isInGroupNamed($name)
    {
        if(isset($this->data['current_data_set']))
        {
            $auth_data_set = \DataSetFactory::getDataSetByName($this->data['current_data_set']);
        }
        else
        {
            $auth_data_set = \DataSetFactory::getDataSetByName('authentication');
        }
        $group_data_table = $auth_data_set['group'];
        $uid = $this->getUid();
        $filter = new \Data\Filter("uid eq '$uid' and gid eq '$name'");
        $groups = $group_data_table->read($filter);
        if($groups === false || !isset($groups[0]))
        {
            return false;
        }
        return true;

    }

    function getEmail()
    {
        if(isset($this->data['mail']))
        {
             return $this->data['mail'];
        }
        return $this->getUid();
    }

    function getUid()
    {
        if(isset($this->data['uid']))
        {
            return $this->data['uid'];
        }
        return false;
    }
}

?>

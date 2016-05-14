<?php
namespace Auth;

class SQLUser extends User
{
    private $uid;

    function __construct($data=false)
    {
        $this->uid = false;
        var_dump($data);
        if($data !== false && !isset($data['extended']))
        {
            //Generic user object
            //TODO get from DB
        }
        else
        {
            if(isset($data['extended']))
            {
                $this->uid = $data['extended'];
            }
        }
    }

    function isInGroupNamed($name)
    {
        $auth_data_set = \DataSetFactory::getDataSetByName('authentication');
        $group_data_table = $auth_data_set['group'];
        $filter = new \Data\Filter("uid eq '$this->uid' and gid eq '$name'");
        $groups = $group_data_table->read($filter);
        if($groups === false || !isset($groups[0]))
        {
            return false;
        }
        return true;

    }

    function getEmail()
    {
        return $this->uid;
    }

    function getUid()
    {
        return $this->uid;
    }
}

?>

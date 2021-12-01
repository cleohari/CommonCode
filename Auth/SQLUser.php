<?php
namespace Flipside\Auth;

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
        if(isset($this->data['title']))
        {
            $this->data['title'] = explode(',', $this->data['title']);
        }
        if(isset($this->data['ou']))
        {
            $this->data['ou'] = explode(',', $this->data['ou']);
        }
        if(isset($this->data['host']))
        {
            $this->data['host'] = explode(',', $this->data['host']);
        }
    }

    public function getGroups()
    {
        if($this->auth === false)
        {
            return false;
        }
        $dt = $this->auth->dataSet['groupUserMap'];
        $sqlMemberData = $dt->read(new \Flipside\Data\Filter("uid eq \"$this->uid\""));
        if(empty($sqlMemberData))
        {
            return false;
        }
        $res = array();
        $count = count($sqlMemberData);
        for($i = 0; $i < $count; $i++)
        {
            array_push($res, new SQLGroup($sqlMemberData[$i], $this->auth));
        }
        return $res;
    }

    public function isInGroupNamed($name)
    {
        if($this->auth === false)
        {
            return false;
        }
        $group = $this->auth->getGroupByName($name);
        if($group === null)
        {
            return false;
        }
        return $group->hasMemberUID($this->uid);
    }

    public function getPasswordResetHash()
    {
        $filter = new \Flipside\Data\Filter('uid eq "'.$this->uid.'"');
        $userDT = $this->auth->getCurrentUserDataTable();
        $data = $userDT->read($filter);
        if(strlen($data[0]['userPassword']) === 0)
        {
             $data[0]['userPassword'] = openssl_random_pseudo_bytes(10);
        }
        $hash = hash('sha512', $data[0]['uid'].';'.$data[0]['userPassword'].';'.$data[0]['mail']);
        $update = array('resetHash' => $hash);
        $res = $userDT->update($filter, $update);
        if($res === false)
        {
            throw new \Exception('Unable to create hash in SQL User!');
        }
        return $hash;
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
        $filter = new \Flipside\Data\Filter('uid eq "'.$this->uid.'"');
        $userDT = $this->auth->getCurrentUserDataTable();
        $data = array($propName => $value);
        $res = $userDT->update($filter, $data);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

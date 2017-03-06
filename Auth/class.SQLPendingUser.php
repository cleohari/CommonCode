<?php
namespace Auth;

class SQLPendingUser extends PendingUser
{
    private $hash;
    private $time;
    private $blob;
    private $table;

    public function __construct($data, $table = false)
    {
        $this->hash = $data['hash'];
        $this->time = new \DateTime($data['time']);
        $this->blob = json_decode($data['data']);
        $this->table = $table;
    }

    public function __get($propName)
    {
        if(is_array($this->blob->{$propName}))
        {
            return $this->blob->{$propName}[0];
        }
        return $this->blob->{$propName};
    }

    public function __set($propName, $value)
    {
    }

    public function __isset($propName)
    {
       return isset($this->blob->{$propName});
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getRegistrationTime()
    {
        return $this->time;
    }

    public function getPassword()
    {
        if(is_array($this->blob->password))
        {
            return $this->blob->password[0];
        }
        return $this->blob->password;
    }

    public function offsetGet($offset)
    {
        return $this->blob->$offset;
    }

    public function delete()
    {
        $this->table->delete(new \Data\Filter("hash eq '{$this->hash}'"));
    }
}


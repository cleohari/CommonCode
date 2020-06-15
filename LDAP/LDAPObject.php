<?php
namespace Flipside\LDAP;

class LDAPObject extends \Flipside\SerializableObject
{
    public $server;

    function __construct($array = false, $server = false)
    {
        parent::__construct($array);
        $this->server = $server;
    }

    public function jsonSerialize()
    {
        $ret = array();
        foreach($this as $key => $value)
        {
            if($key === 'server' || $key === 'count')
            {
                continue;
            }
            if(is_numeric($key))
            {
                continue;
            }
            if($key === 'jpegphoto')
            {
                $ret[$key] = base64_encode($value[0]);
                continue;
            }
            if(is_array($value) && $value['count'] === 1)
            {
                $ret[$key] = $value[0];
            }
            else
            {
                $ret[$key] = $value;
            }
        }
        return $ret;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

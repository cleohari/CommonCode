<?php
namespace Auth;

class NullAuthenticator extends Authenticator
{
    public function login($username, $password)
    {
        return array('res'=>true, 'extended'=>null);
    }

    public function isLoggedIn($data)
    {
        if(isset($data['res']))
        {
            return $data['res'];
        }
        return false;
    }

    public function getUser($data)
    {
        return null;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

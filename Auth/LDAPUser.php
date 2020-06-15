<?php
namespace Flipside\Auth;

class LDAPUser extends User
{
    use LDAPCachableObject;
    use LDAPGettableObject;
    use LDAPSettableObject;

    private $ldapObj;
    private $server;

    /**
     * Initialize a LDAPUser object
     *
     * @param boolean|array|object $data The data to initialize the LDAPUser with or false for an empty User
     *
     * @SuppressWarnings("StaticAccess")
     */
    public function __construct($data = false)
    {
        $this->server = \Flipside\LDAP\LDAPServer::getInstance();
        $this->initialize($data);
    }

    private function checkChildGroup($array)
    {
        $res = false;
        for($i = 0; $i < $array['count']; $i++)
        {
            if(strpos($array[$i], $this->server->group_base) !== false)
            {
                $dn = explode(',', $array[$i]);
                $res = $this->isInGroupNamed(substr($dn[0], 3));
                if($res)
                {
                    return $res;
                }
            }
        }
        return $res;
    }

    /**
     * @param string $listName The name of the list to search
     * @param Group $group The group to search inside
     * @param string $dn The distringuished name to search for
     */
    private function isInListOrChild($listName, $group, $dn)
    {
        if(!isset($group[$listName]))
        {
            return false;
        }
        if(in_array($dn, $group[$listName]))
        {
            return true;
        }
        return $this->checkChildGroup($group[$listName]);
    }

    private function uidInMemberUid($group, $uid)
    {
        return (isset($group['memberUid']) && in_array($uid, $group['memberUid']));
    }

    public function isInGroupNamed($name)
    {
        $filter = new \Flipside\Data\Filter('cn eq '.$name);

        $auth = \Flipside\AuthProvider::getInstance();
        $ldap = $auth->getMethodByName('Flipside\Auth\LDAPAuthenticator');
        if($ldap !== false)
        {
            $this->server = $ldap->getAndBindServer(false);
        }
        $group = $this->server->read($this->server->group_base, $filter);

        if(!empty($group))
        {
            $group = $group[0];
            $dn  = $this->ldapObj->dn;
            $uid = $this->ldapObj->uid[0];
            $ret = $this->isInListOrChild('member', $group, $dn);
            if($ret === false)
            {
                $ret = $this->isInListOrChild('uniquemember', $group, $dn);
            }
            if($ret === false && $this->uidInMemberUid($group, $uid))
            {
                return true;
            }
            return $ret;
        }
        return false;
    }

    protected $valueDefaults = array(
        'o' => 'Volunteer'
    );

    protected $multiValueProps = array(
        'title',
        'ou',
        'host'
    );

    protected $cachedOnlyProps = array(
        'uid'
    );

    /**
     * LDAP does not allow anonymous read
     *
     * @SuppressWarnings("StaticAccess")
     */
    protected function enableRead()
    {
        //Make sure we are bound in read mode
        $auth = \Flipside\AuthProvider::getInstance();
        $ldap = $auth->getMethodByName('\Flipside\Auth\LDAPAuthenticator');
        if($ldap !== false)
        {
            $this->server = $ldap->getAndBindServer(false);
        }
    }

    /**
     * Allow write for the user
     *
     * @SuppressWarnings("StaticAccess")
     */
    protected function enableReadWrite()
    {
        //Make sure we are bound in write mode
        $auth = \Flipside\AuthProvider::getInstance();
        $ldap = $auth->getMethodByName('Flipside\Auth\LDAPAuthenticator');
        if($ldap !== false)
        {
            $this->server = $ldap->getAndBindServer(true);
        }
    }

    public function getGroups()
    {
        $this->enableRead();
        $res = array();
        $groups = $this->server->read($this->server->group_base);
        if(!empty($groups))
        {
            $count = count($groups);
            for($i = 0; $i < $count; $i++)
            {
                if($this->isInGroupNamed($groups[$i]['cn'][0]))
                {
                    array_push($res, new LDAPGroup($groups[$i]));
                }
            }
            return $res;
        }
        return false;
    }

    public function addLoginProvider($provider)
    {
        return $this->appendField('host', $provider);
    }

    private function generateLDAPPass($pass)
    {
        mt_srand((double)microtime() * 1000000);
        $salt = pack("CCCC", mt_rand(), mt_rand(), mt_rand(), mt_rand());
        $hash = base64_encode(pack('H*', sha1($pass.$salt)).$salt);
        return '{SSHA}'.$hash;
    }

    public function setPass($password)
    {
        $password = $this->generateLDAPPass($password);
        if(!is_object($this->ldapObj))
        {
            return $this->setFieldLocal('userPassword', $password);
        }
        $obj = array('dn'=>$this->ldapObj->dn);
        $obj['userPassword'] = $password;
        if(isset($this->ldapObj->uniqueidentifier))
        {
            $obj['uniqueIdentifier'] = null;
        }
        //Make sure we are bound in write mode
        $this->enableReadWrite();
        return $this->update($obj);
    }

    public function validate_password($password)
    {
        return $this->server->bind($this->ldapObj->dn, $password) !== false;
    }

    public function validate_reset_hash($hash)
    {
        if(isset($this->ldapObj->uniqueidentifier) && strcmp($this->ldapObj->uniqueidentifier[0], $hash) === 0)
        {
            return true;
        }
        return false;
    }

    public static function from_name($name, $data)
    {
        if($data === false)
        {
            throw new \Exception('data must be set for LDAPUser');
        }
        $filter = new \Data\Filter("uid eq $name");
        $user = $data->read($data->user_base, $filter);
        if(empty($user))
        {
            return false;
        }
        return new static($user[0]);
    }

    public function flushUser()
    {
        if(is_object($this->ldapObj))
        {
            //In this mode we are always up to date
            return true;
        }
        $obj = $this->ldapObj;
        $obj['objectClass'] = array('top', 'inetOrgPerson', 'extensibleObject');
        $obj['dn'] = 'uid='.$this->ldapObj['uid'].','.$this->server->user_base;
        if(!isset($obj['sn']))
        {
            $obj['sn'] = $obj['uid'];
        }
        if(!isset($obj['cn']))
        {
            $obj['cn'] = $obj['uid'];
        }
        //Make sure we are bound in write mode
        $this->enableReadWrite();
        $ret = $this->server->create($obj);
        return $ret;
    }

    private function getHashFromUser($ldapObj)
    {
        if(isset($ldapObj->userpassword))
        {
            return hash('sha512', $ldapObj->dn.';'.$ldapObj->userpassword[0].';'.$ldapObj->mail[0]);
        }
        return hash('sha512', $ldapObj->dn.';'.openssl_random_pseudo_bytes(10).';'.$ldapObj->mail[0]);
    }

    public function getPasswordResetHash()
    {
        $ldapObj = $this->server->read($this->server->user_base, new \Data\Filter('uid eq '.$this->uid));
        $ldapObj = $ldapObj[0];
        $hash = $this->getHashFromUser($ldapObj);
        $obj = array('dn'=>$this->ldapObj->dn);
        $obj['uniqueIdentifier'] = $hash;
        //Make sure we are bound in write mode
        $this->enableReadWrite();
        if($this->server->update($obj) === false)
        {
            throw new \Exception('Unable to create hash in LDAP object!');
        }
        return $hash;
    }

    public function removeEmail($email)
    {
        $mail = $this->ldapObj->mail;
        if(($key = array_search($email, $mail)) !== false)
        {
            unset($mail[$key]);
            if(isset($mail['count']))
            {
                $mail['count']--;
            }
        }
        $count = count($mail);
        if(isset($mail['count']))
        {
            $count = $mail['count'];
            unset($mail['count']);
        }
        $obj = array('dn'=>$this->ldapObj->dn);
        $obj['mail'] = $mail;
        if($count === 1)
        {
            $obj['labeleduri'] = null;
        }
        //Make sure we are bound in write mode
        $this->enableReadWrite();
        if($this->server->update($obj) === false)
        {
            throw new \Exception('Unable to change mail properties in LDAP object!');
        }
        $this->ldapObj = $this->server->read($this->server->user_base, new \Data\Filter('uid eq '.$this->uid));
        return true;
    }

    public function delete()
    {
        //Make sure we are bound in write mode
        $this->enableReadWrite();
        return $this->server->delete($this->ldapObj->dn);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

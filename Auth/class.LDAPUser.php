<?php
namespace Auth;

class LDAPUser extends User
{
    use LDAPCachableObject;

    private $ldapObj;
    private $server;

    /**
     * Initialize a LDAPUser object
     *
     * @SuppressWarnings("StaticAccess")
     */
    public function __construct($data = false)
    {
        $this->server = \LDAP\LDAPServer::getInstance();
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

    public function isInGroupNamed($name)
    {
        $filter = new \Data\Filter('cn eq '.$name);
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
            if($ret === false && isset($group['memberUid']) && in_array($uid, $group['memberUid']))
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

    protected function getValueWithDefault($propName)
    {
        if(isset($this->valueDefaults[$propName]))
        {
            $tmp = $this->getFieldSingleValue($propName);
            if($tmp === false)
            {
                return $this->valueDefaults[$propName];
            }
            return $tmp;
        }
        return false;
    }

    protected function getMultiValueProp($propName)
    {
        if(in_array($propName, $this->multiValueProps))
        {
            $tmp = $this->getField($propName);
            if(isset($tmp['count']))
            {
                unset($tmp['count']);
            }
            return $tmp;
        }
        return false;
    }

    public function __get($propName)
    {
        $tmp = $this->getValueWithDefault($propName);
        if($tmp !== false)
        {
            return $tmp;
        }
        $tmp = $this->getMultiValueProp($propName);
        if($tmp !== false)
        {
            return $tmp;
        }
        return $this->getFieldSingleValue($propName);
    }

    protected function setCachedOnlyProp($propName, $value)
    {
        if(in_array($propName, $this->cachedOnlyProps))
        {
            if(!is_object($this->ldapObj))
            {
                $this->setFieldLocal($propName, $value);
                return true;
            }
            throw new \Exception('Unsupported!');
        }
        return false;
    }

    protected function setMultiValueProp($propName, $value)
    {
        if(in_array($propName, $this->multiValueProps) && !is_array($value))
        {
             $this->setField($propName, array($value));
             return true;
        }
        return false;
    }

    public function __set($propName, $value)
    {
        if($this->setCachedOnlyProp($propName, $value) === true)
        {
            return;
        }
        if($this->setMultiValueProp($propName, $value) === true)
        {
            return;
        }
        $this->setField($propName, $value);
    }

    /**
     * Allow write for the user
     *
     * @SuppressWarnings("StaticAccess")
     */
    protected function enableReadWrite()
    {
        //Make sure we are bound in write mode
        $auth = \AuthProvider::getInstance();
        $ldap = $auth->getMethodByName('Auth\LDAPAuthenticator');
        if($ldap !== false)
        {
            $ldap->getAndBindServer(true);
        }
    }

    public function getGroups()
    {
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
        if(!is_object($this->ldapObj))
        {
            return $this->setFieldLocal('userPassword', $this->generateLDAPPass($password));
        }
        $obj = array('dn'=>$this->ldapObj->dn);
        $obj['userPassword'] = $this->generateLDAPPass($password);
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

    public static function from_name($name, $data = false)
    {
        if($data === false)
        {
            throw new \Exception('data must be set for LDAPUser');
        }
        $filter = new \Data\Filter("uid eq $name");
        $user = $data->read($data->user_base, $filter);
        if($user === false || !isset($user[0]))
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
        $ret = $this->server->create($obj);
        return $ret;
    }

    public function getPasswordResetHash()
    {
        //Make sure we are bound in write mode
        $this->enableReadWrite();
        $ldapObj = $this->server->read($this->server->user_base, new \Data\Filter('uid eq '.$this->uid));
        $ldapObj = $ldapObj[0];
        $hash = false;
        if(isset($ldapObj->userpassword))
        {
            $hash = hash('sha512', $ldapObj->dn.';'.$ldapObj->userpassword[0].';'.$ldapObj->mail[0]);
        }
        else
        {
            $hash = hash('sha512', $ldapObj->dn.';'.openssl_random_pseudo_bytes(10).';'.$ldapObj->mail[0]);
        }
        $obj = array('dn'=>$this->ldapObj->dn);
        $obj['uniqueIdentifier'] = $hash;
        if($this->server->update($obj) === false)
        {
            throw new \Exception('Unable to create hash in LDAP object!');
        }
        return $hash;
    }

    public function delete()
    {
        //Make sure we are bound in write mode
        $this->enableReadWrite();
        return $this->server->delete($this->ldapObj->dn);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

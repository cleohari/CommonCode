<?php
namespace Auth;

class LDAPUser extends User
{
    use LDAPCachableObject;

    private $ldapObj;
    private $server;

    public function __construct($data=false)
    {
        $this->server = \LDAP\LDAPServer::getInstance();
        if($data !== false && !isset($data['dn']) && !isset($data['extended']))
        {
            //Generic user object
            $filter = new \Data\Filter('mail eq '.$data['mail']);
            $users = $this->server->read($this->server->user_base, $filter);
            if($users === false || !isset($users[0]))
            {
                throw new \Exception('No such LDAP User!');
            }
            $this->ldapObj = $users[0];
        }
        else
        {
            if(isset($data['extended']))
            {
                $this->ldapObj = $data['extended'];
            }
            else
            {
                $this->ldapObj = $data;
            }
        }
    }

    private function check_child_group($array)
    {
        $res = false;
        for($i = 0; $i < $array['count']; $i++)
        {
            if(strpos($array[$i], $this->server->group_base) !== false)
            {
                $dn = explode(',', $array[$i]);
                $res = $this->isInGroupNamed(substr($dn[0], 3));
                if($res) return $res;
            }
        }
        return $res;
    }

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
        return $this->check_child_group($group[$listName]);
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

    public function getDisplayName()
    {
        return $this->getFieldSingleValue('displayName');
    }

    public function getGivenName()
    {
        return $this->getFieldSingleValue('givenName');
    }

    public function getEmail()
    {
        return $this->getFieldSingleValue('mail');
    }

    public function getUid()
    {
        return $this->getFieldSingleValue('uid');
    }

    public function getPhoto()
    {
        return $this->getFieldSingleValue('jpegPhoto');
    }

    public function getPhoneNumber()
    {
        return $this->getFieldSingleValue('mobile');
    }

    public function getOrganization()
    {
        $org = $this->getFieldSingleValue('o');
        if($org === false)
        {
            return 'Volunteer';
        }
        return $org;
    }

    public function getTitles()
    {
        $titles = $this->getField('title');
        if(isset($titles['count']))
        {
            unset($titles['count']);
        }
        return $titles;
    }

    public function getState()
    {
        return $this->getFieldSingleValue('st');
    }

    public function getCity()
    {
        return $this->getFieldSingleValue('l');
    }

    public function getLastName()
    {
        return $this->getFieldSingleValue('sn');
    }

    public function getNickName()
    {
        return $this->getFieldSingleValue('cn');
    }

    public function getAddress()
    {
        return $this->getFieldSingleValue('postalAddress');
    }

    public function getPostalCode()
    {
        return $this->getFieldSingleValue('postalCode');
    }

    public function getCountry()
    {
        return $this->getFieldSingleValue('c');
    }

    public function getOrganizationUnits()
    {
        $units = $this->getField('ou');
        if(isset($units['count']))
        {
            unset($units['count']);
        }
        return $units;
    }

    public function getLoginProviders()
    {
        $hosts = $this->getField('host');
        if(isset($hosts['count']))
        {
            unset($hosts['count']);
        }
        return $hosts;
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
        else
        {
            return false;
        }
    }

    public function addLoginProvider($provider)
    {
        return $this->appendField('host', $provider);
    }

    private function generateLDAPPass($pass)
    {
        mt_srand((double)microtime()*1000000);
        $salt = pack("CCCC", mt_rand(), mt_rand(), mt_rand(), mt_rand());
        $hash = base64_encode(pack('H*',sha1($pass.$salt)).$salt);
        return '{SSHA}'.$hash;
    }

    public function setPass($password)
    {
        if(!is_object($this->ldapObj))
        {
            return $this->setFieldLocal('userPassword',  $this->generateLDAPPass($password));
        }
        else
        {
            $obj = array('dn'=>$this->ldapObj->dn);
            $obj['userPassword'] = $this->generateLDAPPass($password);
            if(isset($this->ldapObj->uniqueidentifier))
            {
               $obj['uniqueIdentifier'] = null;
            }
            //Make sure we are bound in write mode
            $auth = \AuthProvider::getInstance();
            $ldap = $auth->getAuthenticator('Auth\LDAPAuthenticator');
            $ldap->get_and_bind_server(true);
            return $this->update($obj);
        }
    }

    public function validate_password($password)
    {
        if($this->server->bind($this->ldapObj->dn, $password))
        {
            return true;
        }
        return false;
    }

    public function validate_reset_hash($hash)
    {
        if(isset($this->ldapObj->uniqueidentifier) && strcmp($this->ldapObj->uniqueidentifier[0], $hash) === 0)
        {
            return true;
        }
        return false;
    }

    static function from_name($name, $data=false)
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

    static function from_dn($dn, $data=false)
    {
        if($data === false)
        {
            throw new \Exception('data must be set for LDAPUser');
        }
        $filter = new \Data\Filter("dn eq $dn");
        $user = $data->read($data->user_base, $filter);
        if($user === false || !isset($user[0]))
        {
            return false;
        }
        return new static($user[0]);
    }

    public function setDisplayName($name)
    {
        return $this->setField('displayName', $name);
    }

    public function setGivenName($name)
    {
        return $this->setField('givenName', $name);
    }

    public function setLastName($sn)
    {
        return $this->setField('sn', $sn);
    }

    public function setEmail($email)
    {
        return $this->setField('mail', $email);
    }

    public function setUid($uid)
    {
        if(!is_object($this->ldapObj))
        {
            return $this->setFieldLocal('uid', $uid);
        }
        else
        {
            throw new \Exception('Unsupported!');
        }
    }

    public function setPhoto($photo)
    {
        return $this->setField('jpegPhoto', $photo);
    }

    public function setAddress($address)
    {
        return $this->setField('postalAddress', $address);
    }

    public function setPostalCode($postalcode)
    {
        $postalcode = trim($postalcode);
        return $this->setField('postalCode', $postalcode);
    }

    public function setCountry($country)
    {
        return $this->setField('c', $country);
    }

    public function setState($state)
    {
        return $this->setField('st', $state);
    }

    public function setCity($city)
    {
        return $this->setField('l', $city);
    }

    public function setPhoneNumber($phone)
    {
        return $this->setField('mobile', $phone);
    }

    public function setTitles($titles)
    {
        if(!is_array($titles))
        {
            $titles = array($titles);
        }
        return $this->setField('title', $titles);
    }

    public function setOrganizationUnits($ous)
    {
        if(!is_array($ous))
        {
            $ous = array($ous);
        }
        return $this->setField('ou', $ous);
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
        $auth = \AuthProvider::getInstance();
        $ldap = $auth->getAuthenticator('Auth\LDAPAuthenticator');
        $ldap->get_and_bind_server(true);
        $ldapObj = $this->server->read($ldap->user_base, new \Data\Filter('uid eq '.$this->getUid()));
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
        $auth = \AuthProvider::getInstance();
        $ldap = $auth->getAuthenticator('Auth\LDAPAuthenticator');
        $ldap->get_and_bind_server(true);
        return $this->server->delete($this->ldapObj->dn);
    }
}

?>

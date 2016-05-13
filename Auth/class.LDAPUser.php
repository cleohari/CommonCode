<?php
namespace Auth;

class LDAPUser extends User
{
    use LDAPCachableObject;

    private $ldapObj;
    private $server;

    function __construct($data=false)
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

    function isInGroupNamed($name)
    {
        $filter = new \Data\Filter('cn eq '.$name);
        $group = $this->server->read($this->server->group_base, $filter);
        if(!empty($group))
        {
            $group = $group[0];
            $dn  = $this->ldapObj->dn;
            $uid = $this->ldapObj->uid[0];
            if(isset($group['member']))
            {
                if(in_array($dn, $group['member']))
                {
                    return true;
                }
                else
                {
                    return $this->check_child_group($group['member']);
                }
            }
            else if(isset($group['uniquemember']))
            {
                if(in_array($dn, $group['uniquemember']))
                {
                    return true;
                }
                else
                {
                    return $this->check_child_group($group['uniquemember']);
                }
            }
            else if(isset($group['memberUid']) && in_array($uid, $group['memberUid']))
            {
                return true;
            }
        }
        return false;
    }

    function getDisplayName()
    {
        return $this->getFieldSingleValue('displayName');
    }

    function getGivenName()
    {
        return $this->getFieldSingleValue('givenName');
    }

    function getEmail()
    {
        return $this->getFieldSingleValue('mail');
    }

    function getUid()
    {
        return $this->getFieldSingleValue('uid');
    }

    function getPhoto()
    {
        return $this->getFieldSingleValue('jpegPhoto');
    }

    function getPhoneNumber()
    {
        return $this->getFieldSingleValue('mobile');
    }

    function getOrganization()
    {
        $org = $this->getFieldSingleValue('o');
        if($org === false)
        {
            return 'Volunteer';
        }
        return $org;
    }

    function getTitles()
    {
        $titles = $this->getField('title');
        if(isset($titles['count']))
        {
            unset($titles['count']);
        }
        return $titles;
    }

    function getState()
    {
        return $this->getFieldSingleValue('st');
    }

    function getCity()
    {
        return $this->getFieldSingleValue('l');
    }

    function getLastName()
    {
        return $this->getFieldSingleValue('sn');
    }

    function getNickName()
    {
        return $this->getFieldSingleValue('cn');
    }

    function getAddress()
    {
        return $this->getFieldSingleValue('postalAddress');
    }

    function getPostalCode()
    {
        return $this->getFieldSingleValue('postalCode');
    }

    function getCountry()
    {
        return $this->getFieldSingleValue('c');
    }

    function getOrganizationUnits()
    {
        $units = $this->getField('ou');
        if(isset($units['count']))
        {
            unset($units['count']);
        }
        return $units;
    }

    function getLoginProviders()
    {
        $hosts = $this->getField('host');
        if(isset($hosts['count']))
        {
            unset($hosts['count']);
        }
        return $hosts;
    }

    function getGroups()
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

    function addLoginProvider($provider)
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

    function setPass($password)
    {
        if(!is_object($this->ldap_obj))
        {
            return $this->setFieldLocal('userPassword',  $this->generateLDAPPass($password));
        }
        else
        {
            $obj = array('dn'=>$this->ldap_obj->dn);
            $obj['userPassword'] = $this->generateLDAPPass($password);
            if(isset($this->ldap_obj->uniqueidentifier))
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

    function validate_password($password)
    {
        if($this->server->bind($this->ldap_obj->dn, $password))
        {
            return true;
        }
        return false;
    }

    function validate_reset_hash($hash)
    {
        if(isset($this->ldap_obj->uniqueidentifier) && strcmp($this->ldap_obj->uniqueidentifier[0], $hash) === 0)
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

    function setDisplayName($name)
    {
        return $this->setField('displayName', $name);
    }

    function setGivenName($name)
    {
        return $this->setField('givenName', $name);
    }

    function setLastName($sn)
    {
        return $this->setField('sn', $sn);
    }

    function setEmail($email)
    {
        return $this->setField('mail', $email);
    }

    function setUid($uid)
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

    function setPhoto($photo)
    {
        return $this->setField('jpegPhoto', $photo);
    }

    function setAddress($address)
    {
        return $this->setField('postalAddress', $address);
    }

    function setPostalCode($postalcode)
    {
        $postalcode = trim($postalcode);
        return $this->setField('postalCode', $postalcode);
    }

    function setCountry($country)
    {
        return $this->setField('c', $country);
    }

    function setState($state)
    {
        return $this->setField('st', $state);
    }

    function setCity($city)
    {
        return $this->setField('l', $city);
    }

    function setPhoneNumber($phone)
    {
        return $this->setField('mobile', $phone);
    }

    function setTitles($titles)
    {
        if(!is_array($titles))
        {
            $titles = array($titles);
        }
        return $this->setField('title', $titles);
    }

    function setOrganizationUnits($ous)
    {
        if(!is_array($ous))
        {
            $ous = array($ous);
        }
        return $this->setField('ou', $ous);
    }

    function flushUser()
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

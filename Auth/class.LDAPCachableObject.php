<?php
namespace Auth;

trait LDAPCachableObject
{
    protected function initialize($data)
    {
        if($data === false)
        {
            return;
        }
        if(is_string($data))
        {
            $this->ldapObj = $this->initializeFromDN($data);
            return;
        }
        if($data instanceof \LDAP\LDAPObject)
        {
            $this->ldapObj = $data;
            return;
        }
        $this->ldapObj = $this->initializeFromArray($data);
    }

    private function initializeFromDN($dn)
    {
        $filter = new \Data\Filter("dn eq $dn");
        $data = $this->server->read($this->server->user_base, $filter);
        if($data === false || !isset($data[0]))
        {
            $data = $this->server->read($this->server->group_base, $filter);
            if($data === false || !isset($data[0]))
            {
                return false;
            }
        }
        return $data[0];
    }

    private function initializeFromArray($array)
    {
        if(isset($array['extended']))
        {
            return $array['extended'];
        }
        //Generic user object
        $filter = new \Data\Filter('mail eq '.$data['mail']);
        $users = $this->server->read($this->server->user_base, $filter);
        if($users === false || !isset($users[0]))
        {
            return false;
        }
        return $users[0];
    }

    protected function update($obj)
    {
        try
        {
            return $this->server->update($obj);
        }
        catch(\Exception $ex)
        {
            $auth = \AuthProvider::getInstance();
            $ldap = $auth->getMethodByName('Auth\LDAPAuthenticator');
            if($ldap === false)
            {
                return false;
            }
            $this->server = $ldap->getAndBindServer(true);
            return $this->server->update($obj);
        }
    }

    /**
     * Get the specified field from the cached object or LDAPObject
     *
     * @param string $fieldName The name of the field to retrieve
     *
     * @return mixed string|array the value of the field
     */
    protected function getField($fieldName)
    {
        if(!is_object($this->ldapObj))
        {
            return $this->getFieldLocal($fieldName);
        }
        return $this->getFieldServer($fieldName);
    }

    /**
     * Get the value of the specified field from the cached object or LDAPObject
     *
     * @param string $fieldName The name of the field to retrieve
     *
     * @return mixed string the value of the field
     */
    protected function getFieldSingleValue($fieldName)
    {
        if(!is_object($this->ldapObj))
        {
            return $this->getFieldLocalSingleValue($fieldName);
        }
        return $this->getFieldServerSingleValue($fieldName);
    }

    /**
     * Set the value of the specified field in the cached object or LDAPObject
     *
     * @param string $fieldName The name of the field to set
     * @param mixed $fieldValue The value to set in the field
     *
     * @return boolean true if the field is set and false otherwise
     */
    protected function setField($fieldName, $fieldValue)
    {
        if(!is_object($this->ldapObj))
        {
            return $this->setFieldLocal($fieldName, $fieldValue);
        }
        return $this->setFieldServer($fieldName, $fieldValue);
    }

    /**
     * Append a value of the specified field in the cached object or LDAPObject
     *
     * @param string $fieldName The name of the field to set
     * @param mixed $fieldValue The value to append to the field
     *
     * @return  boolean true if the field is set and false otherwise
     */
    protected function appendField($fieldName, $fieldValue)
    {
        if(!is_object($this->ldapObj))
        {
            return $this->appendFieldLocal($fieldName, $fieldValue);
        }
        return $this->appendFieldServer($fieldName, $fieldValue);
    }

    /**
     * Get the value of the field in the local cache
     *
     * @param string $fieldName The name of the field to retrieve
     *
     * @return mixed the value of the field
     */
    private function getFieldLocal($fieldName)
    {
        if($this->ldapObj === false)
        {
            return false;
        }
        if(!isset($this->ldapObj[$fieldName]))
        {
            return false;
        }
        return $this->ldapObj[$fieldName];
    }

    /**
     * Get the value of the field in the server object
     *
     * @param string $fieldName The name of the field to retrieve
     *
     * @return mixed the value of the field
     */
    private function getFieldServer($fieldName)
    {
        $lowerName = strtolower($fieldName);
        if(!isset($this->ldapObj->{$lowerName}))
        {
            return false;
        }
        return $this->ldapObj->{$lowerName};
    }

    /**
     * Get the value of the specified field from the local cache
     *
     * @param string $fieldName The name of the field to retrieve
     *
     * @return string the value of the field
     */
    private function getFieldLocalSingleValue($fieldName)
    {
        if($this->ldapObj === false)
        {
            return false;
        }
        if(!isset($this->ldapObj[$fieldName]))
        {
            return false;
        }
        if(is_array($this->ldapObj[$fieldName]))
        {
            return $this->ldapObj[$fieldName][0];
        }
        return $this->ldapObj[$fieldName];
    }

    /**
     * Get the value of the specified field from the server
     *
     * @param string $fieldName The name of the field to retrieve
     *
     * @return string the value of the field
     */
    private function getFieldServerSingleValue($fieldName)
    {
        $lowerName = strtolower($fieldName);
        if(!isset($this->ldapObj->{$lowerName}))
        {
            return false;
        }
        $field = $this->ldapObj->{$lowerName};
        if(!isset($field[0]))
        {
            return false;
        }
        return $field[0];
    }

    /**
     * Set the specified field in the server
     *
     * @param string $fieldName The name of the field to set
     * @param mixed $fieldValue The value to write to the field
     *
     * @return boolean true if the field is set and false otherwise
     */
    private function setFieldServer($fieldName, $fieldValue)
    {
        $obj = array('dn'=>$this->ldapObj->dn);
        if($fieldValue !== null && strlen($fieldValue) > 0)
        {
            $obj[$fieldName] = $fieldValue;
        }
        else
        {
            $obj[$fieldName] = null;
        }
        $lowerName = strtolower($fieldName);
        $this->ldapObj->{$lowerName} = array($fieldValue);
        return $this->update($obj);
    }

    /**
     * Append a value of the specified field in the server
     *
     * @param string $fieldName The name of the field to set
     * @param mixed $fieldValue The value to append to the field
     *
     * @return  boolean true if the field is set and false otherwise
     */
    private function appendFieldServer($fieldName, $fieldValue)
    {
        $obj = array('dn'=>$this->ldapObj->dn);
        if(isset($this->ldapObj->{$fieldName}))
        {
            $obj[$fieldName] = $this->ldapObj->{$fieldName};
            $obj[$fieldName][$obj[$fieldName]['count']] = $fieldValue;
            $obj[$fieldName]['count']++;
        }
        else
        {
            $obj[$fieldName] = $fieldValue;
        }
        return $this->update($obj);
    }

    /**
     * Set the specified field in the local cache
     *
     * @param string $fieldName The name of the field to set
     * @param mixed $fieldValue The value to write to the field
     *
     * @return boolean true if the field is set and false otherwise
     */
    private function setFieldLocal($fieldName, $fieldValue)
    {
        if($this->ldapObj === false)
        {
            $this->ldapObj = array();
        }
        if($fieldValue === null || (is_string($fieldValue) && strlen($fieldValue) === 0))
        {
            if(isset($this->ldapObj[$fieldName]))
            {
                unset($this->ldapObj[$fieldName]);
            }
            return true;
        }
        $this->ldapObj[$fieldName] = $fieldValue;
        return true;
    }

    /**
     * Append a value of the specified field in the local cache
     *
     * @param string $fieldName The name of the field to set
     * @param mixed $fieldValue The value to append to the field
     *
     * @return  boolean true if the field is set and false otherwise
     */
    private function appendFieldLocal($fieldName, $fieldValue)
    {
        if($this->ldapObj === false)
        {
            $this->ldapObj = array();
        }
        if(!isset($this->ldapObj[$fieldName]))
        {
            $this->ldapObj[$fieldName] = array();
        }
        $this->ldapObj[$fieldName][] = $fieldValue;
        return true;
    }
}

/* vim: set tabstop=4 shiftwidth=4 expandtab: */

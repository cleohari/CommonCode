<?php
namespace Auth;

trait LDAPCachableObject 
{
    protected function update($obj)
    {
        try
        {
            return $this->server->update($obj);
        }
        catch(\Exception $ex)
        {
            $auth = \AuthProvider::getInstance();
            $ldap = $auth->getAuthenticator('Auth\LDAPAuthenticator');
            if($ldap === false) return false;
            $this->server = $ldap->get_and_bind_server(true);
            return $this->server->update($obj);
        }
    }

    protected function getField($fieldName)
    {
        if(!is_object($this->ldapObj))
        {
            return $this->getFieldLocal($fieldName);
        }
        return $this->getFieldServer($fieldName);
    }

    protected function getFieldSingleValue($fieldName)
    {
        if(!is_object($this->ldapObj))
        {
            return $this->getFieldLocalSingleValue($fieldName);
        }
        return $this->getFieldServerSingleValue($fieldName);
    }

    protected function setField($fieldName, $fieldValue)
    {
        if(!is_object($this->ldapObj))
        {
            return $this->setFieldLocal($fieldName, $fieldValue);
        }
        return $this->setFieldServer($fieldName, $fieldValue);
    }

    protected function appendField($fieldName, $fieldValue)
    {
        if(!is_object($this->ldapObj))
        {
            return $this->appendFieldLocal($fieldName, $fieldValue);
        }
        return $this->appendFieldServer($fieldName, $fieldValue);
    }

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

    private function getFieldServer($fieldName)
    {
        $lowerName = strtolower($fieldName);
        if(!isset($this->ldapObj->{$lowerName}))
        {
            return false;
        }
        return $this->ldapObj->{$lowerName};
    }

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

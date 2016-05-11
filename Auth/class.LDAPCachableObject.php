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
            $this->server = $ldap->get_and_bind_server(true);
            return $this->server->update($obj);
        }
    }

    protected function getField($fieldName)
    {
        if(!is_object($this->ldap_obj))
        {
            return $this->getFieldLocal($fieldName);
        }
        return $this->getFieldServer($fieldName);
    }

    protected function getFieldSingleValue($fieldName)
    {
        if(!is_object($this->ldap_obj))
        {
            return $this->getFieldLocalSingleValue($fieldName);
        }
        return $this->getFieldServerSingleValue($fieldName);
    }

    protected function setField($fieldName, $fieldValue)
    {
        if(!is_object($this->ldap_obj))
        {
            return $this->setFieldLocal($fieldName, $fieldValue);
        }
        return $this->setFieldServer($fieldName, $fieldValue);
    }

    protected function appendField($fieldName, $fieldValue)
    {
        if(!is_object($this->ldap_obj))
        {
            return $this->appendFieldLocal($fieldName, $fieldValue);
        }
        return $this->appendFieldServer($fieldName, $fieldValue);
    }

    private function getFieldLocal($fieldName)
    {
        if($this->ldap_obj === false)
        {
            return false;
        }
        if(!isset($this->ldap_obj[$fieldName]))
        {
            return false;
        }
        return $this->ldap_obj[$fieldName];
    }

    private function getFieldServer($fieldName)
    {
        $lowerName = strtolower($fieldName);
        if(!isset($this->ldap_obj->{$lowerName}))
        {
            return false;
        }
        return $this->ldap_obj->{$lowerName};
    }

    private function getFieldLocalSingleValue($fieldName)
    {
        if($this->ldap_obj === false)
        {
            return false;
        }
        if(!isset($this->ldap_obj[$fieldName]))
        {
            return false;
        }
        if(is_array($this->ldap_obj[$fieldName]))
        {
            return $this->ldap_obj[$fieldName][0];
        }
        return $this->ldap_obj[$fieldName];
    }

    private function getFieldServerSingleValue($fieldName)
    {
        $lowerName = strtolower($fieldName);
        if(!isset($this->ldap_obj->{$lowerName}))
        {
            return false;
        }
        $field = $this->ldap_obj->{$lowerName};
        if(!isset($field[0]))
        {
            return false;
        }
        return $field[0];
    }

    private function setFieldServer($fieldName, $fieldValue)
    {
        $obj = array('dn'=>$this->ldap_obj->dn);
        if($fieldValue !== null && strlen($fieldValue) > 0)
        {
            $obj[$fieldName] = $fieldValue;
        }
        else
        {
            $obj[$fieldName] = null;
        }
        $lowerName = strtolower($fieldName);
        $this->ldap_obj->{$lowerName} = array($fieldValue);
        return $this->update($obj);
    }

    private function appendFieldServer($fieldName, $fieldValue)
    {
        $obj = array('dn'=>$this->ldap_obj->dn);
        if(isset($this->ldap_obj->{$fieldName}))
        {
            $obj[$fieldName] = $this->ldap_obj->{$fieldName};
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
        if($this->ldap_obj === false)
        {
            $this->ldap_obj = array();
        }
        if($fieldValue === null || strlen($fieldValue) === 0)
        {
            if(isset($this->ldap_obj[$fieldName]))
            {
                unset($this->ldap_obj[$fieldName]);
            }
            return true;
        }
        $this->ldap_obj[$fieldName] = $fieldValue;
        return true;
    }

    private function appendFieldLocal($fieldName, $fieldValue)
    {
        if($this->ldap_obj === false)
        {
            $this->ldap_obj = array();
        }
        if(!isset($this->ldap_obj[$fieldName]))
        {
            $this->ldap_obj[$fieldName] = array();
        }
        $this->ldap_obj[$fieldName][] = $fieldValue;
        return true;
    }
}

/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

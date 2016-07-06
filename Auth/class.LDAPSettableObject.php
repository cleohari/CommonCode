<?php
namespace Auth;

trait LDAPSettableObject
{
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
}

/* vim: set tabstop=4 shiftwidth=4 expandtab: */

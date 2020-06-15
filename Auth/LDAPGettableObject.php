<?php
namespace Flipside\Auth;

trait LDAPGettableObject
{
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
        if(isset($this->labeleduri))
        {
             //Have multiple emails
             if($propName === 'mail')
             {
                 return $this->getFieldSingleValue('labeleduri');
             }
             if($propName === 'allMail')
             {
                 $tmp = $this->getField('mail');
                 if(isset($tmp['count']))
                 {
                     unset($tmp['count']);
                 }
                 return $tmp;
             }
        }
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

    public function __isset($propName)
    {
        if(isset($this->valueDefaults[$propName]))
        {
            return true;
        }
        if(in_array($propName, $this->multiValueProps))
        {
            return true;
        }
        if(!is_object($this->ldapObj))
        {
            if(isset($this->ldapObj['labeleduri']))
            {
                if($propName === 'allMail')
                {
                    return true;
                }
            }
            return isset($this->ldapObj[$propName]);
        }
        if(is_object($this->ldapObj))
        {
            $lowerName = strtolower($propName);
            if(isset($this->ldapObj->labeleduri))
            {
                if($propName === 'allMail')
                {
                    return true;
                }
            }
            return isset($this->ldapObj->{$lowerName});
        }
        return false;
    }
}

/* vim: set tabstop=4 shiftwidth=4 expandtab: */

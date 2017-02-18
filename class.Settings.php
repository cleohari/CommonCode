<?php

class Settings extends \Singleton
{
    protected function __construct()
    {
        if(isset($GLOBALS['FLIPSIDE_SETTINGS_LOC']))
        {
            require $GLOBALS['FLIPSIDE_SETTINGS_LOC'].'/class.FlipsideSettings.php';
            return;
        }
        if(is_readable('/var/www/secure_settings/class.FlipsideSettings.php'))
        {
            require '/var/www/secure_settings/class.FlipsideSettings.php';
            return;
        }
    }

    public function getClassesByPropName($propName)
    {
        $ret = array();
        if(isset(FlipsideSettings::$$propName))
        {
            $prop = FlipsideSettings::$$propName;
            $keys = array_keys($prop);
            $count = count($keys);
            for($i = 0; $i < $count; $i++)
            {
                $class = $keys[$i];
                array_push($ret, new $class($prop[$keys[$i]]));
            }
        }
        return $ret;
    }

    public function getDataSetData($dataSetName)
    {
        if(!isset(FlipsideSettings::$dataset) || !isset(FlipsideSettings::$dataset[$dataSetName]))
        {
            return false;
        }
        return FlipsideSettings::$dataset[$dataSetName];
    }

    public function getGlobalSetting($propName, $default = false)
    {
        if(isset(FlipsideSettings::$global) && isset(FlipsideSettings::$global[$propName]))
        {
            return FlipsideSettings::$global[$propName];
        }
        return $default;
    }

    public function getSiteLinks()
    {
        if(isset(FlipsideSettings::$sites))
        {
            return FlipsideSettings::$sites;
        }
        return array();
    }

    private function getLDAPHost($default)
    {
        if(!isset(FlipsideSettings::$ldap) || !isset(FlipsideSettings::$ldap['host']))
        {
            return $default;
        }
        if(isset(\FlipsideSettings::$ldap['proto']))
        {
            return \FlipsideSettings::$ldap['proto'].'://'.\FlipsideSettings::$ldap['host'];
        }
        return \FlipsideSettings::$ldap['host'];
    }

    public function getLDAPSetting(string $propName, $ldapAuth = false, $default = false)
    {
        switch($propName)
        {
            case 'host':
                return $this->getLDAPHost($default);
            default:
                if($ldapAuth === false)
                {
                    return FlipsideSettings::$ldap[$propName];
                }
                return FlipsideSettings::$ldap_auth[$propName];
        }
    }
}

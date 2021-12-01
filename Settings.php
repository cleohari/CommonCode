<?php
namespace Flipside;
/**
 * Settings class
 *
 * This file describes the Settings Singleton
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2017, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

/**
 * A Singleton class to abstract access to various settings
 *
 * This class is the primary method to access sensative information such as DB logins
 */
class Settings extends \Flipside\Singleton
{
    /**
     * Load the settings file
     *
     * @see \Singleton::getInstance()
     */ 
    protected function __construct()
    {
        if(isset($GLOBALS['FLIPSIDE_SETTINGS_LOC']))
        {
            require $GLOBALS['FLIPSIDE_SETTINGS_LOC'].'/class.FlipsideSettings.php';
            return;
	}
        if(file_exists('/var/www/secure_settings/class.FlipsideSettings.php'))
        {
            require '/var/www/secure_settings/class.FlipsideSettings.php';
            return;
        }
    }

    /**
     * Instantiate all classes pointed to by the specified property name and return
     *
     * @param string $propName The property name in the settings file
     *
     * @return array An array of classes or empty array if not set
     */
    public function getClassesByPropName($propName)
    {
        $ret = array();
        if(isset(\FlipsideSettings::$$propName))
        {
            $prop = \FlipsideSettings::$$propName;
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

    /**
     * Get the \Data\DataSet parameters by name
     *
     * @param string $dataSetName The name of the dataset
     *
     * @return array|false An array of properties or false if no such dataset
     */
    public function getDataSetData($dataSetName)
    {
        if(!isset(\FlipsideSettings::$dataset) || !isset(\FlipsideSettings::$dataset[$dataSetName]))
	{
            return false;
        }
        return \FlipsideSettings::$dataset[$dataSetName];
    }

    /**
     * Get the property from the settings file
     *
     * @param string $propName The name of the property in the settings file
     * @param mixed $default The default value to return if not set
     *
     * @return mixed The value from the settings file or the value of $default it not set
     */
    public function getGlobalSetting($propName, $default = false)
    {
        if(isset(\FlipsideSettings::$global) && isset(\FlipsideSettings::$global[$propName]))
        {
            return \FlipsideSettings::$global[$propName];
        }
        return $default;
    }

    /**
     * Get the site links for the left hand side of the header
     *
     * @return array The array of links or an empty array if not set
     */
    public function getSiteLinks()
    {
        if(isset(\FlipsideSettings::$sites))
        {
            return \FlipsideSettings::$sites;
        }
        return array();
    }

    /**
     * Get the ldap connection string specified in the settings file
     *
     * @param mixed $default The default value to return if not set
     *
     * @return mixed The LDAP connection string or $default if not set
     */
    private function getLDAPHost($default)
    {
        if(!isset(\FlipsideSettings::$ldap) || !isset(\FlipsideSettings::$ldap['host']))
        {
            return $default;
        }
        if(isset(\FlipsideSettings::$ldap['proto']))
        {
            return \FlipsideSettings::$ldap['proto'].'://'.\FlipsideSettings::$ldap['host'];
        }
        return \FlipsideSettings::$ldap['host'];
    }

    /**
     * Get the specified ldap setting from the settings file
     *
     * @param string $propName The property name to retrieve
     * @param boolean $ldapAuth Is the value authentication or other
     * @param mixed $default The default value to return if not set
     *
     * @return mixed The value from the settings file
     */
    public function getLDAPSetting($propName, $ldapAuth = false, $default = false)
    {
        switch($propName)
        {
            case 'host':
                return $this->getLDAPHost($default);
            default:
                if($ldapAuth === false)
                {
                    if(isset(\FlipsideSettings::$ldap) && isset(\FlipsideSettings::$ldap[$propName]))
                    {
                        return \FlipsideSettings::$ldap[$propName];
                    }
                    return $default;
                }
                if(isset(\FlipsideSettings::$ldap_auth) && isset(\FlipsideSettings::$ldap_auth[$propName]))
                {
                    return \FlipsideSettings::$ldap_auth[$propName];
                }
                return $default;
        }
    }
}

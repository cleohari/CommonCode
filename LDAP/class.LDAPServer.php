<?php
namespace LDAP;

/**
 * function ldap_escape
 * @author Chris Wright
 * @version 2.0
 * @param string $subject The subject string
 * @param bool $dn Treat subject as a DN if TRUE
 * @param string|array $ignore Set of characters to leave untouched
 * @return string The escaped string
 */
function ldap_escape($subject, $distinguishedName = false, $ignore = NULL)
{
    // The base array of characters to escape
    // Flip to keys for easy use of unset()
    $search = array_flip($distinguishedName ? array('\\', '+', '<', '>', ';', '"', '#') : array('\\', '*', '(', ')', "\x00"));

    // Process characters to ignore
    if(is_array($ignore))
    {
        $ignore = array_values($ignore);
    }
    for($char = 0; isset($ignore[$char]); $char++)
    {
        unset($search[$ignore[$char]]);
    }

    // Flip $search back to values and build $replace array
    $search = array_keys($search);
    $replace = array();
    foreach($search as $char)
    {
        $replace[] = sprintf('\\%02x', ord($char));
    }

    // Do the main replacement
    $result = str_replace($search, $replace, $subject);

    // Encode leading/trailing spaces in DN values
    if($distinguishedName)
    {
        $result = cleanupDN($result);
    }

    return $result;
}

function cleanupDN($distinguishedName)
{
    if($distinguishedName[0] == ' ')
    {
        $distinguishedName = '\\20'.substr($distinguishedName, 1);
    }
    if($distinguishedName[strlen($distinguishedName) - 1] == ' ')
    {
        $distinguishedName = substr($distinguishedName, 0, -1).'\\20';
    }
    return $distinguishedName;
}

class LDAPServer extends \Singleton
{
    protected $ldapLink;
    protected $connect;
    protected $binder;
    public $user_base;
    public $group_base;

    protected function __construct()
    {
        $this->ldapLink = null;
        $this->binder = null;
    }

    public function __destruct()
    {
    }

    public function __wakeup()
    {
        $this->ldapLink = ldap_connect($this->connect);
        ldap_set_option($this->ldapLink, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldapLink, LDAP_OPT_REFERRALS, false);
    }

    private function getConnectString($name, $proto = false)
    {
        if(strstr($name, ':') !== false)
        {
            return $name;
        }
        if($proto !== 'ldap')
        {
            return $proto.'://'.$name;
        }
        return $name;
    }

    public function connect($name, $proto = false)
    {
        $connectStr = $this->getConnectString($name, $proto);
        if($this->ldapLink !== null)
        {
            ldap_close($this->ldapLink);
        }
        $this->connect = $connectStr;
        $this->ldapLink = ldap_connect($this->connect);
        if($this->ldapLink === false)
        {
            $this->ldapLink = null;
            return false;
        }
        ldap_set_option($this->ldapLink, LDAP_OPT_PROTOCOL_VERSION, 3);
        return true;
    }

    public function disconnect()
    {
        if($this->ldapLink !== null)
        {
            ldap_close($this->ldapLink);
            $this->ldapLink = null;
        }
        $this->connect = false;
    }

    /**
     * Bind (login0 to the LDAP Server
     *
     * @param string $commonName The common name to bind with, null to bind anonymously
     * @param string $password The password to bind with, null to bind anonymously
     */
    public function bind($commonName = null, $password = null)
    {
        $res = false;
        if($this->ldapLink === null)
        {
            throw new \Exception('Not connected');
        }
        $this->binder = $commonName;
        if($commonName === null || $password === null)
        {
            return @ldap_bind($this->ldapLink);
        }
        try
        {
            $this->ldapLink = ldap_connect($this->connect);
            ldap_set_option($this->ldapLink, LDAP_OPT_PROTOCOL_VERSION, 3);
            $res = ldap_bind($this->ldapLink, $commonName, $password);
        }
        catch(\Exception $ex)
        {
            $this->ldapLink = ldap_connect($this->connect);
            ldap_set_option($this->ldapLink, LDAP_OPT_PROTOCOL_VERSION, 3);
            $res = @ldap_bind($this->ldapLink, $commonName, $password);
        }
        return $res;
    }

    public function unbind()
    {
        if($this->ldapLink === null)
        {
            return true;
        }
        return @ldap_unbind($this->ldapLink);
    }

    private function fixChildArray(&$array, $key, &$entity)
    {
        $count = count($array);
        for($i = 0; $i < $count; $i++)
        {
            if(isset($array[$i]))
            {
                $entity[$key][$i] = $array[$i];
            }
        }
    }

    private function fixObject($object, &$delete = false)
    {
        $entity = $object;
        if(!is_array($object))
        {
            $entity = $object->to_array();
        }
        unset($entity['dn']);
        $keys = array_keys($entity);
        $count = count($keys);
        for($i = 0; $i < $count; $i++)
        {
            if(is_array($entity[$keys[$i]]))
            {
                $this->fixChildArray($entity[$keys[$i]], $keys[$i], $entity);
                //unset($entity[$keys[$i]]);
            }
            else if($delete !== false && $entity[$keys[$i]] === null)
            {
                $delete[$keys[$i]] = array();
                unset($entity[$keys[$i]]);
            }
        }
        return $entity;
    }

    public function create($object)
    {
        $distinguishedName = ldap_escape($object['dn'], true);
        $entity = $this->fixObject($object);
        $ret = ldap_add($this->ldapLink, $distinguishedName, $entity);
        if($ret === false)
        {
            throw new \Exception('Failed to create object with dn='.$distinguishedName);
        }
        return $ret;
    }

    /**
     * Get the LDAP filter represented by the passed object
     *
     * @param boolean|string|\Data\Filter $filter The fiter to use
     *
     * @return string The filter in LDAP format
     */
    private function filterToString($filter)
    {
        if($filter === false)
        {
            return '(objectclass=*)';
        }
        if(is_string($filter))
        {
            return $filter;
        }
        return $filter->to_ldap_string();
    }

    private function searchResultToArray($searchResult)
    {
        if($searchResult === false)
        {
            return false;
        }
        $res = ldap_get_entries($this->ldapLink, $searchResult);
        if(is_array($res))
        {
            $ldap = $res;
            $res = array();
            for($i = 0; $i < $ldap['count']; $i++)
            {
                array_push($res, new LDAPObject($ldap[$i], $this));
            }
        }
        return $res;
    }

    /**
     * Get data from the LDAP Server
     *
     * @param string $baseDN The distinguished name to start the search from
     * @param boolean|string|\Data\Filter $filter The fiter to use
     * @param boolean $single Read only the base DN
     * @param boolean|array $attributes The list of attributes to read
     *
     * @return boolean|array The results from the LDAP Server
     */
    public function read($baseDN, $filter = false, $single = false, $attributes = false)
    {
        $filterStr = $this->filterToString($filter);
        if($this->ldapLink === null)
        {
            throw new \Exception('Not connected');
        }
        try
        {
            if($single === true)
            {
                $searchResult = @ldap_read($this->ldapLink, $baseDN, $filterStr);
                return $this->searchResultToArray($searchResult);
            }
            if($attributes !== false)
            {
                $searchResult = @ldap_list($this->ldapLink, $baseDN, $filterStr, $attributes);
                return $this->searchResultToArray($searchResult);
            }
            $searchResult = @ldap_list($this->ldapLink, $baseDN, $filterStr);
            return $this->searchResultToArray($searchResult);
        }
        catch(\Exception $e)
        {
            throw new \Exception($e->getMessage().' '.$filterStr, $e->getCode(), $e);
        }
    }

    public function count($baseDN, $filter = false)
    {
        $filterStr = $this->filterToString($filter);
        if($this->ldapLink === null)
        {
            throw new \Exception('Not connected');
        }
        try
        {
            $searchResult = ldap_list($this->ldapLink, $baseDN, $filterStr, array('dn'));
        }
        catch(\Exception $e)
        {
            throw new \Exception($e->getMessage().' '.$filterStr, $e->getCode(), $e);
        }
        if($searchResult === false)
        {
            return 0;
        }
        return ldap_count_entries($this->ldapLink, $searchResult);
    }

    public function update($object)
    {
        $distinguishedName = ldap_escape($object['dn'], true);
        $delete = array();
        $entity = $this->fixObject($object, $delete);
        $ret = false;
        if(!empty($entity))
        {
            $ret = @ldap_mod_replace($this->ldapLink, $distinguishedName, $entity);
            if($ret === false)
            {
                $string = 'Failed to update object with dn='.$distinguishedName.' ('.ldap_errno($this->ldapLink).':'.ldap_error($this->ldapLink).') '.print_r($entity, true);
                throw new \Exception($string);
            }
        }
        if(!empty($delete))
        {
            $ret = @ldap_mod_del($this->ldapLink, $distinguishedName, $delete);
        }
        return $ret;
    }

    public function delete($distinguishedName)
    {
        return ldap_delete($this->ldapLink, $distinguishedName);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

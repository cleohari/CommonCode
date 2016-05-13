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
function ldap_escape($subject, $dn = FALSE, $ignore = NULL)
{
    // The base array of characters to escape
    // Flip to keys for easy use of unset()
    $search = array_flip($dn ? array('\\', '+', '<', '>', ';', '"', '#') : array('\\', '*', '(', ')', "\x00"));

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
    if($dn)
    {
        if($result[0] == ' ')
        {
            $result = '\\20'.substr($result, 1);
        }
        if($result[strlen($result) - 1] == ' ')
        {
            $result = substr($result, 0, -1).'\\20';
        }
    }

    return $result;
}

class LDAPServer extends \Singleton
{
    protected $ds;
    protected $connect;
    protected $binder;
    public $user_base;
    public $group_base;

    protected function __construct()
    {
        $this->ds = null;
        $this->binder = null;
    }

    public function __destruct()
    {
    }

    public function __wakeup()
    {
        $this->ds = ldap_connect($this->connect);
    }

    private function getConnectString($name, $proto=false)
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

    function connect($name, $proto=false)
    {
        $connectStr = $this->getConnectString($name, $proto);
        if($this->ds !== null)
        {
            ldap_close($this->ds);
        }
        $this->connect = $connectStr;
        $this->ds      = ldap_connect($this->connect);
        if($this->ds === false)
        {
            $this->ds = null;
            return false;
        }
        ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        return true;
    }

    function disconnect()
    {
        if($this->ds !== null)
        {
            ldap_close($this->ds);
            $this->ds = null;
        }
        $this->connect = false;
    }

    function bind($cn=null, $password=null)
    {
        $res = false;
        if($this->ds === null)
        {
            throw new \Exception('Not connected');
        }
        $this->binder = $cn;
        if($cn === null || $password === null)
        {
            return @ldap_bind($this->ds);
        }
        try
        {
            $res = ldap_bind($this->ds, $cn, $password);
        }
        catch(\Exception $ex)
        {
            $this->ds = ldap_connect($this->connect);
            $res = @ldap_bind($this->ds, $cn, $password);
        }
        return $res;
    }

    function unbind()
    {
        if($this->ds === null)
        {
            return true;
        }
        return @ldap_unbind($this->ds);
    }

    function get_error()
    {
        return ldap_error($this->ds);
    }

    private function _fix_object($object, &$delete = false)
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
                $array = $entity[$keys[$i]];
                unset($entity[$keys[$i]]);
                $count1 = count($array);
                for($j = 0; $j < $count1; $j++)
                {
                    if(isset($array[$j]))
                    {
                        $entity[$keys[$i]][$j] = $array[$j];
                    }
                }
            }
            else if($delete !== false && $entity[$keys[$i]] === null)
            {
                $delete[$keys[$i]] = array();
                unset($entity[$keys[$i]]);
            }
        }
        return $entity;
    }

    function create($object)
    {
        $dn = ldap_escape($object['dn'], true);
        $entity = $this->_fix_object($object);
        $ret = ldap_add($this->ds, $dn, $entity);
        if($ret === false)
        {
            throw new \Exception('Failed to create object with dn='.$dn);
        }
        return $ret;
    }

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

    function read($baseDN, $filter=false, $single=false, $attributes=false)
    {
        $filterStr = $this->filterToString($filter);
        if($this->ds === null)
        {
            throw new \Exception('Not connected');
        }
        $sr = false;
        try
        {
            if($single === true)
            {
                $sr = @ldap_read($this->ds, $baseDN, $filterStr);
            }
            else
            {
                if($attributes !== false)
                {
                    $sr = @ldap_list($this->ds, $baseDN, $filterStr, $attributes);
                }
                else
                {
                    $sr = @ldap_list($this->ds, $baseDN, $filterStr);
                }
            }
        }
        catch(\Exception $e)
        {
            throw new \Exception($e->getMessage().' '.$filterStr, $e->getCode(), $e);
        }
        if($sr === false)
        {
            return false;
        }
        $res = ldap_get_entries($this->ds, $sr);
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

    function count($baseDN, $filter=false)
    {
        $filterStr = $this->filterToString($filter);
        if($this->ds === null)
        {
            throw new \Exception('Not connected');
        }
        try
        {
            $sr = ldap_list($this->ds, $baseDN, $filterStr, array('dn'));
        }
        catch(\Exception $e)
        {
            throw new \Exception($e->getMessage().' '.$filterStr, $e->getCode(), $e);
        }
        if($sr === false)
        {
            return false;
        }
        return ldap_count_entries($this->ds, $sr);
    }

    function update($object)
    {
        $dn = ldap_escape($object['dn'], true);
        $delete = array();
        $entity = $this->_fix_object($object, $delete);
        $ret = false;
        if(!empty($entity))
        {
            $ret = @ldap_mod_replace($this->ds, $dn, $entity);
            if($ret === false)
            {
                throw new \Exception('Failed to update object with dn='.$dn.'('.ldap_errno($this->ds).':'.ldap_error($this->ds).') '.print_r($entity, true));
            }
        }
        if(!empty($delete))
        {
            $ret = @ldap_mod_del($this->ds, $dn, $delete);
        }
        return $ret;
    }

    function delete($dn)
    {
        return ldap_delete($this->ds, $dn);
    }
}

?>

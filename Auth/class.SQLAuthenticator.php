<?php
namespace Auth;

if(!function_exists('password_hash') || !function_exists('password_verify')) 
{
    define('PASSWORD_BCRYPT', 1);
    define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);
    define('PASSWORD_BCRYPT_DEFAULT_COST', 10);

    function password_hash($password, $algo = PASSWORD_DEFAULT)
    {
        if(is_null($password) || is_int($password))
        {
            $password = (string)$password;
        }
        if(!is_string($password))
        {
            trigger_error("password_hash(): Password must be a string", E_USER_WARNING);
            return false;
        }
        if(!is_int($algo))
        {
            trigger_error("password_hash() expects parameter 2 to be long, " . gettype($algo) . " given", E_USER_WARNING);
            return false;
        }
        $resultLength = 0;
        switch($algo)
        {
            case PASSWORD_BCRYPT:
                $cost = PASSWORD_BCRYPT_DEFAULT_COST;
                $raw_salt_len = 16;
                $required_salt_len = 22;
                $hash_format = sprintf("$2y$%02d$", $cost);
                $resultLength = 60;
                break;
            default:
                trigger_error(sprintf("password_hash(): Unknown password hashing algorithm: %s", $algo), E_USER_WARNING);
                return false;
        }
        $salt = openssl_random_pseudo_bytes($raw_salt_len);
        $base64_digits = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
        $bcrypt64_digits = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $base64_string = base64_encode($salt);
        $salt = strtr(rtrim($base64_string, '='), $base64_digits, $bcrypt64_digits);
        $salt = substr($salt, 0, $required_salt_len);
        $hash = $hash_format . $salt;
        $ret = crypt($password, $hash);
        if(!is_string($ret) || strlen($ret) != $resultLength)
        {
            return false;
        }
        return $ret;
    }

    function password_verify($password, $hash)
    {
        $ret = crypt($password, $hash);
        if(!is_string($ret) || strlen($ret) != strlen($hash) || strlen($ret) <= 13)
        {
            return false;
        }
        $status = 0;
        $count  = strlen($ret);
        for($i = 0; $i < $count; $i++)
        {
            $status |= (ord($ret[$i]) ^ ord($hash[$i]));
        }
        return $status === 0;
    }
}

class SQLAuthenticator extends Authenticator
{
    private $data_set = null;
    private $pending_data_set = null;
    private $data_tables = array();
    private $pending_data_tables = array();
    private $params;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->params = $params;
        if($this->current)
        {
            $this->data_set = $this->getCurrentDataSet();
        }
        if($this->pending)
        {
            $this->data_set = $this->getPendingDataSet();
        }
    }

    /**
     * @SuppressWarnings(CleanCode.StaticAccess)
     */
    private function getCurrentDataSet()
    {
        if(isset($this->params['current_data_set']))
        {
            return \DataSetFactory::get_data_set($this->params['current_data_set']);
        }
        return \DataSetFactory::get_data_set('authentication');
    }

    /**
     * @SuppressWarnings(CleanCode.StaticAccess)
     */
    private function getPendingDataSet()
    {
        if(isset($this->params['pending_data_set']))
        {
            return \DataSetFactory::get_data_set($this->params['pending_data_set']);
        }
        return \DataSetFactory::get_data_set('pending_authentication');
    }

    private function get_data_table($name)
    {
         if(isset($this->data_tables[$name]))
         {
             return $this->data_tables[$name];
         }
         $data_set = $this->data_set;
         if($data_set === null)
         {
             throw new \Exception('Unable to obtain dataset for SQL Authentication!');
         }
         $data_table = $data_set[$name];
         $this->data_tables[$name] = $data_table;
         return $this->data_tables[$name];
    }

    private function get_pending_data_table($name)
    {
         if(isset($this->pending_data_tables[$name]))
         {
             return $this->pending_data_tables[$name];
         }
         $data_set = $this->pending_data_set;
         if($data_set === null)
         {
             throw new \Exception('Unable to obtain dataset for SQL Authentication!');
         }
         $data_table = $data_set[$name];
         $this->pending_data_tables[$name] = $data_table;
         return $this->pending_data_tables[$name];
    }

    private function get_pending_user_data_table()
    {
        if(isset($this->params['pending_user_table']))
        {
            return $this->get_pending_data_table($this->params['pending_user_table']);
        }
        else
        {
            return $this->get_pending_data_table('users');
        }
    }

    public function login($username, $password)
    {
        if($this->current === false) return false;
        $user_data_table = $this->get_data_table('user');
        $filter = new \Data\Filter("uid eq '$username'");
        $users = $user_data_table->read($filter, 'uid,pass');
        if($users === false || !isset($users[0]))
        {
            return false;
        }
        if(password_verify($password, $users[0]['pass']))
        {
            return array('res'=>true, 'extended'=>$users[0]['uid']);
        }
        return false;
    }

    public function isLoggedIn($data)
    {
        if(isset($data['res']))
        {
            return $data['res'];
        }
        return false;
    }

    public function getUser($data)
    {
        return new SQLUser($data);
    }

    public function getGroupByName($name)
    {
        $group_data_table = $this->get_data_table('group');
        $filter = new \Data\Filter("gid eq '$name'");
        $groups = $group_data_table->read($filter);
        if($groups === false || !isset($groups[0]))
        {
            return false;
        }
        return new SQLGroup($groups[0]);
    }

    public function getUserByName($name)
    {
        $user_data_table = $this->get_data_table('user');
        $filter = new \Data\Filter("uid eq '$name'");
        $users = $user_data_table->read($filter);
        if($users === false || !isset($users[0]))
        {
            return false;
        }
        return new SQLUser($users[0]);
    }

    public function getGroupsByFilter($filter, $select=false, $top=false, $skip=false, $orderby=false)
    {
        $group_data_table = $this->get_data_table('group');
        $groups = $group_data_table->read($filter, $select, $top, $skip, $orderby);
        if($groups === false)
        {
            return false;
        }
        $count = count($groups);
        for($i = 0; $i < $count; $i++)
        {
            $groups[$i] = new SQLGroup($groups[$i]);
        }
        return $groups;
    }

    public function getUsersByFilter($filter, $select=false, $top=false, $skip=false, $orderby=false)
    {
        $user_data_table = $this->get_data_table('user');
        $users = $user_data_table->read($filter, $select, $top, $skip, $orderby);
        if($users === false)
        {
            return false;
        }
        $count = count($users);
        for($i = 0; $i < $count; $i++)
        {
            $users[$i] = new SQLUser($users[$i]);
        }
        return $users;
    }

    public function getPendingUserCount()
    {
        if($this->pending === false) return 0;
        $data_table = $this->get_pending_user_data_table();
        if($data_table === null) return 0;
        return $data_table->count();
    }

    private function search_pending_users($filter, $select, $top, $skip, $orderby)
    {
        $user_data_table = $this->get_pending_user_data_table();
        $field_data = $filter->to_mongo_filter();
        $first_filter = new \Data\Filter('substringof(data,"'.implode($field_data,' ').'")');
        $users = $user_data_table->read($first_filter, $select, $top, $skip, $orderby);
        if($users === false)
        {
            return false;
        }
        $ret = array();
        $count = count($users);
        for($i = 0; $i < $count; $i++)
        {
            $user = new SQLPendingUser($users[$i], $user_data_table);
            $err = false;
            foreach($field_data as $field=>$data)
            {
                if(strcasecmp($user[$field], $data) !== 0)
                {
                    $err = true; break;
                }
            }
            if(!$err)
            {
                array_push($ret, $user);
            }
        }
        return $ret;
    }

    public function getPendingUsersByFilter($filter, $select=false, $top=false, $skip=false, $orderby=false)

    {
        if($this->pending === false) return false;
        if($filter !== false && !$filter->contains('hash'))
        {
            return $this->search_pending_users($filter, $select, $top, $skip, $orderby);
        }
        $user_data_table = $this->get_pending_user_data_table();
        $users = $user_data_table->read($filter, $select, $top, $skip, $orderby);
        if($users === false)
        {
            return false;
        }
        $count = count($users);
        for($i = 0; $i < $count; $i++)
        {
            $users[$i] = new SQLPendingUser($users[$i], $user_data_table);
        }
        return $users;
    }

    public function createPendingUser($user)
    {
        if($this->pending === false) return false;
        $user_data_table = $this->get_pending_user_data_table();
        if(isset($user->password2))
        {
            unset($user->password2);
        }
        $json = json_encode($user);
        $hash = hash('sha512', $json);
        $array = array('hash'=>$hash, 'data'=>$json);
        $ret = $user_data_table->create($array);
        if($ret !== false)
        {
            $users = $this->getPendingUsersByFilter(new \Data\Filter("hash eq '$hash'"));
            if($users === false || !isset($users[0]))
            {
                throw new \Exception('Error retreiving user object after successful create!');
            }
            $users[0]->sendEmail();
        }
        return $ret;
    }

    public function getTempUserByHash($hash)
    {
        $users = $this->getPendingUsersByFilter(new \Data\Filter("hash eq '$hash'"));
        if($users === false || !isset($users[0]))
        {
            return false;
        }
        return $users[0];
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

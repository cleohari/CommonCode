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
                $rawSaltLen = 16;
                $requiredSaltLen = 22;
                $hashFormat = sprintf("$2y$%02d$", $cost);
                $resultLength = 60;
                break;
            default:
                trigger_error(sprintf("password_hash(): Unknown password hashing algorithm: %s", $algo), E_USER_WARNING);
                return false;
        }
        $salt = openssl_random_pseudo_bytes($rawSaltLen);
        $base64_digits = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
        $bcrypt64Digits = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $base64String = base64_encode($salt);
        $salt = strtr(rtrim($base64String, '='), $base64_digits, $bcrypt64Digits);
        $salt = substr($salt, 0, $requiredSaltLen);
        $hash = $hashFormat . $salt;
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
    private $dataSet = null;
    private $pendingDataSet = null;
    private $dataTables = array();
    private $params;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->params = $params;
        if($this->current)
        {
            $this->dataSet = $this->getCurrentDataSet();
        }
        if($this->pending)
        {
            $this->pendingDataSet = $this->getPendingDataSet();
        }
    }

    /**
     * @SuppressWarnings("StaticAccess")
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
     * @SuppressWarnings("StaticAccess")
     */
    private function getPendingDataSet()
    {
        if(isset($this->params['pending_data_set']))
        {
            return \DataSetFactory::get_data_set($this->params['pending_data_set']);
        }
        return \DataSetFactory::get_data_set('pending_authentication');
    }

    private function getDataTable($name, $pending=false)
    {
         if(isset($this->dataTables[$name]) && isset($this->dataTables[$name][$pending]))
         {
             return $this->dataTables[$name][$pending];
         }
         $dataSet = $this->dataSet;
         if($pending)
         {
             $dataSet = $this->pendingDataSet;
         }
         if($dataSet === null)
         {
             throw new \Exception('Unable to obtain dataset for SQL Authentication!');
         }
         $dataTable = $dataSet[$name];
         if(!isset($this->dataTables[$name]))
         {
             $this->dataTables[$name] = array();
         }
         $this->dataTables[$name][$pending] = $dataTable;
         return $dataTable;
    }

    private function get_pending_user_data_table()
    {
        if(isset($this->params['pending_user_table']))
        {
            return $this->getDataTable($this->params['pending_user_table'], true);
        }
        return $this->getDataTable('users', true);
    }

    public function login($username, $password)
    {
        if($this->current === false) return false;
        $user_data_table = $this->getDataTable('user');
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
        $group_data_table = $this->getDataTable('group');
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
        $user_data_table = $this->getDataTable('user');
        $filter = new \Data\Filter("uid eq '$name'");
        $users = $user_data_table->read($filter);
        if($users === false || !isset($users[0]))
        {
            return false;
        }
        return new SQLUser($users[0]);
    }

    private function getDataByFilter($dataTableName, $filter, $select, $top, $skip, $orderby)
    {
        $dataTable = $this->getDataTable($dataTableName);
        return $dataTable->read($filter, $select, $top, $skip, $orderby);
    }

    public function getGroupsByFilter($filter, $select=false, $top=false, $skip=false, $orderby=false)
    {
        $groups = $this->getDataByFilter('group', $filter, $select, $top, $skip, $orderby);
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
        $users = $this->getDataByFilter('user', $filter, $select, $top, $skip, $orderby);
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

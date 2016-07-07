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
            trigger_error("password_hash() expects parameter 2 to be long, ".gettype($algo)." given", E_USER_WARNING);
            return false;
        }
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
        $base64Digits = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
        $bcrypt64Digits = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $base64String = base64_encode($salt);
        $salt = strtr(rtrim($base64String, '='), $base64Digits, $bcrypt64Digits);
        $salt = substr($salt, 0, $requiredSaltLen);
        $hash = $hashFormat.$salt;
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
    public $dataSet = null;
    public $pendingDataSet = null;
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
            return \DataSetFactory::getDataSetByName($this->params['current_data_set']);
        }
        return \DataSetFactory::getDataSetByName('authentication');
    }

    /**
     * @SuppressWarnings("StaticAccess")
     */
    private function getPendingDataSet()
    {
        if(isset($this->params['pending_data_set']))
        {
            return \DataSetFactory::getDataSetByName($this->params['pending_data_set']);
        }
        return \DataSetFactory::getDataSetByName('pending_authentication');
    }

    private function getDataTable($name, $pending = false)
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

    /**
     * Get the data table for Pending Users
     *
     * @return boolean|\Data\DataTable The Pending User Data Table
     */
    private function getPendingUserDataTable()
    {
        if(isset($this->params['pending_user_table']))
        {
            return $this->getDataTable($this->params['pending_user_table'], true);
        }
        return $this->getDataTable('users', true);
    }

    public function login($username, $password)
    {
        if($this->current === false)
        {
            return false;
        }
        $userDataTable = $this->getDataTable('user');
        $filter = new \Data\Filter("uid eq '$username'");
        $users = $userDataTable->read($filter);
        if($users === false || !isset($users[0]))
        {
            return false;
        }
        if(password_verify($password, $users[0]['pass']))
        {
            return array('res'=>true, 'extended'=>$users[0]);
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
        if(isset($this->params['current_data_set']))
        {
            $data['current_data_set'] = $this->params['current_data_set'];
        }
        return new SQLUser($data, $this);
    }

    public function getGroupByName($name)
    {
        $groupDataTable = $this->getDataTable('group');
        $filter = new \Data\Filter("gid eq '$name'");
        $groups = $groupDataTable->read($filter);
        if($groups === false || !isset($groups[0]))
        {
            return null;
        }
        return new SQLGroup($groups[0]);
    }

    public function getUserByName($name)
    {
        $userDataTable = $this->getDataTable('user');
        $filter = new \Data\Filter("uid eq '$name'");
        $users = $userDataTable->read($filter);
        if($users === false || !isset($users[0]))
        {
            return null;
        }
        return new SQLUser($users[0], $this);
    }

    private function getDataByFilter($dataTableName, $filter, $select, $top, $skip, $orderby)
    {
        $dataTable = $this->getDataTable($dataTableName);
        return $dataTable->read($filter, $select, $top, $skip, $orderby);
    }

    /**
     * @param string $dataTableName The Data Table to serach
     * @param string $className The class to obtain data in
     * @param boolean|array $select The fields to read
     * @param boolean|integer $top The number of entities to read
     * @param boolean|integer $skip The number of entities to skip
     * @param boolean|array $orderby The fields to sort by
     */
    private function convertDataToClass($dataTableName, $className, $filter, $select, $top, $skip, $orderby)
    {
        $data = $this->getDataByFilter($dataTableName, $filter, $select, $top, $skip, $orderby);
        if($data === false)
        {
            return false;
        }
        $count = count($data);
        for($i = 0; $i < $count; $i++)
        {
            $data[$i] = new $className($groups[$i], $this);
        }
        return $data;
    }

    /**
     * @param boolean|array $select The fields to read
     * @param boolean|integer $top The number of entities to read
     * @param boolean|integer $skip The number of entities to skip
     * @param boolean|array $orderby The fields to sort by
     */
    public function getGroupsByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false)
    {
        return $this->convertDataToClass('group', 'SQLGroup', $filter, $select, $top, $skip, $orderby);
    }

    /**
     * @param boolean|array $select The fields to read
     * @param boolean|integer $top The number of entities to read
     * @param boolean|integer $skip The number of entities to skip
     * @param boolean|array $orderby The fields to sort by
     */
    public function getUsersByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false)
    {
        return $this->convertDataToClass('group', 'SQLUser', $filter, $select, $top, $skip, $orderby);
    }

    public function getPendingUserCount()
    {
        if($this->pending === false)
        {
            return 0;
        }
        $dataTable = $this->getPendingUserDataTable();
        if($dataTable === null)
        {
            return 0;
        }
        return $dataTable->count();
    }

    private function searchPendingUsers($filter, $select, $top, $skip, $orderby)
    {
        $userDataTable = $this->getPendingUserDataTable();
        $fieldData = $filter->to_mongo_filter();
        $firstFilter = new \Data\Filter('substringof(data,"'.implode($fieldData, ' ').'")');
        $users = $userDataTable->read($firstFilter, $select, $top, $skip, $orderby);
        if($users === false)
        {
            return false;
        }
        $ret = array();
        $count = count($users);
        for($i = 0; $i < $count; $i++)
        {
            $user = new SQLPendingUser($users[$i], $userDataTable);
            $err = false;
            foreach($fieldData as $field=>$data)
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

    /**
     * @param \Data\Filter $filter The filter to read with
     * @param boolean|array $select The fields to read
     * @param boolean|integer $top The number of entities to read
     * @param boolean|integer $skip The number of entities to skip
     * @param boolean|array $orderby The fields to sort by
     */
    public function getPendingUsersByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false)
    {
        if($this->pending === false)
        {
            return false;
        }
        if($filter !== false && !$filter->contains('hash'))
        {
            return $this->searchPendingUsers($filter, $select, $top, $skip, $orderby);
        }
        $userDataTable = $this->getPendingUserDataTable();
        $users = $userDataTable->read($filter, $select, $top, $skip, $orderby);
        if($users === false)
        {
            return false;
        }
        $count = count($users);
        for($i = 0; $i < $count; $i++)
        {
            $users[$i] = new SQLPendingUser($users[$i], $userDataTable);
        }
        return $users;
    }

    public function createPendingUser($user)
    {
        if($this->pending === false)
        {
            return false;
        }
        $userDataTable = $this->getPendingUserDataTable();
        if(isset($user->password2))
        {
            unset($user->password2);
        }
        $json = json_encode($user);
        $hash = hash('sha512', $json);
        $array = array('hash'=>$hash, 'data'=>$json);
        $ret = $userDataTable->create($array);
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

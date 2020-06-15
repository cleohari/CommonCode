<?php
namespace Flipside\Auth;

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
            return \Flipside\DataSetFactory::getDataSetByName($this->params['current_data_set']);
        }
        return \Flipside\DataSetFactory::getDataSetByName('authentication');
    }

    /**
     * @SuppressWarnings("StaticAccess")
     */
    private function getPendingDataSet()
    {
        if(isset($this->params['pending_data_set']))
        {
            return \Flipside\DataSetFactory::getDataSetByName($this->params['pending_data_set']);
        }
        return \Flipside\DataSetFactory::getDataSetByName('pending_authentication');
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
        $filter = new \Flipside\Data\Filter("uid eq '$username'");
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

    /**
     * Get the specified entity from the specified database table
     *
     * @param string $tableName The name of the table to obtain data from
     * @param string $filterStr The filter string to use to obtain the data
     * @param string $className The class name to pass the data to
     *
     * @return stdClass The data as an object or null if not found
     */
    private function getEntityByFilter($tableName, $filterStr, $className)
    {
        $dataTable = $this->getDataTable($tableName);
        $filter = new \Flipside\Data\Filter($filterStr);
        $entities = $dataTable->read($filter);
        if(empty($entities))
        {
            return null;
        }
        return new $className($entities[0], $this);
    }

    public function getGroupByName($name)
    {
        return $this->getEntityByFilter('group', "gid eq '$name'", '\Flipside\Auth\SQLGroup');
    }

    public function getUserByName($name)
    {
        return $this->getEntityByFilter('user', "uid eq '$name'", '\Flipside\Auth\SQLUser');
    }

    /**
     * Get the specified entities from the specified database table
     *
     * @param string $dataTableName The name of the table to obtain data from
     * @param boolean|\Data\Filter $filter The filter to use while searching the table
     * @param boolean|array $select The array of properties to read
     * @param boolean|integer $top The number of records to read
     * @param boolean|integer $skip The number of records to skip
     * @param boolean|array $orderby The properties to sort on
     *
     * @return array The SQL data returned by the filter
     */
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
            $data[$i] = new $className($data[$i], $this);
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
        return $this->convertDataToClass('group', 'Flipside\Auth\SQLGroup', $filter, $select, $top, $skip, $orderby);
    }

    /**
     * @param boolean|array $select The fields to read
     * @param boolean|integer $top The number of entities to read
     * @param boolean|integer $skip The number of entities to skip
     * @param boolean|array $orderby The fields to sort by
     */
    public function getUsersByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false)
    {
        return $this->convertDataToClass('user', 'Flipside\Auth\SQLUser', $filter, $select, $top, $skip, $orderby);
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

    /**
     * Search all the pending users
     *
     * @param boolean|\Data\Filter $filter The filter to use while searching the table
     * @param boolean|array $select The array of properties to read
     * @param boolean|integer $top The number of records to read
     * @param boolean|integer $skip The number of records to skip
     * @param boolean|array $orderby The properties to sort on
     *
     * @return array The SQL data returned by the filter
     */
    private function searchPendingUsers($filter, $select, $top, $skip, $orderby)
    {
        $userDataTable = $this->getPendingUserDataTable();
        $clause = $filter->getClause('time');
        $fieldData = false;
        if($clause === false)
        {
            $fieldData = $filter->to_mongo_filter();
            $filter = new \Flipside\Data\Filter('substringof(data,"'.implode($fieldData, ' ').'")');
        }
        $users = $userDataTable->read($filter, $select, $top, $skip, $orderby);
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
            if($fieldData !== false)
            {
                foreach($fieldData as $field=>$data)
                {
                    if(strcasecmp($user[$field], $data) !== 0)
                    {
                        $err = true; break;
                    }
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

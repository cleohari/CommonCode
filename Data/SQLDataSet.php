<?php
namespace Flipside\Data;

class SQLDataSet extends DataSet
{
    protected $pdo;
    protected $params;

    /**
     * Create a new SQLDataSet
     *
     * @param array $params An array containing atleast 'dsn' and possibly 'user' and 'pass'
     */
    public function __construct($params)
    {
        $this->params = $params;
        if(isset($params['user']))
        {
            $this->pdo = new \PDO($params['dsn'], $params['user'], $params['pass']);
        }
        else
        {
            $this->pdo = new \PDO($params['dsn']);
        }
    }

    public function __sleep()
    {
        $this->pdo = null;
        return array('params');
    }

    public function __wakeup()
    {
        $this->connect();
    }

    protected function connect()
    {
        if(isset($this->params['user']))
        {
            $this->pdo = new \PDO($this->params['dsn'], $this->params['user'], $this->params['pass'], array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        }
        else
        {
            $this->pdo = new \PDO($this->params['dsn']);
        }
    }

    /**
     * Get the number of rows affected by the query
     *
     * @param string $sql The SQL string
     *
     * @return integer The number of rows affected by the query
     */
    private function _get_row_count_for_query($sql)
    {
        $stmt = $this->pdo->query($sql);
        if($stmt === false)
        {
            return 0;
        }
        $array = $stmt->fetchAll();
        $count = count($array);
        return $count;
    }

    private function _tableExistsNoPrefix($name)
    {
        if($this->_get_row_count_for_query('SHOW TABLES LIKE '.$this->pdo->quote($name)) > 0)
        {
            return true;
        }
        else if($this->_get_row_count_for_query('SELECT * FROM sqlite_master WHERE name LIKE '.$this->pdo->quote($name)) > 0)
        {
            return true;
        }
        return false;
    }

    private function _tableExists($name)
    {
        return $this->_tableExistsNoPrefix('tbl'.$name);
    }

    private function _viewExists($name)
    {
        return $this->_tableExistsNoPrefix('v'.$name);
    }

    public function tableExists($name)
    {
        if($this->_tableExists($name))
        {
            return true;
        }
        if($this->_tableExistsNoPrefix($name))
        {
            return true;
        }
        if($this->_viewExists($name))
        {
            return true;
        }
        return false;
    }

    public function getTable($name)
    {
        if($this->_tableExists($name))
        {
            return new SQLDataTable($this, 'tbl'.$name);
        }
        if($this->_viewExists($name))
        {
            return new SQLDataTable($this, 'v'.$name);
        }
        if($this->_tableExistsNoPrefix($name))
        {
            return new SQLDataTable($this, $name);
        }
        throw new \Exception('No such table '.$name);
    }

    /**
     * @param boolean|array $sort The array to sort by or false to not sort
     */
    private function getOrderByClause($sort)
    {
        if(empty($sort))
        {
            return false;
        }
        $sql = ' ORDER BY ';
        $tmp = array();
        foreach($sort as $sort_col=>$dir)
        {
            array_push($tmp, $sort_col.' '.($dir === 1 ? 'ASC' : 'DESC'));
        }
        $sql .= implode(',', $tmp);
        return $sql;
    }

    /**
     * Convert OData style $count and $skip into SQL LIMIT
     *
     * @param boolean|string $count The number of items to return
     * @param boolean|string $skip The number of items to skip
     */
    private function getLimitClause($count, $skip)
    {
        if($count === false)
        {
            return false;
        }
        $count = intval($count);
        if($skip !== false)
        {
            $skip = intval($count);
            return " LIMIT $skip, $count";
        }
        return ' LIMIT '.$count;
    }

    /**
     * Read data from the specified SQL table
     *
     * @param string $tablename The name of the table to read from
     * @param boolean|string $where The where caluse of the SQL statement
     * @param string $select The colums to read
     * @param boolean|string $count The number of rows to read
     * @param boolean|string $skip The number of rows to skip over
     * @param boolean|array $sort The array to sort by or false to not sort
     *
     * @return false|array An array of all the returned records
     */
    public function read($tablename, $where = false, $select = '*', $count = false, $skip = false, $sort = false)
    {
        if($select === false)
        {
            $select = '*';
        }
        $sql = "SELECT $select FROM `$tablename`";
        if($where !== false)
        {
            $sql .= ' WHERE '.$where;
        }
        $tmp = $this->getLimitClause($count, $skip);
        if($tmp !== false)
        {
            $sql .= $tmp;
        }
        $tmp = $this->getOrderByClause($sort);
        if($tmp !== false)
        {
            $sql .= $tmp;
        }
        $stmt = $this->pdo->query($sql, \PDO::FETCH_ASSOC);
        if($stmt === false)
        {
            return false;
        }
        $ret = $stmt->fetchAll();
        if(empty($ret))
        {
            return false;
        }
        return $ret;
    }

    /**
     * Perform an SQL update on the specified table
     *
     * @param string $tablename The name of the table to insert to
     * @param string $where The where clause in SQL format
     * @param mixed $data The data to write to the table
     *
     * @return boolean true if successful, false otherwise
     */
    public function update($tablename, $where, $data)
    {
        $set = array();
        if(is_object($data))
        {
            $data = (array)$data;
        }
        $cols = array_keys($data);
        $count = count($cols);
        for($i = 0; $i < $count; $i++)
        {
            if($data[$cols[$i]] === null)
            {
                array_push($set, $cols[$i].'=NULL');
            }
            else
            {
                array_push($set, $cols[$i].'='.$this->pdo->quote($data[$cols[$i]]));
            }
        }
        $set = implode(',', $set);
        $sql = "UPDATE $tablename SET $set WHERE $where";
        $stmt = $this->pdo->query($sql);
        if($stmt === false)
        {
            if (php_sapi_name() !== "cli") {
              error_log('DB query failed. '.print_r($this->pdo->errorInfo(), true));
            }
            return false;
        }
        else if($stmt->rowCount() === 0)
        {
            $data = $this->read($tablename, $where);
            if(empty($data))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Perform an SQL insert on the specified table
     *
     * @param string $tablename The name of the table to insert to
     * @param mixed $data The data to write to the table
     *
     * @return boolean true if successful, false otherwise
     */
    public function create($tablename, $data)
    {
        $set = array();
        if(is_object($data))
        {
            $data = (array)$data;
        }
        $cols = array_keys($data);
        $count = count($cols);
        for($i = 0; $i < $count; $i++)
        {
            if($data[$cols[$i]] === null)
            {
                array_push($set, 'NULL');
            }
            else
            {
                array_push($set, $this->pdo->quote($data[$cols[$i]]));
            }
            $cols[$i] = "`".$cols[$i]."`";
        }
        $cols = implode(',', $cols);
        $set = implode(',', $set);
        $sql = "INSERT INTO $tablename ($cols) VALUES ($set);";
        if($this->pdo->exec($sql) === false)
        {
            if (php_sapi_name() !== "cli") {
                error_log('DB query failed. '.print_r($this->pdo->errorInfo(), true));
            }
            return false;
        }
        return true;
    }

    /**
     * Perform an SQL delete on the specified table
     *
     * @param string $tablename The name of the table to insert to
     * @param string $where The where clause in SQL format
     *
     * @return boolean true if successful, false otherwise
     */
    public function delete($tablename, $where)
    {
        $sql = "DELETE FROM $tablename WHERE $where";
        if($this->pdo->exec($sql) === false)
        {
            return false;
        }
        return true;
    }

    /**
     * Perform an SQL query
     *
     * @param string $sql The raw SQL
     *
     * @return mixed false on a failure, an array of data otherwise
     */
    public function raw_query($sql)
    {
        $stmt = $this->pdo->query($sql, \PDO::FETCH_ASSOC);
        if($stmt === false)
        {
            return false;
        }
        $ret = $stmt->fetchAll();
        return $ret;
    }

    public function getLastError()
    {
        return $this->pdo->errorInfo();
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

<?php
namespace Data;

class SQLDataTable extends DataTable
{
    protected $dataset;
    protected $tablename;

    function __construct($dataset, $tablename)
    {
        $this->dataset   = $dataset;
        $this->tablename = $tablename;
    }

    function get_primary_key()
    {
        $res = $this->dataset->raw_query("SHOW INDEX FROM $this->tablename WHERE Key_name='PRIMARY'");
        if($res === false)
        {
            return false;
        }
        return $res[0]['Column_name'];
    }

    function prefetch_all($key = false)
    {
        $array = $this->read(false, false);
        if($key === false)
        {
            $key = $this->get_primary_key();
        }
        $count = count($array);
        $this->data = array();
        for($i = 0; $i < $count; $i++)
        {
            if(isset($this->data[$array[$i][$key]]))
            {
                if(isset($this->data[$array[$i][$key]][0]))
                {
                    array_push($this->data[$array[$i][$key]], $array[$i]);
                }
                else
                {
                    $this->data[$array[$i][$key]] = array($array[$i]);
                }
            }
            else
            {
                $this->data[$array[$i][$key]] = $array[$i];
            }
        }
    }

    function count($filter = false)
    {
        if($this->data !== null)
        {
            return parent::count($filter);
        }
        $where = false;
        if($filter !== false)
        {
            $where = $filter->to_sql_string();
        }
        $ret = $this->dataset->read($this->tablename, $where, 'COUNT(*)');
        if($ret === false || !isset($ret[0]) || !isset($ret[0]['COUNT(*)']))
        {
            return false;
        }
        else
        {
            return $ret[0]['COUNT(*)'];
        }
    }
  
    function search($filter = false, $select = false, $count = false, $skip = false, $sort = false, $params = false)
    {
        if($this->data !== null)
        {
            return parent::search($filter, $select);
        }
        $where = false;
        if($filter !== false)
        {
            $where = $filter->to_sql_string();
        }
        if($select !== false && is_array($select))
        {
            $select = implode(',', $select);
        }
        return $this->dataset->read($this->tablename, $where, $select, $count, $skip, $sort);
    }

    function update($filter, $data)
    {
        $where = $filter->to_sql_string();
        return $this->dataset->update($this->tablename, $where, $data);
    }

    function create($data)
    {
        return $this->dataset->create($this->tablename, $data);
    }

    function delete($filter)
    {
        $where = false;
        if($filter !== false)
        {
            $where = $filter->to_sql_string();
        }
        return $this->dataset->delete($this->tablename, $where);
    }

    function raw_query($sql)
    {
        return $this->dataset->raw_query($sql);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

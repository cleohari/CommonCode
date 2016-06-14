<?php
namespace Data;

class SQLDataTable extends DataTable
{
    protected $dataset;
    protected $tablename;

    /**
     * @param SQLDataSet $dataset The dataset to create this datatable in
     * @param string $tablename The name of the table in the dataset
     */
    public function __construct($dataset, $tablename)
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

    function count($filter = false)
    {
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

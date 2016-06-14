<?php
namespace Data;

abstract class DataTable
{
    public abstract function count($filter = false);

    public abstract function search($filter = false, $select = false, $count = false, $skip = false, $sort = false, $params = false);

    public abstract function create($data);

    public function read($filter = false, $select = false, $count = false, $skip = false, $sort = false, $params = false)
    {
        return $this->search($filter, $select, $count, $skip, $sort, $params);
    }

    public abstract function update($filter, $data);

    public abstract function delete($filter);
}

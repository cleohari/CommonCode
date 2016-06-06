<?php
namespace Data;

class DataTable implements \ArrayAccess
{
    protected $data = null;

    public function count($filter = false)
    {
        throw new \Exception('Unimplemented');
    }

    public function search($filter = false, $select = false, $count = false, $skip = false, $sort = false, $params = false)
    {
        if($this->data === null)
        {
            throw new \Exception('Unimplemented');
        }
        $array = $this->data;
        if($filter !== false)
        {
            $array = $filter->filter_array($this->data);
        }
        return $array;
    }

    public function create($data)
    {
        throw new \Exception('Unimplemented');
    }

    public function read($filter = false, $select = false, $count = false, $skip = false, $sort = false, $params = false)
    {
        return $this->search($filter, $select, $count, $skip, $sort, $params);
    }

    public function update($filter, $data)
    {
        throw new \Exception('Unimplemented');
    }

    public function delete($filter)
    {
        throw new \Exception('Unimplemented');
    }

    public function prefetch_all()
    {
        $this->data = $this->read(false, false);
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }
}

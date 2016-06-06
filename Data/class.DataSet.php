<?php

namespace Data;

class DataSet implements \ArrayAccess
{
    public function offsetSet($offset, $value)
    {
        return;
    }

    public function offsetExists($offset)
    {
        return $this->tableExists($offset);
    }

    public function offsetUnset($offset)
    {
        return;
    }

    public function offsetGet($offset)
    {
        return $this->getTable($offset);
    }

    public function tableExists($name)
    {
        throw new \Exception('Unimplemented');
    }

    public function getTable($name)
    {
        throw new \Exception('Unimplemented');
    }

    public function raw_query($query)
    {
        throw new \Exception('Unimplemented');
    }
}


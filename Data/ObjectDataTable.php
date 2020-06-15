<?php
namespace Flipside\Data;

class ObjectDataTable extends DataTable
{
    protected $dataTable;
    protected $className = '\Flipside\SerializableObject';

    public static function getInstance()
    {
        static $instance = null;
        if(null === $instance)
        {
            $instance = new static();
        }
        return $instance;
    }

    protected function __construct($dataTable)
    {
        $this->dataTable = $dataTable;
    }

    public function create($data)
    {
        if(is_array($data))
        {
            $data = new $this->className($data);
        }
        if(method_exists($data, 'preCreate'))
        {
            $data = $data->preCreate();
        }
        return $this->dataTable->create($data);
    }

    public function read($filter=false, $select=false, $count=false, $skip=false, $sort=false, $params=false, $returnObj=false)
    {
        $res = $this->dataTable->read($filter, $select, $count, $skip, $sort, $params);
        if($res === false)
        {
            return false;
        }
        if(!is_array($res))
        {
            $res = array($res);
        }
        $objCount = count($res);
        for($i = 0; $i < $objCount; $i++)
        {
           $res[$i] = new $this->className($res[$i]);
        }
        return $res;
    }

    public function update($filter, $data)
    {
        if(method_exists($data, 'preUpdate'))
        {
            $data = $data->preUpdate();
        }
        return $this->dataTable->update($filter, $data);
    }

    public function delete($filter)
    {
        return $this->dataTable->delete($filter);
    }

    public function count($filter=false)
    {
        return $this->dataTable->count($filter);
    }
}

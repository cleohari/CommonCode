<?php
namespace Data;

class MongoDataTable extends DataTable
{
    protected $collection;
    protected $name;

    public function __construct($collection, $collection_name = false)
    {
        $this->collection = $collection;
        $this->name = $collection_name;
    }

    public function count($filter = false)
    {
        $criteria = array();
        if($filter !== false)
        {
            if($filter instanceof \Data\Filter)
            {
                $criteria = $filter->to_mongo_filter();
            }
            else
            {
                $criteria = $filter;
            }
        }
        return $this->collection->count($criteria, array(), $this->name);
    }

    private function getCriteriaFromFilter($filter)
    {
        if($filter === false)
        {
            return array();
        }
        if(is_array($filter))
        {
            return $filter;
        }
        return $filter->to_mongo_filter();
    }

    public function read($filter = false, $select = false, $count = false, $skip = false, $sort = false, $params = false)
    {
        $fields   = array();
        $criteria = $this->getCriteriaFromFilter($filter);
        if($select !== false)
        {
            $fields = array_fill_keys($select, 1);
        }
        $cursor = $this->collection->find($criteria, $fields, $this->name);
        if($params !== false && isset($params['fields']))
        {
            $cursor->fields($params['fields']);
        }
        if($sort !== false)
        {
            $cursor->sort($sort);
        }
        if($skip !== false)
        {
            $cursor->skip($skip);
        }
        if($count !== false)
        {
            $cursor->limit($count);
        }
        if(method_exists($cursor, 'setTypeMap'))
        {
            $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
            return $cursor->toArray();
        }
        $ret = array();
        foreach($cursor as $doc)
        {
            array_push($ret, $doc);
        }
        return $ret;
    }

    public function create($data)
    {
        $res = $this->collection->insert($data, array(), $this->name);
        if($res === false || $res['err'] !== null)
        {
            return false;
        }
        return $data['_id'];
    }

    public function update($filter, $data)
    {
        $criteria = $this->getCriteriaFromFilter($filter);
        if(!is_array($data))
        {
            $data = json_decode(json_encode($data), true);
        }
        if(isset($data['_id']))
        {
            unset($data['_id']);
        }
        $res = $this->collection->update($criteria, array('$set' => $data), array(), $this->name);
        if($res === false || $res['err'] !== null)
        {
            return false;
        }
        return true;
    }

    public function delete($filter)
    {
        $criteria = $this->getCriteriaFromFilter($filter);
        $res = $this->collection->remove($criteria, array(), $this->name);
        if($res === false || $res['err'] !== null)
        {
            return false;
        }
        return true;
    }
}

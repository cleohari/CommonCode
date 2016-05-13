<?php
namespace Data;

class MongoDataTable extends DataTable
{
    protected $collection;
    protected $namespace;

    public function __construct($collection, $collection_name=false)
    {
        if($collection_name !== false)
        {
            $this->namespace = $collection.'.'.$collection_name;
        }
        else
        {
            $this->collection = $collection;
        }
    }

    public function count($filter=false)
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
        return $this->collection->count($criteria);
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

    public function search($filter=false, $select=false, $count=false, $skip=false, $sort=false, $params=false)
    {
        $fields   = array();
        $criteria = $this->getCriteriaFromFilter($filter);
        if($select !== false)
        {
            $fields = array_fill_keys($select, 1);
        }
        $cursor   = $this->collection->find($criteria, $fields);
        if($params !== false && isset($params['fields']))
        {
            $cursor->fields($params['fields']);
        }
        if($sort  !== false)
        {
            $cursor->sort($sort);
        }
        if($skip  !== false)
        {
            $cursor->skip($skip);
        }
        if($count !== false)
        {
            $cursor->limit($count);
        }
        $ret      = array();
        foreach($cursor as $doc)
        {
            array_push($ret, $doc);
        }
        return $ret;
    }

    public function create($data)
    {
        $res = $this->collection->insert($data);
        if($res === false || $res['err'] !== null)
        {
            return false;
        }
        return $data['_id'];
    }

    public function update($filter, $data)
    {
        $criteria = $this->getCriteriaFromFilter($filter);
        if(isset($data['_id']))
        {
            unset($data['_id']);
        }
        $res = $this->collection->update($criteria, array('$set' => $data));
        if($res === false || $res['err'] !== null)
        {
            return false;
        }
        return true;
    }

    public function delete($filter)
    {
        $criteria = $this->getCriteriaFromFilter($filter);
        $res = $this->collection->remove($criteria);
        if($res === false || $res['err'] !== null)
        {
            return false;
        }
        return true;
    }
}
?>

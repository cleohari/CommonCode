<?php
namespace Data;

function MongofillAutoload($classname)
{
    $classname = str_replace('/', '\\', $classname);
    $classname = ltrim($classname, '\\');
    $namespace = '';
    if($lastNsPos = strrpos($classname, '\\'))
    {
        $namespace = substr($classname, 0, $lastNsPos);
        $classname = substr($classname, $lastNsPos + 1);
    }
    if(strlen($namespace))
    {
        $namespace .= DIRECTORY_SEPARATOR;
    }
    $filename = __DIR__.'/../libs/mongofill/src/'.$namespace.$classname.'.php';
    if(is_readable($filename))
    {
        require $filename;
    }
}

class MongoDataSet extends DataSet
{
    protected $client;
    protected $manager;
    protected $db;
    protected $db_name;

    function __construct($params)
    {
        $this->client = null;
        $this->mangaer = null;
        $this->db = null;
        $this->db_name = null;
        if(class_exists('MongoClient'))
        {
            $this->setupMongoClient($params);
        }
        else if(class_exists('\MongoDB\Driver\Manager'))
        {
            $this->setupMongoManager($params);
        }
        else
        {
            require __DIR__.'/../libs/mongofill/src/functions.php';
            autoLoadHandler('\Data\MongofillAutoload');
            $this->setupMongoClient($params);
        }
    }

    function tableExists($name)
    {
        $collections = $this->db->getCollectionNames();
        if(in_array($name, $collections))
        {
            return true;
        }
        return false;
    }

    function getTable($name)
    {
        if($this->db !== null)
        {
            return new MongoDataTable($this->db->selectCollection($name));
        }
        else
        {
            return new MongoDataTable($this, $name);
        }
    }

    private function setupMongoClient($params)
    {
        if($params === false)
        {
            return;
        }
        if(isset($params['user']))
        {
            $this->client = new \MongoClient('mongodb://'.$params['host'].'/'.$params['db'], array('username'=>$params['user'], 'password'=>$params['pass']));
        }
        else
        {
            $this->client = new \MongoClient('mongodb://'.$params['host'].'/'.$params['db']);
        }
        $this->db = $this->client->selectDB($params['db']);
    }

    private function setupMongoManager($params)
    {
        if($params === false)
        {
            return;
        }
        if(isset($params['user']))
        {
            $this->manager = new \MongoDB\Driver\Manager('mongodb://'.$params['user'].':'.$params['pass'].'@'.$params['host'].'/'.$params['db']);
        }
        else
        {
            $this->manager = new \MongoDB\Driver\Manager('mongodb://'.$params['host'].'/'.$params['db']);
        }
        $this->db_name = $params['db'];
    }

    public function find($query = array(), $fields = array(), $collectionName)
    {
        $namespace = $this->db_name.'.'.$collectionName;
        $dbQuery = new \MongoDB\Driver\Query($query, $fields);
        return $this->manager->executeQuery($namespace, $dbQuery);
    }

    public function insert(&$document, $options = array(), $collectionName)
    {
        $namespace = $this->db_name.'.'.$collectionName;
        $dbWrite = new \MongoDB\Driver\BulkWrite();
        $id = $dbWrite->insert($document);
        $res = $this->manager->executeBulkWrite($namespace, $dbWrite, $options);
        if($res->getInsertedCount() === 1)
        {
            $document['_id'] = $id;
            return true;
        }
        return false;
    }

    public function remove($criteria = array(), array $options = array(), $collectionName)
    {
        $namespace = $this->db_name.'.'.$collectionName;
        $dbWrite = new \MongoDB\Driver\BulkWrite();
        $dbWrite->delete($criteria);
        $res = $this->manager->executeBulkWrite($namespace, $dbWrite, $options);
        return $res->getDeletedCount() >= 1;
    }

    public function update($criteria, $new_object, $options = array(), $collectionName)
    {
        $namespace = $this->db_name.'.'.$collectionName;
        $dbWrite = new \MongoDB\Driver\BulkWrite();
        $dbWrite->update($criteria, $new_object, $options);
        $res = $this->manager->executeBulkWrite($namespace, $dbWrite, $options);
        return $res->getModifiedCount() === 1;
    }

    public function count($query = array(), $options = array(), $collectionName)
    {
        $cmd = new \MongoDB\Driver\Command(['count'=>$collectionName, 'query'=>$query]);
        return $this->manager->executeCommand($this->db_name, $cmd);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

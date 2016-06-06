<?php
namespace Data;

function MongofillAutoload($classname)
{
    $classname = str_replace('/', '\\', $classname);
    $classname = ltrim($classname, '\\');
    $filename  = '';
    $namespace = '';
    if($lastNsPos = strrpos($classname, '\\'))
    {
        $namespace = substr($classname, 0, $lastNsPos);
        $classname = substr($classname, $lastNsPos + 1);
        $filename  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
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
            if(version_compare(PHP_VERSION, '5.3.0', '>='))
            {
                spl_autoload_register('\Data\MongofillAutoload', true, true);
            }
            else
            {
                spl_autoload_register('\Data\MongofillAutoload');
            }
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
            return new MongoDataTable($this->db_name, $name);
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
}
?>

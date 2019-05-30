<?php
namespace Data;

class DataTableSessionHandler implements \SessionHandlerInterface
{
    private $dataSetName;
    private $dataTableName;
    protected $dataTable;

    public function __construct($dataSet, $dataTable)
    {
        $this->dataSetName = $dataSet;
        $this->dataTableName = $dataTable;
    }

    public function open($savePath, $sessionName)
    {
       $this->dataTable = \DataSetFactory::getDataTableByNames($this->dataSetName, $this->dataTableName);
       if($this->dataTable)
       {
           return true;
       }
       return false;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $filter = new \Data\Filter("sessionId eq '$id'");
        $data = $this->dataTable->read($filter, array('sessionData'));
        if(empty($data))
        {
            return '';
        }
        return $data[0]['sessionData'];
    }

    public function write($id, $data)
    {
        $filter = new \Data\Filter("sessionId eq '$id'");
        $res = $this->dataTable->update($filter, array('sessionData'=>$data, 'sessionLastAccess'=>date("Y-m-d H:i:s")));
        if($res === false)
        {
            $res = $this->dataTable->create(array('sessionId'=>$id, 'sessionData'=>$data, 'sessionLastAccess'=>date("Y-m-d H:i:s")));
        }
        if($res === false)
        {
            var_dump($res);
        }
        return $res;
    }

    public function destroy($id)
    {
        $filter = new \Data\Filter("sessionId eq '$id'");
        return $this->dataTable->delete($filter);
    }

    public function gc($maxlifetime)
    {
        $date = date("Y-m-d H:i:s", time()-$maxlifetime);
        $filter = new \Data\Filter("sessionLastAccess lt $date");
        return $this->dataTable->delete($filter);
    }
}

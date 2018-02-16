<?php
namespace Http\Rest;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

class DataTableAPI extends RestAPI
{
    protected $dataSetName;
    protected $dataTableName;
    protected $primaryKeyName;

    public function __construct($dataSetName, $dataTableName, $primaryKeyName = false)
    {
        $this->dataSetName    = $dataSetName;
        $this->dataTableName  = $dataTableName;
        $this->primaryKeyName = $primaryKeyName;
    }

    public function setup($app)
    {
        $app->get('[/]', array($this, 'readEntries'));
        $app->post('[/]', array($this, 'createEntry'));
        if($this->primaryKeyName !== false)
        {
            $app->get('/{name}[/]', array($this, 'readEntry'));
            $app->patch('/{name}[/]', array($this, 'updateEntry'));
            $app->delete('/{name}[/]', array($this, 'deleteEntry'));
        }
    }

    protected function getDataTable()
    {
        return \DataSetFactory::getDataTableByNames($this->dataSetName, $this->dataTableName);
    }

    protected function canRead($request)
    {
        $this->validateLoggedIn($request);
        //validateLoggedIn is fatal if not logged in...
        return true;
    }

    protected function canCreate($request)
    {
        //Must be overriden in a child class to allow create
        return false;
    }

    protected function canUpdate($request, $entity)
    {
        //Must be overriden in a child class to allow update
        return false;
    }

    protected function canDelete($request, $entity)
    {
        //Must be overriden in a child class to allow update
        return false;
    }

    protected function getFilterForPrimaryKey($value)
    {
        return new \Data\Filter($this->primaryKeyName." eq '$value'");
    }

    protected function manipulateParameters($request, &$odata)
    {
        return false;
    }

    protected function validateCreate(&$obj, $request)
    {
        return true;
    }

    protected function validateUpdate(&$newObj, $request, $oldObj)
    {
        return true;
    }

    public function readEntries($request, $response, $args)
    {
        if($this->canRead($request) === false)
        {
            return $response->withStatus(401);
        }
        $dataTable = $this->getDataTable();
        $odata = $request->getAttribute('odata', new \ODataParams(array()));
        $params = $this->manipulateParameters($request, $odata);
        $areas = $dataTable->read($odata->filter, $odata->select, $odata->top,
                                  $odata->skip, $odata->orderby, $params);
        if($areas === false)
        {
            $areas = array();
        }
        if(method_exists($this, 'processEntry'))
        {
            $count = count($areas);
            for($i = 0; $i < $count; $i++)
            {
                $areas[$i] = $this->processEntry($areas[$i], $request);
            }
        }
        return $response->withJson($areas);
    }

    public function createEntry($request, $response, $args)
    {
        if($this->canCreate($request) === false)
        {
            return $response->withStatus(401);
        }
        $dataTable = $this->getDataTable();
        $obj = $request->getParsedBody();
        if($obj == NULL)
        {
            $obj = json_decode($request->getBody()->getContents(), true);
        }
        if($this->validateCreate($obj, $request) === false)
        {
            return $response->withStatus(400);
        }
        $ret = $dataTable->create($obj);
        return $response->withJson($ret);
    }

    public function readEntry($request, $response, $args)
    {
        if($this->canRead($request) === false)
        {
            return $response->withStatus(401);
        }
        $dataTable = $this->getDataTable();
        $odata = $request->getAttribute('odata', new \ODataParams(array()));
        $filter = $this->getFilterForPrimaryKey($args['name']);
        $areas = $dataTable->read($filter, $odata->select, $odata->top,
                                  $odata->skip, $odata->orderby);
        if(empty($areas))
        {
            return $response->withStatus(404);
        }
        if(method_exists($this, 'processEntry'))
        {
            $areas[0] = $this->processEntry($areas[0], $request);
        }
        return $response->withJson($areas[0]);
    }

    public function updateEntry($request, $response, $args)
    {
        if($this->canRead($request) === false)
        {
            return $response->withStatus(401);
        }
        $filter = $this->getFilterForPrimaryKey($args['name']);
        $dataTable = $this->getDataTable();
        $entry = $dataTable->read($filter);
        if(empty($entry))
        {
            return $response->withStatus(404);
        }
        if($this->canUpdate($request, $entry) === false)
        {
            return $response->withStatus(401);
        }
        $obj = $request->getParsedBody();
        if($this->validateUpdate($obj, $request, $entry) === false)
        {
            return $response->withStatus(400);
        }
        $ret = $dataTable->update($filter, $obj);
        return $response->withJson($ret);
    }

    public function deleteEntry($request, $response, $args)
    {
        if($this->canRead($request) === false)
        {
            return $response->withStatus(401);
        }
        $filter = $this->getFilterForPrimaryKey($args['name']);
        $dataTable = $this->getDataTable();
        $entry = $dataTable->read($filter);
        if(empty($entry))
        {
            return $response->withStatus(404);
        }
        if($this->canDelete($request, $entry) === false)
        {
            return $response->withStatus(401);
        }
        $ret = $dataTable->delete($filter);
        return $response->withJson($ret);
    }
}

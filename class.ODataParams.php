<?php
/**
 * ODataParams class
 *
 * This file describes the ODataParams class
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2017, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

/**
 * A class representing OData style URI query string parameters.
 *
 * The REST APIs use OData style URI query string parameters. 
 * This class abstracts parsing of those into a more PHP friendly format.
 */
class ODataParams
{
    /**
     * The ODataFilter or false if not set
     * @var false|Data\Filter
     */
    public $filter = false;
    /**
     * An array of properties to expand or false if not set
     * @var false|array
     */
    public $expand = false;
    /**
     * An array of properties to display or false if not set
     * @var false|array
     */
    public $select = false;
    /**
     * An array of properties to sort by or false if not set
     * @var false|array
     */
    public $orderby = false;
    /**
     * The number of results to display or false if not set
     * @var false|integer
     */
    public $top = false;
    /**
     * The number of results to skip or false if not set
     * @var false|integer
     */
    public $skip = false;
    /**
     * Display the count of results
     * @var boolean
     */
    public $count = false;
    /**
     * Not yet implemented
     */
    public $search = false;

    /**
     * Parse the parameter array into an ODataParams instance
     *
     * @param string[] $params An key=>value array of strings representing the query string.
     */
    public function __construct($params)
    {
        $this->processFilter($params);
        $this->processExpand($params);
        $this->processSelect($params);
        $this->processOrderBy($params);
        $this->processTop($params);
        $this->processSkip($params);
        $this->processCount($params);
        $this->processSearch($params);
    }

    /**
     * Take the parameter array and find the Filter parameter and convert that to a \Data\Filter if present
     *
     * @param string[] $params An key=>value array of strings representing the query string.
     */
    protected function processFilter($params)
    {
        if(isset($params['filter']))
        {
            $this->filter = new \Data\Filter($params['filter']);
        }
        else if(isset($params['$filter']))
        {
            $this->filter = new \Data\Filter($params['$filter']);
        }
    }

    /**
     * Take the parameter array and find the Expand parameter and convert it to a PHP array
     *
     * @param string[] $params An key=>value array of strings representing the query string.
     */
    protected function processExpand($params)
    {
        if(isset($params['$expand']))
        {
            $this->expand = explode(',', $params['$expand']);
        }
    }

    /**
     * Take the parameter array and find the Select parameter and convert it to a PHP array
     *
     * @param string[] $params An key=>value array of strings representing the query string.
     */
    protected function processSelect($params)
    {
        if(isset($params['select']))
        {
            $this->select = explode(',', $params['select']);
        }
        else if(isset($params['$select']))
        {
            $this->select = explode(',', $params['$select']);
        }
    }

    /**
     * Take the parameter array and find the OrderBy parameter and convert it to a PHP array
     *
     * @param string[] $params An key=>value array of strings representing the query string.
     */
    protected function processOrderBy($params)
    {
        if(isset($params['$orderby']))
        {
            $this->orderby = array();
            $orderby = explode(',', $params['$orderby']);
            $count = count($orderby);
            for($i = 0; $i < $count; $i++)
            {
                $exp = explode(' ', $orderby[$i]);
                if(count($exp) === 1)
                {
                    //Default to assending
                    $this->orderby[$exp[0]] = 1;
                }
                else
                {
                    switch($exp[1])
                    {
                        case 'asc':
                            $this->orderby[$exp[0]] = 1;
                            break;
                        case 'desc':
                            $this->orderby[$exp[0]] = -1;
                            break;
                        default:
                            throw new Exception('Unknown orderby operation');
                    }
                }
            }
        }
    }

    /**
     * Take the parameter array and find the Top parameter and convert it to an int
     *
     * @param string[] $params An key=>value array of strings representing the query string.
     */
    protected function processTop($params)
    {
        if(isset($params['$top']) && is_numeric($params['$top']))
        {
            $this->top = intval($params['$top']);
        }
    }

    /**
     * Take the parameter array and find the Skip parameter and convert it to an int
     *
     * @param string[] $params An key=>value array of strings representing the query string.
     */
    protected function processSkip($params)
    {
        if(isset($params['$skip']) && is_numeric($params['$skip']))
        {
            $this->skip = intval($params['$skip']);
        }
    }

    /**
     * Take the parameter array and find the Count parameter and convert it to a boolean
     *
     * @param string[] $params An key=>value array of strings representing the query string.
     */
    protected function processCount($params)
    {
        if(isset($params['$count']) && strcasecmp($params['$count'], 'true') === 0)
        {
            $this->count = true;
        }
    }

    /**
     * Take the parameter array and find the Search parameter and process it
     *
     * @param string[] $params An key=>value array of strings representing the query string.
     */
    protected function processSearch($params)
    {
        if(isset($params['$search']))
        {
            throw new Exception('Search not yet implemented');
        }
    }

    /**
     * Take an input array and filter the array based on the select parameter
     *
     * @param array $array The array to be filtered
     *
     * @return array The filtered array
     */
    public function filterArrayPerSelect($array)
    {
        $flip = array_flip($this->select);
        $count = count($array);
        for($i = 0; $i < $count; $i++)
        {
            if(is_a($array[$i], 'SerializableObject'))
            {
                $array[$i] = array_intersect_key($array[$i]->jsonSerialize(), $flip);
                continue;
            }
            $array[$i] = array_intersect_key($array[$i], $this->select);
        }
        return $array;
    }
}

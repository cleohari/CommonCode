<?php
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
    public $search = false;

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

    protected function processExpand($params)
    {
        if(isset($params['$expand']))
        {
            $this->expand = explode(',', $params['$expand']);
        }
    }

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

    protected function processTop($params)
    {
        if(isset($params['$top']) && is_numeric($params['$top']))
        {
            $this->top = intval($params['$top']);
        }
    }

    protected function processSkip($params)
    {
        if(isset($params['$skip']) && is_numeric($params['$skip']))
        {
            $this->skip = intval($params['$skip']);
        }
    }

    protected function processCount($params)
    {
        if(isset($params['$count']) && strcasecmp($params['$count'], 'true') === 0)
        {
            $this->count = true;
        }
    }

    protected function processSearch($params)
    {
        if(isset($params['$search']))
        {
            throw new Exception('Search not yet implemented');
        }
    }

    public function filterArrayPerSelect($array)
    {
        $flip = array_flip($this->select);
        $count = count($leads);
        for($i = 0; $i < $count; $i++)
        {
            if(is_a($array[$i], 'SerializableObject'))
            {
                $array[$i] = array_intersect_key($array[$i]->jsonSerialize(), $flip);
                continue;
            }
            $array[$i] = array_intersect_key($array[$i], $select);
        }
        return $array;
    }
}

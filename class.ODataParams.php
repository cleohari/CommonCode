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
        if(isset($params['filter']))
        {
            $this->filter = new \Data\Filter($params['filter']);
        }
        else if(isset($params['$filter']))
        {
            $this->filter = new \Data\Filter($params['$filter']);
        }

        if(isset($params['$expand']))
        {
            $this->expand = explode(',',$params['$expand']);
        }

        if(isset($params['select']))
        {
            $this->select = explode(',',$params['select']);
        }
        else if(isset($params['$select']))
        {
            $this->select = explode(',',$params['$select']);
        }

        if(isset($params['$orderby']))
        {
            $this->orderby = array();
            $orderby = explode(',',$params['$orderby']);
            $count = count($orderby);
            for($i = 0; $i < $count; $i++)
            {
                $exp = explode(' ',$orderby[$i]);
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

        if(isset($params['$top']))
        {
            $this->top = $params['$top'];
        }

        if(isset($params['$skip']))
        {
            $this->skip = $params['$skip'];
        }

        if(isset($params['$count']) && $params['$count'] === 'true')
        {
            $this->count = true;
        }

        if(isset($params['$seach']))
        {
            throw new Exception('Search not yet implemented');
        }
    }
}
?>

<?php
namespace Data;

class FilterClause
{
    public $var1;
    public $var2;
    public $op;

    /**
     * Create a filter clause from the string
     *
     * @param string $string The filter clause string
     */
    public function __construct($string = false)
    {
        if(is_string($string))
        {
            $this->process_filter_string($string);
        }
    }

    /**
     * Find the string inside the other string
     *
     * @param string $haystack The string to search inside
     * @param string $needle The string to search for
     *
     * @return boolean True if the needle exists in the haystack, false otherwise
     */
    protected static function str_startswith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    /**
     * Is the specified filter a function?
     *
     * @param string $string The filter clause
     *
     * @return boolean True if the filter is an operation, false otherwise
     */
    protected function filterIsFunction($string)
    {
        return (self::str_startswith($string, 'substringof') || self::str_startswith($string, 'contains') ||
                self::str_startswith($string, 'indexof'));
    }

    /**
     * Convert the OData simple op to standard operations
     *
     * @param string $op The OData op
     *
     * @return string The standard programatic notation
     */
    protected function odataOpToStd($op)
    {
        switch($op)
        {
            case 'ne':
                return '!=';
            case 'eq':
                return '=';
            case 'lt':
                return '<';
            case 'le':
                return '<=';
            case 'gt':
                return '>';
            case 'ge':
                return '>=';
            default:
                return $op;
        }
    }

    /**
     * Convert the string into an OData Filter
     *
     * @param string $string The string to turn into a filter
     */
    protected function process_filter_string($string)
    {
        if($this->filterIsFunction($string))
        {
            $this->op   = strtok($string, '(');
            $this->var1 = strtok(',');
            $this->var2 = trim(strtok(')'));
            return;
        }
        $field = strtok($string, ' ');
        $op = strtok(' ');
        $rest = strtok("\0");
        $this->var1  = $field;
        $this->op    = $this->odataOpToStd($op);
        $this->var2  = $rest;
    }

    public function to_sql_string()
    {
        switch($this->op)
        {
            case 'substringof':
            case 'contains':
                return $this->var1.' LIKE \'%'.trim($this->var2, "'").'%\'';
                break;
            default:
                return $this->var1.$this->op.$this->var2;
                break;
        }
    }

    public function to_ldap_string()
    {
        $str = '(';
        switch($this->op)
        {
            case 'substringof':
            case 'contains':
                $str .= $this->var1.'=*'.trim($this->var2, "'").'*';
                break;
            case '!=':
                $str .= '!('.$this->var1.'='.$this->var2.')';
                break;
            default:
                $str .= $this->var1.$this->op.$this->var2;
                break;
        }
        return $str.')';
    }

    private function getMongoIndexOfOperator()
    {
        $field = $this->var1;
        $case  = false;
        if(self::str_startswith($this->var1, 'tolower'))
        {
            $field = substr($this->var1, strpos($this->var1, '(') + 1);
            $field = substr($field, 0, strpos($field, ')'));
            $case = true;
        }
        if($case)
        {
            return array($field=>array('$regex'=>new \MongoRegex('/'.$this->var2.'/i')));
        }
        return array($field=>$this->var2);       
    }

    /**
     * Convert the standard operations to Mongo operations
     *
     * @param string $op The standard op
     *
     * @return string The mongo operator
     */
    private function opToMongo($op)
    {
        switch($op)
        {
            case '!=':
                return '$ne';
            case '<';
                return '$lt';
            case '<=':
                return '$lte';
            case '>':
                return '$gt';
            case '>=':
                return '$gte';
            default:
                return $op;
        }
    }

    /**
     * Convert the right hand side of the filter clause into an Mongo array
     *
     * @param string $op The standard operator
     * @param string $var The second variable in the operator
     *
     * @return array An array of mongo operations
     */
    private function getMongoClauseArray($op, $var2)
    {
        return array($this->opToMongo($op)=>$var2);
    }

    public function toMongoFilter()
    {
        if($this->var2 === 'true')
        {
            $this->var2 = true;
        }
        else if($this->var2 === 'false')
        {
            $this->var2 = false;
        }
        else if(is_numeric($this->var2))
        {
            $this->var2 = intval($this->var2);
        }
        else
        {
            $this->var2 = trim($this->var2, "'");
        }
        if($this->var1 === '_id')
        {
            try
            {
                if(class_exists('MongoId'))
                {
                    $this->var2 = new \MongoId($this->var2);
                }
                else
                {
                    $this->var2 = new \MongoDB\BSON\ObjectId($this->var2);
                }
            }
            catch(\MongoException $e)
            {
                //Not a valid mongo ID. Just leave the variable alone and try the query...
            }
            catch(\MongoDB\Driver\Exception\InvalidArgumentException $e)
            {
                //Not a valid mongo ID. Just leave the variable alone and try the query...
            }
        }
        switch($this->op)
        {
            case '=':
                return array($this->var1=>$this->var2);
            case 'substringof':
                return array($this->var1=>array('$regex'=>new \MongoRegex('/'.$this->var2.'/i')));
            case 'indexof':
                return $this->getMongoIndexOfOperator();
            default:
                return array($this->var1=>$this->getMongoClauseArray($this->op, $this->var2));
        }
    }

    public function php_compare($value)
    {
	if(is_array($value))
	{
            return $this->php_compare($value[$this->var1]);
	}
        switch($this->op)
        {
            case '!=':
                return $value != $this->var2;
	    case '=':
                return $value == $this->var2;
            case '<':
                return $value < $this->var2;
            case '<=':
                return $value <= $this->var2;
            case '>':
                return $value > $this->var2;
            case '>=':
                return $value >= $this->var2;
        }
    }
}

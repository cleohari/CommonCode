<?php
namespace Data;

class FilterClause
{
    public $var1;
    public $var2;
    public $op;

    function __construct($string = false)
    {
        if($string !== false)
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
    static function str_startswith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    protected function filterIsFunction($string)
    {
        return (self::str_startswith($string, 'substringof') || self::str_startswith($string, 'contains') ||
                self::str_startswith($string, 'indexof'));
    }

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
                $str .= $this->var1.$this->op.'*'.trim($this->var2, "'").'*';
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

    public function toMongoFilter()
    {
        $this->var2 = trim($this->var2, "'");
        if($this->var1 === '_id')
        {
            $this->var2 = new \MongoId($this->var2);
        }
        switch($this->op)
        {
            case '!=':
                return array($this->var1=>array('$ne'=>$this->var2));
            case '=':
                return array($this->var1=>$this->var2);
            case '<';
                return array($this->var1=>array('$lt'=>$this->var2));
            case '<=':
                return array($this->var1=>array('$lte'=>$this->var2));
            case '>':
                return array($this->var1=>array('$gt'=>$this->var2));
            case '>=':
                return array($this->var1=>array('$gte'=>$this->var2));
            case 'substringof':
                return array($this->var1=>array('$regex'=>new MongoRegex('/'.$this->var2.'/i')));
            case 'indexof':
                return $this->getMongoIndexOfOperator();
        }
    }

    function php_compare($value)
    {
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

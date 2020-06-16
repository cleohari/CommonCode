<?php
namespace Flipside\Data;

class Filter
{
    protected $children = array();
    protected $string;
    protected $sqlAppend = '';

    /**
     * Creates a new filter object
     *
     * @param false|string $string The string to create the filter from or false for an empty filter
     */
    public function __construct($string = false)
    {
        if($string !== false)
        {
            $this->string = $string;
            $this->children = self::process_string($this->string);
        }
    }

    static public function process_string($string)
    {
        $parens = false;
        if(is_a($string, '\Flipside\Data\Filter'))
        {
            $string = $string->string;
        }
        //First check for parenthesis...
        if($string[0] === '(' && substr($string, -1) === ')')
        {
            $string = substr($string, 1, strlen($string) - 2);
            $parens = true;
        }
        if(preg_match('/(.+?)( and | or )(.+)/', $string, $clauses) === 0)
        {
            return array(new FilterClause($string));
        }
        $children = array();
        if($parens)
        {
            array_push($children, '(');
        }
        $children = array_merge($children, self::process_string($clauses[1]));
        array_push($children, trim($clauses[2]));
        $children = array_merge($children, self::process_string($clauses[3]));
        if($parens)
        {
            array_push($children, ')');
        }
        return $children;
    }

    public function to_sql_string()
    {
        $ret = '';
        $count = count($this->children);
        for($i = 0; $i < $count; $i++)
        {
            if($this->children[$i] === '(' || $this->children[$i] === ')')
            {
                $ret .= $this->children[$i];
            }
            else if($this->children[$i] === 'and')
            {
                $ret .= ' AND ';
            }
            else if($this->children[$i] === 'or')
            {
                $ret .= ' OR ';
            }
            else
            {
                $ret .= $this->children[$i]->to_sql_string();
            }
        }
        return $ret.$this->sqlAppend;
    }

    public function to_ldap_string()
    {
        $ret = '';
        $count = count($this->children);
        $prefix = '';
        for($i = 0; $i < $count; $i++)
        {
            if($this->children[$i] === 'and')
            {
                if($prefix == '|')
                {
                    throw new \Exception('Do not support both and or');
                }
                $prefix = '&';
            }
            else if($this->children[$i] === 'or')
            {
                if($prefix == '&')
                {
                    throw new \Exception('Do not support both and or');
                }
                $prefix = '|';
            }
            else
            {
                $ret .= $this->children[$i]->to_ldap_string();
            }
        }
        if($count === 1 && $prefix === '')
        {
            return $ret;
        }
        return '('.$prefix.$ret.')';
    }

    public function to_mongo_filter()
    {
        $ret = array();
        $count = count($this->children);
        for($i = 0; $i < $count; $i++)
        {
            if($this->children[$i] === 'and')
            {
                $old = array_pop($ret);
                array_push($ret, array('$and'=>array($old, $this->children[++$i]->toMongoFilter())));
            }
            else if($this->children[$i] === 'or')
            {
                $old = array_pop($ret);
                array_push($ret, array('$or'=>array($old, $this->children[++$i]->toMongoFilter())));
            }
            else
            {
                array_push($ret, $this->children[$i]->toMongoFilter());
            }
        }
        if(count($ret) == 1 && is_array($ret[0]))
        {
            return $ret[0];
        }
        return $ret;
    }

    public function filterElement($element)
    {
        $res = array();
        $count = count($this->children);
	for($i = 0; $i < $count; $i++)
	{
            if($this->children[$i] === 'and' || $this->children[$i] === 'or')
	    {
                array_push($res, $this->children[$i]);
	    }
	    else
	    {
                $tmp = $this->children[$i]->php_compare($element);
		array_push($res, $tmp);
	    }
	}
	if($count === 1)
	{
            return $res[0];
	}
	while($count >= 3)
	{
	    if($res[1] === 'and')
            {
                $var1 = array_shift($res);
                array_shift($res);
                $var2 = array_shift($res);
	        $res = array_merge(array($var1 && $var2), $res);
            }
	    else if($res[1] === 'or')
            {
                $var1 = array_shift($res);
                array_shift($res);
                $var2 = array_shift($res);
                $res = array_merge(array($var1 || $var2), $res);
	    }
	    $count = count($res);
	}
        return $res[0];
    }

    public function filter_array(&$array)
    {
        if(is_array($array))
	{
            $res = array_filter($array, array($this, 'filterElement'));
            return array_values($res);
        }
        return array();
    }

    public function contains($substr)
    {
        return strstr($this->string, $substr) !== false;
    }

    public function getClause($substr)
    {
        $count = count($this->children);
        for($i = 0; $i < $count; $i++)
        {
            if(!is_object($this->children[$i]))
            {
                continue;
            }
            if(strstr($this->children[$i]->var1, $substr) !== false ||
               strstr($this->children[$i]->var2, $substr) !== false)
            {
                return $this->children[$i];
            }
        }
    }

    public function addToSQLString($string)
    {
        $this->sqlAppend .= $string;
    }

    public function appendChild($child)
    {
        if($child === 'and' || $child === 'or')
        {
            array_push($this->children, $child);
            return;
        }
        else if(is_a($child, '\Data\Filter'))
        {
            $this->children = array_merge($this->children, $child->children);
        }
        else
        {
            $this->children = array_merge($this->children, self::process_string($child));
        }
    }

    public function getChildren()
    {
        return $this->children;
    }
}

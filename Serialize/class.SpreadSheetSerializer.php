<?php
namespace Serialize;

abstract class SpreadSheetSerializer implements ISerializer
{
    public function supportsType($type)
    {
        foreach($this->types as $t)
        {
            if(strcasecmp($t, $type) === 0)
            {
                return true;
            }
        }
        return false;
    }

    protected function getArray(&$array)
    {
        $res = array();
        if(is_object($array))
        {
            $array = get_object_vars($array);
        }
        $first = reset($array);
        $keys = false;
        if(is_array($first))
        {
            $keys = array_keys($first);
        }
        else if(is_object($first))
        {
            $keys = array_keys(get_object_vars($first));
        }
        $colCount = count($keys);
        $res[] = $keys;
        foreach($array as $row)
        {
            $tmp = array();
            if(is_object($row))
            {
                $row = get_object_vars($row);
            }
            if(!is_array($row))
            {
                $row = array($row);
            }
            for($j = 0; $j < $colCount; $j++)
            {
                $colName = $keys[$j];
                if(isset($row[$colName]))
                {
                    $value = $row[$colName];
                    if(is_object($value))
                    {
                        switch($colName)
                        {
                            case '_id':
                                $value = $value->{'$id'};
                                break;
                            default:
                                $value = json_encode($value);
                                break;
                        }
                    }
                    else if(is_array($value))
                    {
                        $value = implode(',', $value);
                    }
                    $tmp[] = $value;
                }
                else
                {
                    $tmp[] = false;
                }
            }
            $res[] = $tmp;
        }
        return $res;
    }
}
?>

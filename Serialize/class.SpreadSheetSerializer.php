<?php
namespace Serialize;

abstract class SpreadSheetSerializer extends Serializer
{
    private function getKeysFromData($array)
    {
        $first = reset($array);
        if(is_array($first))
        {
            return array_keys($first);
        }
        else if(is_object($first))
        {
            return array_keys(get_object_vars($first));
        }
        return false;
    }

    private function getRowArray($row)
    {
        if(is_object($row))
        {
            return get_object_vars($row);
        }
        if(!is_array($row))
        {
            return array($row);
        }
        return $row;
    }

    protected function getArray(&$array)
    {
        $res = array();
        if(is_object($array))
        {
            $array = array(get_object_vars($array));
        }
        $keys = $this->getKeysFromData($array);
        if($keys === false)
        {
            return $array;
        }
        $colCount = count($keys);
        $res[] = $keys;
        foreach($array as $row)
        {
            $tmp = array();
            $row = $this->getRowArray($row);
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

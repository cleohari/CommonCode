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

    private function prependAndMergeKeys(&$keys, $prefix, $newKeys)
    {
        foreach($newKeys as &$key)
        {
            $key = $prefix.$key;
        }
        $keys = array_unique(array_merge($keys, $newKeys));
    }

    protected function addValueByColName(&$cols, &$row, $colName, $value)
    {
        if(is_object($value))
        {
            switch($colName)
            {
                case '_id':
                    $this->addValueByColName($cols, $row, $colName, $value->{'$id'});
                    break;
                default:
                    $props = get_object_vars($value);
                    foreach($props as $key=>$newValue)
                    {
                        $this->addValueByColName($cols, $row, $colName.'.'.$key, $newValue);
                    }
            }
            return;
        }
        $index = array_search($colName, $cols);
        if($index === false)
        {
            $index = count($cols);
            $cols[$index] = $colName;
        }
        if(is_array($value))
        {
            $row[$index] = implode(',', $value);
        }
        else
        {
            $row[$index] = $value;
        }
    }

    protected function getArray(&$array)
    {
        $res = array();
        if(is_object($array))
        {
            $array = array(get_object_vars($array));
        }
        $res[] = array();
        foreach($array as $row)
        {
            $tmp = array();
            $row = $this->getRowArray($row);
            foreach($row as $colName=>$value)
            {
                $this->addValueByColName($res[0], $tmp, $colName, $row[$colName]);
            }
            $tmp = $tmp+array_fill_keys(range(0,max(array_keys($tmp))),false);
            ksort($tmp);
            $res[] = $tmp;
        }
        return $res;
    }
}

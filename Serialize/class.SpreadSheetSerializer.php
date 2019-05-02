<?php
namespace Serialize;

abstract class SpreadSheetSerializer extends Serializer
{
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

    protected function addValueByColName(&$cols, &$row, $colName, $value)
    {
        if(is_object($value))
        {
            switch($colName)
            {
                case '_id':
                    if(isset($value->{'$id'}))
                    {
                        $this->addValueByColName($cols, $row, $colName, $value->{'$id'});
                        break;
                    }
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
            if(isset($value[0]) && is_object($value[0]))
            {
                $count = count($value);
                for($i = 0; $i < $count; $i++)
                {
                    $this->addValueByColName($cols, $row, $colName.'['.$i.']', $value[$i]);
                }
            }
            else
            {
                $row[$index] = implode(',', $value);
            }
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

<?php
namespace Flipside\Serialize;

class JsonSpreadSheet extends SpreadSheetSerializer
{
    protected $types = array('json-ss', 'json-ss-dt');

    public function serializeData(&$type, $array)
    {
        if($this->supportsType($type) === false)
        {
            return null;
        }
        $dataTable = ($type === 'json-ss-dt');
        $type = 'application/json';
        $data = $this->getArray($array);
        $names = array_shift($data);
        $rowCount = count($data);
        for($i = 0; $i < $rowCount; $i++)
        {
            $row = $data[$i];
            $colCount = count($row);
            for($j = 0; $j < $colCount; $j++)
            {
                if(isset($row[$j]) && isset($names[$j]))
                {
                    $row[$names[$j]] = $row[$j];
                    unset($row[$j]);
                }
            }
            $data[$i] = $row;
        }
        if($dataTable)
        {
            return json_encode(array('data'=>$data));
        }
        return json_encode($data);
    }
}

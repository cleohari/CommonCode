<?php
namespace Serialize;

class CSVSerializer extends SpreadSheetSerializer
{
    protected $types = array('csv', 'text/csv');

    public function serializeData(&$type, $array)
    {
        if($this->supportsType($type) === false)
        {
            return null;
        }
        $type = 'text/csv';
        $data = $this->getArray($array);
        if(count($data) === 0)
        {
            return null;
        }
        ob_start();
        $df = fopen('php://output', 'w');
        foreach($data as $row)
        {
            if(!is_array($row))
            {
                $row = array($row);
            }
            fputcsv($df, $row);
        }
        fclose($df);
        $ret = ob_get_clean();
        return $ret;
    }
}

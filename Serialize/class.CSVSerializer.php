<?php
namespace Serialize;

class CSVSerializer extends SpreadSheetSerializer
{
    protected $types = array('csv', 'text/csv');

    public function serializeData($type, $array)
    {
        if($this->supportsType($type) === false)
        {
            return null;
        }
        if(count($array) === 0)
        {
            return null;
        }
        $data = $this->getArray($array);
        ob_start();
        $df = fopen("php://output", 'w');
        foreach($data as $row)
        {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }
}
?>

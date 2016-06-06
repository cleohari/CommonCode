<?php
namespace Serialize;

require_once dirname(__FILE__).'/../libs/PHPExcel/Classes/PHPExcel.php';

class ExcelSerializer extends SpreadSheetSerializer
{
    protected $types = array('xlsx', 'xls', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel');

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
        $ssheat = new \PHPExcel();
        $sheat = $ssheat->setActiveSheetIndex(0);
        $keys = array_shift($data);
        $rowCount = count($data);
        $colCount = count($keys);
        for($i = 0; $i < $colCount; $i++)
        {
            $sheat->setCellValueByColumnAndRow($i, 1, $keys[$i]);
        }
        for($i = 0; $i < $rowCount; $i++)
        {
            for($j = 0; $j < $colCount; $j++)
            {
                $colName = $keys[$j];
                $sheat->setCellValueByColumnAndRow($j, (2 + $i), $data[$i][$j]);
            }
        }
        if(strcasecmp($type, 'xlsx') === 0 || strcasecmp($type, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') === 0)
        {
            $writer = \PHPExcel_IOFactory::createWriter($ssheat, 'Excel2007');
            ob_start();
            $writer->save('php://output');
            return ob_get_clean();
        }
        else
        {
            $writer = \PHPExcel_IOFactory::createWriter($ssheat, 'Excel5');
            ob_start();
            $writer->save('php://output');
            return ob_get_clean();
        }
    }
}
?>

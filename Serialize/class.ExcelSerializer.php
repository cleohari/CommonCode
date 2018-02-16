<?php
namespace Serialize;
require_once dirname(__FILE__).'/../vendor/autoload.php';

class ExcelSerializer extends SpreadSheetSerializer
{
    protected $types = array('xlsx', 'xls', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel');

    protected function setRowFromArray(&$sheat, $row, $array, $count = 0)
    {
        if($count === 0)
        {
            $count = count($array);
        }
        for($i = 0; $i < $count; $i++)
        {
            if(isset($array[$i]))
            {
                $sheat->setCellValueByColumnAndRow($i, $row, $array[$i]);
            }
        }
    }

    public function serializeData(&$type, $array)
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
        $this->setRowFromArray($sheat, 1, $keys, $colCount);
        for($i = 0; $i < $rowCount; $i++)
        {
            $this->setRowFromArray($sheat, (2 + $i), $data[$i], $colCount);
        }
        $writerType = 'Excel5';
        if(strcasecmp($type, 'xlsx') === 0 || strcasecmp($type, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') === 0)
        {
            $writerType = 'Excel2007';
            $type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }
        else
        {
            $type = 'application/vnd.ms-excel';
        }
        $writer = \PHPExcel_IOFactory::createWriter($ssheat, $writerType);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }
}

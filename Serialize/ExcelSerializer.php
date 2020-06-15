<?php
namespace Flipside\Serialize;
require_once dirname(__FILE__).'/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

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
                $sheat->setCellValueByColumnAndRow($i+1, $row, $array[$i]);
            }
        }
    }

    public function serializeData(&$type, $array)
    {
        if($this->supportsType($type) === false)
        {
            return null;
        }
	$data = $this->getArray($array);
        $ssheat = new Spreadsheet();
        $sheat = $ssheat->getActiveSheet();
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
            $writer = new Xlsx($ssheat);
            $writerType = 'Excel2007';
            $type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }
        else
        {
            $writer = new Xls($ssheat);
            $type = 'application/vnd.ms-excel';
        }
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }
}

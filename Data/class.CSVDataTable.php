<?php
namespace Data;

class CSVDataTable extends DataTable
{
    /**
     * @param string $file The csv file
     */
    public function __construct($file)
    {
        $this->data = array_map('str_getcsv', file($file));
	$titles = array_shift($this->data);
	$count = count($this->data);
	for($i = 0; $i < $count; $i++)
	{
            $this->data[$i] = array_combine($titles, $this->data[$i]);
	}
    }

    public function count($filter = false)
    {
        if($filter)
	{
            $res = $this->data;
	    $res = $filter->filter_array($res);
	    return count($res);
	}
	return count($this->data);
    }
  
    public function read($filter = false, $select = false, $count = false, $skip = false, $sort = false, $params = false)
    {
        $res = $this->data;
	if($filter !== false)
	{
            $res = $filter->filter_array($res);
	}
	return $res;
    }

    public function create($data)
    {
        throw new \Exception('CSVDataTable is read only');
    }

    public function update($filter, $data)
    {
        throw new \Exception('CSVDataTable is read only');
    }

    public function delete($filter)
    {
        throw new \Exception('CSVDataTable is read only');
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

<?php
namespace Data;

abstract class DataTable
{
    public abstract function count($filter = false);

    /**
     * A funtion to read data from the table
     *
     * @param boolean|\Data\Filter $filter The filter to use while searching the table
     * @param boolean|array $select The array of properties to read
     * @param boolean|integer $count The number of records to read
     * @param boolean|integer $skip The number of records to skip
     * @param boolean|array $sort The properties to sort on
     * @param boolean|mixed $params The parameters to pass to the search engine
     *
     * @return boolean|array The data read from the table
     */
    public abstract function search($filter = false, $select = false, $count = false, $skip = false, $sort = false, $params = false);

    public abstract function create($data);

    /**
     * Wrapper around search
     *
     * @param boolean|\Data\Filter $filter The filter to use while searching the table
     * @param boolean|array $select The array of properties to read
     * @param boolean|integer $count The number of records to read
     * @param boolean|integer $skip The number of records to skip
     * @param boolean|array $sort The properties to sort on
     * @param boolean|mixed $params The parameters to pass to the search engine
     *
     * @return boolean|array The data read from the table
     */
    public function read($filter = false, $select = false, $count = false, $skip = false, $sort = false, $params = false)
    {
        return $this->search($filter, $select, $count, $skip, $sort, $params);
    }

    public abstract function update($filter, $data);

    public abstract function delete($filter);
}

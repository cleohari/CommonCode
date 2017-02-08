<?php
/**
 * DataSetFactory class
 *
 * This file describes the static DataSetFactory class
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

/**
 * Allow other classes to be loaded as needed
 */
require_once('Autoload.php');

/**
 * A static class allowing the caller to easily obtain \Data\DataSet object instances
 *
 * This class will utilize the Settings class to determine who to construct the \Data\DataSet object requested by the caller
 */
class DataSetFactory
{
    /**
     * Obtain the \Data\DataSet given the name of the dataset used in the settings
     *
     * @param string $setName The name of the DataSet used in the Settings
     *
     * @return \Data\DataSet The DataSet specified
     */
    public static function getDataSetByName($setName)
    {
        static $instances = array();
        if(isset($instances[$setName]))
        {
            return $instances[$setName];
        }
        $settings = \Settings::getInstance();
        $setData = $settings->getDataSetData($setName);
        if($setData === false)
        {
            throw new Exception('Unknown dataset name '.$setName);
        }
        $class_name = '\\Data\\'.$setData['type'];
        $obj = new $class_name($setData['params']);
        $instances[$setName] = $obj;
        return $obj;
    }

    /**
     * Obtain the \Data\DataTable given the name of the dataset used in the settings and the name of the table
     *
     * @param string $dataSetName The name of the DataSet used in the Settings
     * @param string $dataTableName The name of the DataTable
     *
     * @return \Data\DataTable The DataTable specified
     */
    public static function getDataTableByNames($dataSetName, $dataTableName)
    {
        $dataSet = self::getDataSetByName($dataSetName);
        return $dataSet[$dataTableName];
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

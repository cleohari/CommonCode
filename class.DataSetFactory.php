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
 * use the FlipsideSettings class
 */
if(isset($GLOBALS['FLIPSIDE_SETTINGS_LOC']))
{
    require_once($GLOBALS['FLIPSIDE_SETTINGS_LOC'].'/class.FlipsideSettings.php');
}
else
{
    require_once('/var/www/secure_settings/class.FlipsideSettings.php');
}

/**
 * Allow other classes to be loaded as needed
 */
require_once('Autoload.php');

/**
 * A static class allowing the caller to easily obtain \Data\DataSet object instances
 *
 * This class will utilize the FlipsideSettings class to determine who to construct the \Data\DataSet object requested by the caller
 */
class DataSetFactory
{
    /**
     * Obtain the \Data\DataSet given the name of the dataset used in FlipsideSettings
     *
     * @param string $setName The name of the DataSet used in FlipsideSettings
     *
     * @return \Data\DataSet The DataSet specified
     *
     * @deprecated 2.0.0 Utilize the getDataSetByName() instead
     */
    static function get_data_set($setName)
    {
        return static::getDataSetByName($setName);
    }

    /**
     * Obtain the \Data\DataSet given the name of the dataset used in FlipsideSettings
     *
     * @param string $setName The name of the DataSet used in FlipsideSettings
     *
     * @return \Data\DataSet The DataSet specified
     */
    static function getDataSetByName($setName)
    {
        static $instances = array();
        if(isset($instances[$setName]))
        {
            return $instances[$setName];
        }
        if(!isset(FlipsideSettings::$dataset) || !isset(FlipsideSettings::$dataset[$setName]))
        {
            throw new Exception('Unknown dataset name '.$setName);
        }
        $set_data = FlipsideSettings::$dataset[$setName];
        $class_name = '\\Data\\'.$set_data['type'];
        $obj = new $class_name($set_data['params']);
        $instances[$setName] = $obj;
        return $obj;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

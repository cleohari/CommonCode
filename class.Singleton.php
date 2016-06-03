<?php
/**
 * Singleton class
 *
 * This file describes the Singleton parent class
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

/**
 * A class that only allows a single instance to be created.
 *
 * This class only allows a single instance to be created. This is especially useful for 
 * database and other abstractions to reduce the number of connections. 
 */
class Singleton
{
    /**
     * Return the instance of the object
     *
     * This function returns the object instance if it exists and if not it will create a new copy
     */
    public static function getInstance()
    {
        static $instances = array();
        $class = get_called_class();
        if(!isset($instances[$class]))
        {
            $instances[$class] = new static();
        }
        return $instances[$class];
    }

    /**
     * A singleton constructor should not be publically accessible. All callers should use getInstance()
     */
    protected function __construct()
    {
    }

    /**
     * A singleton can not be cloned
     */
    private function __clone()
    {
    }

    /**
     * A singleton can not be serialized and deserialized
     * 
     * @SuppressWarnings("UnusedPrivateMethod")
     */
    private function __wakeup()
    {
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

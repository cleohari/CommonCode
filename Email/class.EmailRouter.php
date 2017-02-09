<?php
/**
 * Email Router class
 *
 * This file describes the Email Router class
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2016, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

namespace Email;

/**
 * An class to represent an Email Router
 */
class EmailRouter extends \Singleton
{
    protected function __construct()
    {
    }

    public function routeSingle($destination, $rawMessage)
    {
        $route = $this->getRouteFromDestination($destination);
        if($route === false || $route === null)
        {
            return false;
        }
        return $route->route($destination, $rawMessage);
    }

    public function routeMany($destinations, $rawMessage)
    {
        $ret = array();
        $tmp = false;
        $error = false;
        foreach($destinations as $dest)
        {
            $tmp = $this->routeSingle($dest, $rawMessage);
            $ret[] = $tmp;
            if($tmp === false)
            {
                $error = true;
            }
        }
        if($error === true)
        {
            return $ret;
        }
        return true;
    }

    public function getRouteFromDestination($destination)
    {
        $dataSet = \DataSetFactory::get_data_set('email');
        $dataTable = $dataSet['Routes'];
        if(!(preg_match('/(.*)@(.*)/', $destination, $parts)))
        {
            return false;
        }
        $routeData = $dataTable->read(array('pattern'=>array('$regex'=>new \MongoRegex('/'.$parts[1].'@/i'))));
        if($routeData === false)
        {
            return false;
        }
        $count = count($routeData);
        if($count === 0)
        {
            return false;
        }
        else if($count > 1)
        {
            //TODO Best Fit
            throw new \Exception('Multiple Routes match! Not implemented!');
        }
        $routeData = $routeData[0];
        $type = $routeData['type'];
        if(isset($routeData['data']))
        {
            return new $type($routeData['data']);
        }
        else
        {
            return new $type();
        }
        return false;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

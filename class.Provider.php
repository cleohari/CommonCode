<?php

class Provider extends Singleton
{
    /** The methods loaded by the provider */
    protected $methods;

    /**
     * Get the method object corresponding to the name
     *
     * @param string $methodName The class name of the method to get the instance for
     *
     * @return boolean|stdClass The specified method class instance or false if it is not loaded
     */
    public function getMethodByName($methodName)
    {
        $count = count($this->methods);
        for($i = 0; $i < $count; $i++)
        {
            if(strcasecmp(get_class($this->methods[$i]), $methodName) === 0)
            {
                return $this->methods[$i];
            }
        }
        return false;
    }

    /**
     * Calls the indicated function on each method and add the result
     *
     * @param string $functionName The function to call
     * @param string $checkField A field to check if it is set a certain way before calling the function
     * @param mixed $checkValue The value that field should be set to to not call the function
     *
     * @return integer The added returnValue
     */
    protected function addFromEach($functionName, $checkField = false, $checkValue = false)
    {
        $retCount = 0;
        $count = count($this->methods);
        for($i = 0; $i < $count; $i++)
        {
            if($checkField !== false)
            {
                if($this->methods[$i]->{$checkField} === $checkValue)
                {
                    continue;
                }
            }
            $res = call_user_func(array($this->methods[$i], $functionName));
            $retCount += $res;
        }
        return $retCount;
    }

    /**
     * Calls the indicated function on each method
     *
     * @param string $functionName The function to call
     * @param array $args The arguments for the function
     * @param boolean|string $checkField A field to check if it is set a certain way before calling the function
     * @param mixed $checkValue The value that field should be set to to not call the function
     * @param callable $resFunction Function to call on the result, otherwise the function will return on the first non-false result
     *
     * @return Auth\Group|Auth\User|false The merged returnValue
     */
    protected function callOnEach($functionName, $args, $checkField = false, $checkValue = false, $resFunction = null)
    {
        $ret = false;
        $count = count($this->methods);
        for($i = 0; $i < $count; $i++)
        {
            if($checkField !== false)
            {
                if($this->methods[$i]->{$checkField} === $checkValue)
                {
                    continue;
                }
            }
            $res = call_user_func_array(array($this->methods[$i], $functionName), $args);
            if($resFunction !== null)
            {
                call_user_func($resFunction, $ret, $res);
                continue;
            }
            if($res !== false)
            {
                return $res;
            }
        }
        return $ret;
    }

    /**
     * Calls the indicated function on the specified method or all methods if false
     *
     * @param string|boolean $methodName The method to call the function on, or false to call on all functions
     * @param string $functionName The function to call
     * @param array $args The arguments for the function
     * @param boolean|string $checkField A field to check if it is set a certain way before calling the function
     * @param mixed $checkValue The value that field should be set to to not call the function
     * @param callable $resFunction Function to call on the result, otherwise the function will return on the first non-false result
     *
     * @return mixed The return value
     */
    protected function callFunction($methodName, $functionName, $args, $checkField = false, $checkValue = false, $resFunction = null)
    {
        if($methodName === false)
        {
            return $this->callOnEach($functionName, $args, $checkField, $checkValue, $resFunction);
        }
        if(is_string($methodName))
        {
            $method = $this->getMethodByName($methodName);
            return call_user_func_array(array($method, $functionName), $args);
        }
        return false;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

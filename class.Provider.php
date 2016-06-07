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
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

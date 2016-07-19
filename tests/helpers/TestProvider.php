<?php

class TestProvider extends Provider
{
    public function __construct($method)
    {
        $this->methods = array($method);
    }

    public function expectTrue($methodName)
    {
        return $this->callFunction($methodName, 'expectTrue', array());
    }

    public function expectFalse($methodName)
    {
        return $this->callFunction($methodName, 'expectFalse', array());
    }
}

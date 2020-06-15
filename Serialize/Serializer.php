<?php
namespace Flipside\Serialize;

abstract class Serializer implements ISerializer
{
    protected $types;

    public function supportsType($type)
    {
        foreach($this->types as $t)
        {
            if(strcasecmp($t, $type) === 0)
            {
                return true;
            }
        }
        return false;
    }
}

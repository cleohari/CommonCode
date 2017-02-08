<?php
namespace Serialize;

class JSONSerializer extends Serializer
{
    protected $types = array('json', 'application/json', 'application/x-javascript', 'text/javascript', 'text/x-javascript', 'text/x-json');

    public function serializeData($type, $array)
    {
        if($this->supportsType($type) === false)
        {
            return null;
        }
        return json_encode($array);
    }
}

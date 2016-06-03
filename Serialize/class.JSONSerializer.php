<?php
namespace Serialize;

class JSONSerializer implements ISerializer
{
    private $types = array('json', 'application/json', 'application/x-javascript', 'text/javascript', 'text/x-javascript', 'text/x-json');

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
 
    public function serializeData($type, $array)
    {
        if($this->supportsType($type) === false)
        {
            return null;
        }
        return json_encode($array);
    }
}
?>

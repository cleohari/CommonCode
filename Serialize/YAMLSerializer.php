<?php
namespace Flipside\Serialize;

use Symfony\Component\Yaml\Yaml;

class YAMLSerializer extends Serializer
{
    protected $types = array('yaml', 'application/x-yaml', 'text/x-yaml');

    public function serializeData(&$type, $array)
    {
        if($this->supportsType($type) === false)
        {
            return null;
        }
        $type = 'text/x-yaml';
        return Yaml::dump($array, 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
    }
}

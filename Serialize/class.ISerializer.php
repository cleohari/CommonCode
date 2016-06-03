<?php
namespace Serialize;

interface ISerializer
{
    /**
     * Does this serializer support this type for serialization?
     * 
     * @param string $type The mimetype to serialize to
     * 
     * @return boolean
     */
    public function supportsType($type);
    
    /**
     * Serialize the data into a byte string to be returned to the client
     * 
     * @param string $type The mimetype to serialize to
     * @param array $array The data to serialize
     * 
     * @return null|string
     */
    public function serializeData($type, $array);
}
?>

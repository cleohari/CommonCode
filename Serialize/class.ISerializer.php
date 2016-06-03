<?php
namespace Serialize;

interface ISerializer
{
    public function supportsType($type);
    public function serializeData($type, $array);
}
?>

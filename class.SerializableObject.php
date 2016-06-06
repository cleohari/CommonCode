<?php
/**
 * An easily serializable class
 *
 * This file describes a serializable object
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

/**
 * An object that can be serialized and accessed as an array.
 *
 * This class can be serialized to various formats
 */
class SerializableObject implements ArrayAccess, JsonSerializable
{
    /**
     * Create the object from an array
     *
     * @param array $array The array of object properties
     */
    public function __construct($array = false)
    {
        if($array !== false)
        {
            if(is_object($array))
            {
                $array = get_object_vars($array);
            }
            if(is_array($array))
            {
                foreach($array as $key => $value)
                {
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * Serialize the object into a format consumable by json_encode
     *
     * @return mixed The object in a more serialized format
     */
    public function jsonSerialize()
    {
        return $this;
    }

    /**
     * Convert the object into an XML string
     *
     * @return string The XML format of the object
     */
    public function xmlSerialize()
    {
        $xml = new XmlWriter();
        $xml->openMemory();
        $xml->startDocument('1.0');
        if(version_compare(PHP_VERSION, '7.0.0', '>='))
        {
            $this->php7XmlSerialize($xml);
        }
        else
        {
            $this->oldPhpSerialize($xml);
        }
        $xml->endElement();
        return $xml->outputMemory(true);
    }

    private function php7XmlSerialize(XMLWriter $xml)
    {
        if(isset($this[0]))
        {
            $xml->startElement('Array');
            $this->array2XML($xml, 'Entity', get_object_vars($this));
            $xml->endElement();
        }
        else
        {
            $this->object2XML($xml, $this);
        }
    }

    private function oldPhpSerialize(XMLWriter $xml)
    {
        $tmp = json_decode(json_encode($this), false);
        $tmpA = get_object_vars($tmp);
        if(isset($tmpA[0]))
        {
            $xml->startElement('Array');
            $this->array2XML($xml, 'Entity', $tmpA);
            $xml->endElement();
        }
        else
        {
            $this->object2XML($xml, $tmp);
        }
    }

    /**
     * Convert an object to XML without document tags
     *
     * @param XmlWriter $xml The XMLWriter to write the object to
     * @param mixed $data The data to serialze to XML
     */
    private function object2XML(XMLWriter $xml, $data)
    {
        foreach($data as $key => $value)
        {
            if(is_array($value) || is_numeric($key))
            {
                $this->array2XML($xml, $key, (array)$value);
            }
            else if(is_object($value))
            {
                $xml->startElement($key);
                $this->object2XML($xml, $value);
                $xml->endElement();
            }
            else
            {
                if($key[0] === '$')
                {
                    $xml->writeElement(substr($key, 1), $value);
                }
                else
                {
                    $key = strtr($key, array(' '=>'', ','=>''));
                    $xml->writeElement($key, $value);
                }
            }
        }
    }

    /**
     * Determine if an array has any string keys
     *
     * @param array $array The array to test
     * 
     * @return boolean True if the array has string keys, false otherwise
     */
    private function arrayHasStringKeys(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * Convert an array to XML without document tags
     *
     * @param XmlWriter $xml The XMLWriter to write the object to
     * @param string $keyParent The key of the parent object
     * @param mixed $data The data to serialze to XML
     */
    private function array2XML(XMLWriter $xml, $keyParent, $data)
    {
        $data = array_values($data);
        $count = count($data);
        for($i = 0; $i < $count; $i++)
        {
            $value = $data[$i];
            if(is_array($value) && isset($value[0]))
            {
                $xml->startElement($keyParent);
                $this->array2XML($xml, 'Child', $value);
                $xml->endElement();
            }
            else if(is_array($value) && $this->arrayHasStringKeys($value))
            {
                $xml->startElement($keyParent);
                $this->object2XML($xml, $value);
                $xml->endElement();
            }
            else if(is_object($value))
            {
                $xml->startElement($keyParent);
                $this->object2XML($xml, $value);
                $xml->endElement();
            }
            else
            {
                $xml->writeElement($keyParent, $value);
            }
        }
    }

    /**
     * Convert json back to an object
     *
     * @param string $json The JSON string to deserialize back into an object
     *
     * @return SerializableObject The object the json deserializes into 
     */
    public static function jsonDeserialize($json)
    {
        $array = json_decode($json, true);
        return new self($array);
    }

    /**
     * Convert the object to a serizlized string
     *
     * @param string $fmt The format to serialize into
     * @param array|false $select Which fields to include
     *
     * @return string The object in string format
     */
    public function serializeObject($fmt = 'json', $select = false)
    {
        $copy = $this;
        if($select !== false)
        {
            $copy = new self();
            $count = count($select);
            for($i = 0; $i < $count; $i++)
            {
                $copy->{$select[$i]} = $this->offsetGet($select[$i]);
            }
        }
        switch($fmt)
        {
            case 'json':
                return json_encode($copy);
            default:
                throw new Exception('Unsupported fmt '.$fmt);
        }
    }

    /**
     * Function to allow the caller to set a value in the object via object[offset] = value
     *
     * @param string $offset The key to set
     * @param mixed $value The value for the key
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /**
     * Function to allow the caller to determin if a value in the object is set
     *
     * @param string $offset The key to determine if it has a value
     *
     * @return boolean Does the key have a value?
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * Function to allow the caller to delete the value in the object for a key
     *
     * @param string $offset The key to unset
     */
    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }

    /**
     * Function to allow the caller to obtain the value for a key
     *
     * @param string $offset The key to return the value for
     *
     * @return mixed the value in the key
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

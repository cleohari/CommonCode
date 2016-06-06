<?php
/**
* Flipside Common Code Autoload Function
*
* Autoload Flipside Common code Classes with the syntax Namespace/class.Classname.php
*
* @author Patrick Boyd / problem@burningflipside.com
* @copyright Copyright (c) 2015, Austin Artistic Reconstruction
* @license http://www.apache.org/licenses/ Apache 2.0 License
*/

/**
* Flipside Common Code Autoload Function
*
* Autoload Flipside Common code Classes with the syntax Namespace/class.Classname.php
*
* @param string $classname The class name with the namespace to load
*/
function FlipsideAutoload($classname)
{
    $classname = str_replace('/', '\\', $classname);
    $classname = ltrim($classname, '\\');
    $filename  = '';
    $namespace = '';
    if($lastNsPos = strrpos($classname, '\\'))
    {
        $namespace = substr($classname, 0, $lastNsPos);
        $classname = substr($classname, $lastNsPos + 1);
        $filename  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
    }
    $filename = __DIR__.DIRECTORY_SEPARATOR.$filename.'class.'.$classname.'.php';
    if(is_readable($filename))
    {
        require $filename;
    }
}

if(version_compare(PHP_VERSION, '5.3.0', '>='))
{
    spl_autoload_register('FlipsideAutoload', true, true);
}
else
{
    spl_autoload_register('FlipsideAutoload');
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

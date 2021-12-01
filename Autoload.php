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
if(file_exists(__DIR__ . '/vendor/autoload.php'))
{
    require(__DIR__ . '/vendor/autoload.php');
}
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'Flipside\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

/* vim: set tabstop=4 shiftwidth=4 expandtab: */

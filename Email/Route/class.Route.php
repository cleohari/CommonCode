<?php
/*
 * Router class
 *
 * This file describes the Abstract base class for all email routers
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2016, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

namespace Email\Route;

/**
 * An class to represent an Email Router
 */
abstract class Route
{
    protected $data;

    public function __construct($data = false)
    {
        $this->data = $data;
    }

    public abstract function route($destination, $message);
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

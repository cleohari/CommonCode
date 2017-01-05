<?php
namespace Email\Route;

/**
 * An class to represent an Email Router
 */
class SimpleAlias extends Route
{
    public function route($destination, $message)
    {
        $alias = $this->data['aliasTo'];
        return false;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

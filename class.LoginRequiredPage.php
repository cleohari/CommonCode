<?php
/**
 * A WebPage class that requires login to view
 *
 * This file describes an abstraction for creating a webpage with JQuery, Bootstrap,
 * and other framework specific abilities that requires a login
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2017, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

/**
 * We need the parent class
 */
require_once('class.FlipPage.php');

/**
 * A webpage abstraction for login required pages
 *
 * This class adds a login requirement to FlipPage
 */
class LoginRequiredPage extends FlipPage
{
    public function printPage($header = true)
    {
        if($this->user === false || $this->user === null)
        {
            $this->body = '
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">You must <a href="'.$this->loginUrl.'?return='.$this->currentUrl().'">log in <span class="glyphicon glyphicon-log-in"></span></a> to access the '.$this->title.' system!</h1>
            </div>
        </div>';
        }
        parent::printPage($header);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

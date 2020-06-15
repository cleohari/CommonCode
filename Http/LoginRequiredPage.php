<?php
namespace Flipside\Http;
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
 * A webpage abstraction for login required pages
 *
 * This class adds a login requirement to FlipPage
 */
class LoginRequiredPage extends WebPage
{
    protected function getContent()
    {
        if($this->user === false || $this->user === null)
        {
          $this->content['body'] = '
            <div id="content">
              <div class="row">
                <div class="col-lg-12">
                  <h1 class="page-header">You must <a href="'.$this->loginUrl.'?return='.$this->currentUrl().'">log in <span class="fa fa-sign-in-alt"></span></a> to access the '.$this->content['pageTitle'].' system!</h1>
                </div>
              </div>
            </div>
          ';
        }
        else if(!isset($this->content['body']))
        {
          $this->content['body'] = $this->body;
        }
        //Add page JS just before rednering so it is after any added by the page explicitly
        $this->addJS('js/'.basename($_SERVER['SCRIPT_NAME'], '.php').'.js');
        return $this->twig->render($this->templateName, $this->content);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

<?php
namespace Http;
class FlipAdminPage extends LoginRequiredPage
{
    public $is_admin = false;

    public function __construct($title, $adminGroup = 'LDAPAdmins')
    {
        parent::__construct($title);
        $this->is_admin = $this->userIsAdmin($adminGroup);
        $this->setTemplateName('admin.html');
    }

    protected function userIsAdmin($adminGroup)
    {
        if($this->user === false || $this->user === null)
        {
            return false;
        }
        return $this->user->isInGroupNamed($adminGroup);
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    const CARD_GREEN  = 'green';
    const CARD_BLUE   = 'blue';
    const CARD_YELLOW = 'yellow';
    const CARD_RED    = 'red';

    public function addCard($icon, $text, $link, $color = false)
    {
        if(!isset($this->content['cards']))
        {
            $this->content['cards'] = array();
        }
        $card = array('icon' => $icon, 'text' => $text, 'link' => $link);
        if($color !== false)
        {
            $card['color'] = $color;
        }
        array_push($this->content['cards'], $card);
    }

    protected function getContent()
    {
        $this->addLink('Home', '..');
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
        else if($this->isAdmin() === false)
        {
          $this->content['body'] = '
            <div id="content">
              <div class="row">
                <div class="col-lg-12">
                  <h1 class="page-header">You must be an administrator to access the '.$this->content['pageTitle'].' system!</h1>
                </div>
              </div>
            </div>
          ';
          $this->content['header'] = array();
          $this->content['cards'] = array();
        }
        return parent::getContent();
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

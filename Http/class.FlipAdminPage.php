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

    protected function getContent()
    {
        if($this->isAdmin() === false)
        {
            $this->body = '
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">The current user does not have access rights to the '.$this->content['pageTitle'].' Admin system!</h1>
            </div>
        </div>';
        }
        return parent::getContent();
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
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

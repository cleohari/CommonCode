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
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

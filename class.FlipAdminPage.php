<?php
require_once('class.LoginRequiredPage.php');

class FlipAdminPage extends LoginRequiredPage
{
    public $user;
    public $is_admin = false;

    public function __construct($title, $adminGroup = 'LDAPAdmins')
    {
        $this->user = FlipSession::getUser();
        $this->is_admin = $this->userIsAdmin($adminGroup);
        parent::__construct($title);
        $adminCSS = '/css/common/admin.min.css';
        if($this->minified !== 'min')
        {
            $adminCSS = '/css/common/admin.css';
        }
        $this->addCSSByURI($adminCSS);
        $this->addWellKnownJS(JS_METISMENU, false);
    }

    protected function userIsAdmin($adminGroup)
    {
        if($this->user === false || $this->user === null)
        {
            return false;
        }
        return $this->user->isInGroupNamed($adminGroup);
    }

    protected function addAllLinks()
    {
        if($this->user === false || $this->user === null)
        {
            $this->addLink('<i class="fa fa-sign-in"></i> Login', $this->loginUrl);
        }
        else
        {
            $this->add_links();
            $this->addLink('<i class="fa fa-sign-out"></i> Logout', $this->logoutUrl);
        }
    }

    protected function getDropdown($link, $name)
    {
        $ret  = '<li>';
        $href = $this->getHrefForDropdown($link);
        $ret .= $this->createLink($name.' <i class="fa fa-arrow-right"></i>', $href);
        $ret .= '<ul>';
        $subNames = array_keys($link);
        foreach($subNames as $subName)
        {
            $ret .= $this->getLinkByName($subName, $link);
        }
        $ret .= '</ul></li>';
        return $ret;
    }

    protected function addHeader()
    {
        $sites   = $this->getSiteLinksForHeader();
        $sideNav = $this->getLinksMenus();
        $log     = '<a href="'.$this->logoutUrl.'"><i class="fa fa-sign-out"></i></a>';
        if($this->user === false || $this->user === null)
        {
            $log = '<a href="'.$this->loginUrl.'?return='.$this->currentUrl().'"><i class="fa fa-sign-in"></i></a>';
        }
        $this->body = '<div id="wrapper">
                  <nav class="navbar navbar-expand-lg navbar-light bg-light" role="navigation" style="margin-bottom: 0">
                      <a class="navbar-brand" href="index.php">'.$this->title.'</a>
                      <ul class="nav navbar-top-links navbar-right">
                          <a href="..">
                              <i class="fa fa-home"></i>
                          </a>
                          &nbsp;&nbsp;'.$log.'
                          <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                  <i class="fa fa-link"></i>
                                  <b class="caret"></b>
                              </a>
                              <ul class="dropdown-menu dropdown-sites">
                                  '.$sites.'
                              </ul>
                          </li>
                      </ul>
                      <div class="navbar-default sidebar" role="navigation">
                          <div class="sidebar-nav navbar-collapse" style="height: 1px;">
                              <ul class="nav" id="side-menu">
                                  '.$sideNav.'
                              </ul>
                          </div>
                      </div>
                  </nav>
                  <div id="page-wrapper" style="min-height: 538px;">'.$this->body.'</div></div>';
    }

    const CARD_GREEN  = 'bg-success';
    const CARD_BLUE   = 'bg-primary';
    const CARD_YELLOW = 'bg-warning';
    const CARD_RED    = 'bg-danger';

    public function add_card($iconName, $bigText, $littleText, $link = '#', $color = self::CARD_BLUE)
    {
        $card = '<div class="col-lg-3 col-md-6 text-white">
                     <div class="card '.$color.'">
                         <div class="card-header">
                             <div class="row">
                                 <div class="col-xs-3">
                                     <i class="fa '.$iconName.'" style="font-size: 5em;"></i>
                                 </div>
                                 <div class="col-xs-9 text-right">
                                     <div style="font-size: 40px;">'.$bigText.'</div>
                                     <div>'.$littleText.'</div>
                                 </div>
                             </div>
                         </div>
                         <a class="text-white card-link" href="'.$link.'">
                         <div class="card-footer">
                             <span class="pull-left">View Details</span>
                             <span class="pull-right fa fa-arrow-circle-right"></span>
                             <div class="clearfix"></div>
                         </div>
                         </a>
                     </div>
                 </div>';
        $this->body .= $card;
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function printPage($header = true)
    {
        if($this->isAdmin() === false)
        {
            $this->body = '
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">The current user does not have access rights to the '.$this->title.' Admin system!</h1>
            </div>
        </div>';
        }
        parent::printPage($header);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

<?php
require_once('class.FlipPage.php');

class FlipAdminPage extends FlipPage
{
    public $user;
    public $is_admin = false;

    function __construct($title, $adminGroup = 'LDAPAdmins')
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

    function addAllLinks()
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

    function addHeader()
    {
        $sites   = $this->getSiteLinksForHeader();
        $sideNav = $this->getLinksMenus();
        $log     = '<a href="https://profiles.burningflipside.com/logout.php"><i class="fa fa-sign-out"></i></a>';
        if($this->user === false || $this->user === null)
        {
            $log = '<a href="https://profiles.burningflipside.com/login.php?return='.$this->currentUrl().'"><i class="fa fa-sign-in"></i></a>';
        }
        $this->body = '<div id="wrapper">
                  <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
                      <div class="navbar-header">
                          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                              <span class="sr-only">Toggle Navigation</span>
                              <span class="icon-bar"></span>
                              <span class="icon-bar"></span>
                              <span class="icon-bar"></span>
                          </button>
                          <a class="navbar-brand" href="index.php">'.$this->title.'</a>
                      </div>
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

    const CARD_GREEN  = 'panel-green';
    const CARD_BLUE   = 'panel-primary';
    const CARD_YELLOW = 'panel-yellow';
    const CARD_RED    = 'panel-red';

    function add_card($iconName, $bigText, $littleText, $link = '#', $color = self::CARD_BLUE, $textColor = false)
    {
        $card = '<div class="col-lg-3 col-md-6">
                     <div class="panel '.$color.'">
                         <div class="panel-heading">
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
                         <a href="'.$link.'">
                         <div class="panel-footer">
                             <span class="pull-left">View Details</span>
                             <span class="pull-right fa fa-arrow-circle-right"></span>
                             <div class="clearfix"></div>
                         </div>
                         </a>
                     </div>
                 </div>';
        $this->body .= $card;
    }

    function printPage($header = true)
    {
        if($this->user === false || $this->user === null)
        {
            $this->body = '
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">You must <a href="'.$this->loginUrl.'?return='.$this->currentUrl().'">log in <span class="glyphicon glyphicon-log-in"></span></a> to access the '.$this->title.' Admin system!</h1>
            </div>
        </div>';
        }
        else if($this->is_admin === false)
        {
            $this->body = '
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">The current user does not have access rights to the '.$this->title.' Admin system!</h1>
            </div>
        </div>';
        }
        parent::printPage();
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

<?php
namespace Http\Html;

define('JS_JQUERY', 0);
define('JS_JQUERY_UI', 1);
define('JS_BOOTSTRAP', 2);
define('JQUERY_VALIDATE', 3);
define('JQUERY_TOUCH', 4);
define('JS_TINYNAV', 5);
define('JS_BOOTSTRAP_FH', 6);
define('JS_BOOTSTRAP_SW', 7);
define('JS_DATATABLE', 8);
define('JS_CHART', 9);
define('JS_METISMENU', 10);
define('JS_BOOTBOX', 11);
define('JS_DATATABLE_ODATA', 12);
define('JS_CRYPTO_MD5_JS', 13);
define('JS_JCROP', 14);
define('JS_TYPEAHEAD', 15);
define('JS_CHEET', 16);
define('JS_FLIPSIDE', 20);
define('JS_LOGIN', 21);
define('CSS_JQUERY_UI', 0);
define('CSS_BOOTSTRAP', 1);
define('CSS_BOOTSTRAP_FH', 2);
define('CSS_BOOTSTRAP_SW', 3);
define('CSS_DATATABLE', 4);
define('CSS_JCROP', 5);
define('CSS_FONTAWESOME', 6);
global $jsArray;
$jsArray = array(
        JS_JQUERY => array(
            'no' => array(
                'no'  => '/js/common/jquery.js',
                'min' => '/js/common/jquery.min.js'
            ),
            'cdn' => array(
                'no'  => '//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.js',
                'min' => '//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js'
            )
        ),
        JS_JQUERY_UI => array(
            'no' => array(
                'no'  => '/js/common/jquery-ui.js',
                'min' => '/js/common/jquery-ui.min.js'
            ),
            'cdn' => array(
                'no'  => '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js',
                'min' => '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js'
            )
        ),
        JS_BOOTSTRAP => array(
            'no' => array(
                'no'  => '/js/common/bootstrap.js',
                'min' => '/js/common/bootstrap.min.js'
            ),
            'cdn' => array(
                'no'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.js',
                'min' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js'
            )
        ),
        JQUERY_VALIDATE => array(
            'no' => array(
                'no'  => '/js/common/jquery.validate.js',
                'min' => '/js/common/jquery.validate.min.js'
            ),
            'cdn' => array(
                'no'  => '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.js',
                'min' => '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js'
            )
        ),
        JQUERY_TOUCH => array(
            'no' => array(
                'no'  => '/js/common/jquery.ui.touch-punch.min.js',
                'min' => '/js/common/jquery.ui.touch-punch.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js'
            )
        ),
        JS_TINYNAV => array(
            'no' => array(
                'no'  => '/js/common/tinynav.js',
                'min' => '/js/common/tinynav.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/TinyNav.js/1.2.0/tinynav.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/TinyNav.js/1.2.0/tinynav.min.js'
            )
        ),
        JS_BOOTSTRAP_FH => array(
            'no' => array(
                'no'  => '/js/common/bootstrap-formhelpers.js',
                'min' => '/js/common/bootstrap-formhelpers.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/js/bootstrap-formhelpers.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/js/bootstrap-formhelpers.min.js'
            )
        ),
        JS_BOOTSTRAP_SW => array(
            'no' => array(
                'no'  => '/js/common/bootstrap-switch.js',
                'min' => '/js/common/bootstrap-switch.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.2/js/bootstrap-switch.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.2/js/bootstrap-switch.min.js'
            )
        ),
        JS_DATATABLE => array(
            'no' => array(
                'no'  => '/js/common/jquery.dataTables.js',
                'min' => '/js/common/jquery.dataTables.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdn.datatables.net/1.10.7/js/jquery.dataTables.js',
                'min' => '//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js'
            )
        ),
        JS_CHART => array(
            'no' => array(
                'no'  => '/js/common/Chart.js',
                'min' => '/js/common/Chart.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js'
            )
        ),
        JS_METISMENU => array(
            'no' => array(
                'no'  => '/js/common/metisMenu.js',
                'min' => '/js/common/metisMenu.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/metisMenu/2.0.2/metisMenu.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/metisMenu/2.0.2/metisMenu.min.js'
            )
        ),
        JS_BOOTBOX => array(
            'no' => array(
                'no'  => '/js/common/bootbox.js',
                'min' => '/js/common/bootbox.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.3.0/bootbox.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.3.0/bootbox.min.js'
            )
        ),
        JS_DATATABLE_ODATA => array(
            'no' => array(
                'no'  => '/js/common/jquery.dataTables.odata.js',
                'min' => '/js/common/jquery.dataTables.odata.js',
            ),
            'cdn' => array(
                'no'  => '/js/common/jquery.dataTables.odata.js',
                'min' => '/js/common/jquery.dataTables.odata.js',
            )
        ),
        JS_CRYPTO_MD5_JS => array(
            'no' => array(
                'no'  => '/js/common/md5.js',
                'min' => '/js/common/md5.js',
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/rollups/md5.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/rollups/md5.js',
            )
        ),
        JS_JCROP => array(
            'no' => array(
                'no'  => '/js/common/jquery.Jcrop.js',
                'min' => '/js/common/jquery.Jcrop.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/0.9.12/js/jquery.Jcrop.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/0.9.12/js/jquery.Jcrop.min.js'
            )
        ),
        JS_TYPEAHEAD => array(
            'no' => array(
                'no'  => '/js/common/typeahead.bundle.js',
                'min' => '/js/common/typeahead.bundle.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js'
            )
        ),
        JS_CHEET => array(
            'no' => array(
                'no'  => '/js/common/cheet.js',
                'min' => '/js/common/cheer.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdn.rawgit.com/namuol/cheet.js/master/cheet.min.js',
                'min' => '//cdn.rawgit.com/namuol/cheet.js/master/cheet.min.js'
            )
        ),
        JS_FLIPSIDE => array(
            'no' => array(
                'no'  => '/js/common/flipside.js',
                'min' => '/js/common/flipside.min.js'
            ),
            'cdn' => array(
                'no'  => '/js/common/flipside.js',
                'min' => '/js/common/flipside.min.js'
            )
        ),
        JS_LOGIN => array(
            'no' => array(
                'no'  => '/js/common/login.js',
                'min' => '/js/common/login.min.js'
            ),
            'cdn' => array(
                'no'  => '/js/common/login.js',
                'min' => '/js/common/login.min.js'
            )
        )
);
global $cssArray;
$cssArray = array(
    CSS_JQUERY_UI => array(
        'no' => array(
                'no'  => '/css/common/jquery-ui.css',
                'min' => '/css/common/jquery-ui.min.css'
            ),
            'cdn' => array(
                'no'  => '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css',
                'min' => '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css'
            )
    ),
    CSS_BOOTSTRAP => array(
            'no' => array(
                'no'  => '/css/common/bootstrap.css',
                'min' => '/css/common/bootstrap.min.css'
            ),
            'cdn' => array(
                'no'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.css',
                'min' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css'
            )
    ),
    CSS_BOOTSTRAP_FH => array(
        'no' => array(
                'no'  => '/css/common/bootstrap-formhelpers.css',
                'min' => '/css/common/bootstrap-formhelpers.min.css'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/css/bootstrap-formhelpers.css',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/css/bootstrap-formhelpers.min.css'
            )
    ),
    CSS_BOOTSTRAP_SW => array(
            'no' => array(
                'no'  => '/css/common/bootstrap-switch.css',
                'min' => '/css/common/bootstrap-switch.min.css'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.2/css/bootstrap3/bootstrap-switch.css',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.2/css/bootstrap3/bootstrap-switch.min.css'
            )
    ),
    CSS_DATATABLE => array(
        'no' => array(
                'no'  => '/css/common/jquery.dataTables.css',
                'min' => '/css/common/jquery.dataTables.min.css'
            ),
            'cdn' => array(
                'no'  => '//cdn.datatables.net/1.10.7/css/jquery.dataTables.css',
                'min' => '//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css'
            )
    ),
    CSS_JCROP => array(
        'no'  => array(
            'no'  => '/css/common/jquery.Jcrop.css',
            'min' => '/css/common/jquery.Jcrop.min.css'
        ),
        'cdn' => array(
            'no'  => '//cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/0.9.12/css/jquery.Jcrop.css',
            'min' => '//cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/0.9.12/css/jquery.Jcrop.min.css'
        )
    ),
    CSS_FONTAWESOME => array(
        'no'  => array(
            'no'  => '/css/common/font-awesome.css',
            'min' => '/css/common/font-awesome.min.css'
        ),
        'cdn' => array(
            'no'  => '//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.css',
            'min' => '//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css'
        )
    )
);

/**
 * A framework specific webpage abstraction layer
 *
 * This class abstracts out some basic webpage creation with JQuery, Bootstrap, and other helpers
 */
class FlipPage extends WebPage
{
    /** The currently logged in user or null if no user is logged in */
    public $user;
    /** An array of links to put in the header */
    public $links;
    /** An array of notifications to draw on the page */
    public $notifications;
    /** Should we draw the header? */
    public $header;
    /** The login page URL */
    public $loginUrl;
    /** The logout page URL */
    public $logoutUrl;
    /** Should we use minified JS/CSS? */
    protected $minified = null;
    /** Should we use local JS/CSS or Content Delivery Networks? */
    protected $cdn = null;
    /** Draw the analytics scripts */
    protected $analytics = true;
    /** An instance of the Settings class */
    protected $settings;

    public $wwwUrl;
    public $wikiUrl;
    public $profilesUrl;
    public $secureUrl;

    /**
     * Create a webpage with JQuery, Bootstrap, etc
     *
     * @param string $title The webpage title
     *
     * @SuppressWarnings("StaticAccess")
     */
    public function __construct($title = '')
    {
        $this->settings = \Settings::getInstance();
        parent::__construct($title);
        $this->setupVars();
        $this->addWellKnownJS(JS_JQUERY, false);
        $this->addWellKnownJS(JS_FLIPSIDE, false);
        $this->addBootstrap();
        $this->links = array();
        $this->notifications = array();
        $this->wwwUrl = $this->settings->getGlobalSetting('www_url', 'https://www.burningflipside.com');
        $this->wikiUrl = $this->settings->getGlobalSetting('wiki_url', 'https://wiki.burningflipside.com');
        $this->aboutUrl = $this->settings->getGlobalSetting('about_url', $this->wwwUrl.'/about');
        $this->aboutMenu = $this->settings->getGlobalSetting('about_menu', array(
            'Burning Flipside' => $this->wwwUrl.'/about/event',
            'AAR, LLC' => $this->wwwUrl.'/organization/aar',
            'Privacy Policy' => $this->wwwUrl.'/about/privacy'
        ));
        $this->profilesUrl = $this->settings->getGlobalSetting('profiles_url', 'https://profiles.burningflipside.com/');
        $this->loginUrl = $this->settings->getGlobalSetting('login_url', $this->profilesUrl.'/login.php');
        $this->logoutUrl = $this->settings->getGlobalSetting('logout_url', $this->profilesUrl.'/logout.php');
        $this->registerUrl = $this->settings->getGlobalSetting('register_url', $this->profilesUrl.'/register.php');
        $this->resetUrl = $this->settings->getGlobalSetting('reset_url', $this->profilesUrl.'/reset.php');
        $this->secureUrl = $this->settings->getGlobalSetting('secure_url', 'https://secure.burningflipside.com/');
        $this->user = \FlipSession::getUser();
        $this->addAllLinks();
    }

    /**
     * Draw the page
     *
     * @param boolean $header Draw the header
     */
    public function printPage($response)
    {
        $this->renderNotifications();
        $this->addNoScript();
        $this->addAnalyticsBlock();
        $this->addJSGlobals();
        $this->addHeader();
        return parent::printPage($response);
    }

    /**
     * Get the external site links for this page
     *
     */
    protected function getSites()
    {
        return $this->settings->getSiteLinks();
    }

    /**
     * Add the links to be used in the header
     */
    protected function addAllLinks()
    {
        if($this->user === false || $this->user === null)
        {
            if(isset($_SERVER['REQUEST_URI']) && strstr($_SERVER['REQUEST_URI'], 'logout.php') === false)
            {
                $this->addLink('Login', $this->loginUrl);
            }
        }
        else
        {
            $this->add_links();
            $this->addLink('Logout', $this->logoutUrl);
        }
        if($this->aboutUrl !== false)
        {
            if(!empty($this->aboutMenu))
            {
                $this->addLink('About', $this->aboutUrl, $this->aboutMenu);
            }
            else
            {
                $this->addLink('About', $this->aboutUrl);
            }
        }
    }

    /**
     * Setup minified and cdn class varibles from defaults or the settings file
     */
    private function setupVars()
    {
        if($this->minified !== null && $this->cdn !== null)
        {
            return;
        }
        $this->minified = 'min';
        $this->cdn      = 'cdn';
        if($this->settings->getGlobalSetting('use_minified', true) == false)
        {
            $this->minified = 'no';
        }
        if($this->settings->getGlobalSetting('use_cdn', true) == false)
        {
            $this->cdn = 'no';
        }
    }
    /**
     * Add a JavaScript file from its src URI
     *
     * @param string $uri The webpath to the JavaScript file
     * @param boolean $async Can the JavaScript be loaded asynchronously?
     */
    public function addJSByURI($uri, $async = true)
    {
        $attributes = array('src'=>$uri, 'type'=>'text/javascript');
        if($async === true)
        {
            $attributes['async'] = true;
        }
        $jsTag = $this->createOpenTag('script', $attributes);
        $closeTag = $this->createCloseTag('script');
        $this->addHeadTag($jsTag);
        $this->addHeadTag($closeTag);
    }

    /**
     * Add a Cascading Style Sheet file from its src URI
     *
     * @param string $uri The webpath to the Cascading Style Sheet file
     * @param boolean $async Can the CSS be loaded asynchronously?
     */
    public function addCSSByURI($uri, $async = false)
    {
        $attributes = array('rel'=>'stylesheet', 'href'=>$uri, 'type'=>'text/css');
        if($async === true && $this->importSupport === true)
        {
            $attributes['rel'] = 'import';
        }
        $cssTag = $this->createOpenTag('link', $attributes, true);
        $this->addHeadTag($cssTag);
    }
    /**
     * Add a JavaScript file from a set of files known to the framework
     *
     * @param string $jsFileID the ID of the JS file
     * @param boolean $async Can the JS file be loaded asynchronously?
     */
    public function addWellKnownJS($jsFileID, $async = false)
    {
        global $jsArray;
        $this->setupVars();
        $src = $jsArray[$jsFileID][$this->cdn][$this->minified];
        $this->addJSByURI($src, $async);
    }
    /**
     * Add a CSS file from a set of files known to the framework
     *
     * @param string $cssFileID the ID of the CSS file
     * @param boolean $async Can the CSS file be loaded asynchronously?
     */
    public function addWellKnownCSS($cssFileID, $async = false)
    {
        global $cssArray;
        $this->setupVars();
        $src = $cssArray[$cssFileID][$this->cdn][$this->minified];
        $this->addCSSByURI($src, $async);
    }

    /**
     * Add files needed by the Bootstrap framework
     */
    private function addBootstrap()
    {
        $this->addWellKnownJS(JS_BOOTSTRAP);
        $this->addWellKnownCSS(CSS_BOOTSTRAP);
        $this->addWellKnownCSS(CSS_FONTAWESOME);
    }
    protected function getSiteLinksForHeader()
    {
        $sites = $this->getSites();
        $names = array_keys($sites);
        $ret = '';
        foreach($names as $name)
        {
            $ret .= '<li>'.$this->createLink($name, $sites[$name]).'</li>';
        }
        return $ret;
    }
    /**
     * Get the link for the HREF
     *
     * @return string The HREF for the dropdown
     */
    protected function getHrefForDropdown(&$link)
    {
        if(isset($link['_']))
        {
            $ret = $link['_'];
            unset($link['_']);
            return $ret;
        }
        return '#';
    }

    protected function getDropdown($link, $name)
    {
        $ret = '<li class="dropdown">';
        $href = $this->getHrefForDropdown($link);
        $ret .= '<a href="'.$href.'" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$name.' <span class="caret"></span></a>';
        $ret .= '<ul class="dropdown-menu">';
        $subNames = array_keys($link);
        foreach($subNames as $subName)
        {
            $ret .= $this->getLinkByName($subName, $link);
        }
        $ret .= '</ul></li>';
        return $ret;
    }
    protected function getLinkByName($name, $links)
    {
        if(is_array($links[$name]))
        {
            return $this->getDropdown($links[$name], $name);
        }
        if($links[$name] === false)
        {
            return '<li>'.$name.'</li>';
        }
        return '<li>'.$this->createLink($name, $links[$name]).'</li>';
    }
    protected function getLinksMenus()
    {
        $names = array_keys($this->links);
        $ret = '';
        foreach($names as $name)
        {
            $ret .= $this->getLinkByName($name, $this->links);
        }
        return $ret;
    }

    /**
     * Draw the header for the page
     */
    protected function addHeader()
    {
        $sites = $this->getSiteLinksForHeader();
        $links = $this->getLinksMenus();
        $header = '<nav class="navbar navbar-default navbar-fixed-top">
                      <div class="container-fluid">
                          <!-- Brand and toggle get grouped for better mobile display -->
                          <div class="navbar-header">
                          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
                              <span class="sr-only">Toggle navigation</span>
                              <span class="icon-bar"></span>
                              <span class="icon-bar"></span>
                              <span class="icon-bar"></span>
                          </button>
                          <a class="navbar-brand" href="#">
                              <picture>
                                  <source srcset="/img/common/logo.svg" style="width: 30px; height:30px"/>
                                  <img alt="Burning Flipside" src="/img/common/logo.png" style="width: 30px; height:30px"/>
                              </picture>
                          </a>
                          </div>
                          <!-- Collect the nav links, forms, and other content for toggling -->
                          <div class="collapse navbar-collapse" id="navbar-collapse">
                              <ul id="site_nav" class="nav navbar-nav">
                              '.$sites.'
                              </ul>
                              <ul class="nav navbar-nav navbar-right">
                              '.$links.'
                              </ul>
                          </div>
                      </div>
                  </nav>';
        $this->body = $header.$this->body;
        $this->body_tags .= 'style="padding-top: 60px;"';
    }

    /** Notification that is green for success */
    const NOTIFICATION_SUCCESS = 'alert-success';
    /** Notification that is blue for infomrational messages */
    const NOTIFICATION_INFO    = 'alert-info';
    /** Notification that is yellow for warning */
    const NOTIFICATION_WARNING = 'alert-warning';
    /** Notification that is red for error */
    const NOTIFICATION_FAILED  = 'alert-danger';
    /**
     * Add a notification to the page
     *
     * @param string $message The message to show in the notifcation
     * @param string $severity The severity of the notifcation
     * @param boolean $dismissible Can the user dismiss the notificaton?
     */
    public function addNotification($message, $severity = self::NOTIFICATION_INFO, $dismissible = true)
    {
        array_push($this->notifications, array('msg'=>$message, 'sev'=>$severity, 'dismissible'=>$dismissible));
    }

    /**
     * Draw all notifications to the page
     */
    private function renderNotifications()
    {
        $count = count($this->notifications);
        if($count === 0)
        {
            return;
        }
        for($i = 0; $i < $count; $i++)
        {
            $class = 'alert '.$this->notifications[$i]['sev'];
            $button = '';
            if($this->notifications[$i]['dismissible'])
            {
                $class .= ' alert-dismissible';
                $button = '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
            }
            $prefix = '';
            switch($this->notifications[$i]['sev'])
            {
                case self::NOTIFICATION_INFO:
                    $prefix = '<strong>Notice:</strong> ';
                    break;
                case self::NOTIFICATION_WARNING:
                    $prefix = '<strong>Warning!</strong> ';
                    break;
                case self::NOTIFICATION_FAILED:
                    $prefix = '<strong>Warning!</strong> ';
                    break;
            }
            $style = '';
            if(($i + 1) < count($this->notifications))
            {
                //Not the last notification, remove the end margin
                $style = 'style="margin: 0px;"';
            }
            $this->body = '
                <div class="'.$class.'" role="alert" '.$style.'>
                    '.$button.$prefix.$this->notifications[$i]['msg'].'
                </div>
            '.$this->body;
        }
    }

    /**
     * Add the no script block
     */
    private function addNoScript()
    {
        $this->body = '<noscript>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <strong>Error!</strong> This site makes extensive use of JavaScript. Please enable JavaScript or this site will not function.
                </div>
            </noscript>'.$this->body;
    }

    /**
     * Add the analytics script block
     */
    private function addAnalyticsBlock()
    {
        if($this->analytics === false)
        {
            return;
        }
        $this->body = $this->body.'<script>
  (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,\'script\',\'//www.google-analytics.com/analytics.js\',\'ga\');
  ga(\'create\', \'UA-64901342-1\', \'auto\');
  ga(\'send\', \'pageview\');
</script>';
    }
    /**
     * Add some global js vars
     */
    private function addJSGlobals()
    {
      $this->body = $this->body.'<script>
var profilesUrl = \''.$this->profilesUrl.'\'
var loginUrl = \''.$this->loginUrl.'\'
var logoutUrl = \''.$this->logoutUrl.'\'
</script>';
    }

    /**
     * Add a link to the header
     *
     * @param string $name The name of the link
     * @param boolean|string $url The URL to link to
     * @param boolean|array $submenu Any submenu items for the dropdown
     */
    public function addLink($name, $url = false, $submenu = false)
    {
        if(is_array($submenu))
        {
            $submenu['_'] = $url;
            $this->links[$name] = $submenu;
            return;
        }
        $this->links[$name] = $url;
    }

    /**
     * Add the login form to the page
     *
     * @SuppressWarnings("StaticAccess")
     */
    public function add_login_form()
    {
        $auth = \AuthProvider::getInstance();
        $authLinks = $auth->getSupplementaryLinks();
        $authLinksStr = '';
        $count = count($authLinks);
        for($i = 0; $i < $count; $i++)
        {
            $authLinksStr .= $authLinks[$i];
        }
        if($count > 0)
        {
            $authLinksStr = 'Sign in with '.$authLinksStr;
        }
        $this->body .= '<div class="modal fade" role="dialog" id="login-dialog" title="Login" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span aria-hidden="true">&times;</span>
                                            <span class="sr-only">Close</span>
                                        </button>
                                        <h4 class="modal-title">Login</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form id="login_dialog_form" role="form">
                                            <input class="form-control" type="text" name="username" placeholder="Username or Email" required autofocus/>
                                            <input class="form-control" type="password" name="password" placeholder="Password" required/>
                                            <input type="hidden" name="return" value="'.$this->currentUrl().'"/>
                                            <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
                                        </form>
                                        '.$authLinksStr.'
                                    </div>
                                </div>
                            </div>
                        </div>';
    }
    /**
     * Add additional links
     */
    public function add_links()
    {
    }
}

<?php
namespace Http;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require_once('static.js_css.php');

class WebPage
{
    public $body = '';
    protected $templateName = 'main.html';
    public $content;
    public $user;

    public function __construct($title)
    {
        \Sentry\init(['dsn' => 'https://8d76f6c4cb3b409bbe7ed4300e054afd@sentry.io/4283882' ]);
        $this->settings = \Settings::getInstance();
        $this->loader = new \Twig_Loader_Filesystem(dirname(__FILE__).'/../templates');
        
        $twigCache = $this->settings->getGlobalSetting('twig_cache', '/var/php_cache/twig');
        $twigSettings = array('cache' => $twigCache);
        //$twigSettings = array('cache' => $twigCache, 'debug' => true);

        $this->wwwUrl = $this->settings->getGlobalSetting('www_url', 'https://www.burningflipside.com');
        $this->wikiUrl = $this->settings->getGlobalSetting('wiki_url', 'https://wiki.burningflipside.com');
        $this->secureUrl = $this->settings->getGlobalSetting('secure_url', 'https://secure.burningflipside.com');
        $this->profilesUrl = $this->settings->getGlobalSetting('profiles_url', 'https://profiles.burningflipside.com');
        $this->registerUrl = $this->settings->getGlobalSetting('register_url', $this->profilesUrl.'/register.php');
        $this->resetUrl = $this->settings->getGlobalSetting('reset_url', $this->profilesUrl.'/reset.php');
        $this->loginUrl = $this->settings->getGlobalSetting('login_url', $this->profilesUrl.'/login.php');
        $this->logoutUrl = $this->settings->getGlobalSetting('logout_url', $this->profilesUrl.'/logout.php');

        $this->twig = new \Twig_Environment($this->loader, $twigSettings);
        //$this->twig->addExtension(new \Twig\Extension\DebugExtension());
        $this->content = array('pageTitle' => $title);
        $this->user = \FlipSession::getUser();
        $this->content['user'] = $this->user;
        $this->content['header'] = array();
        $this->content['header']['sites'] = array();
        $this->content['header']['sites']['Profiles'] = $this->profilesUrl;
        $this->content['header']['sites']['WWW'] = $this->wwwUrl;
        $this->content['header']['sites']['Pyropedia'] = $this->wikiUrl;
        $this->content['header']['sites']['Secure'] = $this->secureUrl;

        $this->aboutUrl = $this->settings->getGlobalSetting('about_url', $this->wwwUrl.'/about');
        $this->content['header']['right']['About'] = array(
          'url' => $this->aboutUrl,
          'menu' => $this->settings->getGlobalSetting('about_menu', array(
            'Burning Flipside' => $this->wwwUrl.'/about/event',
            'AAR, LLC' => $this->wwwUrl.'/organization/aar',
            'Privacy Policy' => $this->wwwUrl.'/about/privacy'
        )));

        $this->content['urls']['wwwUrl'] = $this->wwwUrl;
        $this->content['urls']['wikiUrl'] = $this->wikiUrl;
        $this->content['urls']['profilesUrl'] = $this->profilesUrl;
        $this->content['urls']['secureUrl'] = $this->secureUrl;

        $this->content['urls']['registerUrl'] = $this->registerUrl;
        $this->content['urls']['resetUrl'] = $this->resetUrl;
        $this->content['urls']['loginUrl'] = $this->loginUrl;
        $this->content['urls']['logoutUrl'] = $this->logoutUrl;

	if($this->user === false || $this->user === null)
        {
            if(isset($_SERVER['REQUEST_URI']) && strstr($_SERVER['REQUEST_URI'], 'logout.php') === false)
            {
                $this->addLink('Login', $this->loginUrl);
            }
        }
        else
        {
            $this->addLink('Logout', $this->settings->getGlobalSetting('logout_url', $this->profilesUrl.'/logout.php'));
            $this->addLinks();
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
            $this->content['useCDN'] = false;
        }
    }

    public function addTemplateDir($dir, $namespace)
    {
        $this->loader->addPath($dir, $namespace);
    }

    public function setTemplateName($name)
    {
        $this->templateName = $name;
    }

    public function addCSS($uri)
    {
        if(!isset($this->content['css']))
        {
            $this->content['css'] = array($uri);
            return;
        }
        array_push($this->content['css'],$uri);
    }

    /**
     * Add a JavaScript file from its src URI
     *
     * @param string $uri The webpath to the JavaScript file
     */
    public function addJS($uri)
    {
        if(!isset($this->content['js']))
        {
            $this->content['js'] = array($uri);
            return;
        }
        array_push($this->content['js'],$uri);
    }

    /**
     * Add a JavaScript file from a set of files known to the framework
     *
     * @param string $jsFileID the ID of the JS file
     * @param boolean $async Can the JS file be loaded asynchronously?
     */
    public function addWellKnownJS($jsFileID)
    {
        global $jsArray;
        $src = $jsArray[$jsFileID][$this->cdn][$this->minified];
        if(is_array($src))
        {
            if(!isset($this->content['securejs']))
            {
                $this->content['securejs'] = array();
            }
            array_push($this->content['securejs'], $src);
        }
        else
        {
            $this->addJS($src);
        }
    }

    /**
     * Add a CSS file from a set of files known to the framework
     *
     * @param string $cssFileID the ID of the CSS file
     */
    public function addWellKnownCSS($cssFileID)
    {
        global $cssArray;
        $src = $cssArray[$cssFileID][$this->cdn][$this->minified];
        $this->addCSS($src);
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
        $data = array('url' => $url);
        if(is_array($submenu))
        {
            $data['menu'] = $submenu;
        }
        $this->content['header']['right'] = array($name => $data)+$this->content['header']['right'];
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
        if(!isset($this->content['notifications']))
        {
          $this->content['notifications'] = array();
        }
        array_push($this->content['notifications'], array('msg'=>$message, 'sev'=>$severity, 'dismissible'=>$dismissible));
    }

    protected function addLinks()
    {
    }

    protected function getContent()
    {
        if(!isset($this->content['body']))
        {
          $this->content['body'] = $this->body;
        }
        //Add page JS just before rednering so it is after any added by the page explicitly
        // $this->addJS('js/'.basename($_SERVER['SCRIPT_NAME'], '.php').'.js');
        // this code assumes *.php pages have a corresponding *.js file (many don't)
        return $this->twig->render($this->templateName, $this->content);
    }

    public function handleRequest($request, $response, $args)
    {
        $body = $response->getBody();
        $body->write($this->getContent());
        return $response;
    }

    public function printPage()
    {
        echo $this->getContent();
    }

    /**
     * Get the currently requested URL
     *
     * @return string The full URL of the requested page
     *
     * @SuppressWarnings("Superglobals")
     */
    public function currentURL()
    {
        if(!isset($_SERVER['REQUEST_URI']))
        {
            return '';
        }
        $requestURI = $_SERVER['REQUEST_URI'];
        if($requestURI[0] === '/')
        {
            $requestURI = substr($requestURI, 1);
        }
        return 'http'.(isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/'.$requestURI;
    }
}


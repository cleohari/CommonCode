<?php
/**
 * Generic OAUTH2 Authenitcation Helper class
 *
 * This file describes the OAuth2Authenticator class
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

namespace Flipside\Auth\OAuth2;

/** Only load the HTTPFul bootstrap if it isn't already loaded*/
if(!class_exists('Httpful\Request'))
{
    require dirname(__FILE__).'/../../vendor/autoload.php';
}

/**
 * A helper class to help with common OAUTH2 tasks
 *
 * This class helps convert between OAUTH2 and the \Auth\Authenticator methods
 */
abstract class OAuth2Authenticator extends \Flipside\Auth\Authenticator
{
    /** The URL the OAUTH2 service should redirect to */
    protected $redirect_uri;

    /**
     * Create an instance of the OAuth2Authenticator
     *
     * @param array $params Paremeters to use in initializing this Authenticator
     */
    public function __construct($params)
    {
        parent::__construct($params);
        if(!isset($params['redirect_url']))
        {
            $this->redirect_uri = 'https://'.$_SERVER['HTTP_HOST'].'/oauth/callbacks/'.$this->getHostName();
        }
        else
        {
            $this->redirect_uri = $params['redirect_url'];
        }
    }

    /**
     * Get a link to the supplemental OAUTH2 endpoint
     *
     * @return string A link consiting of an image for use on the login screens
     */
    public function getSupplementLink()
    {
        $auth_url = $this->getAuthorizationUrl();
        return '<a href="'.filter_var($auth_url, FILTER_SANITIZE_URL).'"><img src="'.$this->getSignInImg().'" style="width: 2em;"/></a>';
    }

    /**
     * Get the webpath for the image used on the login screens
     *
     * @return string the webpath for the image
     */
    public function getSignInImg()
    {
        return '/img/common/'.$this->getHostName().'_sign_in.png';
    }

    /** 
     * Get the URL to initially authorize a user
     *
     * @return string The URL to send the user to inordere to authorize the use by this server
     */
    abstract public function getAuthorizationUrl();

    /**
     * Get the URL to retreive an access token from
     *
     * @return string The URL to obtain the access token from after the user authorizes use
     */
    abstract public function getAccessTokenUrl();

    /**
     * Obtain the user given the Access Token
     *
     * @param string $token The OAUTH2 access token
     *
     * @return \Auth\User The user that is now authenicated
     */
    abstract public function getUserFromToken($token);

    /**
     * Send the access token to the server to indicate this server can act on behalf of the user
     *
     * @param array $params An array containing the code to send 
     *
     * @return \Httpful\Response The response from the post to the other server
     */
    public function doAuthPost($params)
    {
        return \Httpful\Request::post($this->getAccessTokenUrl().'&code='.$params['code'])->send();
    }

    /**
     * Authenticate the user is valid and can login throught this method
     *
     * @param $params The set of parameters obtained from the authentication call
     * @param $current_user The user from the current system if the user is not authorized to login via this method
     *
     * @return integer SUCCESS if the user is now logged in. ALREADY_PRESENT if the authorization was
     *                 successful, but the user has not authorized that login method. LOGIN_FAILED for all other errors
     */
    public function authenticate($params, &$current_user)
    {
        $resp = $this->doAuthPost($params);
        if($resp->hasErrors())
        {
            return self::LOGIN_FAILED; 
        }
        \Flipside\FlipSession::setVar('OAuthToken', $resp->body);
        $user = $this->getUserFromToken($resp->body);
        if($user === false)
        {
            return self::LOGIN_FAILED;
        }
        $auth = \Flipside\AuthProvider::getInstance();
        $local_users = $auth->getUsersByFilter(new \Flipside\Data\Filter('mail eq '.$user->mail));
        if($local_users !== false && isset($local_users[0]))
        {
            if($local_users[0]->canLoginWith($this->getHostName()))
            {
                $auth->impersonateUser($local_users[0]);
                return self::SUCCESS;
            }
            $current_user = $local_users[0];
            return self::ALREADY_PRESENT;
        }
        $ret = $auth->activatePendingUser($user);
        if($ret === false)
        {
            throw new \Exception('Unable to create user! '.$res);
        }
        return self::SUCCESS;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

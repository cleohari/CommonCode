<?php
namespace Auth;
require dirname(__FILE__).'/../libs/google/src/Google/autoload.php';

class GoogleAuthenticator extends Authenticator
{
    protected $client;
    protected $token = null;

    public function __construct($params)
    {
        parent::__construct($params);
        if(!isset($params['client_secrets_path']))
        {
            throw new \Exception('Missing required parameter client_secrets_path!');
        }
        if(!isset($params['redirect_url']))
        {
            $params['redirect_url'] = 'https://'.$_SERVER['HTTP_HOST'].'/oauth2callback.php?src=google';
        }
        $this->token = \FlipSession::getVar('GoogleToken', null);
        $this->client = new \Google_Client();
        $this->client->setAuthConfigFile($params['client_secrets_path']);
        $this->client->addScope(array(\Google_Service_Oauth2::USERINFO_PROFILE, \Google_Service_Oauth2::USERINFO_EMAIL));
        $this->client->setRedirectUri($params['redirect_url']);
    }

    /**
     * Get the link to login using this method
     *
     * @return string The link to login using this method
     */
    public function getSupplementLink()
    {
        $authUrl = $this->client->createAuthUrl();
        return '<a href="'.filter_var($authUrl, FILTER_SANITIZE_URL).'"><img src="/img/common/google_sign_in.png" style="width: 2em;"/></a>';
    }

    public function authenticate($code, &$currentUser = false)
    {
        $googleUser = false;
        try
        {
            $this->client->authenticate($code);
            $this->token = $this->client->getAccessToken();
            \FlipSession::setVar('GoogleToken', $this->token);
            $oauth2Service = new \Google_Service_Oauth2($this->client);
            $googleUser = $oauth2Service->userinfo->get();
        }
        catch(\Exception $ex)
        {
            return self::LOGIN_FAILED;
        }

        $auth = \AuthProvider::getInstance();
        $localUsers = $auth->getUsersByFilter(new \Data\Filter('mail eq '.$googleUser->email));
        if($localUsers !== false && isset($localUsers[0]))
        {
            if($localUsers[0]->canLoginWith('google.com'))
            {
                $auth->impersonateUser($localUsers[0]);
                return self::SUCCESS;
            }
            $currentUser = $localUsers[0];
            return self::ALREADY_PRESENT;
        }
        else
        {
            $user = new PendingUser();
            $user->mail = $googleUser->email;
            $user->givenName = $googleUser->givenName;
            $user->sn = $googleUser->familyName;
            $user->addLoginProvider('google.com');
            $ret = $auth->activatePendingUser($user);
            if($ret === false)
            {
                throw new \Exception('Unable to create user! '.$res);
            }
            return self::SUCCESS;
        }
    }

    public function getUser($data = false)
    {
        if($data === false)
        {
            $data = $this->token;
        }
        try
        {
            $this->client->setAccessToken($data);
            $oauth2Service = new \Google_Service_Oauth2($this->client);
            $googleUser = $oauth2Service->userinfo->get();
            $profileUser = array();
            $profileUser['mail'] = $googleUser->email;
            $profileUser['sn'] = $googleUser->familyName;
            $profileUser['givenName'] = $googleUser->givenName;
            $profileUser['displayName'] = $googleUser->name;
            $profileUser['jpegPhoto'] = base64_encode(file_get_contents($googleUser->picture));
            return $profileUser;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

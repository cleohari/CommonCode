<?php
namespace Flipside\Auth\OAuth2;

class GitLabAuthenticator extends OAuth2Authenticator
{
    protected $app_id;
    protected $app_secret;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->app_id = $params['app_id'];
        $this->app_secret = $params['app_secret'];
    }

    public function getHostName()
    {
        return 'gitlab.com';
    }

    public function getAuthorizationUrl()
    {
        return 'https://gitlab.com/oauth/authorize?client_id='.$this->app_id.'&redirect_uri='.urlencode($this->redirect_uri).'&response_type=code';
    }

    public function getAccessTokenUrl()
    {
        return 'https://gitlab.com/oauth/token?client_id='.$this->app_id.'&client_secret='.$this->app_secret.'&grant_type=authorization_code&redirect_uri='.urlencode($this->redirect_uri);
    }

    public function getUserFromToken($token)
    {
        if($token === false)
        {
            $token = \FlipSession::getVar('OAuthToken');
        }
        $resp = \Httpful\Request::get('https://gitlab.com/api/v3/user')->addHeader('Authorization', 'Bearer '.$token->access_token)->send();
        $gitlab_user = $resp->body;
        $user = new \Auth\PendingUser();
        $user->mail = $gitlab_user->email;
        $name = explode(' ', $gitlab_user->name);
        $user->givenName = $name[0];
        $user->sn = $name[1];
        $user->addLoginProvider($this->getHostName());
        return $user;
    }
}

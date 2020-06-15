<?php
namespace Flipside\Auth\OAuth2;

class GitHubAuthenticator extends OAuth2Authenticator
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
        return 'github.com';
    }

    public function getAuthorizationUrl()
    {
        return 'https://github.com/login/oauth/authorize?client_id='.$this->app_id.'&redirect_uri='.urlencode($this->redirect_uri).'&scope=user';
    }

    public function getAccessTokenUrl()
    {
        return 'https://github.com/login/oauth/access_token?client_id='.$this->app_id.'&client_secret='.$this->app_secret.'&redirect_uri='.urlencode($this->redirect_uri);
    }

    public function getUserFromToken($token)
    {
        if($token === false)
        {
            $token = \Flipside\FlipSession::getVar('OAuthToken');
        }
        $resp = \Httpful\Request::get('https://api.github.com/user')->addHeader('Authorization', 'token '.$token['access_token'])->send();
        $github_user = $resp->body;
        $user = new \Flipside\Auth\PendingUser();
        if(isset($github_user->name))
        {
            $name = explode(' ', $github_user->name);
            $user->givenName = $name[0];
            $user->sn = $name[1];
        }
        $resp = \Httpful\Request::get('https://api.github.com/user/emails')->addHeader('Authorization', 'token '.$token['access_token'])->send();
        $user->mail = $resp->body[0]->email;
        $user->addLoginProvider($this->getHostName());
        return $user;
    }
}

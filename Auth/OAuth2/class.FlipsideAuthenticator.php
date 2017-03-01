<?php
namespace Auth\OAuth2;

class FlipsideAuthenticator extends OAuth2Authenticator
{
    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function getHostName()
    {
        return 'burningflipside.com';
    }

    public function getAuthorizationUrl()
    {
        return 'https://profiles.burningflipside.com/OAUTH2/authorize.php?client_id=test&redirect_uri='.urlencode($this->redirect_uri).'&scope=user';
    }

    public function getAccessTokenUrl()
    {
        return 'https://profiles.burningflipside.com/OAUTH2/token.php?client_id=test&redirect_uri='.urlencode($this->redirect_uri);
    }

    public function getUserFromToken($token)
    {
        if($token === false)
        {
            $token = \FlipSession::getVar('OAuthToken');
        }
        $resp = \Httpful\Request::get('https://profiles.burningflipside.com/api/v1/users/me')->addHeader('Authorization', 'token '.$token['access_token'])->send();
        $data = array('extended'=>$resp->body);
        $user = new \Auth\FlipsideAPIUser($data);
        $user->addLoginProvider($this->getHostName());
        return $user;
    }

    public function login($username, $password)
    {
        $resp = \Httpful\Request::post('https://profiles.burningflipside.com/api/v1/login?username='.urlencode($username).'&password='.urlencode($password))->send();
        if($resp->hasErrors())
        {
            return false;
        }
        $this->user = $resp->body->extended;
        $this->user->userPassword = $password;
        return array('res'=>true, 'extended'=>$this->user);
    }

    public function isLoggedIn($data)
    {
        if(isset($this->user))
        {
            return true;
        }
        if(isset($data['res']))
        {
            return $data['res'];
        }
        return false;
    }

    public function getUser($data)
    {
        return new \Auth\FlipsideAPIUser($data);
    }
}

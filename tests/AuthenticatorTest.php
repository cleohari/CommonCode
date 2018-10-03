<?php
require_once('Autoload.php');
class AuthenticatorTest extends PHPUnit\Framework\TestCase
{
    public function testAuthenticator()
    {
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false);
        $auth = new \Auth\Authenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\Authenticator', $auth);
        $this->assertTrue($auth->current);
        $this->assertFalse($auth->pending);
        $this->assertFalse($auth->supplement);

        $this->assertFalse($auth->login('test', 'test'));
        $this->assertFalse($auth->isLoggedIn(false));
        $this->assertNull($auth->getUser(false));
        $this->assertNull($auth->getGroupByName(false));
        $this->assertNull($auth->getUserByName(false));
        $this->assertFalse($auth->getGroupsByFilter(false));
        $this->assertFalse($auth->getUsersByFilter(false));
        $this->assertFalse($auth->getPendingUsersByFilter(false));
        $this->assertEquals(0, $auth->getActiveUserCount(false));
        $this->assertEquals(0, $auth->getPendingUserCount(false));
        $this->assertEquals(0, $auth->getGroupCount(false));
        $this->assertEmpty($auth->getSupplementLink());
        $this->assertFalse($auth->createPendingUser(false));
        $this->assertFalse($auth->activatePendingUser(false));
        $this->assertFalse($auth->getUserByResetHash(false));
        $this->assertFalse($auth->getTempUserByHash(false));
        $this->assertFalse($auth->getHostName(false));

        $params = array('current'=>false, 'pending'=>true, 'supplement'=>false);
        $auth = new \Auth\Authenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\Authenticator', $auth);
        $this->assertFalse($auth->current);
        $this->assertTrue($auth->pending);
        $this->assertFalse($auth->supplement);

        $this->assertFalse($auth->getPendingUsersByFilter(false));
        $this->assertEquals(0, $auth->getActiveUserCount(false));
        $this->assertEquals(0, $auth->getPendingUserCount(false));

        $params = array('current'=>false, 'pending'=>false, 'supplement'=>true);
        $auth = new \Auth\Authenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\Authenticator', $auth);
        $this->assertFalse($auth->current);
        $this->assertFalse($auth->pending);
        $this->assertTrue($auth->supplement);

        $this->assertEmpty($auth->getSupplementLink());
        $this->assertFalse($auth->getHostName(false));
    }

    public function testNullAuthenticator()
    {
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false);
        $auth = new \Auth\NullAuthenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\NullAuthenticator', $auth);
        $res = $auth->login('test', 'test');
        $this->assertNotFalse($res);
        $this->assertFalse($auth->isLoggedIn(false));
        $this->assertTrue($auth->isLoggedIn($res));
        $this->assertNull($auth->getUser(false));
    }

    public function testFlipsideAuthenticator()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false, 'api_url'=>'https://profiles.burningflipside.com/api/v1', 'oauth_url'=>'https://profiles.burningflipside.com/OAUTH2');
        $auth = new \Auth\OAuth2\FlipsideAuthenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\OAuth2\FlipsideAuthenticator', $auth);
        $this->assertEquals('burningflipside.com', $auth->getHostName());
        $this->assertEquals('https://profiles.burningflipside.com/OAUTH2/authorize.php?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fburningflipside.com&scope=user', $auth->getAuthorizationUrl());
        $this->assertEquals('https://profiles.burningflipside.com/OAUTH2/token.php?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fburningflipside.com', $auth->getAccessTokenUrl());
        $this->assertEquals('<a href="https://profiles.burningflipside.com/OAUTH2/authorize.php?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fburningflipside.com&scope=user"><img src="/img/common/burningflipside.com_sign_in.png" style="width: 2em;"/></a>', $auth->getSupplementLink());
        $this->assertEquals('/img/common/burningflipside.com_sign_in.png', $auth->getSignInImg());

        $params['api_url'] = 'https://api.example.org';
        $params['oauth_url'] = 'https://login.example.org/oauth';
        $auth = new \Auth\OAuth2\FlipsideAuthenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\OAuth2\FlipsideAuthenticator', $auth);
        $this->assertEquals('burningflipside.com', $auth->getHostName());
        $this->assertEquals('https://login.example.org/oauth/authorize.php?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fburningflipside.com&scope=user', $auth->getAuthorizationUrl());
        $this->assertEquals('https://login.example.org/oauth/token.php?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fburningflipside.com', $auth->getAccessTokenUrl());
        $this->assertEquals('<a href="https://login.example.org/oauth/authorize.php?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fburningflipside.com&scope=user"><img src="/img/common/burningflipside.com_sign_in.png" style="width: 2em;"/></a>', $auth->getSupplementLink());
        $this->assertEquals('/img/common/burningflipside.com_sign_in.png', $auth->getSignInImg());
    }

    public function testGithubAuthenticator()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'abc');
        $auth = new \Auth\OAuth2\GitHubAuthenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\OAuth2\GitHubAuthenticator', $auth);
        $this->assertEquals('github.com', $auth->getHostName());
        $this->assertEquals('https://github.com/login/oauth/authorize?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fgithub.com&scope=user', $auth->getAuthorizationUrl());
        $this->assertEquals('https://github.com/login/oauth/access_token?client_id=test&client_secret=abc&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fgithub.com', $auth->getAccessTokenUrl());
        $this->assertEquals('<a href="https://github.com/login/oauth/authorize?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fgithub.com&scope=user"><img src="/img/common/github.com_sign_in.png" style="width: 2em;"/></a>', $auth->getSupplementLink());
        $this->assertEquals('/img/common/github.com_sign_in.png', $auth->getSignInImg());
    }

    public function testGitlabAuthenticator()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'abc');
        $auth = new \Auth\OAuth2\GitLabAuthenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\OAuth2\GitLabAuthenticator', $auth);
        $this->assertEquals('gitlab.com', $auth->getHostName());
        $this->assertEquals('https://gitlab.com/oauth/authorize?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fgitlab.com&response_type=code', $auth->getAuthorizationUrl());
        $this->assertEquals('https://gitlab.com/oauth/token?client_id=test&client_secret=abc&grant_type=authorization_code&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fgitlab.com', $auth->getAccessTokenUrl());
        $this->assertEquals('<a href="https://gitlab.com/oauth/authorize?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fgitlab.com&response_type=code"><img src="/img/common/gitlab.com_sign_in.png" style="width: 2em;"/></a>', $auth->getSupplementLink());
        $this->assertEquals('/img/common/gitlab.com_sign_in.png', $auth->getSignInImg());
    }

    public function testLiveAuthenticator()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'abc');
        $auth = new \Auth\OAuth2\LiveAuthenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\OAuth2\LiveAuthenticator', $auth);
        $this->assertEquals('live.com', $auth->getHostName());
        $this->assertEquals('https://login.live.com/oauth20_authorize.srf?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Flive.com&response_type=code&scope=wl.basic,wl.emails', $auth->getAuthorizationUrl());
        $this->assertEquals('https://login.live.com/oauth20_token.srf', $auth->getAccessTokenUrl());
        $this->assertEquals('<a href="https://login.live.com/oauth20_authorize.srf?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Flive.com&response_type=code&scope=wl.basic,wl.emails"><img src="/img/common/live.com_sign_in.png" style="width: 2em;"/></a>', $auth->getSupplementLink());
        $this->assertEquals('/img/common/live.com_sign_in.png', $auth->getSignInImg());
    }

    public function testGoogleAuthenticator()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false, 'client_secrets_path'=>dirname(__FILE__).'/helpers/google.json');
        $auth = new \Auth\GoogleAuthenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\GoogleAuthenticator', $auth);
        
        $linkTxt = $auth->getSupplementLink();
        $dom = new DOMDocument;
        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($linkTxt);
        libxml_use_internal_errors($internalErrors);
        //HTML element, skip
        $doc = $dom->documentElement;
        //Body element, skip
        $body = $doc->childNodes->item(0);

        $this->assertEquals($body->childNodes->length, 1);
        $link = $body->childNodes->item(0);
        $this->assertEquals($link->tagName, 'a');

        $attributes = $link->attributes;
        $this->assertEquals($attributes->length, 1);
        $url = parse_url($attributes->item(0)->value);
        $this->assertEquals($url['scheme'], 'https');
        $this->assertEquals($url['host'], 'accounts.google.com');
        $this->assertEquals($url['path'], '/o/oauth2/auth');
        parse_str($url['query'], $queryStr);
        $this->assertArrayHasKey('response_type', $queryStr);
        $this->assertEquals($queryStr['response_type'], 'code');
        $this->assertArrayHasKey('access_type', $queryStr);
        $this->assertEquals($queryStr['access_type'], 'online');
        $this->assertArrayHasKey('client_id', $queryStr);
        $this->assertEquals($queryStr['client_id'], 'test');
        $this->assertArrayHasKey('redirect_uri', $queryStr);
        $this->assertEquals($queryStr['redirect_uri'], 'https://example.com/oauth2callback.php?src=google');

        $children = $link->childNodes;
        $this->assertEquals($children->length, 1);
        $this->assertEquals($children->item(0)->tagName, 'img');
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

<?php
require_once('Autoload.php');
class AuthenticatorTest extends PHPUnit_Framework_TestCase
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
        $this->assertFalse($auth->getSupplementLink());
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

        $this->assertFalse($auth->getSupplementLink());
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
        $params = array('current'=>true, 'pending'=>false, 'supplement'=>false);
        $auth = new \Auth\OAuth2\FlipsideAuthenticator($params);
        $this->assertNotNull($auth);
        $this->assertInstanceOf('Auth\OAuth2\FlipsideAuthenticator', $auth);
        $this->assertEquals('burningflipside.com', $auth->getHostName());
        $this->assertEquals('https://profiles.burningflipside.com/OAUTH2/authorize.php?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fburningflipside.com&scope=user', $auth->getAuthorizationUrl());
        $this->assertEquals('https://profiles.burningflipside.com/OAUTH2/token.php?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fburningflipside.com', $auth->getAccessTokenUrl());
        $this->assertEquals('<a href="https://profiles.burningflipside.com/OAUTH2/authorize.php?client_id=test&redirect_uri=https%3A%2F%2Fexample.com%2Foauth%2Fcallbacks%2Fburningflipside.com&scope=user"><img src="/img/common/burningflipside.com_sign_in.png" style="width: 2em;"/></a>', $auth->getSupplementLink());
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
        $this->assertEquals('<a href="https://accounts.google.com/o/oauth2/auth?response_type=code&redirect_uri=https%3A%2F%2Fexample.com%2Foauth2callback.php%3Fsrc%3Dgoogle&client_id=test&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email&access_type=online&approval_prompt=auto"><img src="/img/common/google_sign_in.png" style="width: 2em;"/></a>', $auth->getSupplementLink());
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

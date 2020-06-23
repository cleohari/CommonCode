<?php
require_once('Autoload.php');
class OAuthTest extends PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testConstructor()
    {
        $site = new \MyOAuthClass(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'test1', 'redirect_url'=>'test3'));
        $this->assertNotFalse($site);
        $this->assertEquals('test3', $site->getRedirectUri());

        $_SERVER['HTTP_HOST'] = 'example.com';
        $site = new \MyOAuthClass(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'test1'));
        $this->assertNotFalse($site);
        $this->assertEquals('https://example.com/oauth/callbacks/', $site->getRedirectUri());
    }

    public function testDoAuthPost()
    {
        $curl_exec = $this->getFunctionMock('Httpful', "curl_exec");
        $curl_exec->expects($this->exactly(1))->willReturn("HTTP/1.1 200 OK\r\nDate: Tue, 23 Jun 2020 15:25:09 GMT\r\nServer: Apache\r\nCache-Control: no-store, no-cache, must-revalidate\r\nContent-Length: 437\r\nContent-Type: application/json\r\n\r\n{\"first_name\": \"Bob\", \"last_name\": \"Smith\", \"emails\": {\"preferred\": \"test@example.org\"}}");

        $site = new \MyOAuthClass(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'test1', 'redirect_url'=>'test3'));
        $this->assertNotFalse($site->doAuthPost(array('code'=>'test')));
    }

    public function testBadAuth()
    {
        $curl_exec = $this->getFunctionMock('Httpful', "curl_exec");
        $curl_exec->expects($this->exactly(1))->willReturn("HTTP/1.1 500 Server Error\r\nDate: Tue, 23 Jun 2020 15:25:09 GMT\r\nServer: Apache\r\nCache-Control: no-store, no-cache, must-revalidate\r\nContent-Length: 437\r\nContent-Type: application/json\r\n\r\n{\"first_name\": \"Bob\", \"last_name\": \"Smith\", \"emails\": {\"preferred\": \"test@example.org\"}}");

        $site = new \MyOAuthClass(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'test1', 'redirect_url'=>'test3'));
        $user = false;
        $this->assertEquals(\Flipside\Auth\OAuth2\OAuth2Authenticator::LOGIN_FAILED, $site->authenticate(array('code'=>'test'), $user)); 
    }

    public function testBadToken()
    {
        $curl_exec = $this->getFunctionMock('Httpful', "curl_exec");
        $curl_exec->expects($this->exactly(1))->willReturnCallback(function($ch){
            $info = curl_getinfo($ch);
            switch($info['url'])
            {
            case 'test1&code=test':
                return "HTTP/1.1 200 OK\r\nDate: Tue, 23 Jun 2020 15:25:09 GMT\r\nServer: Apache\r\nCache-Control: no-store, no-cache, must-revalidate\r\nContent-Length: 437\r\nContent-Type: application/json\r\n\r\n{\"access_token\": \"badtoken\"}";
            default:
                var_dump($info['url']);
            }
        });

        $site = new \MyOAuthClass(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'test1', 'redirect_url'=>'test3'));
        $user = false;
        $this->assertEquals(\Flipside\Auth\OAuth2\OAuth2Authenticator::LOGIN_FAILED, $site->authenticate(array('code'=>'test'), $user));
    }

    public function testCreateFail()
    {
        $curl_exec = $this->getFunctionMock('Httpful', "curl_exec");
        $curl_exec->expects($this->exactly(1))->willReturnCallback(function($ch){
            $info = curl_getinfo($ch);
            switch($info['url'])
            {
            case 'test1&code=test':
                return "HTTP/1.1 200 OK\r\nDate: Tue, 23 Jun 2020 15:25:09 GMT\r\nServer: Apache\r\nCache-Control: no-store, no-cache, must-revalidate\r\nContent-Length: 437\r\nContent-Type: application/json\r\n\r\n{\"access_token\": \"goodtoken\"}";
            default:
                var_dump($info['url']);
            }
        });

        $site = new \MyOAuthClass(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'test1', 'redirect_url'=>'test3'));
        $user = false;
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to create user!');
        $site->authenticate(array('code'=>'test'), $user);
    }
}

class MyOAuthClass extends \Flipside\Auth\OAuth2\OAuth2Authenticator 
{
    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    public function getAuthorizationUrl()
    {
        return 'test';
    }

    public function getAccessTokenUrl()
    {
        return 'test1';
    }

    public function getUserFromToken($token)
    {
        if(isset($token->access_token) && strcmp($token->access_token, 'goodtoken') === 0)
        {
            $user = new stdClass();
            $user->mail = 'test@example.org';
            return $user;
        }
        return false;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

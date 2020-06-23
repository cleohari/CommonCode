<?php
require_once('Autoload.php');
class LiveAuthTest extends PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testConstructor()
    {
        $site = new \Flipside\Auth\OAuth2\LiveAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'test1', 'redirect_uri'=>'test3'));
        $this->assertFalse(false);
    }

    public function testGetUserFromToken()
    {
        $curl_exec = $this->getFunctionMock('Httpful', "curl_exec");
        $curl_exec->expects($this->exactly(2))->willReturn("HTTP/1.1 200 OK\r\nDate: Tue, 23 Jun 2020 15:25:09 GMT\r\nServer: Apache\r\nCache-Control: no-store, no-cache, must-revalidate\r\nContent-Length: 437\r\nContent-Type: application/json\r\n\r\n{\"first_name\": \"Bob\", \"last_name\": \"Smith\", \"emails\": {\"preferred\": \"test@example.org\"}}");

        $site = new \Flipside\Auth\OAuth2\LiveAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'test1', 'redirect_uri'=>'test3'));
        $obj = new stdClass();
        $obj->access_token = 'badtoken';
        $user = $site->getUserFromToken($obj);
        $this->assertInstanceOf('Flipside\Auth\PendingUser', $user);
        $this->assertEquals('test@example.org', $user->mail);
        $this->assertEquals('Bob', $user->givenName);
        $this->assertEquals('Smith', $user->sn);

        $site = new \Flipside\Auth\OAuth2\LiveAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'test1', 'redirect_uri'=>'test3'));
        $_SESSION['OAuthToken'] = $obj;
        $user = $site->getUserFromToken(false);
        $this->assertInstanceOf('Flipside\Auth\PendingUser', $user);
        $this->assertEquals('test@example.org', $user->mail);
        $this->assertEquals('Bob', $user->givenName);
        $this->assertEquals('Smith', $user->sn);
    }

    public function testDoAuthPost()
    {
        $curl_exec = $this->getFunctionMock('Httpful', "curl_exec");
        $curl_exec->expects($this->exactly(1))->willReturn("HTTP/1.1 200 OK\r\nDate: Tue, 23 Jun 2020 15:25:09 GMT\r\nServer: Apache\r\nCache-Control: no-store, no-cache, must-revalidate\r\nContent-Length: 437\r\nContent-Type: application/json\r\n\r\n{\"first_name\": \"Bob\", \"last_name\": \"Smith\", \"emails\": {\"preferred\": \"test@example.org\"}}");

        $site = new \Flipside\Auth\OAuth2\LiveAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'app_id'=>'test', 'app_secret'=>'test1', 'redirect_uri'=>'test3'));
        $this->assertNotFalse($site->doAuthPost(array('code'=>'test')));
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

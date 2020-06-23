<?php
require_once('Autoload.php');
class FlipsideAuthTest extends PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testConstructor()
    {
        $site = new \Flipside\Auth\OAuth2\FlipsideAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'api_url'=>'test', 'oauth_url'=>'test1'));
        $this->assertFalse(false);
    }

    public function testConstructorNoAPI()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Incorrectly configured! Missing api_url parameter.');
        $site = new \Flipside\Auth\OAuth2\FlipsideAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'oauth_url'=>'test1'));
    }

    public function testConstructorNoOAuth()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Incorrectly configured! Missing oauth_url parameter.');
        $site = new \Flipside\Auth\OAuth2\FlipsideAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'api_url'=>'test'));
    }

    public function testGetUserFromToken()
    {
        $curl_exec = $this->getFunctionMock('Httpful', "curl_exec");
        $curl_exec->expects($this->exactly(2))->willReturn("HTTP/1.1 200 OK\r\nDate: Tue, 23 Jun 2020 15:25:09 GMT\r\nServer: Apache\r\nCache-Control: no-store, no-cache, must-revalidate\r\nContent-Length: 437\r\nContent-Type: application/json\r\n\r\n{\"displayName\":\"Problem\",\"givenName\":\"Patrick\",\"jpegPhoto\":\"\",\"mail\":\"mail@example.com\",\"mobile\":\"012-345-6789\",\"uid\":\"pboyd\",\"o\":\"Austin Artistic Reconstruction, LLC\",\"title\":[\"Director\"],\"titlenames\":[\"Director\"],\"st\":\"TX\",\"l\":\"AUSTIN\",\"sn\":\"Boyd\",\"cn\":\"Problem\",\"postalAddress\":\"123 Fake Street\",\"postalCode\":\"78753\",\"c\":\"US\",\"ou\":[\"AAR\"],\"class\":\"Flipside\\\\Auth\\\\LDAPUser\"}");

        $site = new \Flipside\Auth\OAuth2\FlipsideAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'api_url'=>'test', 'oauth_url'=>'test1'));
        $user = $site->getUserFromToken(array('access_token'=>'badtoken'));
        $this->assertInstanceOf('Flipside\Auth\FlipsideAPIUser', $user);
        $this->assertEquals('mail@example.com', $user->mail);

        $site = new \Flipside\Auth\OAuth2\FlipsideAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'api_url'=>'test', 'oauth_url'=>'test1'));
        $_SESSION['OAuthToken'] = array('access_token'=>'badtoken');
        $user = $site->getUserFromToken(false);
        $this->assertInstanceOf('Flipside\Auth\FlipsideAPIUser', $user);
        $this->assertEquals('mail@example.com', $user->mail);
    }

    public function testBadLogin()
    {
        $curl_exec = $this->getFunctionMock('Httpful', "curl_exec");
        $curl_exec->expects($this->exactly(1))->willReturn("HTTP/1.1 500 Server Error\r\nDate: Tue, 23 Jun 2020 15:25:09 GMT\r\nServer: Apache\r\nCache-Control: no-store, no-cache, must-revalidate\r\nContent-Length: 437\r\nContent-Type: application/json\r\n\r\n{}");

        $site = new \Flipside\Auth\OAuth2\FlipsideAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'api_url'=>'test', 'oauth_url'=>'test1'));
        $res = $site->login('baduser', 'badpass');
        $this->assertFalse($res);
    }

    public function testGoodLogin()
    {
        $curl_exec = $this->getFunctionMock('Httpful', "curl_exec");
        $curl_exec->expects($this->exactly(1))->willReturn("HTTP/1.1 200 OK\r\nDate: Tue, 23 Jun 2020 15:25:09 GMT\r\nServer: Apache\r\nCache-Control: no-store, no-cache, must-revalidate\r\nContent-Length: 437\r\nContent-Type: application/json\r\n\r\n{\"res\": true, \"extended\": {\"displayName\":\"Problem\",\"givenName\":\"Patrick\",\"jpegPhoto\":\"\",\"mail\":\"mail@example.com\",\"mobile\":\"012-345-6789\",\"uid\":\"pboyd\",\"o\":\"Austin Artistic Reconstruction, LLC\",\"title\":[\"Director\"],\"titlenames\":[\"Director\"],\"st\":\"TX\",\"l\":\"AUSTIN\",\"sn\":\"Boyd\",\"cn\":\"Problem\",\"postalAddress\":\"123 Fake Street\",\"postalCode\":\"78753\",\"c\":\"US\",\"ou\":[\"AAR\"],\"class\":\"Flipside\\\\Auth\\\\LDAPUser\"}}");

        $site = new \Flipside\Auth\OAuth2\FlipsideAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'api_url'=>'test', 'oauth_url'=>'test1'));
        $res = $site->login('baduser', 'badpass');
        $this->assertNotFalse($res);
        $this->assertNotFalse($res['res']);
    }

    public function testIsLoggedIn()
    {
        $site = new \Flipside\Auth\OAuth2\FlipsideAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'api_url'=>'test', 'oauth_url'=>'test1'));

        $this->assertTrue($site->isLoggedIn(array('res'=>true)));
        $this->assertFalse($site->isLoggedIn(array('res'=>false)));
        $this->assertFalse($site->isLoggedIn(false));
        $site->user = true;
        $this->assertTrue($site->isLoggedIn(false));
    }

    public function testGetUser()
    {
        $site = new \Flipside\Auth\OAuth2\FlipsideAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'api_url'=>'test', 'oauth_url'=>'test1'));

        $user = $site->getUser(array());
        $this->assertInstanceOf('Flipside\Auth\FlipsideAPIUser', $user);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

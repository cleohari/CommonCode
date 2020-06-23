<?php
require_once('Autoload.php');
class GoogleAuthTest extends PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testConstructor()
    {
        $site = new \Flipside\Auth\GoogleAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'client_secrets_path'=>'tests/helpers/google.json', 'redirect_url'=>'test1'));
        $this->assertFalse(false);

        $site = new \Flipside\Auth\GoogleAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'client_secrets_path'=>'tests/helpers/google.json'));
        $this->assertFalse(false);
    }

    public function testConstructorNoSecrets()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing required parameter client_secrets_path!');
        $site = new \Flipside\Auth\GoogleAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false));
    }

    public function testAuthenticateFails()
    {
        $site = new \Flipside\Auth\GoogleAuthenticator(array('current'=>true, 'pending'=>false, 'supplement'=>false, 'client_secrets_path'=>'tests/helpers/google.json'));
        $res = $site->authenticate('code');
        $this->assertEquals(\Flipside\Auth\GoogleAuthenticator::LOGIN_FAILED, $res);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

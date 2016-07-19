<?php
require_once('Autoload.php');
require_once('./tests/helpers/TestProvider.php');
class ProviderTest extends PHPUnit_Framework_TestCase
{
    public $check = true;
    private $wasCalled = false;

    public function testProvider()
    {
        $provider = new TestProvider($this);
        $res = $provider->expectTrue(false);
        $this->assertTrue($res);

        $res = $provider->expectTrue('ProviderTest');
        $this->assertTrue($res);

        $res = $provider->expectTrue('InvalidMethod');
        $this->assertFalse($res);

        $res = $provider->expectFalse(false);
        $this->assertFalse($res);

        $res = $provider->expectFalse('ProviderTest');
        $this->assertFalse($res);

        $res = $provider->badCall();
        $this->assertFalse($res);

        $res = $provider->allParams(true, null);
        $this->assertFalse($res);

        $res = $provider->allParams(false, null);
        $this->assertTrue($res);

        $res = $provider->allParams(false, array($this, 'mustCall'));
        $this->assertTrue($res);
        $this->assertTrue($this->wasCalled);
    }

    public function expectTrue()
    {
        return true;
    }

    public function expectFalse()
    {
        return false;
    }

    public function mustCall(&$ret, $res)
    {
        $this->assertTrue($res);
        $ret = $res;
        $this->wasCalled = true;
    }
}

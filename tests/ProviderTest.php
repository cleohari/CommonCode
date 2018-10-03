<?php
require_once('Autoload.php');
require_once('./tests/helpers/TestProvider.php');
class ProviderTest extends PHPUnit\Framework\TestCase
{
    public $check = true;
    private $wasCalled = false;
    private $value = 1;

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

        $res = $provider->addInt(true);
        $this->assertEquals($res, 0);

        $res = $provider->addInt(false);
        $this->assertEquals($res, 1);

        $this->value = 2;
        $res = $provider->addInt(false);
        $this->assertEquals($res, 2);
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

    public function addValue()
    {
        return $this->value;
    }
}

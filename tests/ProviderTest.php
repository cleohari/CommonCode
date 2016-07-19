<?php
require_once('Autoload.php');
require_once('./tests/helpers/TestProvider.php');
class ProviderTest extends PHPUnit_Framework_TestCase
{
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
    }

    public function expectTrue()
    {
        return true;
    }

    public function expectFalse()
    {
        return false;
    }
}

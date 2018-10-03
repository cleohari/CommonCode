<?php
namespace Log;
require_once('Autoload.php');
class LogTest extends \PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function setUp()
    {
        $error_log = $this->getFunctionMock(__NAMESPACE__, "error_log");
        $error_log->expects($this->exactly(1))->willReturnCallback(
            function($message)
            {
                echo $message;
            }
        );
    }

    public function testPHPLogEmergency()
    {
        $log = new \Log\PHPLog();
        $this->expectOutputString('[emergency] Test1');
        $log->log(\Psr\Log\LogLevel::EMERGENCY, 'Test1');
    }

    public function testPHPLogAlert()
    {
        $log = new \Log\PHPLog();
        $this->expectOutputString('[alert] Test2');
        $log->log(\Psr\Log\LogLevel::ALERT, 'Test2');
    }

    public function testPHPLogCritical()
    {
        $log = new \Log\PHPLog();
        $this->expectOutputString('[critical] Test3');
        $log->log(\Psr\Log\LogLevel::CRITICAL, 'Test3');
    }

    public function testPHPLogError()
    {
        $log = new \Log\PHPLog();
        $this->expectOutputString('[error] Test4');
        $log->log(\Psr\Log\LogLevel::ERROR, 'Test4');
    }

    public function testPHPLogWarning()
    {
        $log = new \Log\PHPLog();
        $this->expectOutputString('[warning] Test5');
        $log->log(\Psr\Log\LogLevel::WARNING, 'Test5');
    }

    public function testPHPLogNotice()
    {
        $log = new \Log\PHPLog();
        $this->expectOutputString('[notice] Test6');
        $log->log(\Psr\Log\LogLevel::NOTICE, 'Test6');
    }

    public function testPHPLogInterpolate()
    {
        $log = new \Log\PHPLog();
        $this->expectOutputString('[notice] Test7 Test8');
        $log->log(\Psr\Log\LogLevel::NOTICE, '{str1} {str2}', array('str1'=>'Test7', 'str2'=>'Test8'));
    }
}


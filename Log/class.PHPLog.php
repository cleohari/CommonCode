<?php
namespace Log;

class PHPLog extends \Log\LogService
{
    public function __construct($params=false)
    {
        parent::__construct($params);
    }

    public function log($level, $message, array $context = array())
    {
        $severity = $level;
        switch($level)
        {
            case \Psr\Log\LogLevel::EMERGENCY:
            case \Psr\Log\LogLevel::ALERT:
            case \Psr\Log\LogLevel::CRITICAL:
            case \Psr\Log\LogLevel::ERROR:
            case \Psr\Log\LogLevel::WARNING:
            case \Psr\Log\LogLevel::NOTICE:
            case \Psr\Log\LogLevel::INFO:
            case \Psr\Log\LogLevel::DEBUG:
                break;
            default:
                throw new \Psr\Log\InvalidArgumentException('log function only accepts valid levels. Level was: '.$level);
        }
        if($this->shouldLog($level))
        {
            $newMessage = $this->interpolate($message, $context);
            error_log('['.$level.'] '.$newMessage);
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

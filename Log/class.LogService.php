<?php
namespace Log;
require('vendor/autoload.php');

abstract class LogService extends \Psr\Log\AbstractLogger
{
    protected $logLevels = array(
        \Psr\Log\LogLevel::EMERGENCY,
        \Psr\Log\LogLevel::ALERT,
        \Psr\Log\LogLevel::CRITICAL,
        \Psr\Log\LogLevel::ERROR,
        \Psr\Log\LogLevel::WARNING,
        \Psr\Log\LogLevel::NOTICE);

    public function __construct($params=false)
    {
        if(isset($params['defaultLogLevels']))
        {
            $this->logLevels = $params['defaultLogLevels'];
        }
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    protected function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    protected function shouldLog($level)
    {
        return in_array($level, $this->logLevels);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

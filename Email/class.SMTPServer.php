<?php
/**
 * SMTP Server class
 *
 * This file describes the SMTP Server class
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2016, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

namespace Email;

require '/var/www/common/libs/PHPMailer/PHPMailerAutoload.php';

/**
 * An class to represent an SMTPSErver
 */
class SMTPServer extends \Singleton
{
    protected $smtp;

    protected function __construct()
    {
        $this->smtp = new \SMTP();
        $this->smtp->do_debug = \SMTP::DEBUG_LOWLEVEL;
    }

    public function __destruct()
    {
    }

    public function connect($hostName, $port=25, $useTLS=true)
    {
        $ret = $this->smtp->connect($hostName, $port);
        if($ret === false)
        {
            return $ret;
        }
        $ret = $this->smtp->hello('profiles.burningflipside.com');
        if($ret === false)
        {
            return $ret;
        }
        if($useTLS)
        {
            $ret = $this->smtp->startTLS();
            if($ret === false)
            {
                return $ret;
            }
        }
        return $ret;
    }

    public function authenticate($username, $password)
    {
        return $this->smtp->authenticate($username, $password);
    }

    public function sendOne($from, $to, $msgData)
    {
        $ret = $this->smtp->mail($from);
        if($ret === false)
        {
            return $ret;
        }
        $ret = $this->smtp->recipient($to);
        if($ret === false)
        {
            return $ret;
        }
        return $this->smtp->data($msgData);
    }

    public function disconnect()
    {
        return $this->smtp->quit();
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

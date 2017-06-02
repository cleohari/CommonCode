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

require 'vendor/autoload.php';

/**
 * An class to represent an SMTPSErver
 */
class SMTPServer extends EmailService
{
    protected $smtp;

    public function __construct($params)
    {
        $this->smtp = new \PHPMailer();
        $this->smtp->SMTPDebug = 3;
        $this->smtp->isSMTP();
 
        $this->smtp->Host = $params['host'];
        if(isset($params['port']))
        {
            $this->smtp->Port = $params['port'];
        }
        if(isset($params['encryption']))
        {
            $this->smtp->SMTPSecure = $params['encryption'];
        }

        if(isset($params['username']))
        {
            $this->smtp->SMTPAuth = true;
            $this->smtp->Username = $params['username'];
            $this->smtp->Password = $params['password'];
        }
    }

    public function canSend()
    {
        return true;
    }

    public function decodeAddress($address)
    {
        $pos = strpos($address, '<');
        if($pos === false)
        {
            return array($address);
        }
        $ret = array();
        $ret[0] = trim(substr($address, $pos+1), '>');
        $ret[1] = substr($address, 0, $pos-1);
        return $ret;
    }

    public function sendEmail($email)
    {
        foreach($email->getToAddresses() as $to)
        {
            if(strstr($to, 'free.fr') !== false)
            {
                die('Spammer abuse filter!');
            }
        }

        $this->smtp->isHTML(true); 
        $from = $this->decodeAddress($email->getFromAddress());
        call_user_func_array(array($this->smtp, 'setFrom'), $from);
        $to = $email->getToAddresses();
        foreach($to as $recip)
        {
            $this->smtp->addAddress($recip);
        }
        $cc = $email->getCCAddresses();
        foreach($cc as $recip)
        {
            $this->smtp->addCC($recip);
        }
        $bcc = $email->getBCCAddresses();
        foreach($bcc as $recip)
        {
            $this->smtp->addBCC($recip);
        }
        $rep = $this->decodeAddress($email->getReplyTo());
        call_user_func_array(array($this->smtp, 'addReplyTo'), $rep);
        $this->smtp->Subject = $email->getSubject();
        $this->smtp->Body    = $email->getHTMLBody();
        $this->smtp->AltBody = $email->getTextBody();
        if($email->hasAttachments())
        {
            $attachs = $email->getAttachments();
            foreach($attachs as $attach)
            {
                $this->smtp->addStringAttachment($attach['data'], $attach['name'], 'base64', $attach['mimeType']);
            }
        }
        return $this->smtp->send();
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

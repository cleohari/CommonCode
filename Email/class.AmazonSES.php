<?php
namespace Email;

require('/var/www/common/libs/aws/aws-autoloader.php');
class AmazonSES extends EmailService
{
    protected $ses;

    public function __construct($params)
    {
        $credentials = \Aws\Common\Credentials\Credentials::fromIni('default', $params['ini']);

        $this->ses = \Aws\Ses\SesClient::factory([
                'version' => 'latest',
                'region'  => 'us-west-2',
                'credentials' => $credentials]);
    }

    public function canSend()
    {
        $result = $this->ses->getSendQuota();
        $result = $result->getAll();
        $res = $result['Max24HourSend'] - $result['SentLast24Hours'];
        return $res;
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

        if($email->hasAttachments())
        {
            //Amazeon sendEmail doesn't support attachments. We need to use sendRawEmail
            $args = array();
            $args['RawMessage'] = array();
            $args['RawMessage']['Data'] = base64_encode($email->getRawMessage());
            try {
                return $this->ses->sendRawEmail($args);
            } catch(\Exception $e) {
                return false;
            }
        }
        else
        {
            $args = array();
            $args['Source'] = $email->getFromAddress();
            $args['Destination'] = array();
            $args['Destination']['ToAddresses'] = $email->getToAddresses();
            $args['Destination']['CcAddresses'] = $email->getCCAddresses();
            $args['Destination']['BccAddresses'] = $email->getBCCAddresses();
            $args['Message'] = array();
            $args['Message']['Subject'] = array();
            $args['Message']['Subject']['Data'] = $email->getSubject();
            $args['Message']['Body'] = array();
            $args['Message']['Body']['Text'] = array();
            $args['Message']['Body']['Html'] = array();
            $args['Message']['Body']['Text']['Data'] = $email->getTextBody();
            $args['Message']['Body']['Html']['Data'] = $email->getHtmlBody();
            $args['ReplyToAddresses'] = array($email->getReplyTo());
            try {
                return $this->ses->sendEmail($args);
            } catch(\Exception $e) {
                return false;
            }
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

<?php
require_once('Autoload.php');
require_once('vendor/autoload.php');
class EmailTest extends PHPUnit\Framework\TestCase
{
    public function testEmail()
    {
        $email = new \Flipside\Email\Email();
        $this->assertEquals('Burning Flipside <webmaster@burningflipside.com>', $email->getFromAddress());
        $email->setFromAddress('test@example.com');
        $this->assertEquals('test@example.com', $email->getFromAddress());
        $email->setFromAddress('test@example.com', 'Test User');
        $this->assertEquals('Test User <test@example.com>', $email->getFromAddress());

        $this->assertEmpty($email->getToAddresses());
        $this->assertEmpty($email->getCCAddresses());
        $this->assertEmpty($email->getBCCAddresses());
        $this->assertEquals('Test User <test@example.com>', $email->getReplyTo());
        $this->assertFalse($email->getSubject());
        $this->assertEmpty($email->getHTMLBody());
        $this->assertEmpty($email->getTextBody());
        $this->assertFalse($email->hasAttachments());

        $email->setReplyTo('not-test@example.com', 'Not Test User');
        $this->assertEquals('Not Test User <not-test@example.com>', $email->getReplyTo());

        $email->addToAddress('me@me.com', 'Me');
        $this->assertEquals($email->getToAddresses(), array('Me <me@me.com>'));

        $email->addCCAddress('cc@me.com', 'Me');
        $this->assertEquals($email->getCCAddresses(), array('Me <cc@me.com>'));

        $email->addBCCAddress('bcc@me.com', 'Me');
        $this->assertEquals($email->getBCCAddresses(), array('Me <bcc@me.com>'));

        $email->setSubject('Test Subject');
        $this->assertEquals($email->getSubject(), 'Test Subject');

        $this->assertEquals($email->encodeRecipients('test@test.com'), 'test@test.com');
        
        $res = $email->encodeRecipients('Test User <test@test.com>');
        $this->assertEquals($res, '=?UTF-8?B?VGVzdCBVc2VyIA==?= <test@test.com>');

        $res = $email->encodeRecipients(array('test@test.com', 'me@me.com'));
        $this->assertEquals($res, 'test@test.com, me@me.com');

        $res = $email->encodeRecipients(array('Test User <test@test.com>', 'me@me.com'));
        $this->assertEquals($res, '=?UTF-8?B?VGVzdCBVc2VyIA==?= <test@test.com>, me@me.com');

        $res = $email->encodeRecipients(array('Test User <test@test.com>', 'Bob Smith <me@me.com>'));
        $this->assertEquals($res, '=?UTF-8?B?VGVzdCBVc2VyIA==?= <test@test.com>, =?UTF-8?B?Qm9iIFNtaXRoIA==?= <me@me.com>');
    }

    public function testParsedEmail()
    {
        $email = new \Flipside\Email\Email();
        $Parser = new PlancakeEmailParser($email->getRawMessage());

        $from = $Parser->getHeader('from');
        $to = $Parser->getHeader('to');
        $subject = $Parser->getHeader('subject'); 

        $this->assertEquals('=?UTF-8?B?QnVybmluZyBGbGlwc2lkZSA=?= <webmaster@burningflipside.com>', $from);
        $this->assertEmpty($to);
        $this->assertEmpty($subject);

        $email->setFromAddress('sender@test.com', 'Test Sender');
        $email->setReplyTo('reply@test.com', 'Test Reply');
        $email->addToAddress('to@test.com', 'Test Recipient');
        $email->addCCAddress('cc@test.com', 'Test Carbon Copy');
        $email->addBCCAddress('bcc@me.com', 'Test Blind Carbon Copy');
        $email->setSubject('Test Subject');
        $email->setHTMLBody('Test HTML Body');
        $email->appendToHTMLBody('<br/>');
        $email->setTextBody('Test Text Body');
        $email->appendToTextBody('.');

        $Parser = new PlancakeEmailParser($email->getRawMessage());

        $from = $Parser->getHeader('from');
        $to = $Parser->getHeader('to');
        $cc = $Parser->getHeader('cc');
        $bcc = $Parser->getHeader('bcc');
        $subject = $Parser->getHeader('subject');

        $txtBody = $Parser->getPlainBody();
        $htmBody = $Parser->getHTMLBody();

        $this->assertEquals('=?UTF-8?B?VGVzdCBTZW5kZXIg?= <sender@test.com>', $from);
        $this->assertEquals('=?UTF-8?B?VGVzdCBSZWNpcGllbnQg?= <to@test.com>', $to);
        $this->assertEquals('=?UTF-8?B?VGVzdCBDYXJib24gQ29weSA=?= <cc@test.com>', $cc);
        $this->assertEquals('=?UTF-8?B?VGVzdCBCbGluZCBDYXJib24gQ29weSA=?= <bcc@me.com>', $bcc);
        $this->assertEquals('Test Subject', $subject);
        $this->assertEquals('Test Text Body.', $txtBody);
        // Waiting for https://github.com/plancake/official-library-php-email-parser/pull/25
        //$this->assertEquals('Test HTML Body<br/>', $htmBody);
    }

    public function testEmailService()
    {
        $service = new \Flipside\Email\EmailService(false);
        $this->assertFalse($service->canSend());
        $this->assertFalse($service->sendEmail(false));
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

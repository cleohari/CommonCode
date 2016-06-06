<?php
require_once('Autoload.php');
class EmailTest extends PHPUnit_Framework_TestCase
{
    public function testEmail()
    {
        $email = new \Email\Email();
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
    }

    public function testEmailService()
    {
        $service = new \Email\EmailService(false);
        $this->assertFalse($service->canSend());
        $this->assertFalse($service->sendEmail(false));
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

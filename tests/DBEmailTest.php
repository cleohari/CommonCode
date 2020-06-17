<?php
require_once('Autoload.php');
class DBEmailTest extends PHPUnit\Framework\TestCase
{
    public function testBadConstructor()
    {
        $this->expectException(Exception::class);
        $email = new \MyDBEmail('memory', 'badtable', 'badid');
    }

    public function testBadID()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('CREATE TABLE tblemailT (id varchar(255), b varchar(255));');

        $this->expectException(Exception::class);
        $email = new \MyDBEmail('memory', 'emailT', 'badid');
    }

    public function testGoodNoSub()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('CREATE TABLE tblemail2 (id varchar(255), `from` varchar(255), subject varchar(255), body varchar(255));');
        $dt = $dataSet->getTable('email2');
        $dt->create(array('id' => 'test1', 'body' => 'This is my body'));

        $email = new \MyDBEmail('memory', 'email2', '"test1"');
        $this->assertEquals('Burning Flipside <webmaster@burningflipside.com>', $email->getFromAddress());
        $this->assertEquals('Burning Flipside <webmaster@burningflipside.com>', $email->getReplyTo());
        $this->assertEquals('', $email->getSubject());
        $this->assertEquals('This is my body', $email->getHTMLBody());
        $this->assertEquals('This is my body', $email->getTextBody());

        $dt->create(array('id' => 'test2', 'from' => 'test@example.org', 'subject' => 'test', 'body' => '<b>This is my body</b>'));
        $email = new \MyDBEmail('memory', 'email2', '"test2"');
        $this->assertEquals('test@example.org', $email->getFromAddress());
        $this->assertEquals('test@example.org', $email->getReplyTo());
        $this->assertEquals('test', $email->getSubject());
        $this->assertEquals('<b>This is my body</b>', $email->getHTMLBody());
        $this->assertEquals('This is my body', $email->getTextBody());
    }

    public function testGoodWithSub()
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('memory');
        $dataSet->raw_query('CREATE TABLE tblemail3 (id varchar(255), `from` varchar(255), subject varchar(255), body varchar(255));');
        $dt = $dataSet->getTable('email3');
        $dt->create(array('id' => 'test1', 'body' => 'This is ${sub} body'));

        $email = new \MyDBEmail('memory', 'email3', '"test1"');
        $email->vars['${sub}'] = 'your';
        $this->assertEquals('Burning Flipside <webmaster@burningflipside.com>', $email->getFromAddress());
        $this->assertEquals('Burning Flipside <webmaster@burningflipside.com>', $email->getReplyTo());
        $this->assertEquals('', $email->getSubject());
        $this->assertEquals('This is your body', $email->getHTMLBody());
        $this->assertEquals('This is your body', $email->getTextBody());

        $dt->create(array('id' => 'test2', 'from' => 'test@example.org', 'subject' => 'test', 'body' => '<b>This is ${sub} body</b>'));
        $email = new \MyDBEmail('memory', 'email3', '"test2"');
        $email->vars['${sub}'] = 'your';
        $this->assertEquals('test@example.org', $email->getFromAddress());
        $this->assertEquals('test@example.org', $email->getReplyTo());
        $this->assertEquals('test', $email->getSubject());
        $this->assertEquals('<b>This is your body</b>', $email->getHTMLBody());
        $this->assertEquals('This is your body', $email->getTextBody());
    }
}

class MyDBEmail extends \Flipside\Email\DBEmail
{
    public $vars = array();

    public function getSubstituteVars()
    {
        return $this->vars;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

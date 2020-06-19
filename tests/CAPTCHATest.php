<?php
require_once('Autoload.php');
class CAPTCHATest extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        $GLOBALS['FLIPSIDE_SETTINGS_LOC'] = './tests/helpers';
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('profiles');
        $dataSet->raw_query('CREATE TABLE tblcaptcha (id int, question varchar(255), hint varchar(255), answer varchar(255), PRIMARY KEY (id))');
        $dt = $dataSet->getTable('captcha');
        $dt->create(array('id' => 1, 'question' => 'How much water should you bring to Burning Flipside?', 'hint' => 'gallons per person per day', 'answer' => '3'));
        $dt->create(array('id' => 2, 'question' => 'What does MOOP stand for?', 'hint' => '', 'answer' => 'matter out of place'));
    }

    public function testConstructor()
    {
        $found = array(1 => false, 2 => false);
        for($i = 0; $i < 10; $i++)
        {
            $cap = new \Flipside\FlipsideCAPTCHA();
            $this->assertNotNull($cap);
            $this->assertLessThanOrEqual(2, $cap->random_id);
            $found[$cap->random_id] = true;
            if(count(array_filter($found)) === count($found))
            {
                //Found all ids...
                break;
            }
        }
        $this->assertEquals(count($found), count(array_filter($found)), 'Did not locate all IDs in 10 attempts...');
    }

    public function testValidIDs()
    {
        $validIDs = \Flipside\FlipsideCAPTCHA::getValidCaptchaIDs();
        $this->assertNotNull($validIDs);
        $this->assertEquals(array(1, 2), $validIDs);
    }

    public function testGetAll()
    {
        $all = \Flipside\FlipsideCAPTCHA::getAll();
        $this->assertNotNull($all);
        $this->assertCount(2, $all);
        $this->assertEquals('How much water should you bring to Burning Flipside?', $all[0]->get_question());
        $this->assertEquals('What does MOOP stand for?', $all[1]->get_question());
        $this->assertEquals('gallons per person per day', $all[0]->get_hint());
        $this->assertEquals('', $all[1]->get_hint());
        $this->assertTrue($all[0]->is_answer_right('3'));
        $this->assertTrue($all[1]->is_answer_right('matter out of place'));
        $this->assertFalse($all[1]->is_answer_right('3'));
        $this->assertFalse($all[0]->is_answer_right('matter out of place'));
        $this->assertEquals('{"id":"1","question":"How much water should you bring to Burning Flipside?","hint":"gallons per person per day","answer":"3"}', $all[0]->self_json_encode());
        $this->assertEquals('{"id":"2","question":"What does MOOP stand for?","hint":"","answer":"matter out of place"}', $all[1]->self_json_encode());

        ob_start();
        $all[0]->draw_captcha();
        $html = ob_get_clean();
        $this->assertEquals('<label for="captcha" class="col-sm-2 control-label">How much water should you bring to Burning Flipside?</label><div class="col-sm-10"><input class="form-control" type="text" id="captcha" name="captcha" placeholder="gallons per person per day" required/></div><div class="col-sm-10">The answer to this question may be found in the Burning Flipside Survival Guide. It may be found <a href="https://www.burningflipside.com/sg">here</a>.</div>', $html);

        $html = $all[0]->draw_captcha(false, true);
        $this->assertEquals('<label for="captcha" class="col-sm-2 control-label">How much water should you bring to Burning Flipside?</label><div class="col-sm-10"><input class="form-control" type="text" id="captcha" name="captcha" placeholder="gallons per person per day" required/></div>', $html);

        $html = $all[0]->draw_captcha(false, true, true);
        $this->assertEquals('<form id="flipcaptcha" name="flipcaptcha"><label for="captcha" class="col-sm-2 control-label">How much water should you bring to Burning Flipside?</label><div class="col-sm-10"><input class="form-control" type="text" id="captcha" name="captcha" placeholder="gallons per person per day" required/></div></form>', $html);
    }

    /**
     * @depends testGetAll
     */
    public function testAdd()
    {
        $ret = \Flipside\FlipsideCAPTCHA::save_new_captcha('Testing...', '', '123');
        $this->assertTrue($ret);
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('profiles');
        $dt = $dataSet->getTable('captcha');
        $data = $dt->read(new \Flipside\Data\Filter('answer eq 123'));
        $this->assertEquals(array(array('id' => null, 'question' => 'Testing...', 'hint' => '', 'answer' => '123')), $data);
    }

    public static function tearDownAfterClass(): void
    {
        $dataSet = \Flipside\DataSetFactory::getDataSetByName('profiles');
        $dataSet->raw_query('DROP TABLE tblcaptcha;');
        unset($GLOBALS['FLIPSIDE_SETTINGS_LOC']);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

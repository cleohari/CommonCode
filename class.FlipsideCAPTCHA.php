<?php
/**
 * FlipsideCAPTCHA class
 *
 * This file describes the FlipsideCAPTCHA class
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

/**
 * Allow other classes to be loaded as needed
 */
require_once('Autoload.php');

/**
 * A class to represent a Completely Automated Public Turing test to tell Computers and Humans Apart
 */
class FlipsideCAPTCHA implements JsonSerializable
{
    /**
     * The ID of the CAPTCHA in the DB
     */
    public  $random_id;
    /**
     * An array of all valid IDs in the DB
     */
    private $validIDs;

    public  $wwwUrl;

    /**
     * Get all valid CAPTCH IDs
     *
     * @return array An array of captch IDs
     *
     * @deprecated 2.1 Will be removed in favor of self::getValidCaptchaIDs()
     */
    public static function get_valid_captcha_ids()
    {
        return self::getValidCaptchaIDs();
    }

    /**
     * Get all valid CAPTCH IDs
     *
     * @return array An array of captch IDs
     */
    public static function getValidCaptchaIDs()
    {
        $datatable = DataSetFactory::getDataTableByNames('profiles', 'captcha');
        $data = $datatable->read(false, array('id'));
        $count = count($data);
        for($i = 0; $i < $count; $i++)
        {
            $data[$i] = $data[$i]['id'];
        }
        return $data;
    }

    /**
     * Get an array of all CAPTCHAs
     *
     * @return array An array of captchas
     *
     * @deprecated 2.1 Will be removed in favor of self::getAll()
     */
    public static function get_all()
    {
        return self::getAll();
    }

    /**
     * Get an array of all CAPTCHAs
     *
     * @return array An array of captchas
     */
    public static function getAll()
    {
        $res = array();
        $ids = self::getValidCaptchaIDs();
        $count = count($ids);
        for($i = 0; $i < $count; $i++)
        {
            $captcha = new FlipsideCAPTCHA();
            $captcha->random_id = $ids[$i];
            array_push($res, $captcha);
        }
        return $res;
    }

    public static function save_new_captcha($question, $hint, $answer)
    {
        $dataset = DataSetFactory::getDataSetByName('profiles');
        $datatable = $dataset['captcha'];
        return $datatable->create(array('question'=>$question, 'hint'=>$hint, 'answer'=>$answer));
    }

    public function __construct()
    {
        $this->validIDs = FlipsideCAPTCHA::get_valid_captcha_ids();
        $this->random_id = mt_rand(0, count($this->validIDs) - 1);
        $this->random_id = $this->validIDs[$this->random_id];
        $settings = \Settings::getInstance();
        $this->wwwUrl = $settings->getGlobalSetting('www_url', 'https://www.burningflipside.com');
    }

    protected function getCaptchField($fieldName)
    {
        $dataset = DataSetFactory::getDataSetByName('profiles');
        $datatable = $dataset['captcha'];
        $data = $datatable->read(new \Data\Filter('id eq '.$this->random_id), array($fieldName));
        if(empty($data))
        {
            return false;
        }
        return $data[0][$fieldName];
    }

    public function get_question()
    {
        return $this->getCaptchField('question');
    }

    public function get_hint()
    {
        return $this->getCaptchField('hint');
    }

    private function get_answer()
    {
        return $this->getCaptchField('answer');
    }

    public function is_answer_right($answer)
    {
        return strcasecmp($this->get_answer(), $answer) === 0;
    }

    public function draw_captcha($explination = true, $return = false, $ownForm = false)
    {
        $string = '';

        if($ownForm)
        {
            $string .= '<form id="flipcaptcha" name="flipcaptcha">';
        }

        $string .= '<label for="captcha" class="col-sm-2 control-label">'.$this->get_question().'</label><div class="col-sm-10"><input class="form-control" type="text" id="captcha" name="captcha" placeholder="'.$this->get_hint().'" required/></div>';
        if($ownForm)
        {
            $string .= '</form>';
        }
        if($explination)
        {
            $string .= '<div class="col-sm-10">The answer to this question may be found in the Burning Flipside Survival Guide. It may be found <a href="'.$this->wwwUrl.'/sg">here</a>.</div>';
        }
        
        if(!$return)
        {
            echo $string;
        }
        return $string;
    }

    public function self_json_encode()
    {
        return json_encode($this->jsonSerialize());
    }

    public function jsonSerialize()
    {
        $res = array();
        $res['id'] = $this->random_id;
        $res['question'] = $this->get_question();
        $res['hint'] = $this->get_hint();
        $res['answer'] = $this->get_answer();
        return $res;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

<?php
/**
 * EmailProvider class
 *
 * This file describes the Singleton EmailProvider class
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

/**
 * use the FlipsideSettings class
 */
require_once("/var/www/secure_settings/class.FlipsideSettings.php");

/**
 * Allow other classes to be loaded as needed
 */
require_once('Autoload.php');

/**
 * A singleton class allowing the caller to send Email
 *
 * This class will abstract out how email is sent
 */
class EmailProvider extends Singleton
{
    /** An array of methods that can be used to send email */
    protected $methods;

    /**
     * Enumerate all supported EmailServices and instacetate them
     */
    protected function __construct()
    {
        $this->methods = array();
        if(isset(FlipsideSettings::$email_providers))
        {
            $keys = array_keys(FlipsideSettings::$email_providers);
            $count = count($keys);
            for($i = 0; $i < $count; $i++)
            {
                $class = $keys[$i];
                array_push($this->methods, new $class(FlipsideSettings::$email_providers[$keys[$i]]));
            }
        }
    }

    /**
     * Get the email provider by name
     *
     * @param string $methodName The class name of the email method
     *
     * @return false|\Email\EmailService The Email service specified or false if it is not found
     */
    public function getEmailMethod($methodName)
    {
        $count = count($this->methods);
        for($i = 0; $i < $count; $i++)
        {
            if(strcasecmp(get_class($this->methods[$i]), $methodName) === 0)
            {
                return $this->methods[$i];
            }
        }
        return false;
    }

    /**
     * Send the email
     *
     * @param Email\Email $email The email message to send
     * @param string $methodName The class name of the email method
     *
     * @return boolean True if the email was sent, false otherwise
     */
    public function sendEmail($email, $methodName = false)
    {
        if($methodName === false)
        {
            $res = false;
            $count = count($this->methods);
            for($i = 0; $i < $count; $i++)
            {
                $res = $this->methods[$i]->sendEmail($email);
                if($res !== false)
                {
                    return $res;
                }
            }
            return $res;
        }
        else
        {
            $method = $this->getEmailMethod($methodName);
            return $method->sendEmail($email);
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

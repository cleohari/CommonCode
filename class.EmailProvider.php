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
 * Allow other classes to be loaded as needed
 */
require_once('Autoload.php');

/**
 * A singleton class allowing the caller to send Email
 *
 * This class will abstract out how email is sent
 */
class EmailProvider extends Provider
{
    /** An array of methods that can be used to send email */
    protected $methods;

    /**
     * Enumerate all supported EmailServices and instacetate them
     */
    protected function __construct()
    {
        $settings = \Settings::getInstance();
        $this->methods = $settings->getClassesByPropName('email_providers');
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
            return $this->callOnEach('sendEmail', array($email));
        }
        else
        {
            $method = $this->getMethodByName($methodName);
            return $method->sendEmail($email);
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

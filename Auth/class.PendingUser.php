<?php
/**
 * PendingUser class
 *
 * This file describes the PendingUser classes
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

namespace Auth;

/**
 * A class to abstract access to PendingUsers (users that have not completed registration) regardless of the Authentication type used.
 *
 * This class is the primary method to access pending user information.
 */
class PendingUser extends User
{
    protected $intData = array();

    public function getHash()
    {
        return false;
    }

    public function getRegistrationTime()
    {
        return false;
    }

    /**
     * Is this user in the Group or a child of that group?
     *
     * @param string $name The name of the group to check if the user is in
     *
     * @return boolean True if the user is in the group, false otherwise
     */
    public function isInGroupNamed($name)
    {
        return false;
    }

    public function __get($propName)
    {
        if(isset($this->intData[$propName]))
        {
            return $this->intData[$propName];
        }
        return parent::__get($propName);
    }

    public function __set($propName, $value)
    {
        $this->intData[$propName] = $value;
    }

    public function __isset($propName)
    {
        return isset($this->intData[$propName]);
    }

    /**
     * Get the user's password as specified during registration
     *
     * We need the ability to obtain the user's unhashed plain text password to allow for it to be sent 
     * to the correct backend which will hash it
     *
     * @return boolean|string The current password
     */
    public function getPassword()
    {
        return false;
    }

    /**
     * Set the user's email address
     *
     * @param string $email The user's new email address
     *
     * @return boolean true if the user's email address was changed, false otherwise
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return true;
    }

    /**
     * Set the user's given (first) name
     *
     * @param string $givenName The user's new given name
     *
     * @return boolean true if the user's given name was changed, false otherwise
     */
    public function setGivenName($givenName)
    {
        $this->givenName = $givenName;
        return true;
    }

    /**
     * Set the user's last name
     *
     * @param string $sn The user's new last name
     *
     * @return boolean true if the user's last name was changed, false otherwise
     */
    public function setLastName($sn)
    {
        $this->sn = $sn;
        return true;
    }

    /**
     * Serialize the user data into a format usable by the json_encode method
     *
     * @return array A simple keyed array representing the user
     */
    public function jsonSerialize()
    {
        $user = array();
        $user['hash'] = $this->getHash();
        $user['mail'] = $this->mail;
        $user['uid'] = $this->uid;
        $time = $this->getRegistrationTime();
        if($time !== false)
        {
            $user['time'] = $time->format(\DateTime::RFC822);
        }
        $user['class'] = get_class($this);
        return $user; 
    }

    public function sendEmail()
    {
        $email_msg = new \Email\Email();
        $email_msg->addToAddress($this->mail);
        $email_msg->setTextBody('Thank you for signing up with Burning Flipside. Your registration is not complete until you goto the address below.
                https://profiles.burningflipside.com/finish.php?hash='.$this->getHash().'
                Thank you,
                Burning Flipside Technology Team');
        $email_msg->setHTMLBody('Thank you for signing up with Burning Flipside. Your registration is not complete until you follow the link below.<br/>
                <a href="https://profiles.burningflipside.com/finish.php?hash='.$this->getHash().'">Complete Registration</a><br/>
                Thank you,<br/>
                Burning Flipside Technology Team');
        $email_msg->setSubject('Burning Flipside Registration');
        $email_provider = \EmailProvider::getInstance();
        if($email_provider->sendEmail($email_msg) === false)
        {
            throw new \Exception('Unable to send email!');
        }
        return true;
    }

    public function delete()
    {
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

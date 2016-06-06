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
     * @return true|false True if the user is in the group, false otherwise
     */
    public function isInGroupNamed($name)
    {
        return false;
    }

    /**
     * The email address for the user
     *
     * @return string The user's email address
     */
    public function getEmail()
    {
        if(isset($this->email))
        {
            return $this->email;
        }
        return parent::getEmail();
    }

    /**
     * The given (or first) name for the user
     *
     * @return string The user's first name
     */
    public function getGivenName()
    {
        if(isset($this->givenName))
        {
            return $this->givenName;
        }
        return parent::getGivenName();
    }

    /**
     * The last name for the user
     *
     * @return string The user's last name
     */
    public function getLastName()
    {
        if(isset($this->sn))
        {
            return $this->sn;
        }
        return parent::getLastName();
    }

    /**
     * Get the user's password as specified during registration
     *
     * We need the ability to obtain the user's unhashed plain text password to allow for it to be sent 
     * to the correct backend which will hash it
     *
     * @return string The current password
     */
    public function getPassword()
    {
        return false;
    }

    /**
     * The supplemental login types that the user can use to login
     *
     * @return array The user's login providers
     */
    function getLoginProviders()
    {
        if(isset($this->host))
        {
            return $this->host;
        }
        return parent::getLoginProviders();
    }

    /**
     * Add a supplemental login type that the user can use to login
     *
     * @param string $provider The hostname for the provider
     *
     * @return true|false true if the addition worked, false otherwise
     */
    function addLoginProvider($provider)
    {
        if(isset($this->host))
        {
            array_push($this->host, $provider);
        }
        else
        {
            $this->host = array($provider);
        }
    }

    /**
     * Set the user's email address
     *
     * @param string $email The user's new email address
     *
     * @return true|false true if the user's email address was changed, false otherwise
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return true;
    }

    /**
     * Set the user's given (first) name
     *
     * @param string $name The user's new given name
     *
     * @return true|false true if the user's given name was changed, false otherwise
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
     * @return true|false true if the user's last name was changed, false otherwise
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
        $user['mail'] = $this->getEmail();
        $user['uid'] = $this->getUid();
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
        $email_msg->addToAddress($this->getEmail());
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
?>

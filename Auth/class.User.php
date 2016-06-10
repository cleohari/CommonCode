<?php
/**
 * User class
 *
 * This file describes the User classes
 *
 * PHP version 5 and 7
 *
 * @author Patrick Boyd / problem@burningflipside.com
 * @copyright Copyright (c) 2015, Austin Artistic Reconstruction
 * @license http://www.apache.org/licenses/ Apache 2.0 License
 */

namespace Auth;

/**
 * A class to abstract access to Users regardless of the Authentication type used.
 *
 * This class is the primary method to access user information.
 */
class User extends \SerializableObject
{
    /**
     * An array to cache the title to string mappings so that they don't need to be pulled from the database
     * everytime
     */ 
    public static $titlenames = null;

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
        return false;
    }

    public function __set($propName, $value)
    {
    }

    public function __isset($propName)
    {
        return false;
    }

    /**
     * The list of titles for the user
     *
     * @return boolean|array The user's title(s) in user friendly strings
     *
     * @SuppressWarnings("StaticAccess")
     */
    public function getTitleNames()
    {
        $titles = $this->title;
        if($titles === false)
        {
            return false;
        }
        if(self::$titlenames === null)
        {
            $dataSet = \DataSetFactory::getDataSetByName('profiles');
            $dataTable = $dataSet['position'];
            $titlenames = $dataTable->read();
            self::$titlenames = array();
            $count = count($titlenames);
            for($i = 0; $i < $count; $i++)
            {
                self::$titlenames[$titlenames[$i]['short_name']] = $titlenames[$i];
            }
        }
        $count = count($titles);
        for($i = 0; $i < $count; $i++)
        {
            if(isset(self::$titlenames[$titles[$i]]))
            {
                $title = self::$titlenames[$titles[$i]];
                $titles[$i] = $title['name'];
            }
        }
        return $titles;
    }

    /**
     * The groups the user is a part of
     *
     * @return boolean|array The user's Auth\Group structures
     */
    public function getGroups()
    {
        return false;
    }

    /**
     * Add a supplemental login type that the user can use to login
     *
     * @param string $provider The hostname for the provider
     */
    public function addLoginProvider($provider)
    {
        if(isset($this->host))
        {
            $tmp = $this->host;
            $tmp[] = $provider;
            $this->host = $tmp;
        }
        else
        {
            $this->host = array($provider);
        }
    }

    /**
     * Can the user login with this provider?
     *
     * @param string $provider The hostname for the provider
     *
     * @return boolean true if they can login with the provider, false otherwise
     */
    public function canLoginWith($provider)
    {
        $hosts = $this->host;
        if($hosts === false)
        {
            return false;
        }
        $count = count($hosts);
        for($i = 0; $i < $count; $i++)
        {
            if(strcasecmp($hosts[$i], $provider) === 0)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Set the user's password without verifying the current password
     *
     * @param string $password The new user password
     *
     * @return boolean true if the user's password was changed, false otherwise
     */
    protected function setPass($password)
    {
        return false;
    }

    /**
     * Has the user completely filled out their user profile?
     *
     * @return boolean true if the user's profile is complete, false otherwise
     */
    public function isProfileComplete()
    {
        if($this->c === false || $this->postalAddress === false ||
           $this->postalCode === false || $this->l === false ||
           $this->st === false || $this->mobile === false)
        {
            return false;
        }
        return true;
    }

    /**
     * Validate that the user's password is the specified password
     *
     * @param string $password The user's current password
     *
     * @return boolean true if the user's password is correct, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function validate_password($password)
    {
        return false;
    }

    /**
     * Validate that the user's reset hash is the sepcified hash
     *
     * @param string $hash The user's reset hash
     *
     * @return boolean true if the user's hash is correct, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function validate_reset_hash($hash)
    {
        return false;
    }

    /**
     * Change the user's password, validating the old password or reset hash
     *
     * @param string $oldpass The user's original password or reset hash if $isHash is true
     * @param string $newpass The user's new password
     * @param boolean $isHash Is $old_pass a password or a hash
     *
     * @return boolean true if the user's password was changed, false otherwise
     */
    public function change_pass($oldpass, $newpass, $isHash = false)
    {
        if($isHash === false && $this->validate_password($oldpass) === false)
        {
            throw new \Exception('Invalid Password!', 3);
        }
        if($isHash === true && $this->validate_reset_hash($oldpass) === false)
        {
            throw new \Exception('Invalid Reset Hash!', 3);
        }
        if($this->setPass($newpass) === false)
        {
            throw new \Exception('Unable to set password!', 6);
        }
        return true;
    }

    /**
     * Set the user's display name
     *
     * @param string $name The user's new display name
     *
     * @return boolean true if the user's display name was changed, false otherwise
     */
    public function setDisplayName($name)
    {
        return $this->setNickName($name);
    }

    /**
     * Set the user's given (first) name
     *
     * @param string $name The user's new given name
     *
     * @return boolean true if the user's given name was changed, false otherwise
     */
    public function setGivenName($name)
    {
        return $this->setUid($name);
    }

    /**
     * Set the user's email address
     *
     * @param string $email The user's new email address
     *
     * @return boolean true if the user's email address was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setEmail($email)
    {
        return false;
    }

    /**
     * Set the user's user ID or user name
     *
     * @param string $uid The user's new user ID
     *
     * @return boolean true if the user's ID was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setUid($uid)
    {
        return false;
    }

    /**
     * Set the user's photo
     *
     * @param string $photo The user's new photo as a binary string
     *
     * @return boolean true if the user's photo was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setPhoto($photo)
    {
        return false;
    }

    /**
     * Set the user's phone number
     *
     * @param string $phone The user's new phonew number
     *
     * @return boolean true if the user's phone number was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setPhoneNumber($phone)
    {
        return false;
    }

    /**
     * Set the user's organization
     *
     * @param string $org The user's new organization
     *
     * @return boolean true if the user's organization was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setOrganization($org)
    {
        return false;
    }

    /**
     * Set the user's titles
     *
     * @param string $titles The user's new titles
     *
     * @return boolean true if the user's titles were changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setTitles($titles)
    {
        return false;
    }

    /**
     * Set the user's state
     *
     * @param string $state The user's new state
     *
     * @return boolean true if the user's state was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setState($state)
    {
        return false;
    }

    /**
     * Set the user's city
     *
     * @param string $city The user's new city
     *
     * @return boolean true if the user's city was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setCity($city)
    {
        return false;
    }

    /**
     * Set the user's last name
     *
     * @param string $sn The user's new last name
     *
     * @return boolean true if the user's last name was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setLastName($sn)
    {
        return false;
    }

    /**
     * Set the user's nick name
     *
     * @param string $displayName The user's new nick name
     *
     * @return boolean true if the user's nick name was changed, false otherwise
     */
    public function setNickName($displayName)
    {
        return $this->setUid($displayName);
    }

    /**
     * Set the user's mailing address
     *
     * @param string $address The user's new mailing address
     *
     * @return boolean true if the user's mailing address was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setAddress($address)
    {
        return false;
    }

    /**
     * Set the user's postal or zip code
     *
     * @param string $postalcode The user's new postal code
     *
     * @return boolean true if the user's postal code was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setPostalCode($postalcode)
    {
        return false;
    }

    /**
     * Set the user's country
     *
     * @param string $country The user's new country
     *
     * @return boolean true if the user's country was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setCountry($country)
    {
        return false;
    }

    /**
     * Set the user's organizations
     *
     * @param string $ous The user's new organizations
     *
     * @return boolean true if the user's organizations was changed, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function setOrganizationUnits($ous)
    {
        return false;
    }

    /**
     * Allow write for the user
     */
    protected function enableReadWrite()
    {
        //Make sure we are bound in write mode
        $auth = \AuthProvider::getInstance();
        $ldap = $auth->getMethodByName('Auth\LDAPAuthenticator');
        if($ldap !== false)
        {
            $ldap->get_and_bind_server(true);
        }
    }

    /**
     * Update the user password if required
     */
    private function editUserPassword($data)
    {
        if(isset($data->password))
        {
            if(isset($data->oldpass))
            {
                $this->change_pass($data->oldpass, $data->password);
                unset($data->oldpass);
            }
            else if(isset($data->hash))
            {
                $this->change_pass($data->hash, $data->password, true);
                unset($data->hash);
            }
            unset($data->password);
        }
    }

    private function editNames($data)
    {
        if(isset($data->displayName))
        {
            $this->setDisplayName($data->displayName);
            unset($data->displayName);
        }
        if(isset($data->givenName))
        {
            $this->setGivenName($data->givenName);
            unset($data->givenName);
        }
        if(isset($data->sn))
        {
            $this->setLastName($data->sn);
            unset($data->sn);
        }
        if(isset($data->cn))
        {
            $this->setNickName($data->cn);
            unset($data->cn);
        }
    }

    private function checkForUnsettableElements($data)
    {
        if(isset($data->mail))
        {
            if($data->mail !== $this->mail)
            {
                throw new \Exception('Unable to change email!');
            }
            unset($data->mail);
        }
        if(isset($data->uid))
        {
            if($data->uid !== $this->uid)
            {
                throw new \Exception('Unable to change uid!');
            }
            unset($data->uid);
        }
    }

    private function editAddressElements($data)
    {
        if(isset($data->postalAddress))
        {
            $this->setAddress($data->postalAddress);
            unset($data->postalAddress);
        }
        if(isset($data->l))
        {
            $this->setCity($data->l);
            unset($data->l);
        }
        if(isset($data->st))
        {
            $this->setState($data->st);
            unset($data->st);
        }
        if(isset($data->postalCode))
        {
            $this->setPostalCode($data->postalCode);
            unset($data->postalCode);
        }
        if(isset($data->c))
        {
            $this->setCountry($data->c);
            unset($data->c);
        }
    }

    private function editOrganizationElements($data)
    {
        if(isset($data->o))
        {
            $this->setOrganization($data->o);
            unset($data->o);
        }
        if(isset($data->title))
        {
            $this->setTitles($data->title);
            unset($data->title);
        }
        if(isset($data->ou))
        {
            $this->setOrganizationUnits($data->ou);
            unset($data->ou);
        }
    }

    /**
     * Modify the user given the provided data object
     *
     * @param stdClass $data The user's new data
     *
     * @return boolean true if the user's data was changed, false otherwise
     */
    public function editUser($data)
    {
        $this->enableReadWrite();

        $this->checkForUnsettableElements($data);
        $this->editUserPassword($data);
        $this->editNames($data);
        $this->editAddressElements($data);
        $this->editOrganizationElements($data);

        if(isset($data->jpegPhoto))
        {
            $this->setPhoto(base64_decode($data->jpegPhoto));
            unset($data->jpegPhoto);
        }
        if(isset($data->mobile))
        {
            $this->setPhoneNumber($data->mobile);
            unset($data->mobile);
        }
    }

    /**
     * Obtain the user's password reset hash
     *
     * @return string|false A hash if available, false otherwise
     */
    public function getPasswordResetHash()
    {
        return false;
    }

    /**
     * Serialize the user data into a format usable by the json_encode method
     *
     * @return array A simple keyed array representing the user
     */
    public function jsonSerialize()
    {
        $user = array();
        $user['displayName'] = $this->displayName;
        $user['givenName'] = $this->givenName;
        $user['jpegPhoto'] = base64_encode($this->jpegPhoto);
        $user['mail'] = $this->mail;
        $user['mobile'] = $this->mobile;
        $user['uid'] = $this->uid;
        $user['o'] = $this->o;
        $user['title'] = $this->title;
        $user['titlenames'] = $this->getTitleNames();
        $user['st'] = $this->st;
        $user['l'] = $this->l;
        $user['sn'] = $this->sn;
        $user['cn'] = $this->cn;
        $user['postalAddress'] = $this->postalAddress;
        $user['postalCode'] = $this->postalCode;
        $user['c'] = $this->c;
        $user['ou'] = $this->ou;
        $user['host'] = $this->host;
        $user['class'] = get_class($this);
        return $user;
    }

    /**
     * Serialize the user data into a VCARD 2.1 format
     *
     * @return string The VCARD for the user
     */
    public function getVcard()
    {
        $ret = "BEGIN:VCARD\nVERSION:2.1\n";
        $ret .= 'N:'.$this->sn.';'.$this->givenName."\n";
        $ret .= 'FN:'.$this->givenName."\n";
        $titles = $this->title;
        if($titles !== false)
        {
            $ret .= 'TITLE:'.implode(',', $titles)."\n";
        }
        $ret .= "ORG: Austin Artistic Reconstruction\n";
        $ret .= 'TEL;TYPE=MOBILE,VOICE:'.$this->mobile."\n";
        $ret .= 'EMAIL;TYPE=PREF,INTERNET:'.$this->mail."\n";
        $ret .= "END:VCARD\n";
        return $ret;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

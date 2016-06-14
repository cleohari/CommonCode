<?php
/**
 * AuthProvider class
 *
 * This file describes the AuthProvider Singleton
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
 * A Singleton class to abstract access to the authentication providers.
 *
 * This class is the primary method to access user data, login, and other authenication information.
 */
class AuthProvider extends Provider
{
    /**
     * Load the authentrication providers specified in the Settings $authProviders array
     *
     * @SuppressWarnings("StaticAccess")
     */
    protected function __construct()
    {
        $settings = \Settings::getInstance();
        $this->methods = $settings->getClassesByPropName('authProviders');
    }

    /**
     * Get the Auth\User class instance for the specified login
     *
     * Unlike the AuthProvider::login() function. This function will not impact the SESSION
     *
     * @param string $username The username of the User
     * @param string $password The password of the User
     *
     * @return Auth\User|false The User with the specified credentials or false if the credentials are not valid
     */
    public function getUserByLogin($username, $password)
    {
        $res = false;
        $count = count($this->methods);
        for($i = 0; $i < $count; $i++)
        {
            $res = $this->methods[$i]->login($username, $password);
            if($res !== false)
            {
                return $this->methods[$i]->getUser($res);
            }
        }
        return $res;
    }

    /**
     * Use the provided credetials to log the user on
     *
     * @param string $username The username of the User
     * @param string $password The password of the User
     *
     * @return true|false true if the login was successful, false otherwise
     */
    public function login($username, $password)
    {
        $res = false;
        $count = count($this->methods);
        for($i = 0; $i < $count; $i++)
        {
            $res = $this->methods[$i]->login($username, $password);
            if($res !== false)
            {
                FlipSession::setVar('AuthMethod', get_class($this->methods[$i]));
                FlipSession::setVar('AuthData', $res);
                break;
            }
        }
        return $res;
    }

    /**
     * Determine if the user is still logged on from the session data
     *
     * @param stdClass $data The AuthData from the session
     * @param string $methodName The AuthMethod from the session
     *
     * @return true|false true if user is logged on, false otherwise
     */
    public function isLoggedIn($data, $methodName)
    {
        $auth = $this->getMethodByName($methodName);
        return $auth->isLoggedIn($data);
    }

    /**
     * Obtain the currently logged in user from the session data
     *
     * @param stdClass $data The AuthData from the session
     * @param string $methodName The AuthMethod from the session
     *
     * @return Auth\User|false The User instance if user is logged on, false otherwise
     */
    public function getUser($data, $methodName)
    {
        $auth = $this->getMethodByName($methodName);
        return $auth->getUser($data);
    }

    /**
     * Merge or set the returnValue as appropriate
     *
     * @param false|Auth\Group|Auth\User $returnValue The value to merge to
     * @param Auth\Group|Auth\User $res The value to merge from
     *
     * @return Auth\Group|false The merged returnValue
     */
    private function mergeResult(&$returnValue, $res)
    {
        if($res === false)
        {
            return;
        }
        if($returnValue === false)
        {
            $returnValue = $res;
            return;
        }
        $returnValue->merge($res);
    }

    /**
     * Calls the indicated function on each Authenticator and merges the result
     *
     * @param string $functionName The function to call
     * @param array $args The arguments for the function
     * @param string $checkField A field to check if it is set a certain way before calling the function
     * @param mixed $checkValue The value that field should be set to to not call the function
     *
     * @return Auth\Group|Auth\User|false The merged returnValue
     */
    private function callOnEach($functionName, $args, $checkField = false, $checkValue = false)
    {
        $ret = false;
        $count = count($this->methods);
        for($i = 0; $i < $count; $i++)
        {
            if($checkField !== false)
            {
                if($this->methods[$i]->{$checkField} === $checkValue)
                {
                    continue;
                }
            }
            $res = call_user_func_array(array($this->methods[$i], $functionName), $args);
            $this->mergeResult($ret, $res);
        }
        return $ret;
    }

    /**
     * Calls the indicated function on each Authenticator and add the result
     *
     * @param string $functionName The function to call
     * @param string $checkField A field to check if it is set a certain way before calling the function
     * @param mixed $checkValue The value that field should be set to to not call the function
     *
     * @return integer The added returnValue
     */
    private function addFromEach($functionName, $checkField = false, $checkValue = false)
    {
        $retCount = 0;
        $count = count($this->methods);
        for($i = 0; $i < $count; $i++)
        {
            if($checkField !== false)
            {
                if($this->methods[$i]->{$checkField} === $checkValue)
                {
                    continue;
                }
            }
            $res = call_user_func(array($this->methods[$i], $functionName));
            $retCount += $res;
        }
        return $retCount;
    }

    /**
     * Get an Auth\Group by its name
     *
     * @param string $name The name of the group
     * @param string $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return Auth\Group|false The Group instance if a group with that name exists, false otherwise
     */
    public function getGroupByName($name, $methodName = false)
    {
        if($methodName === false)
        {
            return $this->callOnEach('getGroupByName', array($name));
        }
        $auth = $this->getMethodByName($methodName);
        return $auth->getGroupByName($name);
    }

    /**
     * Get an array of Auth\User from a filtered set
     *
     * @param Data\Filter|boolean $filter The filter conditions or false to retreive all
     * @param array|boolean $select The user fields to obtain or false to obtain all
     * @param integer|boolean $top The number of users to obtain or false to obtain all
     * @param integer|boolean $skip The number of users to skip or false to skip none
     * @param array|boolean $orderby The field to sort by and the method to sort or false to not sort
     * @param string|boolean $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return array|boolean An array of Auth\User objects or false if no users were found
     */
    public function getUsersByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false, $methodName = false)
    {
        if($methodName === false)
        {
            return $this->callOnEach('getUsersByFilter', array($filter, $select, $top, $skip, $orderby), 'current');
        }
        $auth = $this->getMethodByName($methodName);
        return $auth->getUsersByFilter($filter, $select, $top, $skip, $orderby);
    }

    /**
     * Get an array of Auth\PendingUser from a filtered set
     *
     * @param Data\Filter|boolean $filter The filter conditions or false to retreive all
     * @param array|boolean $select The user fields to obtain or false to obtain all
     * @param integer|boolean $top The number of users to obtain or false to obtain all
     * @param integer|boolean $skip The number of users to skip or false to skip none
     * @param array|boolean $orderby The field to sort by and the method to sort or false to not sort
     * @param string|boolean $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return array|boolean An array of Auth\PendingUser objects or false if no pending users were found
     */
    public function getPendingUsersByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false, $methodName = false)
    {
        if($methodName === false)
        {
            return $this->callOnEach('getPendingUsersByFilter', array($filter, $select, $top, $skip, $orderby), 'pending');
        }
        $auth = $this->getMethodByName($methodName);
        return $auth->getPendingUsersByFilter($filter, $select, $top, $skip, $orderby);
    }

    /**
     * Get an array of Auth\Group from a filtered set
     *
     * @param Data\Filter|false $filter The filter conditions or false to retreive all
     * @param array|false $select The group fields to obtain or false to obtain all
     * @param integer|false $top The number of groups to obtain or false to obtain all
     * @param integer|false $skip The number of groups to skip or false to skip none
     * @param array|false $orderby The field to sort by and the method to sort or false to not sort
     * @param string|false $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return array|false An array of Auth\Group objects or false if no pending users were found
     */
    public function getGroupsByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false, $methodName = false)
    {
        if($methodName === false)
        {
            return $this->callOnEach('getGroupsByFilter', array($filter, $select, $top, $skip, $orderby), 'current');
        }
        $auth = $this->getMethodByName($methodName);
        return $auth->getGroupsByFilter($filter, $select, $top, $skip, $orderby);
    }

    /**
     * Get the number of currently active users on the system
     *
     * @param string|false $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return integer The number of currently active users on the system
     */
    public function getActiveUserCount($methodName = false)
    {
        if($methodName === false)
        {
            return $this->addFromEach('getActiveUserCount', 'current');
        }
        $auth = $this->getMethodByName($methodName);
        return $auth->getActiveUserCount();
    }

    /**
     * Get the number of currently pending users on the system
     *
     * @param string|false $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return integer The number of currently pending users on the system
     */
    public function getPendingUserCount($methodName = false)
    {
        if($methodName === false)
        {
            return $this->addFromEach('getPendingUserCount', 'pending');
        }
        $auth = $this->getMethodByName($methodName);
        return $auth->getPendingUserCount();
    }

    /**
     * Get the number of current groups on the system
     *
     * @param string|false $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return integer The number of current groups on the system
     */
    public function getGroupCount($methodName = false)
    {
        if($methodName === false)
        {
            return $this->addFromEach('getGroupCount', 'current');
        }
        $auth = $this->getMethodByName($methodName);
        return $auth->getGroupCount();
    }

    /**
     * Get the login links for all supplementary Authenitcation mechanisms
     *
     * This will return an array of links to any supplementary authentication mechanims. For example, Goodle is 
     * a supplementary authentication mechanism.
     *
     * @return array An array of suppmentary authentication mechanism links
     */
    public function getSupplementaryLinks()
    {
        $ret = array();
        $count = count($this->methods);
        for($i = 0; $i < $count; $i++)
        {
            if($this->methods[$i]->supplement === false)
            {
                continue;
            }

            array_push($ret, $this->methods[$i]->getSupplementLink());
        }
        return $ret;
    }

    /**
     * Impersonate the user specified
     *
     * This will replace the user in the session with the specified user. In order
     * to undo this operation a user must logout.
     *
     * @param array|Auth\User $userArray Data representing the user
     */
    public function impersonateUser($userArray)
    {
        if(!is_object($userArray))
        {
            $userArray = new $userArray['class']($userArray);
        }
        \FlipSession::setUser($userArray);
    }

    /**
     * Get the pending user reresented by the supplied hash
     *
     * @param string $hash The hash value representing the Penging User
     * @param string|false $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return Auth\PendingUser|false The Auth\PendingUser instance or false if no user is matched by the provided hash
     */
    public function getTempUserByHash($hash, $methodName = false)
    {
        if($methodName === false)
        {
            return $this->callOnEach('getTempUserByHash', array($hash), 'pending');
        }
        $auth = $this->getMethodByName($methodName);
        return $auth->getTempUserByHash($hash);
    }

    /**
     * Create a pending user
     *
     * @param array $user An array of information about the user to create
     * @param string|false $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return boolean true if the user was successfully created. Otherwise false.
     */
    public function createPendingUser($user, $methodName = false)
    {
        if($methodName === false)
        {
            $count = count($this->methods);
            for($i = 0; $i < $count; $i++)
            {
                if($this->methods[$i]->pending === false)
                {
                    continue;
                }

                $ret = $this->methods[$i]->createPendingUser($user);
                if($ret !== false)
                {
                    return true;
                }
            }
            return false;
        }
        $auth = $this->getMethodByName($methodName);
        return $auth->createPendingUser($user);
    }

    /**
     * Convert a Auth\PendingUser into an Auth\User
     *
     * This will allow a previously pending user the ability to log on in the future as an active user. It will also
     * have the side effect of logging the user on now.
     *
     * @param Auth\PendingUser $user The user to turn into a current user
     * @param string|false $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return boolean true if the user was successfully created. Otherwise false.
     */
    public function activatePendingUser($user, $methodName = false)
    {
        if($methodName === false)
        {
            $count = count($this->methods);
            for($i = 0; $i < $count; $i++)
            {
                if($this->methods[$i]->current === false)
                {
                    continue;
                }

                $ret = $this->methods[$i]->activatePendingUser($user);
                if($ret !== false)
                {
                    $this->impersonateUser($ret);
                    return true;
                }
            }
            return false;
        }
        $auth = $this->getMethodByName($methodName);
        return $auth->activatePendingUser($user);
    }

    /**
     * Get a current user by a password reset hash
     *
     * @param string $hash The current password reset hash for the user
     * @param string|false $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return Auth\User|false The user if the password reset hash is valid. Otherwise false.
     */
    public function getUserByResetHash($hash, $methodName = false)
    {
        if($methodName === false)
        {
            return $this->callOnEach('getUserByResetHash', array($hash), 'current');
        }
        $auth = $this->getMethodByName($methodName);
        if($auth === false)
        {
            return $this->getUserByResetHash($hash, false);
        }
        return $auth->getUserByResetHash($hash);
    }

    /**
     * Get the Auth\Authenticator by host name
     *
     * @param string $host The host name used by the supplemental authentication mechanism
     *
     * @return Auth\Authenticator|false The Authenticator if the host is supported by a loaded Authenticator. Otherwise false.
     */
    public function getSuplementalProviderByHost($host)
    {
        $count = count($this->methods);
        for($i = 0; $i < $count; $i++)
        {
            if($this->methods[$i]->supplement === false)
            {
                continue;
            }

            if($this->methods[$i]->getHostName() === $host)
            {
                return $this->methods[$i];
            }
        }
        return false;
    }

    /**
     * Delete any pending users that match the filter
     *
     * @param \Data\Filter|boolean $filter The filter to delete with or false to delete all
     * @param string|boolean $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return boolean True if the users were deleted, false otherwise
     */
    public function deletePendingUsersByFilter($filter, $methodName = false)
    {
        $users = $this->getPendingUsersByFilter($filter, false, false, false, false, $methodName);
        if($users === false)
        {
            return false;
        }
        $count = count($users);
        for($i = 0; $i < $count; $i++)
        {
            $users[$i]->delete();
        }
        return true;
    }

    /**
     * Get the user by the one time access code
     *
     * @param string $key The user's access code
     * @param string|boolean $methodName The AuthMethod if information is desired only from a particular Auth\Authenticator
     *
     * @return boolean|\Auth\User The User specified by the access code or false otherwise
     */
    public function getUserByAccessCode($key, $methodName = false)
    {
        if($methodName === false)
        {
            return $this->callOnEach('getUserByAccessCode', array($key), 'current');
        }
        $auth = $this->getMethodByName($methodName);
        return $auth->getUserByAccessCode($key);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

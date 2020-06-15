<?php
namespace Flipside\Auth;

class Authenticator
{
    const SUCCESS         = 0;
    const ALREADY_PRESENT = 1;
    const LOGIN_FAILED    = 2;

    public $current = false;
    public $pending = false;
    public $supplement = false;

    public function __construct($params)
    {
        $this->current = $params['current'];
        $this->pending = $params['pending'];
        $this->supplement = $params['supplement'];
    }

    /**
     * Login the user by username and password
     *
     * @param string $username The username
     * @param string $password The password
     *
     * @return boolean|array False if unsuccessfuly login, opaque data otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function login($username, $password)
    {
        return false;
    }

    /**
     * Does the data indicate a logged in user?
     *
     * @param mixed $data The data returned from login
     *
     * @return boolean True if successfully logged in, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function isLoggedIn($data)
    {
        return false;
    }

    /**
     * Get the user data for the specified data
     *
     * @param mixed $data The data returned from login
     *
     * @return null|\Auth\User The user object if successfully logged in or null otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function getUser($data)
    {
        return null;
    }

    /**
     * Get the group by the group's name
     *
     * @param string $name The name of the group
     *
     * @return null|\Auth\Group The group object
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function getGroupByName($name)
    {
        return null;
    }

    /**
     * Get the user by the user's name
     *
     * @param string $name The name of the user
     *
     * @return Auth\User The user object
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function getUserByName($name)
    {
        return null;
    }

    /**
     * Get the groups that match the filters
     *
     * @param mixed $filter The filter to use to find a set of groups
     * @param mixed $select The list of fields to select
     * @param mixed $top The number of groups to select
     * @param mixed $skip The number of groups to skip
     * @param mixed $orderby The fields to sort by
     *
     * @return array All groups that fit the filters
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function getGroupsByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false)
    {
        return false;
    }

    /**
     * Get the users that match the filters
     *
     * @param mixed $filter The filter to use to find a set of users
     * @param mixed $select The list of fields to select
     * @param mixed $top The number of users to select
     * @param mixed $skip The number of users to skip
     * @param mixed $orderby The fields to sort by
     *
     * @return array All users that fit the filters
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function getUsersByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false)
    {
        return false;
    }

    /**
     * Get the pending users that match the filters
     *
     * @param mixed $filter The filter to use to find a set of users
     * @param mixed $select The list of fields to select
     * @param mixed $top The number of users to select
     * @param mixed $skip The number of users to skip
     * @param mixed $orderby The fields to sort by
     *
     * @return array All users that fit the filters
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function getPendingUsersByFilter($filter, $select = false, $top = false, $skip = false, $orderby = false)
    {
        return false;
    }

    /**
     * Get the number of active users
     *
     * @return integer The number of active users
     */
    public function getActiveUserCount()
    {
        $users = $this->getUsersByFilter(false);
        if($users === false)
        {
            return 0;
        }
        return count($users);
    }

    /**
     * Get the number of pending users
     *
     * @return integer The number of pending users
     */
    public function getPendingUserCount()
    {
        $users = $this->getPendingUsersByFilter(false);
        if($users === false)
        {
            return 0;
        }
        return count($users);
    }

    /**
     * Get the number of groups
     *
     * @return integer The number of groups
     */
    public function getGroupCount()
    {
        $groups = $this->getGroupsByFilter(false);
        if($groups === false)
        {
            return 0;
        }
        return count($groups);
    }

    /**
     * Get the link to login using this method
     *
     * @return string The link to login using this method
     */
    public function getSupplementLink()
    {
        return '';
    }

    /**
     * Create a new pending user
     *
     * @param \Auth\PendingUser $user The user to create
     *
     * @return boolean True if created, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function createPendingUser($user)
    {
        return false;
    }

    /**
     * Convert a pending user to an active user
     *
     * @param Auth\PendingUser $user The user to activate
     *
     * @return boolean True if activated, false otherwise
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function activatePendingUser($user)
    {
        return false;
    }

    /**
     * Find the user by their password reset hash
     *
     * @param string $hash The hash to search by
     *
     * @return Auth\User The user whoes hash was specified
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function getUserByResetHash($hash)
    {
        return false;
    }

    /**
     * Find the PendingUser by their hash
     *
     * @param string $hash The hash to search by
     *
     * @return Auth\PendingUser The user whoes hash was specified
     *
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function getTempUserByHash($hash)
    {
        return false;
    }

    /**
     * Get the host name this authenticator uses
     *
     * @return string The host name this authenticator uses
     */
    public function getHostName()
    {
        return false;
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

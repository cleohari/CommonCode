<?php
require_once('Autoload.php');
if(!isset($_SESSION) && php_sapi_name() !== 'cli') { session_start(); }
if(!isset($_SESSION['ip_address']) && isset($_SERVER['REMOTE_ADDR']))
{
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
}
if(!isset($_SESSION['init_time']))
{
    $_SESSION['init_time'] = date('c');
}

class FlipSession extends Singleton
{
    /**
     * Does the variable exist in the session
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    static function doesVarExist($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Get a variable from the session
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    static function getVar($name, $default = false)
    {
        if(FlipSession::doesVarExist($name))
        {
            return $_SESSION[$name];
        }
        else
        {
            return $default;
        }
    }

    /**
     * Set a variable in the session
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    static function setVar($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Is a user currently logged in?
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    static function isLoggedIn()
    {
        if(isset($_SESSION['flipside_user']))
        {
            return true;
        }
        else if(isset($_SESSION['AuthMethod']) && isset($_SESSION['AuthData']))
        {
            $auth = AuthProvider::getInstance();
            return $auth->isLoggedIn($_SESSION['AuthData'], $_SESSION['AuthMethod']);
        }
        else
        {
            return false;
        }
    }

    /**
     * Get the currently logged in user
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    static function getUser()
    {
        if(isset($_SESSION['flipside_user']))
        {
            return $_SESSION['flipside_user'];
        }
        else if(isset($_SESSION['AuthMethod']) && isset($_SESSION['AuthData']))
        {
            $auth = AuthProvider::getInstance();
            $user = $auth->getUser($_SESSION['AuthData'], $_SESSION['AuthMethod']);
            if($user !== null)
            {
                $_SESSION['flipside_user'] = $user;
            }
            return $user;
        }
        else
        {
            return null;
        }
    }

    /**
     * Set the currently logged in user
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    static function setUser($user)
    {
        $_SESSION['flipside_user'] = $user;
    }

    /**
     * Obtain the current users email address
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    static function getUserEmail()
    {
        if(isset($_SESSION['flipside_email']))
        {
            return $_SESSION['flipside_email'];
        }
        $user = FlipSession::getUser();
        if($user === false || $user === null)
        {
            return false;
        }
        if(isset($user->mail) && isset($user->mail[0]))
        {
            $_SESSION['flipside_email'] = $user->mail[0];
            return $_SESSION['flipside_email'];
        }
        return false;
    }

    /**
     * This will end your session
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    static function end()
    {
        if(isset($_SESSION) && !empty($_SESSION))
        {
            $_SESSION = array();
            session_destroy();
        }
    }

    static function unserializePhpSession($sessionData)
    {
        $res = array();
        $offset = 0;
        $length = strlen($sessionData);
        while($offset < $length)
        {
            $pos = strpos($sessionData, "|", $offset);
            $len = $pos - $offset;
            $name = substr($sessionData, $offset, $len);
            if($name === false) break;
            $offset += $len + 1;
            $data = @unserialize(substr($sessionData, $offset));
            $res[$name] = $data;
            $offset += strlen(serialize($data));
        }
        return $res;
    }

    static function getAllSessions()
    {
        $res = array();
        $sessFiles = scandir(ini_get('session.save_path'));
        $count = count($sessFiles);
        for($i = 0; $i < $count; $i++)
        {
            if($sessFiles[$i][0] === '.')
            {
                continue;
            }
            $sessionId = substr($sessFiles[$i], 5);
            $sessionData = file_get_contents(ini_get('session.save_path').'/'.$sessFiles[$i]);
            if($sessionData === false)
            {
                array_push($res, array('sid' => $sessionId));
            }
            else
            {
                $tmp = FlipSession::unserializePhpSession($sessionData);
                $tmp['sid'] = $sessionId;
                array_push($res, $tmp);
            }
        }
        if(count($res) == 0)
        {
            return false;
        }
        return $res;
    }

    static function getSessionById($sid)
    {
        $sessionData = file_get_contents(ini_get('session.save_path').'/sess_'.$sid);
        return FlipSession::unserializePhpSession($sessionData);
    }

    static function deleteSessionById($sid)
    {
        return unlink(ini_get('session.save_path').'/sess_'.$sid); 
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
?>

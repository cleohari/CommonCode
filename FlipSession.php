<?php
namespace Flipside;

require_once('Autoload.php');

$settings = \Flipside\Settings::getInstance();
//Use PHP based session handling if DB session handling isn't setup
if($settings->getDataSetData('profiles') !== false)
{
    $handler = new \Flipside\Data\DataTableSessionHandler('profiles', 'sessions');
    session_set_save_handler($handler, true);
}

if(!isset($_SESSION) && php_sapi_name() !== 'cli')
{
    session_start();
}
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
    public static function doesVarExist($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Get a variable from the session
     *
     * @param string $name The name of variable to obtain from the session
     * @param mixed $default The default value
     *
     * @return mixed The value stored in the session or the default if not set
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getVar($name, $default = false)
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
     * @param string $name The name of variable to set in the session
     * @param mixed $value The value to store in the variable
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function setVar($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Is a user currently logged in?
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function isLoggedIn()
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
    public static function getUser()
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
    public static function setUser($user)
    {
        $_SESSION['flipside_user'] = $user;
    }

    /**
     * Obtain the current users email address
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getUserEmail()
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
    public static function end()
    {
        if(isset($_SESSION) && !empty($_SESSION))
        {
            $_SESSION = array();
            session_destroy();
        }
    }

    public static function unserializePhpSession($sessionData)
    {
        $res = array();
        $offset = 0;
        $length = strlen($sessionData);
        while($offset < $length)
        {
            $pos = strpos($sessionData, "|", $offset);
            $len = $pos - $offset;
            $name = substr($sessionData, $offset, $len);
            if($name === false)
            {
                break;
            }
            $offset += $len + 1;
            $data = @unserialize(substr($sessionData, $offset));
            $res[$name] = $data;
            $offset += strlen(serialize($data));
        }
        return $res;
    }

    public static function getAllSessions()
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

    public static function getSessionById($sid)
    {
        $sessionData = file_get_contents(ini_get('session.save_path').'/sess_'.$sid);
        return FlipSession::unserializePhpSession($sessionData);
    }

    public static function deleteSessionById($sid)
    {
        return unlink(ini_get('session.save_path').'/sess_'.$sid); 
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

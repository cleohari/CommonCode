<?php
namespace Flipside\Http;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

class AuthMiddleware
{
    private function getUserFromSession()
    {
        if(\Flipside\FlipSession::isLoggedIn())
        {
            return \FlipSession::getUser();
        }
        return false;
    }

    /*
     * @SuppressWarnings("Superglobals")
     * @SuppressWarnings("StaticAccess")
     */
    private function getUserFromBasicAuth($header)
    {
        $auth = \Flipside\AuthProvider::getInstance();
        $auth->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        $user = \Flipside\FlipSession::getUser();
        if($user === false)
        {
            $data = substr($header, 6);
            $userpass = explode(':', base64_decode($data));
            $user = $auth->getUserByLogin($userpass[0], $userpass[1]);
        }
        return $user;
    }

    /*
     * @SuppressWarnings("StaticAccess")
     */
    private function getUserFromToken($header)
    {
        $auth = \Flipside\AuthProvider::getInstance();
        $key = substr($header, 6);
        return $auth->getUserByAccessCode($key);
    }

    private function getUserByApiKey($header)
    {
        $key = substr($header, 7);
        $dataTable = \Flipside\DataSetFactory::getDataTableByNames('profiles', 'apikeys');
        $filter = new \Flipside\Data\Filter('apikey eq "'.$key.'"');
        $keys = $dataTable->read($filter);
        if(empty($keys))
        {
            return false;
        }
        $auth = \Flipside\AuthProvider::getInstance();
        $users = $auth->getUsersByFilter(new \Data\Filter('uid eq '.$keys[0]['actas']));
        if(empty($users))
        {
            return false;
        }
        return $users[0];
    }

    private function getUserFromHeader($header)
    {
        if(strncmp($header, 'Basic', 5) == 0)
        {
            return $this->getUserFromBasicAuth($header);
        }
        else if(strncasecmp($header, 'ApiKey', 6) === 0)
        {
            return $this->getUserByApiKey($header);
        }
        return $this->getUserFromToken($header);
    }

    public function __invoke($request, $response, $next)
    {
        $auth = $request->getHeaderLine('Authorization');
        if(empty($auth))
        {
            $request = $request->withAttribute('user', $this->getUserFromSession());
        }
        else
        {
            $request = $request->withAttribute('user', $this->getUserFromHeader($auth));
        }
        $response = $next($request, $response);
        return $response;
    }
}

<?php
namespace Http;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

class AuthMiddleware
{
    private function getUserFromSession()
    {
        if(\FlipSession::isLoggedIn())
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
        $auth = \AuthProvider::getInstance();
        $auth->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        $user = FlipSession::getUser();
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
        $auth = \AuthProvider::getInstance();
        $key = substr($header, 6);
        return $auth->getUserByAccessCode($key);
    }

    private function getUserByApiKey($header)
    {
        $key = substr($header, 7);
        $dataTable = \DataSetFactory::getDataTableByNames('profiles', 'apikeys');
        $filter = new \Data\Filter('apikey eq "'.$key.'"');
        $keys = $dataTable->read($filter);
        if(empty($keys))
        {
            return false;
        }
        $auth = \AuthProvider::getInstance();
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

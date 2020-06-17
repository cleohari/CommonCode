<?php
namespace Flipside\Http\Rest;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

const SUCCESS = 0;
const UNRECOGNIZED_METHOD = 1;
const INVALID_PARAM = 2;
const ALREADY_LOGGED_IN = 3;
const INVALID_LOGIN = 4;
const ACCESS_DENIED = 5;
const INTERNAL_ERROR = 6;
const UNKNOWN_ERROR = 255;

class RestAPI
{
    protected $user = null;

    public function setup($app)
    {
        \Sentry\init(['dsn' => 'https://8d76f6c4cb3b409bbe7ed4300e054afd@sentry.io/4283882' ]);
        return $app->any('[/]', $this);
    }

    public function validateLoggedIn($request)
    {
        $this->user = $request->getAttribute('user');
        if($this->user === false || $this->user === null)
        {
            throw new \Exception('Must be logged in', \Flipside\Http\Rest\ACCESS_DENIED);
        }
    }

    protected function getParsedBody($request)
    {
        $obj = $request->getParsedBody();
        if($obj === null)
        {
            $request->getBody()->rewind();
            $obj = $request->getBody()->getContents();
            $tmp = json_decode($obj, true);
            if($tmp !== null)
            {
                $obj = $tmp;
            }
        }
        return $obj;
    }

    protected function sendEmail($email)
    {
        $emailProvider = \EmailProvider::getInstance();
        if($emailProvider->sendEmail($email) === false)
        {
            throw new \Exception('Unable to send email!');
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

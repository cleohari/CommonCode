<?php
namespace Http\Rest;

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
        return $app->any('[/]', $this);
    }

    public function validateLoggedIn($request)
    {
        $this->user = $request->getAttribute('user');
        if($this->user === false)
        {
            throw new \Exception('Must be logged in', \Http\Rest\ACCESS_DENIED);
        }
    }
}

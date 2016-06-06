<?php
require_once('class.FlipSession.php');
require_once('libs/Slim/Slim/Slim.php');
require_once('Autoload.php');
\Slim\Slim::registerAutoloader();

const SUCCESS = 0;
const UNRECOGNIZED_METHOD = 1;
const INVALID_PARAM = 2;
const ALREADY_LOGGED_IN = 3;
const INVALID_LOGIN = 4;
const ACCESS_DENIED = 5;
const INTERNAL_ERROR = 6;

const UNKNOWN_ERROR = 255;

class OAuth2Auth extends \Slim\Middleware
{
    protected $headers = array();

    public function __construct($headers)
    {
        $this->headers = $headers;
    }

    private function getUserFromSession()
    {
        if(FlipSession::isLoggedIn())
        {
            return FlipSession::getUser();
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
        $key = substr($header, 7);
        return $auth->getUserByAccessCode($key);
    }

    private function getUserFromHeader($header)
    {
        if(strncmp($header, 'Basic', 5) == 0)
        {
            return $this->getUserFromBasicAuth($header);
        }
        return $this->getUserFromToken($header);
    }

    public function call()
    {
        // no auth header
        if(!isset($this->headers['Authorization']))
        {
            $this->app->user = $this->getUserFromSession();
        } 
        else 
        {
            $header = $this->headers['Authorization'];
            $this->app->user = $this->getUserFromHeader($header);
        }

        if($this->app->user === false)
        {
            $this->app->getLog()->error("No user found for call");
        }

        // this line is required for the application to proceed
        $this->next->call();
    }
}

class FlipRESTFormat extends \Slim\Middleware
{
    private function fix_encoded_element($key, $value, &$array, $prefix = '')
    {
        if(is_array($value))
        {
            $array[$key] = implode(';', $value);
        }
        else if($key === '_id' && is_object($value))
        {
            $array[$key] = $value->{'$id'};
        }
        else if(is_object($value))
        {
            $array[$key] = $this->app->request->getUrl().$this->app->request->getPath().$prefix.'/'.$key;
        }
        else if(strncmp($value, 'data:', 5) === 0)
        {
            $array[$key] = $this->app->request->getUrl().$this->app->request->getPath().$prefix.'/'.$key;
        }
    }

    private function createCSV(&$array)
    {
        if(count($array) == 0)
        {
            return null;
        }
        ob_start();
        $df = fopen("php://output", 'w');
        if(is_array($array))
        {
            $first = reset($array);
            $keys = FALSE;
            if(is_array($first))
            {
                $keys = array_keys($first);
            }
            else if(is_object($first))
            {
                $keys = array_keys(get_object_vars($first));
            }
            fputcsv($df, $keys);
            foreach($array as $row)
            {
                if(is_array($row))
                {
                    $id = $row[$keys[0]];
                    foreach($row as $key=>$value)
                    {
                        $this->fix_encoded_element($key, $value, $row, '/'.$id);
                    }
                    fputcsv($df, $row);
                }
                else if(is_object($row))
                {
                    $keyName = $keys[0];
                    $id = $row->$keyName;
                    if(is_object($id))
                    {
                        $id = $id->{'$id'};
                    }
                    $values = get_object_vars($row);
                    foreach($values as $key=>$value)
                    {
                        $this->fix_encoded_element($key, $value, $values, '/'.$id);
                    }
                    fputcsv($df, $values);
                }
            }
        }
        else
        {
            $array = get_object_vars($array);
            fputcsv($df, array_keys($array));
            foreach($array as $key=>$value)
            {
                $this->fix_encoded_element($key, $value, $array);
            }
            fputcsv($df, $array);
        }
        fclose($df);
        return ob_get_clean();
    }

    private function createXML(&$array)
    {
        $obj = new SerializableObject($array);
        return $obj->xmlSerialize();
    }

    public function call()
    {
        if($this->app->request->isOptions())
        {
            return;
        }
        $params = $this->app->request->params();
        $fmt = null;
        if(isset($params['fmt']))
        {
            $fmt = $params['fmt'];
        }
        if($fmt === null && isset($params['$format']))
        {
            $fmt = $params['$format'];
            if(strstr($fmt, 'odata.streaming=true'))
            {
                $this->app->response->setStatus(406);
                return;
            }
        }
        if($fmt === null)
        {
            $mimeType = $this->app->request->headers->get('Accept');
            if(strstr($mimeType, 'odata.streaming=true'))
            {
                $this->app->response->setStatus(406);
                return;
            }
            switch($mimeType)
            {
                case 'text/csv':
                    $fmt = 'csv';
                    break;
                case 'text/x-vCard':
                    $fmt = 'vcard';
                    break;
                default:
                    $fmt = 'json';
                    break;
            }
        }

        $this->app->fmt     = $fmt;
        $this->app->odata   = new ODataParams($params);


        $this->next->call();

        if($this->app->response->getStatus() == 200 && $this->app->fmt !== 'json')
        {
            $data = json_decode($this->app->response->getBody());
            $text = false;
            switch($this->app->fmt)
            {
                case 'data-table':
                    $this->app->response->headers->set('Content-Type', 'application/json');
                    $text = json_encode(array('data'=>$data));
                    break;
                case 'csv':
                    $this->app->response->headers->set('Content-Type', 'text/csv');
                    $path = $this->app->request->getPathInfo();
                    $path = strrchr($path, '/');
                    $path = substr($path, 1);
                    $this->app->response->headers->set('Content-Disposition', 'attachment; filename='.$path.'.csv');
                    $text = $this->createCSV($data);
                    break;
                case 'xml':
                    $this->app->response->headers->set('Content-Type', 'application/xml');
                    $text = $this->createXML($data);
                    break;
                case 'passthru':
                    $text = $this->app->response->getBody();
                    break;
                default:
                    $text = 'Unknown fmt '.$fmt;
                    break;
            }
            $this->app->response->setBody($text);
        }
        else if($this->app->response->getStatus() == 200)
        {
            $this->app->response->headers->set('Content-Type', 'application/json;odata.metadata=none');
        }
    }
}

class FlipREST extends \Slim\Slim
{
    public function __construct()
    {
        parent::__construct();
        $this->config('debug', false);
        $headers = array();
        if(php_sapi_name() !== "cli")
        {
            $headers = apache_request_headers();
        }
        $this->add(new OAuth2Auth($headers));
        $this->add(new FlipRESTFormat());
        $errorHandler = array($this, 'errorHandler');
        $this->error($errorHandler);
    }

    public function get_json_body($array = false)
    {
        return $this->getJsonBody($array);
    }

    public function getJsonBody($array = false)
    {
        $body = $this->request->getBody();
        return json_decode($body, $array);
    }

    public function errorHandler($exception)
    {
        $error = array(
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        );
        $this->response->headers->set('Content-Type', 'application/json');
        error_log(print_r($error, true));
        echo json_encode($error);
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */

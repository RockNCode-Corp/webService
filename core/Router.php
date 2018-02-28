<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 25/11/17
 * Time: 10:10 AM
 */

use view\Templating;
use view\View;
use view\ViewLoader;

class Router{

    /**
     * @var array contains all routes
     */
    private static $routes = [];

    /**
     * @var Closure for call error 404
     */
    private $notFound;

    /**
     * @var View
     */
    private $view;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(){
        self::constructRoutes();

        $this->view = new View(new ViewLoader(VIEWS_PATH), new Templating());
        $this->logger = new Logger();

    }

    /**
     * People the array $routes
     */
    private static function constructRoutes() : void{
        $fileRoutesPath = BASEPATH.'/app/routing.yml';

        $routes = [];
        try{
            $routes = yaml_parse_file($fileRoutesPath);
        }catch (Exception $e){
            echo "Routing file not found";
            exit;
        }

        foreach ($routes as $key => $value){
            $filePath = BASEPATH.'/app/routes/'.$value['filename'].'.yml';
            if(file_exists($filePath)){
                self::$routes[$key] = array(
                    'routes' => yaml_parse_file($filePath)
                );
                if(!empty($value['prefix'])){
                    self::$routes[$key]['prefix'] = $value['prefix'];
                }
            }
        }
    }

    public function dispatch(){
        $url = $_SERVER['REQUEST_URI'];
        $urlExplode = explode('/', $url);

        $params = [];
        $pattern = '(?:[a-zA-Z0-9]*)';

        try{
            foreach (self::$routes as $key => $value){
                if(!empty($value['prefix'])){
                    if($urlExplode[1] == $value['prefix']){
                        $prefix = $value['prefix'];
                        $routes = $value['routes'];
                    }
                }
            }
            if(empty($routes)){
                $routes = self::$routes['public']['routes'];
                if(!empty(self::$routes['public']['prefix'])){
                    $prefix = self::$routes['public']['prefix'];
                }
            }

            $route = null;

            foreach ($routes as $key => $spec){
                $params = [];
                $path = $spec['path'];

                if(!empty($prefix)){
                    $path = '/'.$prefix.$path;
                }

                $path = preg_replace_callback('/(\{((?:\w*))\})/', function($match) use (&$params, $pattern){
                    $params[$match[2]] = null;
                    return $pattern;
                }, $path);

                $pathReg = str_replace('/', '\\/', $path);
                $pathExplode = explode('/', $path);

                if(preg_match('/'.$pathReg.'/', $url) && $pathExplode[1] == $urlExplode[1]){
                    for($i = 1; $i < count($pathExplode); $i++){
                        if($pathExplode[$i] == $pattern){
                            $params[key($params)] = $urlExplode[$i];
                            next($params);
                        }
                    }
                    $route = $routes[$key];
                    break;
                }
            }

            if(!empty($route)){
                $get_post_merge = [];
                if(!empty($_GET))
                    $get_post_merge = array_merge($get_post_merge, json_decode($_GET, true));
                if(!empty($_POST))
                    $get_post_merge = array_merge($get_post_merge, json_decode($_POST, true));

                $request = new Request($get_post_merge, $_SERVER['REQUEST_METHOD']);

                $typeAllow = (empty($route['type'])) ? true:$route['type'] == $request->getType();

                if($typeAllow){
                    //if(is_callable($route)) return $route['action']();

                    $actionController = explode(':', $route['action']['controller']);
                    $nbParams = !empty($route['action']['params']) ? count($route['action']['params']):0;
                    if(!empty($actionController[0]) && !empty($actionController[1]) && !empty($actionController[2]) && count($params) == $nbParams){
                        $nameController = $actionController[0].'Module\\controllers\\'.$actionController[1]. 'Controller';
                        $action = $actionController[2].'Action';
                        return $this->appelActionControlleur($nameController, $action, $request, $params);
                    }
                }
            }elseif(file_exists(ASSETS_PATH.$url)){
                $this->callFile($url);
                return 1;
            }

            return call_user_func_array($this->notFound, [$url]);

        }catch (Exception $e){
            $this->logger->info('Error 500');
            $this->manageError(500, array('error' => $e->getMessage(), 'code' => $e->getCode(), 'trace' => $e->getTraceAsString()));
        }
        return 0;
    }

    /**
     * Call action controller
     * @param $controller string name of controller to call
     * @param $action string name of action to call
     * @param $request Request
     * @param $params
     * @return mixed
     */
    private function appelActionControlleur($controller, $action, $request, $params){
        $controller = new $controller();

        return $controller->$action($request, $params);
    }

    /**
     * Display file
     * @param $url string name file to call
     */
    private function callFile($url){
        $extension = array_reverse(explode('.', $url))[0];
        $this->view->displayFile(ASSETS_PATH.$url, $extension);
    }

    /**
     * Log and display error page
     * @param $codeError int error code
     * @param $error array contains message, stack trace, code error
     */
    public function manageError($codeError, $error){
        $msg = $codeError.' - ';
        if(!empty($error['code']))
            $msg .= $error['error'].' code: '.$error['code'].' StackTrace: '.$error['trace'];
        else
            $msg .= $error['error'];
        $this->view->display('error/'.$codeError.'.php', $error);
        $this->logger->error($msg);
    }

    /**
     * Log warning
     * @param $warning string type warning
     * @param $message string
     * @param $file string
     * @param $line string
     */
    public function manageWarning($warning, $message, $file, $line){
        $msg = $warning.' : '.$message.' StackTrace: #1 '.$file.' at '.$line;
        $this->logger->warning($msg);
    }

    public function setNotFound($action){
        $this->notFound = $action;
    }

    /**
     * Construct a url which on this domain
     * @param String $path URL unformatted
     * @param array $params parameters needed for construct the URL
     * @return String complete URL
     * @throws Exception hostname missing in configuration file
     */
    private static function constructURL(String $path, array $params) : String{
        $path = preg_replace_callback('/(\{((?:\w*))\})/', function($match) use (&$params){
            return $params[$match[2]];
        }, $path);

        return Configuration::get('hostname').$path;
    }

    /**
     *
     * @param String $name typeRoute/nameInRouteFile
     * @param array $params
     * @return String URL
     * @throws Exception
     */
    public static function getURL(String $name, array $params = []) : String {
        if(empty(self::$routes)){
            self::constructRoutes();
        }

        $explodeName = explode('/', $name, 2);

        if(empty($explodeName[0])){
            $routes = self::$routes['public'];
        }else{
            foreach (self::$routes as $key => $value){
                if($explodeName[0] == $key){
                    $routes = $value;
                }
            }
        }

        foreach ($routes['routes'] as $key => $value){
            if($explodeName[1] == $key){
                $nbParamsNeeded = (!empty($value['action']['params'])) ? count($value['action']['params']):0;
                if(count($params) == $nbParamsNeeded){
                    $path = $value['path'];
                    if(!empty($routes['prefix'])){
                        $path = "/".$routes['prefix'].$path;
                    }
                    return self::constructURL($path, $params);
                }
            }
        }
        throw new Exception("Any route find for $value");
    }
}
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
     * @var array contains public routes
     */
    private $routesPublic = [];

    /**
     * @var array contains admin routes
     */
    private $routesAdmin = [];

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
        $fileRoutesPath = BASEPATH.'/app/routes/public.yml';

        try{
            $this->routesPublic = yaml_parse_file($fileRoutesPath);
        }catch (Exception $e){
            echo "Public routes file not found";
            exit;
        }

        if(file_exists(BASEPATH.'/app/routes/admins.yml')){
            $this->routesAdmin = yaml_parse_file(BASEPATH.'/app/routes/admins.yml');
        }

        $this->view = new View(new ViewLoader(VIEWS_PATH), new Templating());
        $this->logger = new Logger();

    }

    public function dispatch(){
        $url = $_SERVER['REQUEST_URI'];
        $urlExplode = explode('/', $url);

        $params = [];
        $pattern = '(?:[a-zA-Z0-9]*)';

        try{
            $routes = $this->routesPublic;

            $route = null;

            foreach ($routes as $key => $spec){
                $params = [];
                $path = $spec['chemin'];

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

                $typeAllow = (empty($route['type'])) ? true:$route['type'];

                if($typeAllow == $request->getType()){
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
        $this->view->display('error/error'.$codeError.'.php', $error);
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
}
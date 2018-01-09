<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 25/11/17
 * Time: 10:10 AM
 */

class Routeur{

    private $routesPublic = [];

    private $routesAdmin = [];

    public function __construct(){
        $fileRoutesPath = BASEPATH.'/app/routes/public.yml';
        try{
            $this->routesPublic = yaml_parse_file($fileRoutesPath);
        }catch (Exception $e){
            echo "Public routes file not found";
            exit;    
        }
        
        if(file_exists($fileRoutesPath)){
            $this->routesAdmin = yaml_parse_file(BASEPATH.'/app/routes/admins.yml');
        }

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
                    if(!empty($actionController[0]) && !empty($actionController[1]) && count($params) == $nbParams){
                        $nameController = 'controllers\\'.$actionController[0]. 'Controller';
                        $action = $actionController[1].'Action';
                        return $this->appelActionControlleur($nameController, $action, $request, $params);
                    }
                }
            }

            return null;

        }catch (Exception $e){
            return null;
        }
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
}
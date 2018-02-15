<?php
/**
 * Created by PhpStorm.
 * User: Apomalyn
 * Date: 2017-07-21
 * Time: 19:21
 */

    define('BASEPATH', __DIR__);
    define('VIEWS_PATH', BASEPATH . '/src/views/');
    define('ASSETS_PATH', BASEPATH . '/public/assets/');

    require('core/AutoLoad.php');

    $autoloader = new Autoload();

    spl_autoload_register([$autoloader, 'load']);

    $router = new Router();

    ini_set('display_errors', 0);

    set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($router) {
        if($errno == E_USER_NOTICE || $errno == E_USER_WARNING){
            $router->manageWarning($errno, $errstr, $errfile, $errline);
        }else{
            $router->manageError(500, array(
                'error' => $errstr.' in '.$errfile.' at '.$errline,
                'code' => $errno,
                'trace' => '#1 '.$errfile.' at '.$errline
            ));
        }
    });

    register_shutdown_function(function () use ($router) {
        $last = error_get_last();

        if(!empty($last)){
            $error = explode('Stack trace:', $last['message']);
            $router->manageError(500, array(
                'error' => $error[0],
                'code' => $last['type'],
                'trace' => $error[1]
            ));
        }
    });

    session_set_save_handler(new Session(), true);
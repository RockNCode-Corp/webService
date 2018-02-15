<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 25/11/17
 * Time: 9:35 AM
 */

    define('BASEPATH', __DIR__);

    require('core/AutoLoad.php');

    $autoloader = new AutoLoad();

    spl_autoload_register([$autoloader, 'load']);

    $routeur = new Router();

    $routeur->dispatch();

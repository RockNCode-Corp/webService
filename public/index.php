<?php
/**
 * Created by PhpStorm.
 * User: Apomalyn
 * Date: 2017-07-21
 * Time: 18:45
 */

    require('../bootstrap.php');

    session_start();

    $router->dispatch();

?>



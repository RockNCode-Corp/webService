<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 25/11/17
 * Time: 10:09 AM
 */

class AutoLoad{

    private $autoloadable = [];

    /**
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function load($name){
        $nameLow = strtolower($name);

        if (!empty($this->autoloadable[$nameLow])) {
            return $this->autoloadable[$nameLow]($nameLow);
        }

        $name = str_replace('\\', '/', $name);
        $filePath = BASEPATH . '/modules/' . $name . '.php';


        if (file_exists($filePath)) {
            return require($filePath);
        }

        $filePath = BASEPATH . '/core/' . $name . '.php';
        if (file_exists($filePath)) {
            return require($filePath);
        }

        throw new Exception($name . ' is not loaded or registred for autoloading');
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 25/11/17
 * Time: 10:10 AM
 */

class Configuration{

    private static $parameters;


    public static function get($nom){
        if(isset(self::getParameters()[$nom])){
            return self::getParameters()[$nom];
        }
        throw new Exception($nom." not found in config file");
    }

    private static function getParameters(){
        if(self::$parameters == null){
            $fileConfigPath = BASEPATH."/app/config.yml";
            if(!file_exists($fileConfigPath)){
                $fileConfigPath = BASEPATH."/app/config_dev.yml";
            }
            if(!file_exists($fileConfigPath)){
                throw new Exception("Config file not found");
            }else{
                self::$parameters = yaml_parse_file($fileConfigPath);
            }
        }

        return self::$parameters;
    }

}
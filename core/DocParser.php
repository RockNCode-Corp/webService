<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 18/01/18
 * Time: 12:20 PM
 */

/**
 * Class DocParser
 */
class DocParser {

    /**
     * @param $target string @ to return value
     * @param $haystack string where to search
     * @return string | null
     */
    public static function getValueOf(string $target, string $haystack) : string {
        $return = [];
        if(strpos($target, '@') === FALSE){
            $target = '@'.$target;
        }

        $lines = preg_split("/((\r?\n)|(\r\n?))/", $haystack);
        foreach($lines as $line){
            if(strpos($line, $target) !== FALSE){
                $array = explode($target." ", $line);
                $params = preg_split("/\s/", $array[1]);
                foreach ($params as $param){
                    $param = explode("=", $param);
                    $return[$param[0]] = $param[1];
                }
                if(empty($return)){
                    $return = $array[1];
                }
            }
        }

        return empty($return) ? null:$return;
    }
}
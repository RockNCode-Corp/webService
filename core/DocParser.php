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
        if(strpos($target, '@') === FALSE){
            $target = '@'.$target;
        }

        foreach(preg_split("/((\r?\n)|(\r\n?))/", $haystack) as $line){
            if(strpos($line, $target) !== FALSE){
                $array = explode($target." ", $line);
                return $array[1];
            }
        }
        return null;
    }
}
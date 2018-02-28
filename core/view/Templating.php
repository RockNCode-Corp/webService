<?php
/**
 * Created by PhpStorm.
 * User: Apomalyn
 * Date: 2017-07-22
 * Time: 13:31
 */

namespace view;



class Templating{

    /**
     * @var array with all function helpers
     */
    private $helpers = [];

    public function __construct(){
        $this->helpers = array(
            'scriptList' =>
                function($params, $scriptList){
                    $data = $scriptList[$params[0]];
                    $tag = $data['tag'];
                    $endTag = $data['endTag'];
                    $scriptList = $data['scriptList'];

                    if (empty($scriptList)) return '';

                    $result = '';
                    foreach ($scriptList as $script){
                        $result .= $tag.$script.$endTag;
                    }

                    return $result;
            },
            'url' =>
                function($params){
                    return \Router::getURL($params[0]);
                }

        );
    }
    private function replaceVariables(&$view, $variables){
        $view = preg_replace_callback('/(\{)(\{)((?:[a-zA-Z\s]*))(\})(\})/',
            function($match) use ($variables){
                if(!empty($variables[trim($match[3])]))
                    return htmlspecialchars($variables[trim($match[3])], ENT_QUOTES, 'UTF-8', false);
                return $match[0];
            }, $view);
    }

    private function applyHelpers(&$view, $data){
        $view = preg_replace_callback('/(\{)(\{)((?:[a-zA-Z0-9_,\s]*))(\-)(\>)((?:[a-zA-Z0-9_,\/\s]*))(\})(\})/',
            function($match) use ($data){
                $helper = trim($match[3]);
                $params = trim($match[6]);
                $params = array(
                    explode(',', $params),
                    $data
                );

                if(!empty($this->helpers[$helper]))
                    return call_user_func_array($this->helpers[$helper], $params);
            }, $view);
    }

    public function parse($view, $variables){
        $this->replaceVariables($view, $variables);
        $this->applyHelpers($view, $variables['data']);
        return $view;
    }
}
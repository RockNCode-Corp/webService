<?php
/**
 * Created by PhpStorm.
 * User: Apomalyn
 * Date: 2017-07-21
 * Time: 19:11
 */

namespace view;

class ViewLoader{

    private $path;

    private $title;

    private $template = VIEWS_PATH.'template.php';

    public function __construct($path){
        $this->path = $path;
    }

    public function load($viewName){

        $path = $this->path.$viewName;

        if(file_exists($path)){
            $body = file_get_contents($path);
            $this->loadTemplate($body);
            $title = $this->title;
            $template = $this->template;

            ob_start();
            require $template;

            return ob_get_clean();
        }
        throw new \Exception("View does not exist: ".$viewName);
    }

    private function loadTemplate(&$file){
        $file = preg_replace_callback('/(\:)(\:)((?:[a-zA-Z0-9\-\(\)\s]*))(\:)(\:)/',
            function($match) use ($file) {
                $data = explode('-', $match[3]);

                switch ($data[0]){
                    case 'title':
                        $this->title = $data[1];
                        break;
                    case 'template':
                        $this->template = VIEWS_PATH.$data[1].'.php';
                        break;
                }

                return '';
            }, $file);
    }
}
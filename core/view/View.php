<?php
/**
 * Created by PhpStorm.
 * User: Apomalyn
 * Date: 2017-07-21
 * Time: 19:15
 */

namespace view;

class View{

    /**
     * @var instance of viewLoader
     */
    private $viewLoader;

    /**
     * @var instance of Templating
     */
    private $engine;

    /**
     * @var string root of style folder
     */
    private $cssRoot = 'css/';

    /**
     * @var string root of script folder
     */
    private $jsRoot = 'js/';

    /**
     * @var array styles of the page
     */
    private $style = [
        'tag' => '<link rel="stylesheet" type="text/css" href="',
        'endTag' => '.css">',
        'scriptList' => []
    ];

    /**
     * @var array script of the page
     */
    private $script = [
        'tag' => '<script src="',
        'endTag' => '.js"></script>',
        'scriptList' => []
    ];

    /**
     * View constructor.
     * @param ViewLoader $viewLoader
     * @param Templating $engine
     */
    public function __construct(ViewLoader $viewLoader, Templating $engine){
        $this->viewLoader = $viewLoader;
        $this->engine = $engine;
    }

    /**
     * @param $style string style to add (does not need the extension, but needs tha path relative to $cssRoot)
     */
    public function addStyle($style){
        $this->style['scriptList'][] = $this->cssRoot.$style;
    }

    /**
     * @param $script string script to add (does not need the extension, but needs the path relative to $jsRoot)
     */
    public function addScript($script){
        $this->script['scriptList'][] = $this->jsRoot.$script;
    }

    public function display($viewName, $variables = []){
        $variables['data'] = array(
            'stylesArray' => $this->style,
            'scriptsArray' => $this->script
        );

        echo $this->engine->parse($this->viewLoader->load($viewName), $variables);
    }

    public function displayFile($url, $extension){
        ob_start();
        header('Content-Type: text/'.$extension);

        echo file_get_contents($url);

        echo ob_get_clean();
    }

}
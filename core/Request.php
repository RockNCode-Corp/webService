<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 25/11/17
 * Time: 10:10 AM
 */

class Request{

    /**
     * @var array Contient toutes les donnees passees en parametres
     */
    private $parametres = [];

    /**
     * @var string - Type requete GET, POST, PUT, DELETE
     */
    private $type;

    /**
     * @param $param array merge POST et GET
     * @param $type string
     */
    public function __construct($param, $type) {
        $this->parametres = $param;
        $this->type = $type;
    }

    public function get($name) {
        return !empty($this->parametres[$name]) ? $this->parametres[$name] : null;
    }

    public function getType() {
        return $this->type;
    }

}
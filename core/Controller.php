<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 25/11/17
 * Time: 10:21 AM
 */

class Controller{

    /**
     * Access to config file
     * @param $nom
     * @return null || string
     */
    protected function get($nom){
        try{
            return Configuration::get($nom);
        }catch(Exception $e){
            return null;
        }
    }

    /**
     * Generate a secure string
     * @param int $limit
     * @return string securiser
     */
    protected function generateSecureString($limit = 8){
        return bin2hex(random_bytes($limit));
    }

    /**
     * Ajax response in JSON
     * @param array $data
     * @return mixed
     */
    protected function ajax($data = []){
        if(!array_key_exists('succes', $data)) $data['succes'] = true;

        $reponse = new Response(json_encode($data));
        $reponse->addHeader("Access-Control-Allow-Origin", "*");
        $reponse->setContentType('application/json');

        $reponse->envoyer();
    }

}
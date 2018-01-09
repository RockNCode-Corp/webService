<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 25/11/17
 * Time: 10:10 AM
 */

class Response{


    const HTTP_OK = 200;
    const HTTP_NOT_FOUND = 404;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    private $contentType;

    private $content;

    private $status;


    public function __construct($content = '', $status = 200){
        $this->setContent($content);
        $this->setStatus($status);
    }

    /**
     * @param String content type
     */
    public function setContentType($contentType){
        $this->contentType = $contentType;
        header('Content-Type: '.$contentType);
    }

    /**
     * @param $header
     * @param $type
     */
    public function addHeader($header, $type){
        header($header.': '.$type);
    }

    /**
     * @return mixed
     */
    public function getContent(){
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content){
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getStatus(){
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status){
        $this->status = $status;
        http_response_code($this->status);
    }

    public function envoyer(){
        echo $this->content;
    }

}
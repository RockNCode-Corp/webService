<?php
/**
 * Created by PhpStorm.
 * User: Apomalyn
 * Date: 2017-07-30
 * Time: 15:25
 */

class Logger {

    /**
     * @var - file use in log
     */
    private $file;

    public function __construct() {
        if(!file_exists('../logs')){
            mkdir('../logs', 0777);
        }
        $this->file = fopen('../logs/dev-'.date('Y-m-d').'.log', 'a+');


    }

    /**
     * @param $message string to write in $file $message with the current date
     */
    private function write($message){
        $msg = '['.date('Y-m-d H:i:s').'] '.$message;
        fwrite($this->file, preg_replace('/([\r\n\t])/','', $msg).PHP_EOL);
    }

    /**
     * @param $message string to write a line in $file with the tag INFO
     */
    public function info($message){
        $this->write('INFO - '.$message);
    }

    /**
     * @param $message string to write a line in $file with the tag ERROR
     */
    public function error($message){
        $this->write('ERROR '.$message);
    }

    /**
     * @param $message string to write a line in $file with the tag WARNING
     */
    public function warning($message){
        $this->write('WARNING - '.$message);
    }
}
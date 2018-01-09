<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 25/11/17
 * Time: 10:25 AM
 */

use Configuration;
use PDO;

/**
 * Class DataBase connection to Database
 */
class DataBase{

    private static $db;

    /**
     * @return PDO connection instance
     */
    private static function getDb()
    {
        if(self::$db == null){
            try{
                $dbConnectData = Configuration::get('db');
            }catch (Exception $e){

            }

            $dsn = 'mysql:host='.$dbConnectData['host'].';dbname='.$dbConnectData['dbname'].';charset=utf8';
            $login = $dbConnectData['login'];
            $password = $dbConnectData['password'];

            self::$db = new PDO($dsn, $login, $password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        }

        return self::$db;
    }

    /**
     * Execute SQL request
     * @param $sql
     * @param null $params
     * @param bool $noReturn
     * @param bool $lastId
     * @return array|mixed|null
     */
    protected function execute($sql, $params = null, $noReturn = false, $lastId = false){
        if($params){
            $result = self::getDb()->prepare($sql);
            $result->execute($params);
        }else{
            $result = self::getDb()->query($sql);
        }

        if(!$noReturn){
            $data = $result->fetchAll();
            if($result->rowCount() == 1){
                $data = $data[0];
            }
            $data['nombre_lignes'] = $result->rowCount();
        }else if($lastId){
            $data[] = array(
                'id' => self::getDb()->lastInsertId()
            );
        }

        return (!empty($data)) ? $data:null;
    }

}
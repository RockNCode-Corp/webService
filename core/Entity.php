<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 25/11/17
 * Time: 10:29 AM
 */

abstract class Entity extends DataBase implements JsonSerializable {

    /**
     * @var string, id of this entity
     */
    protected $id;

    /**
     * @var array contains columns to update
     * [
     *  table => TABLE TO UPDATE
     *  NAME_COLUMN_TO_UPDATE => NEW VALUE,
     *  sub-table => [
     *                  table => TABLE TO UPDATE,
     *                  where => COLUMN TO USE FOR WHERE
     *                  data => [
     *                              NAME_COLUMN_TO_UPDATE => NEW VALUE,
     *                          ]
     *              ]
     * ]
     */
    protected $modify = [];

    /**
     * Entity constructor.
     * @param $id integer
     * @param $tableName string
     */
    public function __construct($id, $tableName) {
        $this->id = $id;
        $this->modify['table'] = $tableName;
    }

    /**
     * @return int
     */
    public function getId(){
        return $this->id;
    }

    /**
     * Update this entity
     */
    public function update(){

        if($this->id == -1){
            return $this->insert();
        }

        $params = $this->modify;
        $sql = 'UPDATE '.$params['table'].' SET ';
        unset($params['table']);

        $execArray = [];
        $subRequests = [];
        foreach ($params as $key => $value){
            if($key == 'sub-table'){
                $subRequests[] = $value;
            }else{
                $sql .= (count($execArray) < count($params) && count($execArray) > 0) ? ', '.$key.' = :'.$value : ' '.$key.' = :'.$value;
                $execArray[':'.$key] = $value;
            }
        }

        $sql .= ' WHERE id = :id';

        if(!empty($subRequests)){
            foreach ($subRequests as $request){
                $sql .= '; UPDATE '.$request['table']. ' SET ';
                $where = ' WHERE '.$request['where'].' = :id';
                $execTmpArray = [];
                foreach ($request['data'] as $key => $value){
                    $sql .= (count($execTmpArray) < count($request['data']) && count($execTmpArray) > 0) ? ', '.$key.' = :'.$value : ' '.$key.' = :'.$value;
                    $execTmpArray[':'.$key] = $value;
                }

                $sql .= $where;
                array_merge($execArray, $execTmpArray);
            }
        }

        $this->modify = array('table' => $this->modify['table']);

        $execArray[':id'] = $this->getId();

        $this->execute($sql, $execArray, true);
    }

    /**
     * Delete this entity
     */
    public function delete(){
        $sql = 'DELETE FROM '.$this->modify['table'].' WHERE id = :id';
        $this->execute($sql, array(':id' => $this->getId()), true);
    }

    /**
     * Insert this entity on database
     */
    public function insert(){
        $params = $this->modify;
        $sql = 'INSERT INTO '.$params['table'].' (';
        $values = ") VALUE (";
        unset($params['table']);

        $execArray = [];
        $subRequests = [];
        foreach ($params as $key => $value){
            if($key == 'sub-table'){
                $subRequests[] = $value;
            }else{
                $sql .= (count($execArray) < count($params) && count($execArray) > 0) ? ', '.$key: ' '.$key;
                $values .= (count($execArray) < count($params) && count($execArray) > 0) ? ', :'.$key: ' :'.$key;
                $execArray[':'.$key] = $value;
            }
        }

        $sql .= $values.")";

        if(!empty($subRequests)){
            foreach ($subRequests as $request){
                $sql .= '; INSERT INTO '.$request['table']. ' ( ';
                $values = ') VALUE (';

                $execTmpArray = [];
                foreach ($request['data'] as $key => $value){
                    $sql .= (count($execTmpArray) < count($request['data']) && count($execTmpArray) > 0) ? ', '.$key: ' '.$key;
                    $values .= (count($execTmpArray) < count($request['data']) && count($execTmpArray) > 0) ? ', :'.$key: ' :'.$key;
                    $execTmpArray[':'.$key] = $value;
                }

                $sql .= $values.")";
                array_merge($execArray, $execTmpArray);
            }
        }

        $this->modify = array('table' => $this->modify['table']);

        $this->id = $this->execute($sql, $execArray, true, true)['id'];
    }
}
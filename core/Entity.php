<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 25/11/17
 * Time: 10:29 AM
 */

abstract class Entity extends DataBase implements JsonSerializable {

    private const UPDATE = 0;
    private const INSERT = 1;

    /**
     * @var string, id of this entity
     */
    protected $id;

    /**
     * @var array contains data actually save in the database
     */
    private $currentData = [];

    /**
     * Entity constructor.
     * @param $id integer
     * @param $tableName string
     */
    public function __construct($id) {
        $this->id = $id;

        $this->__updateCurrentData();
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
        if($this->id == -1) return $this->insert();

        $params = $this->__modify(Entity::UPDATE);

        $sql = 'UPDATE '.$params['table'].' SET ';
        unset($params['table']);

        $execArray = [];
        foreach ($params as $key => $value){
            $sql .= (count($execArray) < count($params) && count($execArray) > 0) ? ', '.$key.' = :'.$key : ' '.$key.' = :'.$key;
            $execArray[$key] = $value;
        }

        $sql .= ' WHERE id = :id';

        $execArray['id'] = $this->id;

        $this->execute($sql, $execArray, true);
        $this->__updateCurrentData();
    }

    /**
     * Delete this entity
     */
    public function delete(){
        $sql = 'DELETE FROM '.$this->currentData['table'].' WHERE id = :id';
        $this->execute($sql, array('id' => $this->id), true);
    }

    /**
     * Insert this entity on database
     */
    private function insert(){
        $params = $this->__modify(Entity::INSERT);
        $sql = 'INSERT INTO '.$params['table'].' (';
        $values = ") VALUE (";
        unset($params['table']);

        $execArray = [];
        foreach ($params as $key => $value){
            $sql .= (count($execArray) < count($params) && count($execArray) > 0) ? ', '.$key: ' '.$key;
            $values .= (count($execArray) < count($params) && count($execArray) > 0) ? ', :'.$key: ' :'.$key;
            $execArray[$key] = $value;
        }

        $sql .= $values.")";

        $this->id = $this->execute($sql, $execArray, true, true)['id'];
        $this->__updateCurrentData();
    }

    /**
     * Calculate the diffence between the version in database and this version
     * @param int $type UPDATE or INSERT
     * @return array change to save in database
     */
    private function __modify(int $type) : array {
        $return = [];
        $reflection = new ReflectionClass($this);
        $return['table'] = DocParser::getValueOf('table', $reflection->getDocComment());

        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property){
            if($property->getName() != 'id' || $property->getName() != 'currentData'){
                $doc = $property->getDocComment();

                $name = DocParser::getValueOf('name', $doc);
                $property->setAccessible(1);
                $value = $property->getValue($this);
                $property->setAccessible(0);

                if(!empty(DocParser::getValueOf("sub", $doc)) && empty(DocParser::getValueOf('foreign', $doc))){
                    if($type == Entity::UPDATE) $value->update();
                    else if($type == Entity::INSERT) $value->insert();
                }else if(!empty($name) && $this->currentData[$name] != $value){
                    $return[$name] = $value;
                }
            }
        }
        return $return;
    }

    /**
     * Synchronise the version in database and this version. This function is use for calculate the difference during a update
     */
    private function __updateCurrentData(){
        $reflection = new ReflectionClass($this);
        $this->currentData['table'] = DocParser::getValueOf('table', $reflection->getDocComment());
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property){
            if($property->getName() != 'id'){
                $doc = $property->getDocComment();

                $name = DocParser::getValueOf('name', $doc);
                $property->setAccessible(1);
                $value = $property->getValue($this);
                $property->setAccessible(0);

                if(!empty($name) && empty(DocParser::getValueOf('sub', $doc)) && empty(DocParser::getValueOf('foreign', $doc))){
                    $this->currentData[$name] = $value;
                }
            }
        }
    }
}
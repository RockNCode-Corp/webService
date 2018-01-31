<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 17/01/18
 * Time: 3:15 PM
 */

abstract class Repository extends DataBase {

    /**
     * Construct a entity with specific data
     * @param string $entityName
     * @param array $data database response
     * @param array $properties
     * @return Entity
     */
    private function construct(string $entityName, array $data, array $properties) : Entity{
        $reflection = new ReflectionClass($entityName);

        $classProperties = $reflection->getProperties();

        $entity = $reflection->newInstanceWithoutConstructor();

        $id = $reflection->getProperty('id');
        $id->setAccessible(true);
        $id->setValue($entity, $data['id']);
        $id->setAccessible(false);

        foreach ($classProperties as $property){
            if(!empty($properties[$property->getName()])){
                $name = $property->getName();

                if($property->isPrivate() || $property->isProtected())
                    $property->setAccessible(true);

                if(is_array($properties[$name]) && array_key_exists('entity', $properties[$name])){
                    $property->setValue($entity, $properties[$name]['value']);
                }else{
                    $property->setValue($entity, $data[$properties[$name]]);
                }

                if($property->isPrivate() || $property->isProtected())
                    $property->setAccessible(false);
            }
        }

        $update = $reflection->getMethod('__updateCurrentData');
        $update->setAccessible(true);
        $update->invoke($entity);
        $update->setAccessible(false);

        return $entity;
    }

    /**
     * Return array contains entity properties (name, name in DB, foreign, etc...)
     * @param string $name
     * @return array
     */
    private function getProperties(string $name){
        $properties = [];
        $reflection = new ReflectionClass($name);

        $properties['table'] = DocParser::getValueOf('table', $reflection->getDocComment());
        $propertiesClass = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);

        foreach ($propertiesClass as $property){
            if($property->getName() != 'id'){
                $doc = $property->getDocComment();
                $name = DocParser::getValueOf('name', $doc);

                $foreign = DocParser::getValueOf('foreign', $doc);
                $oneTo = DocParser::getValueOf('one-to', $doc);

                if(!empty($foreign)){
                    $properties[$property->getName()] = array(
                        'name' => $name,
                        'table' => $foreign['table'],
                        'key' => $foreign['key']
                    );
                }else if(!empty($oneTo)){
                    $properties[$property->getName()] = array(
                        'entity' => $oneTo['entity'],
                        'isNull' => $oneTo['default'],
                        'key' => $oneTo['key']
                    );
                }else if(!empty($name)){
                    $properties[$property->getName()] = $name;
                }
            }
        }

        return $properties;
    }

    /**
     * Get a entity by id
     * @param string $entityTarget ModuleName:EntityName
     * @param int $id
     * @return Entity
     */
    public function get(string $entityTarget, int $id) : Entity {
        $entityTarget = str_replace(':', '\\entities\\', $entityTarget);

        $properties = $this->getProperties($entityTarget);
        $table = $properties['table'];
        $select = "SELECT $table.id";
        $from = 'FROM '.$table;
        $join = '';

        unset($properties['table']);
        unset($properties['constructor']);

        foreach ($properties as $key => $value){
            if(is_array($value)){
                if(array_key_exists('table', $value)){
                    $join .= 'INNER JOIN '.$value['table'].' ON '.$table.'.id'.'='.$value['table'].'.'.$value['key'].' ';
                    $select .= ", ".$value['table'].'.'.$value['name'];
                }else if(array_key_exists('entity', $value) && !array_key_exists('default', $value)){
                    $value['value'] =  $this->getBy($value['entity'], array($value['key'] => $id));
                }
            }else{
                $select .= ", ".$table.'.'.$value;
            }
        }

        $sql = $select." ".$from." ".$join." WHERE id = :id";
        $data = $this->execute($sql, array(
            'id' => $id
        ));

        return $this->construct($entityTarget, $data, $properties);
    }

    /**
     * NOT FINISH
     * @param string $entityTarget ModuleName:EntityName
     * @param array $params
     * @return Entity
     */
    public function getBy(string $entityTarget, array $params) : Entity {

    }
}
<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 17/01/18
 * Time: 3:15 PM
 */

abstract class Repository extends DataBase {

    public function update(Entity $entity) : void {
        $entity->update();
    }


}
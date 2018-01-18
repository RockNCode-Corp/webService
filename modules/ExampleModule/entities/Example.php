<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 23/12/17
 * Time: 7:51 PM
 */

namespace ExampleModule\entities;

use Entity;

class Example extends Entity {

    private $someAttribute;

//    private $someSubTableAttribute;

    public function __construct($id, $someAttribute) {
        $this->someAttribute = $someAttribute;

        parent::__construct($id, "TABLE NAME");
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
        );
    }
}
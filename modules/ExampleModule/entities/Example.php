<?php
/**
 * Created by PhpStorm.
 * User: apomalyn
 * Date: 23/12/17
 * Time: 7:51 PM
 */

namespace ExampleModule\entities;

use Entity;

/**
 * Class Example
 * @table NAME of table in database
 */
class Example extends Entity {

    /**
     * @var
     * @name name of this attributes in database
     */
    private $someAttribute;

    /**
     * @var
     * @name someSubTableAttribute
     * @foreign table=SUB TABLE NAME key=NAME FOREIGN KEY This attribute is a foreign key in database
     */
    private $someAttributeForeignKey;

    /**
     * @one-to entity=NAME key=NAME FOREIGN KEY default=null
     */
    private $someSubEntity = null;

    /**
     * @one-to entity=MODULE:NAME key=NAME FOREIGN KEY
     */
    private $someSubEntityNotNull;

    public function __construct($id, $someAttribute, $someSubEntityNotNull) {
        $this->someAttribute = $someAttribute;
        $this->someSubEntityNotNull = $someSubEntityNotNull;

        parent::__construct($id);
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id,
        );
    }
}
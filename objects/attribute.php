<?php
namespace Mytfg\Objects;

class Attribute extends AppObject {
    private $name;
    private $value;
    private $context;
    private $created;
    private $edited;
    private $isPublic;


    public function __construct() {
        $this->edited   = time();
        $this->created  = time();
        $this->isPublic = false;
    }

    public function setContext($context) {
        $this->context = $context;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getContext() {
        return $this->context;
    }

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function getType() {
        return gettype($this->value);
    }

    public function isPublic() {
        return $this->isPublic;
    }

    public function setPublic($bool) {
        $this->isPublic = $bool;
    }

    public function create() {
        global $res;
        if (!empty($this->name) && isset($this->value) && ($this->context instanceof AppObject)) {
            $query = Mysql::insert("attributes", array(
                    "context"  => $this->context->getGid(),
                    "type"     => gettype($this->value),
                    "name"     => $this->name,
                    "value"    => json_encode($this->value),
                    "created"  => $this->created,
                    "isPublic" => $this->isPublic ? 1 : 0,
                    "edited"   => $this->edited
                ));
            if (Mysql::exec($query) > 0) {
                $this->id = Mysql::insertId();
                AppObject::createObject($this);
            } else {
                return false;
            }
        } else {
            $res->log("Not all fields for attribute specified or they have invalid values");
            return false;
        }
    }

    public function save() {
        $query = Mysql::update("attributes", array(
                    "context" => $this->context->getGid(),
                    "type"  => gettype($this->value),
                    "name"   => $this->name,
                    "value" => json_encode($this->value),
                    "isPublic" => $this->isPublic ? 1 : 0,
                    "edited" => $this->edited
                ), $this->id);
        return Mysql::exec($query) == 1;
    }

    public function delete() {
        AppObject::deleteObject($this);
        $query = Mysql::delete("attributes", $this->id);
        return Mysql::exec($query) == 1;
    }

    public function load($id, $data = null) {
        if (is_null($data)) {
            $query = Mysql::load("attributes", $id);
            $r = Mysql::query($query);
            if ($r->rowCount() == 0) {
                return false;
            }
            $data = $r->fetch();
            $r->closeCursor();
        }
        $this->id       = $data["id"];
        $this->name     = $data["name"];
        $this->context  = AppObject::loadObj($data["context"]);
        $this->value    = json_decode($data["value"]);
        $this->created  = $data["created"];
        $this->isPublic = $data["isPublic"] ? true : false;
        $this->edited   = $data["edited"];

        return true;
    }

    public function toString() {
        return $this->name . " => " . json_encode($this->value);
    }

    public function toArray(&$references) {
        return array();
    }
}
?>

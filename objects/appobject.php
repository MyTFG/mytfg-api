<?php
namespace Mytfg\Objects;
/**
 * General Object
 */
abstract class AppObject {
    protected $id;
    protected $globalId = false;
    private static $cache = array();

    /**
     * Returns a JSON representation of this object.
     * Used references are added to $references
     */
    public abstract function toArray(&$references);

    public abstract function toString();

    public function getId() {
        return $this->id;
    }

    public function getObjType() {
        return strtolower("\\" . get_class($this));
    }

    /**
     * Loads the object from the database
     * @id      The local Id of the Object
     * @data    The data already loaded from the database or null
     */
    protected abstract function load($id, $data = null);

    public abstract function save();

    public abstract function delete();

    public abstract function create();

    public function getGid() {
        if ($this->globalId === false) {
            $this->globalId = AppObject::getGlobalId($this->getId(), $this->getObjType());
        }
        return $this->globalId;
    }

    public static function loadObj($id, $type = null, $data = null) {
        global $res;

        if (gettype($type) != "NULL") {
            $exploded = explode("\\", $type);
            if (sizeof($exploded) == 1) {
                $type = "\\mytfg\\objects\\$type";
            }
            return AppObject::loadLocal($id, $type, $data);
        }

        // Load by global ID: Use cache if possible
        if (AppObject::cached($id)) {
            return AppObject::$cache[$id];
        }

        $query = Mysql::select("objects", array(
                "id" => $id
            ));
        $r = Mysql::query($query);
        if ($data = $r->fetch()) {
            $localid = $data["localId"];
            $type    = $data["type"];
        } else {
            $res->send(500);
        }
        $r->closeCursor();
        return AppObject::loadLocal($localid, $type, null, $id);
    }

    public static function loadLocal($id, $type, $data = null, $gid = false) {
        global $res;
        if (!($gid === false) && (is_null($data) || !isset($data["gid"]))) {
            $gid = AppObject::getGlobalId($id, $type);
        } elseif ($gid === false) {
            $gid = $data["gid"];
        }

        // Check if cached
        if (AppObject::cached($gid)) {
            return AppObject::$cache[$gid];
        }
        $obj = new $type();
        $obj->globalId = $gid;

        // Add to cache
        AppObject::$cache[$gid] = $obj;

        if (!$obj->load($id, $data)) {
            $res->log("Could not load object of type $type with id $id");
            unset(AppObject::$cache[$gid]);
            return false;
        }

        return $obj;
    }

    public static function getGlobalId($privateId, $type) {
        $query = Mysql::select("objects", array(
            "localId" => $privateId,
            "type"    => $type
        ), array("id"));
        $r = Mysql::query($query);
        if ($data = $r->fetch()) {
            $r->closeCursor();
            return $data["id"];
        } else {
            $r->closeCursor();
            return false;
        }
    }

    protected static function deleteObject($obj) {
        $gid = $obj->getGid();
        // Remove from cache
        unset(AppObject::$cache[$gid]);

        $query = Mysql::delete("objects", $gid);
        return Mysql::exec($query) == 1;
    }

    protected static function createObject($obj) {
        global $res;

        if (AppObject::getGlobalId($obj->getId(), $obj->getObjType()) === false) {
            $query = Mysql::insert("objects", array(
                "localId" => $obj->getId(),
                "type"    => $obj->getObjType()
            ));
            if (Mysql::exec($query) == 1) {
                // Add to cache
                $gid = $obj->getGid();
                AppObject::$cache[$gid] = $obj;
                return true;
            }
            return false;
        } else {
            $res->log("Object to create already exists!");
        }
    }

    private static function cached($gid) {
        return isset(AppObject::$cache[$gid]);
    }
}
?>

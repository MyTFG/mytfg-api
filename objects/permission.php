<?php
namespace Mytfg\Objects;

class Permission extends AppObject {
    private $context;
    private $created;
    private $permission;
    private $isDerived;

    public function __construct() {
        $this->created   = time();
        $this->isDerived = false;
    }

    /**
     * Creates a new sub-permission from a parent and a permission-string.
     */
    public static function createDerived($parent, $permission) {
        $perm = new Permission();
        $perm->id           = -1;
        $perm->globalId     = -1;
        $perm->context      = $parent->getContext();
        $perm->permission   = $permission;
        $perm->isDerived    = true;
        return $perm;
    }

    public function create() {
        global $res;
        if (!empty($this->permission) && ($this->context instanceof AppObject)) {
            $query = Mysql::insert("permissions", array(
                    "context" 		=> $this->context->getGid(),
                    "permission"    => $this->permission,
                    "created" 		=> $this->created
                ));
            if (Mysql::exec($query) > 0) {
                $this->id = Mysql::insertId();
                return AppObject::createObject($this);
            } else {
                return false;
            }
        } else {
            $res->log("Not all fields for permission specified or they have invalid values");
            return false;
        }
    }

    public function save() {
        $query = Mysql::update("permissions", array(
                    "context"  	  => $this->context->getGid(),
                    "permission"  => $this->permission
                ), $this->id);
        return Mysql::exec($query) == 1;
    }

    public function delete() {
        if ($this->isDerived) {
            return false;
        }
        AppObject::deleteObject($this);
        $query = Mysql::delete("permissions", $this->id);
        return Mysql::exec($query) == 1;
    }

    public function load($id, $data = null) {
        if (is_null($data)) {
            $query = Mysql::load("permissions", $id);
            $r = Mysql::query($query);
            if ($r->rowCount() == 0) {
                return false;
            }
            $data = $r->fetch();
            $r->closeCursor();
        }
        $this->id         = $data["id"];
        $this->permission = $data["permission"];
        $this->context    = AppObject::loadObj($data["context"]);
        $this->created    = $data["created"];

        return true;
    }

    public function toString() {
        return $this->permission;
    }

    public function toArray(&$references) {
        $j = array(
            "id" => $this->id,
            "permission" => $this->permission,
            "context" => $this->context->getGid(),
            "created" => $this->created,
            "derived" => $this->isDerived,
            "type" => $this->getObjType(),
            "gid"  => $this->getGid()
        );
        return $j;
    }

	public function getPermission() {
		return $this->permission;
	}

	public function setPermission($permission) {
		$this->permission = $permission;
	}

	public function getContext() {
		return $this->context;
	}

	public function setContext($context) {
		$this->context = $context;
	}

	public function getCreated() {
		return $this->created;
	}

    public function isDerived() {
        return $this->isDerived;
    }

    # Returns an array of all derived permissions
    public function getDerived() {
        return PermissionGroup::resolve($this);
    }
}
?>

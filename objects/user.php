<?php
namespace Mytfg\Objects;

class User extends AppObject {
    protected $id;
    // Unique username
    protected $username;
    // Date of creation
    protected $created;
    // Array of Permissions
    protected $permissions = array();
    // Array of Attributes
    protected $attributes = array();
    // Array of Authentications
    protected $authentications = array();

    public function __construct() {
		$this->created = time();
    }

    public function save() {

    }

    public function load($id, $data = null) {
        if (is_null($data)) {
            $query = Mysql::load("users", $id);
            $r = Mysql::query($query);
            $data = $r->fetch();
            $r->closeCursor();
        }

        $this->id       = $data["id"];
        $this->username = $data["username"];
        $this->created  = $data["created"];

        // Attributes
        $this->attributes = array();
        $attrIds = array();
        $query = Mysql::select("attributes", array(
                "context" => $this->getGid()
            ));
        $attrData = Mysql::query($query, "\\attribute");
        while ($ds = $attrData->fetch()) {
            $attribute = AppObject::loadObj($ds["id"], "attribute", $ds);
            $this->attributes[$attribute->getName()] = $attribute;
        }
        $attrData->closeCursor();

        // Authentications
        $this->authentications = array();
        $authIds = array();
        $query = Mysql::select("authentications", array(
                "context" => $this->getGid()
            ));
        $authData = Mysql::query($query, "\\authentication");
        while ($ds = $authData->fetch()) {
            $auth = AppObject::loadObj($ds["id"], "authentication", $ds);
            $this->authentications[$auth->getDevice()] = $auth;
        }
        $authData->closeCursor();

		// Permissions
		$this->permissions = array();
		$permIds = array();
        $query = Mysql::select("permissions", array(
                "context" => $this->getGid()
            ));
        $permData = Mysql::query($query, "\\permission");
        while ($ds = $permData->fetch()) {
            $perm = AppObject::loadObj($ds["id"], "permission", $ds);
            $this->permissions[$perm->getPermission()] = $perm;
        }
        $this->updateDerivedPermissions();

        return true;
    }

    public function create() {
        global $res;
        if (!empty($this->username)) {
            $query = Mysql::insert("users", array(
                "username" => $this->username,
                "created"  => $this->created
            ));
            if (Mysql::exec($query) > 0) {
                $this->id = Mysql::insertId();
                return AppObject::createObject($this);
            } else {
                $res->code(409, "User could not be created");
                $res->send();
            }
        } else {
            $res->code(409, "Empty username not allowed");
            $res->send();
        }
    }

    public function delete() {
        // Delete Attributes
        foreach ($this->attributes as $attribute) {
            $attribute->delete();
        }
        // Delete Authentications
        foreach ($this->authentications as $auth) {
            $auth->delete();
        }

        // TODO: Delete other related stuff

        AppObject::deleteObject($this);
        $query = Mysql::delete("users", $this->id);
        return (Mysql::exec($query) > 0);
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getUsername() {
        return $this->username;
    }

	// Checks, if a given password matches the users password
	public function validatePassword($password) {
		$cryptpw = $this->attr("password")->getValue();
		return password_verify($password, $cryptpw);
	}

    // Checks if an authentication for the given device is present.
    public function hasAuth($device) {
        return isset($this->authentications[$device]);
    }

    // Adds an authentication for the given device (or just returns it, if present)
    public function auth($device, $renew = false, $expiretime = 0) {
        if ($this->hasAuth($device)) {
            $auth = $this->authentications[$device];
            if ($renew) {
                $auth->generateToken();
                $auth->setExpiretime($expiretime);
                $auth->save();
            }
        } else {
            $auth = new Authentication();
            $auth->setContext($this);
            $auth->setDevice($device);
            $auth->setExpiretime($expiretime);
            $auth->create();
            $this->authentications[$device] = $auth;
        }
        return $auth;
    }

    public function removeAuth($device) {
        if ($this->hasAuth($device)) {
            $auth = $this->authentications[$device];
            $auth->delete();
        }
        return true;
    }

	// Checks for a specific permission
	public function hasPerm($perm) {
		return (isset($this->permissions[$perm]) or Guard::special($this, $perm));
	}

	public function grantPerm($perm) {
		if (!isset($this->permissions[$perm]) || $this->permissions[$perm]->isDerived()) {
			$permission = new Permission();
			$permission->setPermission($perm);
			$permission->setContext($this);
			if ($permission->create()) {
				$this->permissions[$perm] = $permission;
				return true;
			} else {
				global $res;
				$res->log("Permission $perm could not be granted");
				return false;
			}
		}
		return true;
	}

	public function removePerm($perm) {
		if (isset($this->permissions[$perm])) {
			$permission = $this->permissions[$perm];
			if ($permission->delete()) {
				unset($this->permissions[$perm]);
				return true;
			} else {
				global $res;
				$res->log("Permission $perm could not be removed");
				return false;
			}
		}
		return true;
	}

    public function hasAttr($key) {
        return isset($this->attributes[$key]);
    }

    public function attr($key, $val = null, $isPublic = false) {
        if (gettype($val) != "NULL") {
            if ($this->hasAttr($key)) {
                $attr = $this->attributes[$key];
                $attr->setValue($val);
                $attr->setPublic($isPublic);
                $attr->save();
            } else {
                $attr = new Attribute();
                $attr->setContext($this);
                $attr->setName($key);
                $attr->setValue($val);
                $attr->setPublic($isPublic);
                $attr->create();
                $this->attributes[$key] = $attr;
            }
        }
        return $this->attributes[$key];
    }

    public function removeAttr($key) {
        if (isset($this->attributes[$key])) {
            $attr = $this->attributes[$key];
            $attr->delete();
            unset($this->attributes[$key]);
        }
        return true;
    }

    public function getPermissions() {
        return $this->permissions;
    }

    public function getAuths() {
        return $this->authentications;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function toString() {
        return $this->username;
    }

    public function toArray(&$references) {
        $displayAttr = $this->attributes["display"];
        $display = $displayAttr->isPublic() ? $display = $displayAttr->getValue() : $display = "";

        $attributes = array();
        foreach ($this->attributes as $attr) {
            if ($attr->isPublic()) {
                $attributes[] = $attr->getGid();
            }
        }

        $authentications = array();
        foreach ($this->authentications as $auth) {
            $authentications = $auth->getGid();
        }

        $arr = array(
                "id" => $this->getId(),
                "created" => $this->created,
                "username" => $username,
                "display"  => $display,
                "attributes" => $attributes,
                "authentications" => $authentications
            );

        return $arr;
    }

    private function updateDerivedPermissions() {
        $permissions = $this->permissions;
        foreach ($permissions as $permission) {
            $this->permissions = permission_merge($permission->getDerived(), $this->permissions);
        }
    }

    // STATIC FUNCTIONS
    public static function crypt($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function usernameExists($username) {
        $query = Mysql::select("users", array(
            "username" => $username
        ), array("id"));
        $r = Mysql::query($query);
        $r->closeCursor();
        return $r->rowCount() > 0;
    }

    public static function get($username) {
        $query = Mysql::select("users", array(
            "username" => $username
        ));
        $r = Mysql::query($query, "\\user");
        $data = $r->fetch();
        $r->closeCursor();
        $id = $data["id"];
        $user = AppObject::loadObj($id, "user", $data);
        return $user;
    }

	public static function validateAuth() {
		$noUser = new User();
		global $res;
		if (isset($_SESSION["sys_auth_username"])) {
			$username = $_SESSION["sys_auth_username"];
			$token	  = $_SESSION["sys_auth_token"];
			$device   = $_SESSION["sys_auth_device"];
		} else {
			$res->log("No authentication fields found");
			return $noUser;
		}

		if (User::usernameExists($username)) {
			$user = User::get($username);
			if ($user->hasAuth($device)) {
				$auth = $user->auth($device);
				if ($auth->getToken() == $token) {
					$res->log("User $username authenticated for device $device");
					return $user;
				} else {
					$res->log("Invalid token for device $device and user $username");
				}
			} else {
				$res->log("Invalid Device for $username");
			}
		}

		return $noUser;
	}
}
?>

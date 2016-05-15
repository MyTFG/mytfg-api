<?php
namespace Mytfg\Objects;

class Authentication extends AppObject {
    private $device;
    private $token;
    private $context;
    private $created;
    private $lastused;
    private $expiretime;


    public function __construct() {
        $this->lastused   = 0;       // Never
        $this->expiretime = 0;       // Never
        $this->created  = time();
    }

    public function create() {
        global $res;
        $this->generateToken();
        if (!empty($this->device) && !empty($this->token) && ($this->context instanceof AppObject)) {
            $query = Mysql::insert("authentications", array(
                    "context"    => $this->context->getGid(),
                    "device"     => $this->device,
                    "token"      => $this->token,
                    "lastused"   => $this->lastused,
                    "expiretime" => $this->expiretime,
                    "created"    => $this->created
                ));
            if (Mysql::exec($query) > 0) {
                $this->id = Mysql::insertId();
                AppObject::createObject($this);
            } else {
                return false;
            }
        } else {
            $res->log("Not all fields for authentication specified or they have invalid values");
            return false;
        }
    }

    public function save() {
        $query = Mysql::update("authentications", array(
                    "context"    => $this->context->getGid(),
                    "device"     => $this->device,
                    "token"      => $this->token,
                    "expiretime" => $this->expiretime,
                    "lastused"   => $this->lastused
                ), $this->id);
        return Mysql::exec($query) == 1;
    }

    public function delete() {
        AppObject::deleteObject($this);
        $query = Mysql::delete("authentications", $this->id);
        return Mysql::exec($query) == 1;
    }

    public function load($id, $data = null) {
        if (is_null($data)) {
            $query = Mysql::load("authentications", $id);
            $r = Mysql::query($query);
            if ($r->rowCount() == 0) {
                return false;
            }
            $data = $r->fetch();
            $r->closeCursor();
        }

        $this->id         = $data["id"];
        $this->device     = $data["device"];
        $this->token      = $data["token"];
        $this->created    = $data["created"];
        $this->lastused   = $data["lastused"];
        $this->expiretime = $data["expiretime"];
        $this->context    = AppObject::loadObj($data["context"]);

        return true;
    }

    public function toString() {
        return $this->device . " - " . $this->token;
    }

    public function toArray(&$references) {
        $j = array(
            "id"         => $this->id,
            "token"      => $this->token,
            "device"     => $this->device,
            "context"    => $this->context->getGid(),
            "created"    => $this->created,
            "lastused"   => $this->lastused,
            "expiretime" => $this->expiretime,
            "expiredate" => $this->getExpireTimestamp(),
            "type"       => $this->getObjType(),
            "gid"        => $this->getGid()
        );
        return $j;
    }

    ##################################################

    public function validate($device, $token, $context) {
        $start      = max($this->created, $this->lastused);
        // Check whether the token will expire and if it does, check whether the date of
        // expiration is in the future
        $valid_time = ($this->expiretime > 0) ? ($start + $this->expiretime > time()) : true;

        return ($valid_time && $this->device == $device && $this->token == $token && $this->context->getGid() == $context->getGid());
    }

    /**
    * Uses the token: Updates the time
    */
    public function update() {
        $this->setLastused(time());
        $this->save();
    }

    /**
    * Generates a new token.
    */
    public function generateToken() {
        $true = true;
        $this->setToken(bin2hex(openssl_random_pseudo_bytes(48, $true)));
    }

    /**
    * Gets the value of device.
    */
    public function getDevice() {
        return $this->device;
    }

    /**
    * Sets the value of device.
    */
    public function setDevice($device) {
        $this->device = $device;
    }

    /**
    * Gets the value of token.
    */
    public function getToken() {
        return $this->token;
    }

    /**
    * Sets the value of token.
    */
    public function setToken($token) {
        $this->token = $token;
    }

    /**
    * Gets the value of context.
    */
    public function getContext() {
        return $this->context;
    }

    /**
    * Sets the value of context.
    */
    public function setContext($context) {
        $this->context = $context;
    }

    /**
    * Gets the value of created.
    */
    public function getCreated() {
        return $this->created;
    }

    /**
    * Gets the value of lastused.
    */
    public function getLastused() {
        return $this->lastused;
    }

    /**
    * Sets the value of lastused.
    */
    public function setLastused($lastused) {
        $this->lastused = $lastused;
    }


    /**
    * Gets the value of expiretime.
    */
    public function getExpiretime() {
        return $this->expiretime;
    }

    /**
    * Sets the value of expiretime.
    */
    public function setExpiretime($expiretime) {
        $this->expiretime = $expiretime;
    }

    public function getExpireTimestamp() {
        if ($this->expiretime == 0) {
            return 0;
        } else {
            $start = max($this->created, $this->lastused);
            return $start + $this->expiretime;
        }
    }

    public function isExpired() {
        $expiretime = $this->getExpireTimestamp();
        if ($expiretime == 0) {
            return false;
        } else {
            return ($expiretime - time()) > 0;
        }
    }


    public static function get($token) {
        $query = Mysql::select("authentications", array(
            "token" => $token
        ));
        $r = Mysql::query($query, "\\authentication");
        if ($r->rowCount() == 0) {
            return false;
        }
        $data = $r->fetch();
        $r->closeCursor();
        $id = $data["id"];
        $auth = AppObject::loadObj($id, "authentication", $data);
        return $auth;
    }

}
?>

<?php
namespace Mytfg\Api\User;

class Permission {
    public function exec() {
        global $call;
        global $res;

        $function = $call["subfct"];
        if (!empty($function) && method_exists($this, $function)) {
            $this->$function();
        } else {
            $res->code(400, "The request did not specify a valid sub-function");
            $res->send();
        }
    }

    private function grant() {
        global $p;
        global $res;
        \Mytfg\Objects\Guard::need("user/permission/grant");

        $required_fields = array("username", "permission");
        \Mytfg\Objects\RequestValidator::lock("contains", $p, $required_fields);

        $username    = $p["username"];
        $permission  = $p["permission"];

        if (\Mytfg\Objects\User::usernameExists($username)) {
            $user = \Mytfg\Objects\User::get($username);
            $user->grantPerm($permission);
            $res->send(200);
        } else {
            $res->code(400, "User does not exist");
            $res->send();
        }
    }

    private function take() {
        global $p;
        global $res;
        \Mytfg\Objects\Guard::need("user/permission/take");

        $required_fields = array("username", "permission");
        \Mytfg\Objects\RequestValidator::lock("contains", $p, $required_fields);

        $username    = $p["username"];
        $permission  = $p["permission"];

        if (\Mytfg\Objects\User::usernameExists($username)) {
            $user = \Mytfg\Objects\User::get($username);
            $user->removePerm($permission);
            $res->send(200);
        } else {
            $res->code(400, "User does not exist");
            $res->send();
        }
    }

    private function get() {
        global $p;
        global $res;
        global $currentuser;

        $required_fields = array("username");
        \Mytfg\Objects\RequestValidator::lock("contains", $p, $required_fields);

        $username = $p["username"];

        \Mytfg\Objects\Guard::needOne("user/permission/get", "user/is/$username");

        if (\Mytfg\Objects\User::usernameExists($username)) {
            $user = \Mytfg\Objects\User::get($username);
            $permissions = $user->getPermissions();
            $result = array();
            foreach ($permissions as $perm) {
                $a = array();
                $result[] = $perm->toArray($a);
            }
            $res->set("result", $result);
            $res->send(200);
        } else {
            $res->code(400, "User does not exist");
            $res->send();
        }
    }
}
?>

<?php
namespace Mytfg\Api\Auth;

class Logout {
    public function exec() {
        global $p;
        global $res;
        global $currentuser;

        $required_fields = array("sys_auth_token");

        \Mytfg\Objects\RequestValidator::lock("contains", $p, $required_fields);

        $token = $p["sys_auth_token"];

        if ($currentuser->hasAuth($token)) {
            $currentuser->removeAuth($token);
            $res->send(200);
        } else {
            // If not logged in.
            $res->code(401, "Invalid authentication token");
            $res->send();
        }
    }
}
?>

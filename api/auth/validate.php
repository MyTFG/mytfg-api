<?php
namespace Mytfg\Api\Auth;

class Validate {
    public function __construct() {

    }

    public function exec() {
        global $p;
        global $res;
        global $currentuser;

        $required_fields = array("sys_auth_token");

        \Mytfg\Objects\RequestValidator::lock("contains", $p, $required_fields);

        if ($currentuser->hasAuth($p["sys_auth_token"])) {
            $res->send(200);
        } else {
            $res->send(401);
        }
    }
}
?>

<?php
namespace Mytfg\Api\Auth;

class Login {
    public function __construct() {

    }

    public function exec() {
        global $p;
        global $res;

        $required_fields = array("username", "token", "device");

        \Mytfg\Objects\RequestValidator::lock("contains", $p, $required_fields);

        $username = $p["username"];
        $token    = $p["token"];
        $device   = $p["device"];

        if (\Mytfg\Objects\User::usernameExists($username)) {
            $user = \Mytfg\Objects\User::get($username);

            if ($user->hasAuth($device)) {
                $auth = $user->auth($device);
                if ($auth->validate($device, $token, $user)) {
                    $res->set("result", true);
                    $res->send(200);
                } else {
                    $res->set("result", false);
                    $res->code(200, "Invalid token");
                    $res->send();
                }
            } else {
                $res->set("result", false);
                $res->code(200, "Unknown device");
                $res->send();
            }
        } else {
            $res->code(400, "User does not exist");
            $res->send();
        }
    }
}
?>

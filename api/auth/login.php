<?php
namespace Mytfg\Api\Auth;

class Login {
    public function __construct() {

    }

    public function exec() {
        global $p;
        global $res;

        $required_fields = array("username", "password", "device", "session");

        \Mytfg\Objects\RequestValidator::lock("contains", $p, $required_fields);

        $username = $p["username"];
        $password = $p["password"];
        $device   = $p["device"];
        $session  = eval_bool($p["session"]);

        if (\Mytfg\Objects\User::usernameExists($username)) {
            $user = \Mytfg\Objects\User::get($username);
            $isValid = $user->validatePassword($password);

            if ($isValid) {
                $activated = $user->attr("activated")->getValue();
                if (!$activated) {
                    $res->code(412, "User not activated");
                    $res->send();
                } else {
                    if ($session) {
                        $expiretime = session_expiretime;
                    } else {
                        $expiretime = 0;
                    }
                    $auth = $user->auth($device, true, $expiretime);
                    $refs = array();
                    $res->set("result", $auth->toArray($refs));
                    $res->set("references", $refs);
                    $res->send(200);
                }
            } else {
                $res->code(401);
                $res->send();
            }
        } else {
            $res->code(400, "User does not exist");
            $res->send();
        }
    }
}
?>

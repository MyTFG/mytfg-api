<?php
namespace Mytfg\Api\Auth;

class Logout {
    public function exec() {
        global $p;
        global $res;

        $required_fields = array("username", "device");

        \Mytfg\Objects\RequestValidator::lock("contains", $p, $required_fields);

        $username = $p["username"];
        $device   = $p["device"];

        if (\Mytfg\Objects\User::usernameExists($username)) {
            $user = \Mytfg\Objects\User::get($username);
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

<?php
namespace Mytfg\Api\User;

class Delete {
    public function exec() {
        \Mytfg\Objects\Guard::need("user/delete");

        global $res;
        global $p;
        $required_fields = array("username");

        // Gather required data
        \Mytfg\Objects\RequestValidator::lock("contains", $p, $required_fields);

        $username = $p["username"];

        if (\Mytfg\Objects\User::usernameExists($username)) {
            $user = \Mytfg\Objects\User::get($username);
            if ($user->delete()) {
                $res->send(200);
            } else {
                $res->send(500);
            }
        } else {
            $res->code(400, "User does not exist");
            $res->send();
        }
    }
}
?>

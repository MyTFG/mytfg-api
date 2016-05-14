<?php
namespace Mytfg\Api\User;

class Create {
    public function __construct() {

    }

    public function exec() {
        \Mytfg\Objects\Guard::need("user/create");

        global $p;
        global $res;

        if (!isset($p["user/create"])) {
            $res->code(400, "No JSON for user/create found");
            $res->send();
        }

        $required_fields = array("username", "password", "attributes");
        $json     = $p["user/create"];

        \Mytfg\Objects\RequestValidator::lock("contains", $json, $required_fields);

        $username   = $json["username"];
        $password   = $json["password"];
        $attributes = $json["attributes"];
        $activate   = isset($json["activate"]) ? ($json["activate"]) : false;

        $required_attr = array("firstname", "lastname");
        $valid_attr    = array_merge($required_attr, array("birthday", "mail", "type", "display", "grade", "externalId"));

        \Mytfg\Objects\RequestValidator::lock("contains", $attributes, $required_attr);
        \Mytfg\Objects\RequestValidator::lock("containsOnly", $attributes, $valid_attr);

        #############

        if (strlen($password) < 6) {
            $res->code(400, "Password needs to be at least length 6");
            $res->send();
        }

        if (\Mytfg\Objects\User::usernameExists($username)) {
            $res->code(409, "Username already exists");
            $res->send();
        }

        // Create User
        $user = new \Mytfg\Objects\User();
        $user->setUsername($username);
        if ($user->create()) {
            $user->attr("password", \Mytfg\Objects\User::crypt($password));
            $user->attr("display", $username, true);
            $user->attr("activated", $activate);

            // Set all given attributes (Extraction: see above)
            foreach ($attributes as $attr => $value) {
                $user->attr($attr, $value["value"], $value["private"]);
            }

            $res->send(200);
        } else {
            $res->send(500);
        }
    }
}
?>

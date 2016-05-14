<?php
namespace Mytfg\Objects;

class Call {
    public static function parse() {
        global $call;
        $url = $_SERVER["REQUEST_URI"];
        // Extract part in front of GET Vars
        $url = explode("?", $url);
        $url = $url[0];

        $url_split = array_values(array_filter(explode("/", $url), function($k) {
             return !empty($k);
        }));

        if (sizeof($url_split) < 3) {
            global $res;
            $res->code(400, "The request did not specify a valid module and a valid function.");
            $res->send();
        }


        $body = file_get_contents("php://input");

        array_shift($url_split);
        $call["module"]    = $url_split[0];
        $call["function"]  = $url_split[1];
        $call["subfct"]    = isset($url_split[2]) ? $url_split[2] : "";
        $call["subsubfct"] = isset($url_split[3]) ? $url_split[3] : "";
        $call["params"]    = array();

        $body_vars = array();
        if (!empty($body)) {
            $body_vars = json_decode($body, true);
            if (!is_array($body_vars)) {
                $body_vars = array();
            }
        }
        $call["params"] = array_merge($_GET, $_POST, $body_vars);
    }
}
?>

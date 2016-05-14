<?php
namespace Mytfg\Objects;

class RequestValidator {
    public static function lock($function, $obj, $keys = array(), $path = array()) {
        if (!RequestValidator::$function($obj, $keys, $path)) {
            switch ($function) {
                case "contains":
                    $errormsg = "Required fields: ";
                    break;
                case "containsOnly":
                    $errormsg = "Allowed fields: ";
                    break;
                case "containsExactly":
                    $errormsg = "Exactly required fields: ";
                    break;
                default:
                    $errormsg = "Invalid fields: ";
                    break;
            }
            global $res;
            $res->code(400, $errormsg . implode(", ", $keys));
            $res->send();
        }
        return true;
    }

    public static function contains($obj, $keys = array(), $path = array()) {
        global $res;
        if (sizeof($path) > 0) {
            $identifier = array_shift($path);
            if (isset($obj[$identifier])) {
                return RequestValidator::contains($obj[$identifier], $keys, $path);
            } else {
                return false;
            }
        } else {
            if (is_array($obj)) {
                foreach ($keys as $key) {
                    if (!isset($obj[$key])) {
                        $res->log("Missing key: " . $key);
                        return false;
                    }
                }
                return true;
            } else {
                $res->log("Missing keys due to invalid object");
                return false;
            }
        }
    }

    public static function containsOnly($obj, $keys = array(), $path = array()) {
        global $res;
        if (sizeof($path) > 0) {
            $identifier = array_shift($path);
            if (isset($obj[$identifier])) {
                return RequestValidator::containsOnly($obj[$identifier], $keys, $path);
            } else {
                return true;
            }
        } else {
            if (is_array($obj)) {
                foreach ($obj as $key => $val) {
                    if (!in_array($key, $keys)) {
                        $res->log("Forbidden key: " . $key);
                        return false;
                    }
                }
                return true;
            } else {
                return true;
            }
        }
    }

    public static function containsExactly($obj, $keys = array(), $path = array()) {
        return RequestValidator::contains($obj, $keys, $path) && RequestValidator::containsOnly($obj, $keys, $path);
    }
}

?>

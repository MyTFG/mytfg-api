<?php
namespace Mytfg\Objects;

class Result {
    private $obj;

    // Statistics
    private $start_time;
    private $end_time;
    private $db_calls;
    private $db_queries = array();

    private $callback;
    private $subResult;

    public function __construct($obj = array()) {
        $this->obj = $obj;
        $this->obj = array(
            "status" => false,
            "message" => false,
            "apicode" => false,
            "log" => array()
        );
        $this->callback   = false;
        $this->subResult  = false;
        $this->start_time = microtime(true);
        $this->db_calls   = 0;
    }

    public function code($code, $msg = false) {
        $this->obj["status"] = $code;
        if (!$msg) {
            $this->obj["message"] = Result::codeToString($code);
        } else {
            $this->obj["message"] = $msg;
        }
        $this->log($code . ": " . $this->obj["message"]);
    }

    public function msg($msg) {
        $this->obj["message"] = $msg;
    }

    public function api($apicode) {
        $this->obj["apicode"] = $apicode;
    }

    public function send($code = false) {
        if ($code !== false) {
            $this->code($code);
        }

        if (empty($this->obj["status"])) {
            $this->obj["status"] = 500;
            $this->obj["message"] = "API returned invalid response code";
        }

        if ($this->obj["apicode"] === false) {
            $this->obj["apicode"] = $this->obj["status"];
        }

        # This prevents modules called by modules to return an actual result.
        # So they just get executed and when they have finished their execution,
        # the original module is called again via a callback function.
        if ($this->subResult) {
            $callback();
            return;
        }

        #  This does not accept all status codes and free status messages:
        // http_response_code($this->obj["status"]);

        # Workaround: Set header manually
        header("HTTP/1.0 " . $this->obj["status"] . " " . $this->obj["message"]);

        $this->end_time = microtime(true);
        $time = $this->end_time - $this->start_time;

        $this->obj["runtime"] = TimeUtils::secondsToString($time);
        if (DB_DEBUG) {
            $this->obj["db_calls"] = $this->db_calls;
            $this->obj["db_queries"] = $this->db_queries;
        }

        # Stop execution and send the result
        die(json_encode($this->obj));
    }

    public function log($message) {
        $this->obj["log"][] = $message;
    }

    public function set($key, $val) {
        $this->obj[$key] = $val;
    }

    public function setSubresult($bool) {
        $this->subResult = $bool;
    }

    public function getSubresult() {
        return $this->subResult;
    }

    public function setCallback($callback) {
        $this->callback = $callback;
    }

    public function db_call($query = "") {
        if (!DB_DEBUG) {
            return;
        }
        $this->db_calls++;
        $trace = debug_backtrace();

        $hist = array();
        if (sizeof($trace) > 0) {
            array_shift($trace);
            foreach ($trace as $pos) {
                $hist[] = "Called by " . $pos["function"] . " in " . $pos["file"]
                    . " on line " . $pos["line"];
            }
        }

        $this->db_queries[] = array("query" => $query, "trace" => $hist);;
    }

    private static function codeToString($code) {
        global $call;
        $codes = array(
            200 => "Ok",
            400 => "Bad request",
            401 => "The given Login is invalid",
            403 => "Insufficient permissions",
            404 => "API function not found",
            409 => "Conflict",
            412 => "Precondition Failed",
            500 => "Internal server error",
            501 => "Function " . $call["function"] . " not available in module " . $call["module"]
        );
        if (isset($codes[$code])) {
            return $codes[$code];
        } else {
            return "Unknown response code";
        }
    }
}
?>

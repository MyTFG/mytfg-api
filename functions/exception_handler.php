<?php
function exceptionHandler($ex) {
    global $res;
    if (is_null($res)) {
        $res = new \Mytfg\Objects\Result();
    }
    $res->log($ex->getMessage());
    $res->code(500);
    $res->send();
}

function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
    global $res;
    if (is_null($res)) {
        $res = new \Mytfg\Objects\Result();
    }

    if (DEBUG) {
        $res->log($errstr . " in " . $errfile . " on line " . $errline);
        $trace = debug_backtrace();
        $hist  = array();
        if (sizeof($trace) > 1) {
            array_shift($trace);
            foreach ($trace as $pos) {
                $hist[] = "Called by " . $pos["function"] . " in " . $pos["file"]
                    . " on line " . $pos["line"];
            }
            $res->log($hist);
        }
    }

    $res->code(500);
    $res->send();
}

function check_for_fatal() {
    $error = error_get_last();
    if ( $error["type"] == E_ERROR )
        errorHandler($error["type"], $error["message"], $error["file"], $error["line"] );
}

?>

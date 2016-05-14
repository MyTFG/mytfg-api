<?php

function __autoload($class) {
    $parts = explode('\\', $class);
    $parts = array_map(function($val) {return strtolower($val);}, $parts);
    array_shift($parts);
    $file = implode("/", $parts) . '.php';
    if (file_exists($file)) {
        require $file;
    }
}

?>

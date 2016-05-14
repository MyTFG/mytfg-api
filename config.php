<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

include("config/auth.php");
include("config/database.php");
include("config/session.php");

define("APP_NAME", "MyTFG");
define("APP_VERSION_CODE", 7);
define("APP_VERSION_NAME", "7.0");
define("APP_VERSION_CHANNEL", "develop");

define("DEBUG", true);
define("DB_DEBUG", false);
?>

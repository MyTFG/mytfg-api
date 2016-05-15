<?php
namespace Mytfg;


session_start();

include "include.php";
// ini_set('display_errors', false);

set_exception_handler("exceptionHandler");
set_error_handler("errorHandler");
register_shutdown_function("check_for_fatal");

header("Content-Type: application/json");

Objects\Mysql::init(db_server, db_database, db_user, db_password);

// The result
global $res;
$res = new Objects\Result();
// Represents the call
global $call;
global $p;

Objects\Call::parse();
$p = $call["params"];

// Extract authentication
if (isset($p["sys_auth_token"])) {
	$_SESSION["sys_auth_token"]    = $p["sys_auth_token"];
}
// Validate authentication
global $currentuser;
$currentuser = Objects\User::validateAuth();

// Execute the module
$file = "api/" . $call["module"] . "/" . $call["module"] . ".php";

$module = new Api\Module($call["module"], $call["function"]);
$module->exec();

$res->send();
?>

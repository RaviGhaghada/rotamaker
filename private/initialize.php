<?php 

    // Super useful code that let's you run see why your code crashed
    // with stack trace and stuff. yeah... super cool!
	ini_set('display_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE);

	require_once("../private/functions.php");

    require_once('../private/database.php');
    require_once('../private/query_functions.php');

    // yo we need a login page right before we go any further
    $db = Database::db_connect();
    require_once('../private/components_algorithm.php');
    require_once('../private/rota_algorithm.php');    
    

	require_once('../private/display_functions.php');
?>

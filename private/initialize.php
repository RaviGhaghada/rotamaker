<?php 

	ini_set('display_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE);

	require_once("../private/functions.php");

    require_once('../private/database.php');
    require_once('../private/query_functions.php');



    $db = Database::db_connect();

    require_once('../private/components_algorithm.php');
    require_once('../private/rota_algorithm.php');    
    
?>

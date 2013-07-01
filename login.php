<?php

require_once 'NAApiClient.php';
require_once 'Config.php';

$test_username = $_POST["username"]; 
$test_password = $_POST["password"]; 
$client = new NAApiClient(array("client_id" => $client_id, "client_secret" => $client_secret, "username" => $test_username, "password" => $test_password));
session_start();  
try {
    $tokens = $client->getAccessToken();        
	} catch(NAClientException $ex) {
    	$_SESSION['msg'] = 'Identifiant ou mot de passe incorrect';
    	header("location:index.php");
		exit(-1);	
	}
$_SESSION['client'] = $client;
header("location:iconesExt.php");
?>

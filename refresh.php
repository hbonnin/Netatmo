<?php 
require_once 'NAApiClient.php';
require_once 'initClient.php';
session_start(); 
date_default_timezone_set("Europe/Paris");
//checkToken();
//initClient();
$tl = getTimeLeft();
echo "Time left for token:{$tl}s";
?>

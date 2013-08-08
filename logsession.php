<?php 
require_once 'AppliCommonPublic.php';
require_once 'NAApiClient.php';
require_once 'initClient.php';
session_start();
echo "<pre>";
print_r($_SESSION['LogMsg']);
echo("-------------------------------\n");
echo "Time left for token: ".getTimeLeft()."s\n";
echo("-------------------------------\n");
print_r($_SESSION['client']);
echo("-------------------------------\n");
print_r($_SESSION['mydevices']);

?>

<?php 
require_once 'AppliCommonPublic.php';
require_once 'NAApiClient.php';
require_once 'initClient.php';
session_start();
date_default_timezone_set("Europe/Paris");
echo "<pre>";
print_r($_SESSION['LogMsg']);
echo("-------------------------------\n");
echo "Token valide &#8594; ".date('d/m/Y H:i',time()+getTimeLeft()).";\n";
echo("-------------------------------\n");
print_r($_SESSION['client']);
echo("-------------------------------\n");
print_r($_SESSION['mydevices']);

?>

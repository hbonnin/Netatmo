<?php 
require_once 'Config.php';
require_once 'AppliCommonPublic.php';
require_once 'NAApiClient.php';
require_once 'initClient.php';
session_start();
date_default_timezone_set($timezone);
echo "<pre>";
echo("Temperature_unit = $Temperature_unit <br>");
print_r($_SESSION['LogMsg']);
echo("-------------------------------\n");
$t = $_SESSION['timeToken'] + $_SESSION['expires_in'];
$dt = $t - time();
echo "Token valide &#8594; ".date('d/m/Y H:i s',$t)."(".$dt."s);\n";
echo("-------------------------------\n");
print_r($_SESSION['client']);
echo("-------------------------------\n");
print_r($_SESSION['mydevices']);

?>

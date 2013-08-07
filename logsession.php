<?php 
require_once 'AppliCommonPublic.php';
require_once 'NAApiClient.php';
session_start();
echo "<pre>";
print_r($_SESSION['LogMsg']);
print_r($_SESSION['client']);
print_r($_SESSION['mydevices']);
?>

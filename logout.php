<?php session_start();?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<title>Stations Netatmo</title>
<meta charset='utf-8'>
<link rel='icon' href='favicon.ico' />
</head>
<body >

<h2> You are logged off </h2>

<?php 
if(isset($_SESSION['emsg']))
    echo "{$_SESSION['emsg']} <br>";
echo "<pre>";print_r($_SESSION);echo("</pre");
echo("</body></html>");
$_SESSION=array();
session_destroy();
?>

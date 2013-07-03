<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<meta charset='utf-8'>
</head>
<body>
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

?>
   	<script>
    var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
    x = w.innerWidth || e.clientWidth || g.clientWidth,
    y = w.innerHeight|| e.clientHeight|| g.clientHeight;
    top.location.href = 'iconesExt.php?width='+x+'&height='+y;
	</script>

</body>
</html>

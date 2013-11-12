<?php
require_once 'NAApiClient.php';
session_start(); 
require_once 'Config.php';
?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<meta charset='utf-8'>
<link rel="apple-touch-icon" href="icone/meteo.png" >
<link rel="apple-touch-startup-image" href="icone/startup.png">
</head>
<body>
<?php

if(isset($_POST["username"]))
    {$username = $_POST["username"]; 
    $password = $_POST["password"]; 
    }
else if(isset($_GET["username"]))   
    {$username = $_GET["username"]; 
    $password = $_GET["password"]; 
    }

$client = new NAApiClient(array("client_id" => $client_id, "client_secret" => $client_secret
                            , "username" => $username, "password" => $password));

try {
    $tokens = $client->getAccessToken();        
	} catch(NAClientException $ex) {
    	$_SESSION['msg'] = 'Identifiant ou mot de passe incorrect';
    	echo("<script>top.location.href = 'indexLogin.php';</script>");
	}

$_SESSION['username'] = $username;
$_SESSION['password'] = $password;
$_SESSION['client'] = $client;   
$_SESSION['timeToken'] = time();	
$_SESSION['refresh_token'] = $tokens['refresh_token'];
$_SESSION['expires_in'] = $tokens['expires_in'];

if(isset($_POST['saveCookie']))
    $_SESSION['saveCookie'] = 1;
echo("<script>	top.location.href = 'iconesExt.php';</script>");
//header('location:iconesExt.php');
// avant header('location:xxx'); util si appel ajax ?	
?>

</body>
</html>

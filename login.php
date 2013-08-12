<?php
require_once 'NAApiClient.php';
session_start(); 
require_once 'Config.php';
?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<meta charset='utf-8'>
</head>
<body>
<?php



$username = $_POST["username"]; 
$password = $_POST["password"]; 

$client = new NAApiClient(array("client_id" => $client_id, "client_secret" => $client_secret
                            , "username" => $username, "password" => $password));

try {
    $tokens = $client->getAccessToken();        
	} catch(NAClientException $ex) {
    	$_SESSION['msg'] = 'Identifiant ou mot de passe incorrect';
    	//echo("<pre>");print_r($ex);echo("</pre>");exit(-1);
    	echo("<script>top.location.href = 'indexLogin.php';</script>");
	}
$_SESSION['username'] = $username;
$_SESSION['password'] = $password;
$_SESSION['client'] = $client;

?>
   	<script>
    top.location.href = 'iconesExt.php';
	</script>

</body>
</html>

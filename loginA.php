<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<meta charset='utf-8'>
<link rel="apple-touch-icon" href="icone/meteo.png" >
<link rel="apple-touch-startup-image" href="icone/startup.png">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="js/jcookies.js"></script>
</head>

<body>
<h3>Please wait</h3>
<form name="xxx" method="post" action="Nlogin.php">
<input type="hidden" name="refresh_token" value="aaa" >
</form> 
</body>
</html>

<?php
define('__ROOT__', dirname(__FILE__));
require_once (__ROOT__.'/src/Netatmo/autoload.php');
require_once 'Config.php';
require_once 'initClient.php';
 
session_start(); 
$config = array("client_id" => $client_id,
                "client_secret" => $client_secret,
                "scope" => Netatmo\Common\NAScopes::SCOPE_READ_STATION);
$client = new Netatmo\Clients\NAWSApiClient($config);

if(isset($_GET["code"]))
    login($client,0);
else if(isset($_GET['error']))
    {if($_GET['error'] === "access_denied")
        echo " You refused to let this application access your Netatmo data \n";
    else echo "An error occured";
    }
else if(isset($_GET['start']))
    {
    //Ok redirect to Netatmo Authorize URL
    $redirect_url = $client->getAuthorizeUrl();
    header("HTTP/1.1 ". 302);
    header("Location: " . $redirect_url);
    logout();
    }
?>

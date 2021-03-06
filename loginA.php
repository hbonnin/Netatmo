<?php
define('__ROOT__', dirname(__FILE__));
require_once (__ROOT__.'/src/Netatmo/autoload.php');
require_once 'Config.php';
session_start(); 
?>

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
</body>
</html>

<?php
require_once 'initClient.php';
 
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
    echo "<script>";
    echo("path = \"$redirect_url\";\n");
    echo "top.location.href=path;";
    echo "</script>";    
    logout();
    }
?>

<?php
require_once 'NAApiClient.php';
session_start(); 
require_once 'Config.php';
require_once 'initClient.php';
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
<h1>Please wait</h1>
<?php

if(!isset($_SESSION['LogMsg']))
    $_SESSION['LogMsg'] = "Log started:<br>";
    
if(isset($_POST["refresh_token"]) )
    {$_SESSION['refresh_token'] = $_POST["refresh_token"]; 
    if(refreshToken() != -1)
        echo "<script>	top.location.href = 'iconesExt.php';</script>";
     else        
        {alert("erasing bad cookie");
        echo "<script>";
        echo "$.jCookies({ erase : 'netatmotoken' });";
        echo "top.location.href = 'index.php';";
        echo "</script>";
        }  
    }
/*    
if(isset($_GET["refresh_token"]) )
    {$_SESSION['refresh_token'] = $_GET["refresh_token"]; 
    if(refreshToken() != -1)
        echo "<script>	top.location.href = 'iconesExt.php';</script>";        
    else        
        {echo "<script>";
        echo "$.jCookies({ erase : 'netatmotoken' });";
        echo "top.location.href = 'index.php';";
        echo "</script>";
        }       
    }
*/    
if(isset($_POST["username"])) // used indexLogin.php
    {$username = $_POST["username"]; 
    $password = $_POST["password"];    
    if(isset($_POST['saveCookie']))
        {echo "<script>";
        echo("var username = \"$username\";\n");
        echo("var password = \"$password\";\n");
        echo("$.jCookies({name:'netatmologin',value:{Username:username,Password:password},days:10});");
        echo "</script>";
        }
    logMsg("login with indexLogin.php");
    }
else if(isset($_GET["username"]))    // username from cookie
    {$username = $_GET["username"]; 
    $password = $_GET["password"]; 
    logMsg("login with cookie password");
    }

if(isset($username))    
    {$client = new NAApiClient(array("client_id" => $client_id, "client_secret" => $client_secret
                                , "username" => $username, "password" => $password,"scope" => NAScopes::SCOPE_READ_STATION));
    try {
        $tokens = $client->getAccessToken();        
        } catch(NAClientException $ex) {
            $_SESSION['msg'] = 'Identifiant ou mot de passe incorrect';
            echo("<script>top.location.href = 'indexLogin.php';</script>");
        }
    $refresh_token = $tokens['refresh_token'];
    $_SESSION['client'] = $client;   
    $_SESSION['timeToken'] = time();	
    $_SESSION['refresh_token'] = $refresh_token;
    $_SESSION['expires_in'] = $tokens['expires_in'];
    saveTokenCookie($refresh_token);
    //if(isset($_POST['saveCookie']))$_SESSION['saveCookie'] = 1;   
    }
    
echo("<script>	top.location.href = 'iconesExt.php';</script>");
//header('location:iconesExt.php');
// avant header('location:xxx'); util si appel ajax ?	
?>

</body>
</html>

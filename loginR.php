<?php
define('__ROOT__', dirname(__FILE__));
require_once (__ROOT__.'/src/Netatmo/autoload.php');
require_once 'Config.php';
require_once 'initClient.php';

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
<h3>Patientez ...</h3>
<?php
    
if(isset($_POST["refresh_token"]) )
    {$_SESSION['refresh_token'] = $_POST["refresh_token"]; 
    if(refreshToken() != -1)
        {$client = $_SESSION['client'];
         login($client,1);
        }
     else        
        {$refresh_token = $_SESSION['refresh_token'];
        echo("erasing bad cookie:$refresh_token");
        echo "<script>";
        echo "$.jCookies({ erase : 'nntoken' });";
        echo "top.location.href = 'index.php';";
        echo "</script>";
        }  
    }
?>

</body>
</html>

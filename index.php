<?php 
/*
Name: Netatmo PHP Graphics
Author: Hubert de Fraysseix
URI: https://github.com/hbonnin/Netatmo

Netatmo PHP Graphics is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License.

Netatmo PHP Graphics is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Netatmo PHP Graphics.  If not, see <http://www.gnu.org/licenses/>.
*/

define('__ROOT__', dirname(__FILE__));
require_once (__ROOT__.'/src/Netatmo/autoload.php');
session_start(); 
require_once 'Config.php';
require_once 'initClient.php';


$_SESSION=array();
$path = dirname($_SERVER['PHP_SELF']).'/';
$_SESSION['path'] = $path;  
$_SESSION['LogMsg'] = "Log started:(GMT)<br>";
$config = array("client_id" => $client_id,
                "client_secret" => $client_secret,
                "scope" => Netatmo\Common\NAScopes::SCOPE_READ_STATION);
$client = new Netatmo\Clients\NAWSApiClient($config);
$_SESSION['client'] = $client;
 ?>
 
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<meta charset='utf-8'>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="apple-touch-icon" href="icone/meteo.png" >
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="js/jcookies.js"></script>
</head>

<body>
<h3>Please wait</h3>
<form name="xxx" method="post" action="loginR.php">
<input type="hidden" name="refresh_token" value="aaa" >
</form> 

<form name="yyy" method="get" action="loginA.php">
<input type="hidden" name="start" value="Start" >
</form> 


<script>
    <?php echo("save_token = $save_token;\n"); ?>
    rutabaga = $.jCookies({get:'nntoken'}); 
    if(rutabaga && save_token)
        {refresh_token  = rutabaga.Refresh_token;
        $('[name=refresh_token]').val(refresh_token);
        document.xxx.submit();
        }
    else
        document.yyy.submit();
</script>


</body>
</html>

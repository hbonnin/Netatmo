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

require_once 'NAApiClient.php';
require_once 'Config.php';

session_start(); 
$_SESSION=array();
$path = dirname($_SERVER['PHP_SELF']).'/';
$_SESSION['path'] = $path;  
 
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
<h1>Please wait</h1>
<form name="xxx" method="post" action="login.php">
<input type="hidden" name="refresh_token" value="aaa" >
</form> 


<script>
    <?php echo("save_token = $save_token;\n"); ?>
    rutabaga = $.jCookies({get:'netatmotoken'}); 
    if(rutabaga && save_token)
        {refresh_token  = rutabaga.Refresh_token;
        $('[name=refresh_token]').val(refresh_token);
        //top.location.href = 'login.php?refresh_token='+refresh_token;
        document.xxx.submit();
        }
</script>


<?php 
    $my_url = "http://" . $_SERVER['SERVER_NAME'] .$path. "iconesExt.php";
    $_SESSION['state'] = md5(uniqid(rand(), TRUE));
    $dialog_url="https://api.netatmo.net/oauth2/authorize?client_id=" 
        . $client_id 
        . "&redirect_uri=" . urlencode($my_url) 
        . "&state=". $_SESSION['state']
        . "&scope=read_station%20read_thermostat%20write_thermostat";
    echo("<script> top.location.href='" . $dialog_url . "'</script>");
            
?>
</body>
</html>

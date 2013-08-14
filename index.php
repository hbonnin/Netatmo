<?php 
require_once 'NAApiClient.php';
require_once 'NAApiClient.php';session_start(); 
$_SESSION=array();
?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<meta charset='utf-8'>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="apple-touch-icon" href="icone/meteo.png" >
<link rel="apple-touch-startup-image" href="icone/startup.png">
</head>
<body>

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

require_once 'Config.php';
    //session_start();
    if(!empty($test_username) && !empty($test_password))
   			echo("
    	   	<script>
    		top.location.href = 'iconesExt.php';
			</script>
			");    	
    else
     	{$my_url = "http://" . $_SERVER['SERVER_NAME'] . "/Netatmo/iconesExt.php";
    	$_SESSION['state'] = md5(uniqid(rand(), TRUE));
    	$dialog_url="https://api.netatmo.net/oauth2/authorize?client_id=" 
    		. $client_id . "&redirect_uri=" . urlencode($my_url) . "&state="
    		. $_SESSION['state'];
    	echo("<script> top.location.href='" . $dialog_url . "'</script>");
    	}   	   	
?>
</body>
</html>

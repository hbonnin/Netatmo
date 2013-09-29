<?php 
session_start();
$_SESSION=array();
require_once 'Config.php';

	$msg = '';	
	if(isset($_SESSION['msg']))
		$msg = $_SESSION['msg'];
	$_SESSION=array();
	session_destroy();
    $path = dirname($_SERVER['PHP_SELF']).'/';
    $_SESSION['path'] = $path;  
	
    if(!empty($test_username) && !empty($test_password))
    	echo("<script>top.location.href = 'iconesExt.php';</script>");

?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<!--
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
-->
<head>
<title>Stations Netatmo</title>
<meta charset='utf-8'>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="apple-touch-icon" href="icone/meteo.png" >
<link rel="apple-touch-startup-image" href="icone/startup.png">
<link rel='icon' href='favicon.ico'>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="js/jcookies.js"></script>
</head>
<body style='text-align:center;'>
<script>
    var rutabaga = $.jCookies({get:'Netatmo Login'}); 
    if(rutabaga)
        {
        username = rutabaga.Username;
        password  = rutabaga.Password;
        //$.post('login.php',{username:username,password:password});
        /*
        $.ajax({type : 'POST', 
                url : 'login.php',
                data : {username:username,password:password},
                success : function(data){alert('success'+data);}
                });
        */    
        top.location.href = 'login.php?username='+username+'&password='+password;
        }    
</script>
<h2> Entrez vos identifiants Netatmo </h2>

<h3 style='color:red;'><?php $msg ?></h3>
<form id='login' action='login.php' method='post' accept-charset='UTF-8'>

<table style='margin-left:auto; margin-right:auto; text-align:right'>
<tr> 
<td>UserName:</td>
<td style='text-align:left'><input type='text' name='username'   maxlength='50' /></td>
</tr><tr>
<td>Password:</td>
<td style='text-align:left'><input type='password' name='password' maxlength='50' /></td>
</tr><tr>
<td>Save:</td>
<td style='text-align:left'><input type='checkbox' name='saveCookie' checked = 'checked' /></td>
</tr>

</table> 
<input type='submit' name='Submit' value='Submit' />
 
</form>
<br><br>
<p style='font-size: 12px;'> Ce logiciel libre, sous license GPL, est disponible sur le site:<br>
<a href='https://github.com/hbonnin/Netatmo'>https://github.com/hbonnin/Netatmo'</a>
</p>
</body>
</html>


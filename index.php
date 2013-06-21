<!DOCTYPE html SYSTEM 'about:legacy-compat'>

<?php 
	session_start();
	$msg = '';	
	if(isset($_SESSION['msg']))
		$msg = $_SESSION['msg'];
	//unset($_SESSION['msg']);	
	//if(isset($_COOKIE[session_name()]))
		//setcookie(session_name(), “”,time()-3600,“/”);
	$_SESSION=array();
	session_destroy();
//phpinfo();
echo("
<!--
Name: Netatmo PHP Graphics
Author: Hubert de Fraysseix
URI: https://github.com/hbonnin/Netatmo

Netatmo PHP Graphics is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License.

Netatmo PHP Widget is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Netatmo PHP Graphics.  If not, see <http://www.gnu.org/licenses/>.
-->


<head>
<title>Stations Netatmo</title>
<meta charset='utf-8'>
</head>
<body style='text-align:center;'>

<h2> Entrez vos identifiants Netatmo </h2>

<h3 style='color:red;'>$msg</h3>
<form id='login' action='login.php' method='post' accept-charset='UTF-8'>

<table style='margin-left:auto; margin-right:auto;'>
<tr> 
<td>UserName:</td>
<td><input type='text' name='username' id='username'  maxlength='50' /></td>
</tr><tr>
<td>Password:</td>
<td><input type='password' name='password' id='password' maxlength='50' /></td>
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
");
?>
<?php
/*
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
*/

require_once 'NAApiClient.php';
require_once 'Config.php';
session_start();

if(isset($_SESSION['client']))// menu called from login or reload
    {$client = $_SESSION['client'];
	//echo("<pre>");print_r($client);echo("</pre");	  
    }
else
	{$client = new NAApiClient(array("client_id" => $client_id, "client_secret" => $client_secret, "username" => $test_username, "password" => $test_password));
	try {
    	$tokens = $client->getAccessToken();       
		} catch(NAClientException $ex) {
    		echo ("Identifiant ou mot de passe incorrect");
		exit(-1);	
		}
	$_SESSION['client'] = $client;	
	}      

$helper = new NAApiHelper();
if(isset($_SESSION['devicelist']))
    $devicelist = $_SESSION['devicelist'];
else
	{try {
		$devicelist = $client->api("devicelist", "POST");
		}
	catch(NAClientException $ex) {
		$ex = stristr(stristr($ex,"Stack trace:",true),"message");
		echo("$ex");
		exit(-1);
		}	
	$devicelist = $helper->SimplifyDeviceList($devicelist);
    $_SESSION['devicelist'] = $devicelist;
    }
//*************************************************************    
if(isset($_SESSION['mesures']))
    $mesures = $_SESSION['mesures'];
else
	{$mesures = $helper->GetLastMeasures($client,$devicelist);
	$_SESSION['mesures'] = $mesures;
	}
//***************************************************************
$num = count($devicelist["devices"]);
date_default_timezone_set("Europe/Paris");
$dateend = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d'),date('y')));
$datebeg = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d')-30,date('y')));
?>


<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<title>Stations Netatmo</title>
<link rel='icon' href='favicon.ico' />
<meta charset='utf-8'>
<script type='text/javascript' src='calendrier.js'></script>
<link rel='stylesheet' media='screen' type='text/css' title='Design' href='calendrier.css' />
<script type='text/javascript' src='validate.js'></script>	

<style type='text/css'>
table	{
	text-align:left;
	}
.date  {color:black;
	width:95px;
	border:1px solid blue;
	font-size:15px;
	}
</style>
 
</head>

<body style='text-align:center;'>
<div style='text-align:left;'>
<a href='http://www.000webhost.com/' target='_blank' ><img src='http://www.000webhost.com/images/80x15_powered.gif' alt='Web Hosting' width='80' height='15'/></a>
</div>
<H1> Stations Netatmo</H1>
	<table style='width:180px; height:30px; margin-left:auto; margin-right:auto;'>
	<caption><b>Dernières mesures</b></caption>
	<tr>
	<td style='text-align:center;'>
	<form method='get' action='icones.php'>
	<input type='submit'>
	</form>	
	</td></tr>
	</table>
	
<table style='border-spacing:5px; margin-left:auto; margin-right:auto;'>
<tr>
<td>
	<!-- ################################ -->
	<form method='post' action='graphiques.php' onsubmit='return valider(this)'>	
	<table  style='border:2px solid grey;'>
	<caption><b>Graphiques d'une station</b></caption>
	<tr>
	<td style='height:25px; width:180px;'>Début des mesures
	</td>
	<td><input class='date' id='id_date0'  hidden disabled  type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);'></td>
	</tr><tr>
	<td style='height:25px;'>Fin des mesures</td>
	<td><input class='date' id='id_date1' type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);'></td>
	</tr><tr>
	<td id='id_duree' style='height:25px;'>Fréquence (durée: 2 jours)
	</td>	
	<td>
	<table><tr><td>
	<select name='select' onChange='Allow(this);'>
		<option value='1week' > 1 semaine </option>
		<option value='1day' > 1 journée </option>
		<option value='3hours' > 3 heures </option>
		<option value='30min'selected='selected'> 30 minutes </option>
		<option value='max' > 5 minutes </option>
	</select>
	</td></tr>
	</table>
	</tr><tr>
	
	<td>Choisir une station
	</td>
	<td>
	<table>

<?php
for($i = 0;$i < $num;$i++)
	{$stat = $mesures[$i]['station_name'];
	$arr = str_split($stat,17);
    $stat = $arr[0];
    if($i == 0)
		echo("<tr><td><input type='radio' name='station' value='$i' checked='checked'> $stat </td></tr>");
	else
		echo("<tr><td><input type='radio' name='station' value='$i'> $stat </td></tr>");		
	}
?>	
	

	</table>
	</td></tr></TABLE>
	<input type='submit'>
	</form>

<td>
	<!-- ################################ -->
	<form method='post' action='compareALL.php' onsubmit='return valider(this)'>
	<table  style='border:2px solid grey;'>
	<caption><b>Comparaison des température extérieures </b></caption>
	<tr>
	<td style='height:25px; width:180px;'>Début des mesures
	</td>
	<td><input class='date' type='text' name='date0' value='<?php echo($datebeg); ?>' onclick='ds_sh(this,0);' />
	</td></tr>
	<tr>
	<td style='height:25px;'>Fin des mesures</td>	
	<td><input class='date' type='text' name='date1' value='<?php echo($dateend); ?>' onclick='ds_sh(this,1);'></td>	
    </tr>
	<tr><td style='height:25px;'>Fréquence</td>
	<td>
	<select name='select'>
		<option value='1week'> 1 semaine</option>
		<option value='1day' selected='selected'> 1 journée </option>
	</select>
	
	</td></tr>
	<tr>
	<td>Choisir des stations
	</td>
	<td>
	<table>

<?php
for($i = 0;$i < $num;$i++)
	{$stat = $mesures[$i]['station_name'];
	$arr = str_split($stat,17);
    $stat = $arr[0];
    if( $i == 0)
		echo("<tr><td><input type='checkbox' name='stats[]' value='$i' checked='checked' > $stat </td></tr>");
	else
		echo("<tr><td><input type='checkbox' name='stats[]' value='$i'  > $stat </td></tr>");
	}
?>	

	</table>
	</td></TABLE>
	<input type='submit'>
	</form>

</td>
</table>

<form method='post' action='index.php'>
<input type='submit' value='logout' style='color: #aa0000; font-size: 14px;'>
</form>



<!-- Invisible table --> 
<table class='ds_box'  id='ds_conclass' style='display: none;'>
	<caption id='id_caption' style='background-color: #ccc; color: #00F; font-family: Arial, Helvetica, sans-serif; font-size: 15px;'>xxxx</caption>
	<tr><td id='ds_calclass'></td></tr>
</table>


<!-- START OF HIT COUNTER CODE -->
<br><script src='http://www.counter160.com/js.js?img=11'></script><br>
<a href='http://www.000webhost.com'>
<img src='http://www.counter160.com/images/11/left.png' alt='Free web hosting' style='border:0px'>
</a>
<a href='http://www.hosting24.com'>
<img alt='Web hosting' src='http://www.counter160.com/images/11/right.png' style='border:0px' >
</a>


<!-- END OF HIT COUNTER CODE -->


</body>
</html>

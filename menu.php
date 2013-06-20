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
/*
require_once('../FirePHPCore/fb.php');
if($_SERVER['HTTP_HOST'] != '127.0.0.1')
	FB::setEnabled(false);
FB::log($_SERVER, "dumping an array");
*/
session_start();

$code = $_GET["code"];

if(!empty($code))
	{$my_url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] ;
    if($_SESSION['state'] && ($_SESSION['state'] == $_GET['state'])) 
    	{$token_url = "https://api.netatmo.net/oauth2/token";
    	$postdata = http_build_query(array(
            							'grant_type' => "authorization_code",
            							'client_id' => $client_id,
            							'client_secret' => $client_secret,
            							'code' => $code,
            							'redirect_uri' => $my_url               
        								));
    	$opts = array('http' => array(
        							'method'  => 'POST',
        							'header'  => 'Content-type: application/x-www-form-urlencoded;charset=UTF-8',
        							'content' => $postdata
    								));
    	$context  = stream_context_create($opts);
    	$response = file_get_contents($token_url, false, $context);
    	$params = null;
    	$params = json_decode($response, true);
		$access_token = $params['access_token'];
		$refresh_token = $params['refresh_token'];
		$client = new NAApiClient(array("access_token" => $access_token,"refresh_token" => $refresh_token)); 
		$_SESSION['client'] = $client;	
		}
	else
		echo("The state does not match.");
	}		
if(isset($_SESSION['tokens'])) // menu called from login
    {$tokens = $_SESSION['tokens'];
	$access_token = $tokens['access_token'];
	$refresh_token = $tokens['refresh_token'];
	$client = new NAApiClient(array("access_token" => $access_token,"refresh_token" => $refresh_token)); 
	$_SESSION['client'] = $client;
    }
	
if(isset($_SESSION['client']))
    $client = $_SESSION['client'];
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
if(isset($_SESSION['mesures']))
    $mesures = $_SESSION['mesures'];
else
	{$mesures = $helper->GetLastMeasures($client,$devicelist);
	$_SESSION['mesures'] = $mesures;
	}
/*
echo("<pre>");
echo "session:" . session_id();
print_r($_COOKIE);
echo("</pre>");  
FB::log($mesures, "array");
*/
$num = count($devicelist["devices"]);
date_default_timezone_set("Europe/Paris");
$dateend = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d'),date('y')));
$datebeg = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d')-30,date('y')));

echo("
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<title>Stations Netatmo</title>
<meta charset='utf-8'>
<script type='text/javascript' src='calendrier.js'></script>
<link rel='stylesheet' media='screen' type='text/css' title='Design' href='calendrier.css' />

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
 
<script type='text/javascript'>
//<![CDATA[

function valider(frm)
	{
	var date0 = frm.elements['date0'].value;
	var tab = frm.elements['select'];
	for (var i = 0;i < tab.length;i++)
		{if(tab[i].selected)
			{var inter = tab[i].value;
			break;
			}
		}
	var saisie = (date0).split('/');
	var date = new Date(eval(saisie[2]),eval(saisie[1])-1,eval(saisie[0]));
	var date1 = frm.elements['date1'].value;
	var saisie1 = (date1).split('/');
	var endday = new Date(eval(saisie1[2]),eval(saisie1[1])-1,eval(saisie1[0]));
	if((endday - date < 24*60*60*1000) && (i < 2))	
		{frm.date1.focus();		
		alert('Date ' + date.getDate() +'/'+ (date.getMonth()+1) +'/'+ date.getFullYear()
		 +' non inférieure à '+ endday.getDate() +'/'+ (endday.getMonth()+1) +'/'+ endday.getFullYear() );
    	return false;
    	}
 	// i=0 1week i=1 1day  i=2 3hours i=3 30minute	i=4 max
	var nmesure = (endday-date)/(24*60*60*1000);
	if(i == 2)nmesure *= 8;
	else if(i == 3){nmesure *= 48;return true;}
	else if(i == 4){nmesure *= 288;return true;}
	else if(i == 0)nmesure /= 7;
	nmesure = Math.floor(nmesure+.5);		  	
    if(nmesure > 1024) 
    	{alert(nmesure + ' > 1024 mesures');
    	return false;
    	}	
    return true;
  }
function Allow(tab) 
  	{for (var i = 0;i < tab.length;i++)
		if(tab[i].selected)break;		
	var el1 = document.getElementById('id_date1');
	var el0 = document.getElementById('id_date0');
	var duree = document.getElementById('id_duree');	
    if(i < 3)
		{duree.innerHTML = 'Fréquence';
		el0.disabled = false;
		el0.hidden = false;
		el1.disabled=false;
		el1.hidden = false;
		}
	else if(i == 3)
		{duree.innerHTML = 'Fréquence (durée: 14 jours)';
		el0.disabled = true;
		el0.hidden = true;
		el1.disabled = false;
		el1.hidden = false;
		}				
	else
		{duree.innerHTML = 'Fréquence (durée: 2 jours)';
		el0.disabled=true;
		el0.hidden=true;
		el0.disabled = true;
		el1.disabled=true;
		el1.hidden = true;
		}									
    return true;		
	}

//]]>
</script>

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
	
<table style='border-spacing:5px; 30px; margin-left:auto; margin-right:auto;'>
<tr>
<td>
	<!-- ################################ -->
	<form method='post' action='graphiques.php' onsubmit='return valider(this)'>	
	<table  style='border:2px solid grey;'>
	<caption><b>Graphiques d'une station</b></caption>
	<tr>
	<td style='height:25px; width:180px;'>Début des mesures
	</td>
	<td><input class='date' id='id_date0'  hidden disabled  type='text' name='date0' value=\"$datebeg\" onclick='ds_sh(this,0);'></td>
	</tr><tr>
	<td style='height:25px;'>Fin des mesures</td>
	<td><input class='date' id='id_date1' hidden disabled  type='text' name='date1' value=\"$dateend\" onclick='ds_sh(this,1);'></td>
	</tr><tr>
	<td id='id_duree' style='height:25px;'>Fréquence (durée: 2 jours)
	</td>	
	<td>
	<table><tr><td>
	<select name='select' onChange='Allow(this);'>
		<option value='1week' > 1 semaine </option>
		<option value='1day' > 1 journée </option>
		<option value='3hours' > 3 heures </option>
		<option value='30min'> 30 minutes </option>
		<option value='max' selected='selected'> 5 minutes </option>
	</select>
	</td></tr>
	</table>
	</tr><tr>
	
	<td>Choisir une station
	</td>
	<td>
	<table>
");

for($i = 0;$i < $num;$i++)
	{$stat = $mesures[$i]['station_name'];
	$arr = str_split($stat,17);
    $stat = $arr[0];
    if($i == 0)
		echo("<tr><td><input type='radio' name='station' value='$i' checked='checked'> $stat </td></tr>");
	else
		echo("<tr><td><input type='radio' name='station' value='$i'> $stat </td></tr>");		
	}
	
	
echo("
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
	<td><input class='date' type='text' name='date0' value=\"$datebeg\" onclick='ds_sh(this,0);' />
	</td></tr>
	<tr>
	<td style='height:25px;'>Fin des mesures</td>
	<td><input class='date' type='text' name='date1' value=\"$dateend\" onclick='ds_sh(this,1);'></td>
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
");

for($i = 0;$i < $num;$i++)
	{$stat = $mesures[$i]['station_name'];
	$arr = str_split($stat,17);
    $stat = $arr[0];
    if( $i == 0)
		echo("<tr><td><input type='checkbox' name='stats[]' value='$i' checked='checked' > $stat </td></tr>");
	else
		echo("<tr><td><input type='checkbox' name='stats[]' value='$i'  > $stat </td></tr>");
	}
	
echo("
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
");
?>
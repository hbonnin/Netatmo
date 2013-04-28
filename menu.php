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
if(isset($_SESSION['client']))
    $client = $_SESSION['client'];

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
    
    
$num = count($devicelist["devices"]);
date_default_timezone_set("Europe/Paris");
$dateend = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d'),date('y')));
$datebeg = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d')-30,date('y')));



echo("
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>

<html xmlns='http://www.w3.org/1999/xhtml'>

<head>
<title>Staions Netatmo</title>
<meta http-equiv='Content-Type' content='text/html; charset=utf8' />
<script type='text/javascript' src='calendrier.js'></script>
<link rel='stylesheet' media='screen' type='text/css' title='Design' href='calendrier.css' />
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
	if(endday - date < 24*60*60*1000)	
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
    if(i < 3)
		{el0.style.color = 'black';
		el0.disabled = false;
		el1.style.color = 'black';
		el1.disabled=false;
		}
	else if(i == 3)
		{el0.style.color = 'red';
		el0.disabled = true;
		el1.style.color = 'black';
		el1.disabled = false;
		}				
	else
		{el0.style.color = 'red';
		el0.disabled=true;
		el1.style.color = 'red';
		el1.disabled=true;
		}					
    return true;		
	}

//]]>
</script>

</head>

<body>

<a href='http://www.000webhost.com/' target='_blank'><img src='http://www.000webhost.com/images/80x15_powered.gif' alt='Web Hosting' width='80' height='15' border='0' /></a>

<center>
<H1> Stations Netatmo</H1>
	<table style='width:180px; height:30px;'>
	<caption><b>Dernières mesures</b></caption>
	<tr>
	<td style='text-align:center;'>
	<form method='get' action='icones.php'>
	<input type='submit'>
	</form>	
	</td></tr>
	</table>
	


<table style='border-spacing:5px 30px;'>
<tr>
<td>
	<!-- ################################ -->
	<form method='post' action='graphiques.php' onsubmit='return valider(this)'>	
<!--
	<form method='post' action='lastDays.php' onsubmit='return valider(this)'>	
-->	
	
	<!--<TABLE  style='width:420px; height:215px; border:2px solid grey;'>-->
	<TABLE  style='border:2px solid grey;'>
	<caption><b>Graphiques d'une station</b></caption>
	<TR>
	<TD style='height:25px; width:200px;'>Début des mesures
	</TD>
	<TD><input id='id_date0' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date0' value=\"$datebeg\" onclick='ds_sh(this,0);'></TD>
	</TR><TR>
	<TD style='height:25px;'>Fin des mesures</TD>
	<TD><input id='id_date1' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date1' value=\"$dateend\" onclick='ds_sh(this,1);'></TD>
	</TR><TR>
	<TD>Intervalle des mesures
	</TD>
	<td><table>
	<select name='select' onChange='Allow(this);'>
		<option value='1week' > 1 semaine </option>
		<option value='1day' selected='selected'> 1 journée </option>
		<option value='3hours' > 3 heures </option>
		<option value='30min' > 30 minutes </option>
		<option value='max' > max </option>
	</select>
	</td></tr>

	</table>
	</TR><TR>
	<TD>Choisir une station
	</TD>
	<TD>
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
	</TD></TR></TABLE>
	<input type='submit'>
	</form>

<td>
	<!-- ################################ -->
	<form method='post' action='compareALL.php' onsubmit='return valider(this)'>
	<TABLE  style='border:2px solid grey;'>
	<caption><b>Comparaison des température extérieures </b></caption>
	<TR>
	<TD style='height:25px; width:200px;'>Début des mesures
	</TD>
	<TD><input id='id_date' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date0' value=\"$datebeg\" onclick='ds_sh(this,0);' />
	</TD></TR>
	<tr>
	<TD style='height:25px;'>Fin des mesures</TD>
	<TD><input id='id_date' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date1' value=\"$dateend\" onclick='ds_sh(this,1);'></TD>
    </tr>
	<TR><td>Intervalle des mesures</td>
	<td>
	<select name='select'>
		<option value='1week'> 1 semaine</option>
		<option value='1day' selected='selected'> 1 journée </option>
	<select>
	
	</td></TR>
	<TR>
	<TD>Choisir une station
	</TD>
	<TD>
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
	</TD></TABLE>
	<input type='submit'>
	</form>

</td>
</table>


<!-- Invisible table --> 
<table class='ds_box' cellpadding='0' cellspacing='0' id='ds_conclass' style='display: none;'>
	<caption id='id_caption'style='background-color: #ccc; color: #00F; font-family: Arial, Helvetica, sans-serif; font-size: 15px;'>xxxx</caption>
	<tr><td id='ds_calclass'></td></tr>
</table>


<!-- START OF HIT COUNTER CODE -->
<br><script language='JavaScript' src='http://www.counter160.com/js.js?img=11'></script><br>
<a href='http://www.000webhost.com'><img src='http://www.counter160.com/images/11/left.png' alt='Free web hosting' border='0' align='texttop'></a>
<a href='http://www.hosting24.com'><img alt='Web hosting' src='http://www.counter160.com/images/11/right.png' border='0' align='texttop'></a>
<!-- END OF HIT COUNTER CODE -->

</center>
</body>

</html>
");
?>
<?php

require_once 'NAApiClient.php';
require_once 'Config.php';

$client = new NAApiClient(array("client_id" => $client_id, "client_secret" => $client_secret, "username" => $test_username, "password" => $test_password));
$helper = new NAApiHelper();

try {
    $tokens = $client->getAccessToken();        
	} catch(NAClientException $ex) {
    	echo "An error happend while trying to retrieve your tokens\n";
    	exit(-1);
	}


try {
$devicelist = $client->api("devicelist", "POST");
	}
	catch(NAClientException $ex) {
		$ex = stristr(stristr($ex,"Stack trace:",true),"message");
		echo("$ex");
		exit(-1);
	}

$devicelist = $helper->SimplifyDeviceList($devicelist);
$mesures = $helper->GetLastMeasures($client,$devicelist);
$num = count($devicelist["devices"]);


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
	var saisie = (date0).split('/');
	var date = new Date(eval(saisie[2]),eval(saisie[1])-1,eval(saisie[0]));
	var today = new Date();
	if(today - date <= 24*60*60*1000)	
		{frm.date0.focus();		
		alert('Date ' + date.getDate() +'/'+ (date.getMonth()+1) +'/'+ date.getFullYear()
		 +' non inférieure à '+ today.getDate() +'/'+ (today.getMonth()+1) +'/'+ today.getFullYear() );
    	return false;
    	}
    else
    	return true;
  }

//]]>
</script>

</head>

<body>

<a href='http://www.000webhost.com/' target='_blank'><img src='http://www.000webhost.com/images/80x15_powered.gif' alt='Web Hosting' width='80' height='15' border='0' /></a>

<center>
<H1> Stations Netatmo</H1>

<!--<h4><a href='google.php'>Carte des Stations</a></h4>-->

<table style='border-spacing:5px 30px;'>
<tr><td>
	<!-- ################################ -->
	<!--<form method='get' action='lastALL.php'>-->
	<TABLE style='width:300px; height:150px; border:2px solid grey;'>
	<caption><b>Dernières mesures</b></caption>
	<TR><TD HEIGHT=25 >
	<form method='get' action='google.php'>
	<input type='submit' value='Sur une carte'>	
	</form>
	</td><td>
	<form method='get' action='lastALL.php'>
	<input type='submit' value='Mode texte' style='float:right;'>	
	</form>
	
	</TD>
	</TABLE>
	
	
<td>
	<!-- ################################ -->
	<form method='get' action='lastDays.php' onsubmit='return valider(this)'>
	
	<TABLE  style='width:300px; border:2px solid grey;'>
	<caption><b>Dernière semaine</b></caption>
	<TR>
	<TD
	</TD>
	<TD>
	</TD>
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
	</form>");
	

	
	
	
echo("		
	</tr> <td>
	<!-- ################################ -->	
<!--	
	<form method='get' action='compareALL.php' onsubmit='return valider(this)''>
	<TABLE style='width:300px; border:2px solid grey;'>
	
	<caption><b>Températures extérieures</b></caption>
	<tr>
	<td style='height:25px; width:130px;'>Début des mesures</td>	
	<TD><input id='id_date' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date0' value='15/12/2012' onclick='ds_sh(this);' />	
	</td>
	</tr>
	</table>
	<input type='submit'>	
	</form>	
-->
</td></tr>
<tr><td>
	<!-- ################################ -->
	<form method='get' action='station.php' onsubmit='return valider(this)'>
	<TABLE style='width:300px;  border:2px solid grey;'>
	<caption><b>Graphiques d'une station</b></caption>
	<TR>
	<TD style='height:25px; width:130px;'>Début des mesures
	</TD>
	<TD><input id='id_date' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date0' value='15/12/2012' onclick='ds_sh(this);' />
	</TD>
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
	</TD></TR>		
	</TABLE>
	<input type='submit'>
	</form>	
</td><td>
	<!-- ################################ -->
	<!--<form method='get' action='lastDays.php' onsubmit='return valider(this)'>-->
	<form method='get' action='minmaxStation.php' onsubmit='return valider(this)'>
	
	<TABLE  style='width:300px; border:2px solid grey;'>
	<caption><b>Température extrêmes d'une station</b></caption>
	<TR>
	<TD style='height:25px; width:130px;'>Début des mesures
	</TD>
	<TD><input id='id_date' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date0' value='15/12/2012' onclick='ds_sh(this);' />
	</TD>
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
	


</td><td>
	<!-- ################################ -->
	<form method='post' action='compareALL.php' onsubmit='return valider(this)'>
	<TABLE  style='width:300px; border:2px solid grey;'>
	<caption><b>Température extérieures </b></caption>
	<TR>
	<TD style='height:25px; width:130px;'>Début des mesures
	</TD>
	<TD><input id='id_date' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date0' value='15/12/2012' onclick='ds_sh(this);' />
	</TD>
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
	echo("<tr><td><input type='checkbox' name='stats[]' value='$i' checked='checked' > $stat </td></tr>");
	}
echo("
	</table>
		</TD></TR></TABLE>
		<input type='submit'>
	</form>

</td></tr>
</table>


<!-- Invisible table --> 
<table class='ds_box' cellpadding='0' cellspacing='0' id='ds_conclass' style='display: none;'>
	<caption>Date de début des mesures</caption>
	<tr><td id='ds_calclass'></td></tr>
</table>

<!-- START OF HIT COUNTER CODE -->
<br><script language='JavaScript' src='http://www.counter160.com/js.js?img=11'></script><br><a href='http://www.000webhost.com'><img src='http://www.counter160.com/images/11/left.png' alt='Free web hosting' border='0' align='texttop'></a><a href='http://www.hosting24.com'><img alt='Web hosting' src='http://www.counter160.com/images/11/right.png' border='0' align='texttop'></a>
<!-- END OF HIT COUNTER CODE -->

</center>
</body>

</html>
");
?>
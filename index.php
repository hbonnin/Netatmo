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
	var saisie = (date0).split('/');
	var date = new Date(eval(saisie[2]),eval(saisie[1])-1,eval(saisie[0]));
	var date1 = frm.elements['date1'].value;
	var saisie1 = (date1).split('/');
	var endday = new Date(eval(saisie1[2]),eval(saisie1[1])-1,eval(saisie1[0]));	
	//var today = new Date();
	if(endday - date <= 24*60*60*1000)	
		{frm.date0.focus();		
		alert('Date ' + date.getDate() +'/'+ (date.getMonth()+1) +'/'+ date.getFullYear()
		 +' non inférieure à '+ endday.getDate() +'/'+ (endday.getMonth()+1) +'/'+ endday.getFullYear() );
    	return false;
    	}
    else
    	return true;
  }
function valider2(frm)
	{
	var date0 = frm.elements['date0'].value;
	var tab = frm.elements['interval'];
	for (var i = 0;i < tab.length;i++)
		{if(tab[i].checked)
			{var inter = tab[i].value;
			break;
			}
		}
	// i=0 1day  i=1 3hours	
	var limit = 1024;
	if(i == 1)limit = limit/8;		
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
    if(endday - date > limit*24*60*60*1000) 
    	{alert('plus de 1024 mesures');
    	return false;
    	}	
    //frm.date1.focus();	
    //alert(i +'  '+ inter + ' '+ (endday - date)/(24*60*60*1000));
    return true;
  }

//]]>
</script>

</head>

<body>

<a href='http://www.000webhost.com/' target='_blank'><img src='http://www.000webhost.com/images/80x15_powered.gif' alt='Web Hosting' width='80' height='15' border='0' /></a>

<center>
<H1> Stations Netatmo</H1>
	<TABLE style='width:180px; height:30px; '>
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
	


<table style='border-spacing:5px 30px;'>
<tr>
<td>
	<!-- ################################ -->
	<form method='post' action='graphiques.php' onsubmit='return valider2(this)'>	
	<!--<TABLE  style='width:420px; height:215px; border:2px solid grey;'>-->
	<TABLE  style='width:420px; height:215px; border:2px solid grey;'>
	<caption><b>Graphiques d'une station</b></caption>
	<TR>
	<TD style='height:25px; width:200px;'>Début des mesures
	</TD>
	<TD><input id='id_date' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date0' value=\"$datebeg\" onclick='ds_sh(this);'></TD>
	</TR><TR>
	<TD style='height:25px;'>Fin des mesures</TD>
	<TD><input id='id_date' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date1' value=\"$dateend\" onclick='ds_sh(this);'></TD>
	</TR><TR>
	<TD>Intervalle des mesures
	</TD>
	<td><table>
	<tr><td><input type='radio' name='interval' value='1day' checked='checked'> 1 journée</td>
	<td><input type='radio' name='interval' value='3hours'> 3 heures	</td></tr>
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
	<TD><input id='id_date' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date0' value=\"$datebeg\" onclick='ds_sh(this);' />
	</TD></TR>
	<tr>
	<TD style='height:25px;'>Fin des mesures</TD>
	<TD><input id='id_date' style='width: 95px; height: 19px; border:1px solid blue; font-size:15px;'type='text' name='date1' value=\"$dateend\" onclick='ds_sh(this);'></TD>
    </tr>
	<TR><td>&nbsp; </td></TR>
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
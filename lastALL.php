<?php

require_once 'NAApiClient.php';
require_once 'Config.php';
require_once 'Geolocalize.php';


$client = new NAApiClient(array("client_id" => $client_id, "client_secret" => $client_secret, "username" => $test_username, "password" => $test_password));
$helper = new NAApiHelper();

try {
    $tokens = $client->getAccessToken();        
    
} catch(NAClientException $ex) {
    echo "An error happend while trying to retrieve your tokens\n";
    exit(-1);
}
 
$devicelist = $client->api("devicelist", "POST");
$devicelist = $helper->SimplifyDeviceList($devicelist);
$numStations = count($devicelist["devices"]);
$mesures = $helper->GetLastMeasures($client,$devicelist);

echo('<!doctype html  PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
 <html>
  <meta charset="UTF-8"/>
  <head><title>Netatmo</title>
 <style type="text/css">

		body {
			background:#fff;		
			padding: 10px;
			color: #333;
			font-family: Arial;
			font-size: 27px;
		}
		table {
			background:#fff;
			border: 10;
			margin: 0;
			padding: 2;
		}

	</style> 
  </head>
<body><center>');

date_default_timezone_set("Europe/Paris");



echo("<table>");
	echo("<TR>\n");
	for($i = 0; $i < $numStations; $i++) 
	{$res = $mesures[$i]["modules"];
	if(($i > 1) && ($i %2  == 0))echo("</TR><TD HEIGHT=20></TD><TR>\n");

	if($i % 2 == 0)
		{$lng=$devicelist["devices"][$i]["place"]["location"][0];
		$lat=$devicelist["devices"][$i]["place"]["location"][1];	
		$places = geolocalize($lat,$lng);	
    	$city = $places[1];
    	$street = $places[0];
    	$alt = $devicelist["devices"][$i]["place"]["altitude"];
		echo("</TR><TR><TD HEIGHT=40 align=center><b><font size=4>$city &nbsp</font></b>($alt m)<br><font size=2>$street</font></TD>\n");				
		if($i < $numStations -1)
			{$lng=$devicelist["devices"][$i+1]["place"]["location"][0];
			$lat=$devicelist["devices"][$i+1]["place"]["location"][1];
			$places = geolocalize($lat,$lng);	
    		$city = $places[1];
    		$street = $places[0];
    		$alt = $devicelist["devices"][$i+1]["place"]["altitude"];
			echo("<TD HEIGHT=40 align=center><b><font size=4>$city &nbsp</font></b>($alt m)<br><font size=2>$street</font></TD>\n");
			}
		echo("</TR><TR>\n");
		}

	echo("<TD>\n");
	$dat=date('d/m/Y H:i',$res[0]['time']);
	echo("<TABLE WIDTH=400 style='border-spacing:5px 3px;'>\n");
		echo("<caption><i><font size=2>{$res[0]['module_name']}:  $dat </font>  </i></caption>\n");
		echo("<TR><TD width=200>Température</TD> <td></td><TD ALIGN='right'>{$res[0]['Temperature']} </TD><TD ALIGN='left'>°C</TD></TR>\n");
		echo("<TR><TD>Humidité</TD> <td></td> <TD ALIGN='right'>{$res[0]['Humidity']} </TD><TD>%</TD></TR>\n");
		echo("<TR><TD>Pression</TD> <td></td> <TD ALIGN='right'>{$res[0]['Pressure']} </TD><TD>mb</TD></TR>\n");
		echo("<TR><TD>CO2</TD>  <td></td><TD ALIGN='right'>{$res[0]['CO2']} </TD><TD>ppm</TD></TR>\n");
		echo("<TR><TD>Son</TD> <td></td> <TD ALIGN='right'>{$res[0]['Noise']} </TD><TD>db</TD></TR>\n");
	echo("</TABLE>\n");

	$dat=date('d/m/Y H:i',$res[1]['time']);
	echo("<TABLE WIDTH=400 style='border-spacing:5px 3px;'>\n");
		echo("<caption><i><font size=2>{$res[1]['module_name']}:  $dat  </font> </i></caption>\n");
		echo("<TR><TD width=200>Température</TD> <td></td> <TD ALIGN='right'>{$res[1]['Temperature']} </TD><TD>°C</TD></TR>\n");
		echo("<TR><TD>Humidité</TD> <td></td> <TD ALIGN='right'>{$res[1]['Humidity']} </TD><TD>%</TD></TR>\n");
	echo("</TABLE>\n");

	echo("</TD>\n");
	} 
	echo("</TR>\n");
echo("</table>\n");
echo("</center></body></html>");
?>

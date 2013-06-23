<?php
require_once 'fill.php';
require_once 'NAApiClient.php';
require_once 'Config.php';
require_once 'Geolocalize.php';

session_set_cookie_params(1200); 
session_start();
date_default_timezone_set("Europe/Paris");

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
		echo("erreur:$ex");
		exit(-1);
		}	
	$devicelist = $helper->SimplifyDeviceList($devicelist);
    $_SESSION['devicelist'] = $devicelist;
    }
    
if(isset($_GET["action"]) && $_GET["action"]  == 'refresh')
	{$mesures = $helper->GetLastMeasures($client,$devicelist);
	$_SESSION['mesures'] = $mesures;	
	}   
else if(isset($_SESSION['mesures']))
    $mesures = $_SESSION['mesures'];
else
	{$mesures = $helper->GetLastMeasures($client,$devicelist);
	$_SESSION['mesures'] = $mesures;
	}
       
$numStations = count($devicelist["devices"]);
$latitude = array($numStations);
$latitude = array($numStations);
$alt = array($numStations);
$slabel = array($numStations);
for($i = 0;$i < $numStations;$i++)
	{$latitude[$i] = $devicelist["devices"][$i]["place"]["location"][1];
    $longitude[$i] = $devicelist["devices"][$i]["place"]["location"][0];
    $res = $mesures[$i]["modules"];
    $alt[$i] = $devicelist["devices"][$i]["place"]["altitude"];
    $places = geolocalize($latitude[$i],$longitude[$i]);
    $int_name = $devicelist["devices"][$i]["module_name"];
	$ext_name = $devicelist["devices"][$i]["modules"][0]["module_name"];

    $txtEXT = sprintf("<font size=2>$ext_name :</font> %3.1f°  %d%%  %dmb",$res[1]['Temperature'],$res[1]['Humidity'],$res[0]['Pressure']);
	$txtINT = sprintf("<font size=2>$int_name:</font> %3.1f°  %d%%  %dppm  %ddb",$res[0]['Temperature'],$res[0]['Humidity']
			,$res[0]['CO2'],$res[0]['Noise']);
	if($places == "BAD")		
    	$p = '<b>' . $mesures[$i]['station_name'] . ' (' . $alt[$i] . 'm)' . '</b><br>';
	else
    	$p = '<b>' . $places[1] . '</b><br><font size=2>' . $places[0] . '</font>'; 
    	
    $label[$i] = $p	. '<br><ul style=\"text-align:left; font-size:11px; list-style-type:none;\"><li>' . $txtINT . '</li><li>' . $txtEXT .'</li>';
    	
	$nModule = count($res);
  	for($j = 2; $j < $nModule ; $j++)
  		{$name = $res[$j]["module_name"];
  		$temp = $res[$j]["Temperature"];
  		$hum = $res[$j]["Humidity"];
  		$co2 = $res[$j]["CO2"];		
  		$text = '<li><font size=2>' . $name. ':</font> ' . $temp .'° ' .$hum. ' % ' .$co2. ' ppm</li>';
  		$label[$i] = $label[$i] . $text;
  		}
  	$label[$i] = $label[$i] . '</ul>';

    $slabel[$i] = $res[1]['Temperature'] . '°';	      	  
	}	

echo("
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<title>Stations Netatmo</title>
<meta charset='utf-8'>
<link rel='icon' href='favicon.ico' />
<link type='text/css' rel='stylesheet'  href='style.css'>
    <script type='text/javascript'
");    
	if($use_google_key == 1)
		echo("src='https://maps.googleapis.com/maps/api/js?libraries=weather,places?key=$google_key&amp;sensor=false'>");
	else
		echo("src='https://maps.googleapis.com/maps/api/js?libraries=weather,places&amp;sensor=false'>");
			
echo("
    </script>
    <script type='text/javascript' src='StyledMarker.js'></script>
    <script type='text/javascript'>
    var cloudLayer;
    var map;
    var show = 1;
    
	function createMarker(pos,label,slabel,map) 
	    {var marker = new StyledMarker({styleIcon:new StyledIcon(StyledIconTypes.BUBBLE,{color:'00ff00',text:slabel}),position:pos,map:map});
		//var marker = new google.maps.Marker({'position':pos ,'map':map });
		marker.setZIndex(103);
		var infowindow = new google.maps.InfoWindow({'content'  : label});
	   	google.maps.event.addListener(marker, 'click', function() 
       		{marker.setZIndex(marker.getZIndex()-1);
       		});  
       google.maps.event.addListener(marker, 'mouseover', function(){infowindow.open(map, marker);});
       google.maps.event.addListener(marker, 'mouseout', function(){infowindow.close(map, marker);});         	 
    	return marker;  
		}

      function initialize() {
  		var markers = [];
  		var lat = [];
  		var lng = [];
  		var LatLng = [];
  		var label = [];
  		var slabel = [];
");
  		echo("var num = $numStations;\n");
  		for($i = 0;$i < $numStations;$i++)
  			{echo("lat[$i] = $latitude[$i];\n");
  			echo("lng[$i] = $longitude[$i];\n");
  			echo("label[$i] = \"$label[$i]\";\n");
  			echo("slabel[$i] = \"$slabel[$i]\";\n");  			
  			}
echo("  				
  		for(i=0;i < num;i++)
  			LatLng[i] = new google.maps.LatLng(lat[i],lng[i]);
  					
    	var center = new google.maps.LatLngBounds(LatLng[0]);
  		for(i=1;i < num;i++)
    		center.extend(LatLng[i]);

       var mapOptions = {
          zoom: 5,
          center: center.getCenter(),
          mapTypeId: google.maps.MapTypeId.HYBRID
        };
        
        map = new google.maps.Map(document.getElementById('map_canvas'),mapOptions);
  		//map.fitBounds(center);		  		
    	 	
		for(i=0 ; i < num;i++)
			markers[i] = createMarker(LatLng[i],label[i],slabel[i],map);

		cloudLayer = new google.maps.weather.CloudLayer();
		cloudLayer.setMap(map);
/*		
	var weatherLayer = new google.maps.weather.WeatherLayer({temperatureUnits: google.maps.weather.TemperatureUnit.CELSIUS});
	google.maps.event.addListener(weatherLayer, 'click', function(e) {
  alert('The current temperature at ' + e.featureDetails.location + ' is '
        + e.featureDetails.current.temperature + ' degrees.');
		});
*/		
	}
	function showHideCloud()
		{if(show)
			{cloudLayer.setMap(null);show = 0;}
		else
			{cloudLayer.setMap(map);show = 1;}	
		}

    </script>
    </head>
  
  <body style='text-align:center;' onload='initialize()'>
  <!--<body>-->
  <!--<div  style='width: 100%; height: 20%;' >--> 

	
<table style='margin-left:auto; margin-right:auto; margin-top:0px;'>
<tr>
");

// calcul des minimax
$date_end = time();
$date_beg = $date_end - (24 * 60 * 60);
$tmins =  array($numStations);
$tmaxs =  array($numStations);
for($i = 0;$i < $numStations;$i++)
	{$device_id = $devicelist["devices"][$i]["_id"];
	$module_id = $devicelist["devices"][$i]["modules"][0]["_id"];
	$params = array("scale" => "1day"
    	, "type" => "min_temp,max_temp"
    	, "date_begin" => $date_beg
    	, "date_end" => $date_end
    	, "optimize" => true
    	, "device_id" => $device_id
    	, "module_id" => $module_id);
    $tmesure = $client->api("getmeasure", "POST", $params);	
    //if(count($tmesure[0]['value'][0]))
    if(count($tmesure))
    	{$tmins[$i] = $tmesure[0]['value'][0][0];   
    	$tmaxs[$i] = $tmesure[0]['value'][0][1];
    	}
    else
       $tmins[$i] = $tmaxs[$i] = '-'; 
    }
 
// Tracé des icones    
for($i = 0;$i < $numStations;$i++)
	{$res = $mesures[$i]["modules"];
	echo("<td>");
	fill($devicelist["devices"][$i],$alt[$i],$res,$tmins[$i],$tmaxs[$i]);
	echo("</td>");
	}
echo("</tr></table>
	<input type=\"button\" style=\"color:#030; background-color: #cceeff;\" value=\"Refresh\" onclick=\"window.location='icones.php?action=refresh';\">		
  	<div style='width: 50%; height:5px;'> </div>
	<div style='width: 640px; height: 20px; position: relative; margin-left:auto; margin-right:auto;'> 
	<i>Déplacer la souris sur un marqueur pour voir les informations &nbsp;&nbsp;</i>
 	</div>		
  	<div id='map_canvas' style='width: 50%; height:385px; border:solid 3px black; margin-left:auto; margin-right:auto;'> </div>
  	<div style='width: 50%; height:5px;'> </div>
	<input type=\"button\" style=\"color:#000000; background-color: #ffffff;\" value=\"Back\" onclick=\"window.location='menu.php';\">		
 	<input type=\"button\" style=\"color:#030;  background-color: #eeaa00;\" value=\"NUAGES\" onclick=\"showHideCloud();\">	


<!-- START OF HIT COUNTER CODE -->
<!--
<script src='http://www.counter160.com/js.js?img=11'></script><br>
<a href='http://www.000webhost.com'>
<img src='http://www.counter160.com/images/11/left.png' alt='Free web hosting' style='border:0px'>
</a>
<a href='http://www.hosting24.com'>
<img alt='Web hosting' src='http://www.counter160.com/images/11/right.png' style='border:0px' >
</a>
-->
<!-- END OF HIT COUNTER CODE -->


</body>
</html>
");

?>
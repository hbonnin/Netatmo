<?php
require_once 'fill.php';
require_once 'NAApiClient.php';
require_once 'Config.php';
require_once 'Geolocalize.php';

date_default_timezone_set("Europe/Paris");
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
   
    
$numStations = count($devicelist["devices"]);
$latitude = array($numStations);
$longitude  = array($numStations);
$label = array($numStations);
$slabel = array($numStations);
for($i = 0;$i < $numStations;$i++)
	{$latitude[$i] = $devicelist["devices"][$i]["place"]["location"][1];
    $longitude[$i] = $devicelist["devices"][$i]["place"]["location"][0];
    $res = $mesures[$i]["modules"];
    $alt = $devicelist["devices"][$i]["place"]["altitude"];
    $name = $mesures[$i]['station_name'] . ' (' . $alt . 'm)';
    $places = geolocalize($latitude[$i],$longitude[$i]);
    $txtEXT = sprintf("<font size=2>Ext:</font> %3.1f°  %d%%  %dmb",$res[1]['Temperature'],$res[1]['Humidity'],$res[0]['Pressure']);
	$txtINT = sprintf("<font size=2>Int:</font> %3.1f°  %d%%  %dppm  %ddb",$res[0]['Temperature'],$res[0]['Humidity']
			,$res[0]['CO2'],$res[0]['Noise']);
	if($places == "BAD")		
    	$label[$i] = '<b>' . $name . '</b><br>' . $txtINT . '<br>' . $txtEXT ;
	else
    	$label[$i] = '<b>' . $places[1] . '</b><br><font size=2>' . $places[0] . '</font><br><br>' . $txtINT . '<br>' . $txtEXT;
    $slabel[$i] = $res[1]['Temperature'] . '°';	      	  
	}	

echo("
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 
'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<title>Staions Netatmo</title>
<meta http-equiv='Content-Type' content='text/html; charset=utf8' />
<link type='text/css' rel='stylesheet'  href='style.css'>
    <script type='text/javascript'
");    
	if($use_google_key ==1)
		echo("src='https://maps.googleapis.com/maps/api/js?key=$google_key&sensor=false'>");
	else
		echo("src='https://maps.googleapis.com/maps/api/js?&sensor=false'>");
echo("
    </script>
    <script type='text/javascript' src='StyledMarker.js'></script>
    <script type='text/javascript'>
    
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
          zoom: 10,
          center: center.getCenter(),
          mapTypeId: google.maps.MapTypeId.HYBRID
        };
        
        var map = new google.maps.Map(document.getElementById('map_canvas'),mapOptions);
  		map.fitBounds(center)		  		
    	 	
		for(i=0 ; i < num;i++)
			markers[i] = createMarker(LatLng[i],label[i],slabel[i],map)

	}

    </script>
    </head>
  
  <body onload='initialize()'>
  <!--<body>-->
  <!--<div  style='width: 100%; height: 20%;' >--> 
  	
<center>
<table>
<tr>
");

for($i = 0;$i < $numStations;$i++)
	{$res = $mesures[$i]["modules"];
	$station = $mesures[$i]['station_name'];
	echo("<td>");
	fill($station,$res);
	echo("</td>");
	}
echo("</tr></table>
	
  <div style='width: 640px; height: 30px; position: relative;'> </div>
  <div style='width: 640px; height: 20px; position: relative;'> 
  <i>Déplacer la souris sur un marqueur pour voir les informations</i>
  </div>	
  
  <div id='map_canvas' style='width: 50%; height:400px; border:solid 3px black;'> 
  	</div>
<!-- START OF HIT COUNTER CODE -->
<br><script language='JavaScript' src='http://www.counter160.com/js.js?img=11'></script><br><a href='http://www.000webhost.com'><img src='http://www.counter160.com/images/11/left.png' alt='Free web hosting' border='0' align='texttop'></a><a href='http://www.hosting24.com'><img alt='Web hosting' src='http://www.counter160.com/images/11/right.png' border='0' align='texttop'></a>
<!-- END OF HIT COUNTER CODE -->
</center>
</body>
</html>
");

?>
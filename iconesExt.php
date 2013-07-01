<?php
require_once 'NAApiClient.php';
require_once 'Config.php';
require_once 'initClient.php';
require_once 'Geolocalize.php';
require_once 'fill.php';
require_once 'menus.php';

session_start();
date_default_timezone_set("Europe/Paris");
// reload page => recalculer $mesures
if(isset($_SESSION['mesures']))unset($_SESSION['mesures']);
initClient();
$client = $_SESSION['client'];
$devicelist = $_SESSION['devicelist'];
$mesures = $_SESSION['mesures'];
	
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

?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<title>Stations Netatmo</title>
<meta charset='utf-8'>
<link rel='icon' href='favicon.ico' />
<link type='text/css' rel='stylesheet'  href='style.css'/>
<script type='text/javascript' src='validate.js'></script>	
<link rel='stylesheet' media='screen' type='text/css' title='Design' href='calendrierBleu.css' />
<script type='text/javascript'
<?php   
	if($use_google_key == 1)
		echo("src='https://maps.googleapis.com/maps/api/js?libraries=weather,places?key=$google_key&amp;sensor=false'>");
	else
		echo("src='https://maps.googleapis.com/maps/api/js?libraries=weather,places&amp;sensor=false'>");
?>
</script>
<script type='text/javascript' src='StyledMarker.js'></script>
<script type='text/javascript'>
    var cloudLayer;
    var map;
    var show = 1;
    var showMarker = 1;
    var controlText;
    
	function createMarker(pos,label,slabel,map) 
	    {var marker = new StyledMarker({styleIcon:new StyledIcon(StyledIconTypes.BUBBLE,{color:'00ff00',text:slabel}),position:pos,map:map});
		//var marker = new google.maps.Marker({'position':pos ,'map':map });
		marker.setZIndex(1);
		var infowindow = new google.maps.InfoWindow({'content'  : label});
	   	google.maps.event.addListener(marker, 'rightclick', function() 
       		{//marker.setZIndex(marker.getZIndex()-1);
       		marker.setVisible(false);
       		controlText.innerHTML = 'Show Markers';showMarker =0;
       		});  
       google.maps.event.addListener(marker, 'mouseover', function(){infowindow.open(map, marker);});
       google.maps.event.addListener(marker, 'mouseout', function(){infowindow.close(map, marker);}); 
       google.maps.event.addListener(marker, 'click', function()
       		{map.setCenter(marker.getPosition());
  			map.setZoom(12);
       		}); 
 
    	return marker;  
		}

      function initialize() {
  		var markers = [];
  		var lat = [];
  		var lng = [];
  		var LatLng = [];
  		var label = [];
  		var slabel = [];
<?php
	echo("var num = $numStations;\n");
  	for($i = 0;$i < $numStations;$i++)
  		{echo("lat[$i] = $latitude[$i];\n");
  		echo("lng[$i] = $longitude[$i];\n");
  		echo("label[$i] = \"$label[$i]\";\n");
  		echo("slabel[$i] = \"$slabel[$i]\";\n");  			
  		}
?> 				
	for(i=0;i < num;i++)
  		LatLng[i] = new google.maps.LatLng(lat[i],lng[i]);
  					
    var center = new google.maps.LatLngBounds(LatLng[0]);
  	for(i=1;i < num;i++)
    	center.extend(LatLng[i]);

	var mapOptions = {
        zoom: 5,
        center: center.getCenter(),
        disableDefaultUI: true,
        disableDoubleClickZoom: true,
        mapTypeId: google.maps.MapTypeId.HYBRID
        };
        
    map = new google.maps.Map(document.getElementById('map_canvas'),mapOptions);
  	//map.fitBounds(center);		  		
    	 	
	for(i=0 ; i < num;i++)
		markers[i] = createMarker(LatLng[i],label[i],slabel[i],map);

	// add home control
	var homeControlDiv = document.createElement('div');
  	var homeControl = new HomeControl(homeControlDiv, map);
  	homeControlDiv.index = 1;
  	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);

	// add cloud layer
	cloudLayer = new google.maps.weather.CloudLayer();
	cloudLayer.setMap(map);

	// add cloud control
	var cloudControlDiv = document.createElement('div');
  	var cloudControl = new CloudControl(cloudControlDiv, map);
  	cloudControlDiv.index = 1;
  	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(cloudControlDiv);

	// add marker control
	markerControlDiv = document.createElement('div');
  	var markerControl = new MarkerControl(markerControlDiv, map);
  	markerControlDiv.index = 1;
  	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(markerControlDiv);

  	// add weather layer
	var weatherLayer = new google.maps.weather.WeatherLayer({
 	 temperatureUnits: google.maps.weather.TemperatureUnit.CELSIUS
	});
	weatherLayer.setMap(map);	  			

	function HomeControl(controlDiv, map) {
	  // Set CSS styles for the DIV containing the control
 	 // Setting padding to 5 px will offset the control
	  // from the edge of the map.
	  controlDiv.style.padding = '5px  0px 0px 0px';
	  // Set CSS for the control border.
	  var controlUI = document.createElement('div');
	  controlUI.style.backgroundColor = 'white';
	  controlUI.style.borderStyle = 'solid';
	  controlUI.style.borderColor = 'gray';	  
	  controlUI.style.borderWidth = '1px';
	  controlUI.style.cursor = 'pointer';
 	  controlUI.style.textAlign = 'center';
	  controlUI.title = 'Click recenter the map';
	  controlDiv.appendChild(controlUI);
	  // Set CSS for the control interior.
	  var controlText = document.createElement('div');
	  controlText.style.fontFamily = 'Arial,sans-serif';
	  controlText.style.fontSize = '15px';
	  controlText.style.paddingLeft = '4px';
	  controlText.style.paddingRight = '4px';
	  controlText.innerHTML = 'Home';
	  controlUI.appendChild(controlText);
	  // Setup the click event listeners
  	  google.maps.event.addDomListener(controlUI, 'click', function() 
  		{map.setCenter(center.getCenter());
  		map.setZoom(5);
  		});
	  }
	 
	function CloudControl(controlDiv, map) {
	  // Set CSS styles for the DIV containing the control
 	 // Setting padding to 5 px will offset the control
	  // from the edge of the map.
	  controlDiv.style.padding = '5px 0px 0px 0px'; //5 1 0 0

	  // Set CSS for the control border.
	  var controlUI = document.createElement('div');
	  controlUI.style.backgroundColor = 'white';
	  controlUI.style.borderStyle = 'solid';
	  controlUI.style.borderColor = 'gray';	  
	  controlUI.style.borderWidth = '1px';
	  controlUI.style.cursor = 'pointer';
 	  controlUI.style.textAlign = 'center';
	  controlUI.title = 'Click hide/display the clouds';
	  controlDiv.appendChild(controlUI);

	  // Set CSS for the control interior.
	  var controlText = document.createElement('div');
	  controlText.style.fontFamily = 'Arial,sans-serif';
	  controlText.style.fontSize = '15px';
	  controlText.style.paddingLeft = '4px';
	  controlText.style.paddingRight = '4px';
	  controlText.innerHTML = 'Hide Clouds';
	  controlUI.appendChild(controlText);	
  	  // Setup the click event listeners
  	  google.maps.event.addDomListener(controlUI, 'click', function() 
  	  	{if(show)
			{cloudLayer.setMap(null);show = 0;
			controlText.innerHTML = 'Show Clouds';
			}
		else
			{cloudLayer.setMap(map);show = 1;
  			controlText.innerHTML = 'Hide Clouds';
			}	
		});
		}
	function MarkerControl(controlDiv, map) {
	  // Set CSS styles for the DIV containing the control
 	 // Setting padding to 5 px will offset the control
	  // from the edge of the map.
	  controlDiv.style.padding = '5px 0px 0px 0px'; //5 1 0 0

	  // Set CSS for the control border.
	  var controlUI = document.createElement('div');
	  controlUI.style.backgroundColor = 'white';
	  controlUI.style.borderStyle = 'solid';
	  controlUI.style.borderColor = 'gray';	  
	  controlUI.style.borderWidth = '1px';
	  controlUI.style.cursor = 'pointer';
 	  controlUI.style.textAlign = 'center';
	  controlUI.title = 'Click hide/display the markers';
	  controlDiv.appendChild(controlUI);

	  // Set CSS for the control interior.
	  controlText = document.createElement('div');
	  controlText.style.fontFamily = 'Arial,sans-serif';
	  controlText.style.fontSize = '15px';
	  controlText.style.paddingLeft = '4px';
	  controlText.style.paddingRight = '4px';
	  controlText.innerHTML = 'Hide Markers';
	  controlUI.appendChild(controlText);	
  	  // Setup the click event listeners
  	  google.maps.event.addDomListener(controlUI, 'click', function() 
  	  	{if(showMarker)
			{for(i=0 ; i < num;i++)
				markers[i].setVisible(false);
			controlText.innerHTML = 'Show Markers';
			showMarker = 0;
			}
		else
			{for(i=0 ; i < num;i++)
				markers[i].setVisible(true);
  			controlText.innerHTML = 'Hide Markers';
  			showMarker = 1;
			}	
		});
	 }
	}//initialize
</script>
<script type='text/javascript' src='calendrier.js'></script> 
</head>
  <body class='iconesExt' onload='initialize()'>

<!-- Invisible table for calendar --> 
<table class="ds_box"  id="ds_conclass" style="display: none;" >
	<caption id="id_caption" class='ds_caption'>xxxx</caption>
	<tr><td id="ds_calclass">aaa</td></tr>
</table>
	
<table style='margin-left:auto; margin-right:auto;  margin-top:0px; margin-bottom:0px; padding:0px '>
<tr>
<?php

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
echo("</tr></table>");	
$dateend = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d'),date('y')));
$datebeg = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d')-30,date('y')));
$num = count($devicelist["devices"]);

?>

<div class='container'>
<table class='container'>
<tr><td class='container'>
<?php
drawMenuStation();
?>
</td>
<td><div id='map_canvas'  class='map_canvas'> </div></td>
<td class='container'>
<?php
drawMenuCompare();
?>	
</td></tr></table></div>


<!-- START OF HIT COUNTER CODE -->
<a href='http://www.000webhost.com/' target='_blank' ><img src='http://www.000webhost.com/images/80x15_powered.gif' alt='Web Hosting' width='80' height='10'/></a>
<!--
<table class='counter'>
<tr>
<td><td class='container'>
<a href='http://www.000webhost.com/' target='_blank' ><img src='http://www.000webhost.com/images/80x15_powered.gif' alt='Web Hosting' width='80' height='15'/></a>
</td>
<td class='container'>
<script src='http://www.counter160.com/js.js?img=15'></script>
<br>
<a href='http://www.000webhost.com'>
<img src='http://www.counter160.com/images/15/left.png' alt='Free web hosting' style='border:0px'>
</a>
<a href='http://www.hosting24.com'>
<img alt='Web hosting' src='http://www.counter160.com/images/15/right.png' style='border:0px' >
</a>
</td></tr></table>
-->
</body>
</html>


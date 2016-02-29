<?php
define('__ROOT__', dirname(__FILE__));
require_once (__ROOT__.'/src/Netatmo/autoload.php');
session_start(); 
?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<title>Stations Netatmo</title>
<meta charset='utf-8'>
<meta name="author" content="Hubert de Fraysseix">
<meta name="description" content="Php program to display Netatmo measures">
<meta name="keywords" content="Netatmo, Meteo, Google api">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="viewport" content="width=devive-width, initial-scale=.5, minimum-scale=.2,  user-scalable=yes">

<script src='js/size.js'></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="js/jcookies.js"></script>
<script src="js/login.js"></script>
<link rel='icon' href='favicon.ico'>
<link rel="apple-touch-icon" href="apple/meteo.png" >

<!-- iPhone -->
<link href="apple/320x480.png" media="screen and (device-width: 320px)" rel="apple-touch-startup-image">
<!-- iPhone 5 ou apple-touch-startup-image-640x1096 640x1136.png-->
<link  href="apple/apple-touch-startup-image-640x1096.png" rel="apple-touch-startup-image" media="screen and (device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)">
<!-- iPad (non-Retina) -->
<link href="apple/768x1004.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)" rel="apple-touch-startup-image">
<link href="apple/1024x748.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)" rel="apple-touch-startup-image">
<!-- iPhone 6+ -->
<link href="apple/1242x2208.png"
    media="(device-width: 414px) and (device-height: 736px)
    and (-webkit-device-pixel-ratio: 3)
    and (orientation: portrait)"
    rel="apple-touch-startup-image"> 

<link href="apple/Default-414w-736h-landscape@3x.png" media="screen and (device-width: 414px) and (orientation: landscape)" rel="apple-touch-startup-image">

<!--
For third-generation iPad with high-resolution Retina display:
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="apple-touch-icon-144x144-precomposed.png">
For iPhone with high-resolution Retina display: 
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="apple-touch-icon-114x114-precomposed.png">
For first- and second-generation iPad:
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="apple-touch-icon-72x72-precomposed.png">
For non-Retina iPhone, iPod Touch, and Android 2.1+ devices:
<link rel="apple-touch-icon-precomposed" href="apple-touch-icon-precomposed.png">

iPhone SPLASHSCREEN
<link href="apple-touch-startup-image-320x460.png" media="(device-width: 320px)" rel="apple-touch-startup-image">
iPhone (Retina) SPLASHSCREEN
<link href="apple-touch-startup-image-640x920.png" media="(device-width: 320px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">
iPad (Retina) (Portrait)
<link href="apple-touch-startup-image-1536x2008.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait) and (-webkit-min-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
iPad (Retina) (Landscape)
<link href="apple-touch-startup-image-2048x1496.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape) and (-webkit-min-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />

iPhone 6
<link href="750x1294.png" media="(device-width: 375px) and (device-height: 667px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">
iPhone 6+ Portrait
<link href="1242x2148.png" media="(device-width: 414px) and (device-height: 736px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image">
iPhone 6+ Landscape
<link href="1182x2208r.png" media="(device-width: 414px) and (device-height: 736px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image">
-->


<!--<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Clearface">-->
<link type='text/css' rel='stylesheet'  href='style.css'>
<link rel='stylesheet' media='screen' type='text/css' href='calendrierBleu.css'>

<?php
require_once 'Config.php';
require_once 'initClient.php';
require_once 'Geolocalize.php';
require_once 'fill.php';
require_once 'menus.php';
require_once 'moontime.php';
require_once 'MoonPhase.php';
require_once 'translate.php';

initClient();
$timezone = $_SESSION['timezone'];
date_default_timezone_set($timezone);
$mydevices = $_SESSION['mydevices']; 
$numStations = $mydevices["num"];
//$Temperature_unit = $_SESSION['Temperature_unit'];
if(!isset($_SESSION['adr']))getAdr($mydevices);
$adr = $_SESSION['adr'];

function getAdr($myDevices)
    {global $geocode;
    $numStations = $myDevices['num'];
    for($i = 0;$i < $numStations;$i++)
        {if(isset($geocode) && $geocode)
            {$lat =  $myDevices[$i]["place"]["location"][1];
            $long = $myDevices[$i]["place"]["location"][0];     
            $adr[$i] = geolocalize($lat,$long);
            } 
        else
            $adr[$i] = 'BAD';
        }
    $_SESSION['adr'] = $adr;
    }
function printLat($lat)
    {$latm = ($lat-intval($lat))*60;
    $lats = ($latm - intval($latm))*60;
    $lats = intval(1000*$lats)/1000;
    return intval($lat).'° '.intval($latm)."' ".$lats."''";
    }
function getTimeOffset($localZone)
    {
    $dateTimeZoneLocal = new DateTimeZone("$localZone");
    $dateTimeZoneGmt = new DateTimeZone("UTC");//UTC
    $dateTimeLocal = new DateTime('now', $dateTimeZoneLocal);
    $dateTimeGmt = new DateTime('now', $dateTimeZoneGmt);
    $offset = ($dateTimeZoneLocal->getOffset($dateTimeGmt))/3600;
    return $offset; 
    }      
$slabel = array($numStations);
$label = array($numStations);


	
//Creation des InfoWindow
// moon phase
$moonphase = new MoonPhase();
$phase = intval($moonphase->phase()*28 +.5)%28;
$imgnum = sprintf('%1$02d',$phase);
$moonimg = "'".'icone/Moon/MoonDay'.$imgnum.'.png'."'";
$moonpercent = intval($moonphase->phase()*1000)/10;
$lumen = intval($moonphase->illumination()*1000)/10;
$day = idate('d');
$month = idate('m');
$year = idate('Y');

$timeOffset =  getTimeOffset($timezone); 

function daydiff($lat,$long)
    {$t = time();
    $tp = $t - 24*60*60;
    $sun = date_sun_info($t,$lat,$long);
    $sunp = date_sun_info($tp,$lat,$long);
    return $sun['sunset'] - $sun['sunrise'] - ($sunp['sunset'] - $sunp['sunrise']);
    }
function daylength($lat,$long)
    {$sun = date_sun_info(time(),$lat,$long);
    $daylength = $sun['sunset'] - $sun['sunrise'];
	$dayH = intval($daylength/3600);
	$day = $daylength - 3600*$dayH;
	$dayM = intval($day/60); 
	$dayS = $day - 60*$dayM;   
	return sprintf("%d:%02d:%02d",$dayH,$dayM,$dayS);
    } 
for($i = 0;$i < $numStations;$i++)
	{$altitude = $mydevices[$i]['place']['altitude'];
    $place = $adr[$i];
    $int_name = $mydevices[$i]["module_name"];
	$ext_name = $mydevices[$i]["modules"][0]["module_name"];
	// Lever/Coucher du soleil
	$Zenith = 90 + (50/60);
	//$lat = $mydevices[$i]['place']['latitude'];
	//$long = $mydevices[$i]['place']['longitude'];
	$lat = $mydevices[$i]['place']['location'][1];
	$long = $mydevices[$i]['place']['location'][0];
	
	$diff = daydiff($lat,$long);
	$arrow = ($diff > 0) ? '&#10138;':'&#10136;'; 
	if(abs($diff) < 1)$arrow = '&#8596;';
	$diff = abs($diff);
	$diffm = intval($diff/60); $diffs = abs($diff%60); $tdiff =  sprintf("%2dm %2ds", $diffm,$diffs);
	$tdaylength = daylength($lat,$long);
	$soleil = date("H:i:s",date_sunrise(time(),SUNFUNCS_RET_TIMESTAMP,$lat,$long, $Zenith,$timeOffset))."&nbsp;&nbsp;"
	        .date("H:i:s",date_sunset(time(),SUNFUNCS_RET_TIMESTAMP,$lat,$long, $Zenith,$timeOffset));
//	$soleil = date_sunrise(time(),SUNFUNCS_RET_STRING,$lat,$long, $Zenith,$timeOffset)."&nbsp;&nbsp;"
//	        .date_sunset(time(),SUNFUNCS_RET_STRING,$lat,$long, $Zenith,$timeOffset);

    // Lever/Coucher lune
    $mrise = $mset = 1;
    $moon = new moontime();
    $ret = $moon->calculateMoonTimes($month, $day, $year, $lat, $long, $timeOffset); 
    $moonrise = date("H:i",$ret->moonrise);
    $moonset = date("H:i",$ret->moonset);
    $mrise = $ret->mrise; 
    $mset = $ret->mset;    
    //echo "        rise: $mrise set: $mset <br>";
    if($mrise == false)
        {$time2 = time() - 24*60*60;
        $day2 = idate('d',$time2);
        $moon2 = new moontime();
        $ret2 = $moon2->calculateMoonTimes($month, $day2, $year, $lat, $long, $timeOffset); 
        $moonrise = date("H:i",$ret2->moonrise).'-';
        }      
    $moon = $moonrise . '&nbsp;&nbsp;'. $moonset;
 
    if(($mrise && $ret->moonset < $ret->moonrise) ||!$mset)
        {$time1 = time() + 24*60*60;
        $day1 = idate('d',$time1);
        $moon1 = new moontime();
        $ret1 = $moon1->calculateMoonTimes($month, $day1, $year, $lat, $long, $timeOffset); 
        $moonset = date("H:i",$ret1->moonset);
        if(!$ret1->mset) $moonset = '-';
        $moon = $moonrise . '&nbsp;&nbsp;'. $moonset.'+';
        }
    $txt = '('.printlat($lat).', '.printlat($long).', '.$altitude.'m)';
    
    if($adr[$i] == "BAD")	
    	$p = "<b>".$mydevices[$i]['station_name']."</b><span style='font-size:12px;'><br>$txt</span>";   
	else
    	$p = "<b>$place[1]</b><span style='font-size:12px;'><br>$place[0]<br>$txt</span>"; 
 
 
 
// sun and moon
    $p .= "<br><div style='font-size:12px; font-weight:400; '>";
    $p .= "<table border='0' style='width:260px; margin-right:auto; margin-left:auto; margin-top:10px; margin-bottom:5px; line-height: 0.8em;'><tr>";
    $p .= " <td><img src='icone/csun.png' ALT='sun' style='height:25px;'/></td>";
    $p .= "<td style='vertical-align:middle;'> &nbsp; $soleil</td>"; 
    $p .= "<td style='width:30px;'>&nbsp;</td>";
    $p .= "<td><img src=$moonimg ALT='moon' style='height:25px; '/></td>";
    $p .= "<td>&nbsp;  $moon</td></tr>";
    $p .= "<tr><td style='font-size:20px; text-align:center; '>$arrow</td>";
    $p .= "<td colspan='2'>&nbsp; $tdiff &nbsp; $tdaylength</td></tr>";
    $p .= "</table></div>";
//*************************************
    $dataInt = $mydevices[$i]['dashboard_data'];
    $dataExt = $mydevices[$i]['modules'][0]['dashboard_data'];
    // station intérieure
    $temp = degree2($dataInt["Temperature"]);
    $hum = $dataInt["Humidity"];
    $co2 = $dataInt["CO2"];
    $db = $dataInt["Noise"];
	
	$red = "style='color:#900'";
	$green = "style='color:#070'";
	$orange = "style='color: brown'";
	$violet = "style='color:#007'";
	
	$tabINT = "<tr><td class='name'>$int_name</td> <td></td><td $red>$temp</td> <td $green>$hum</td>  <td $orange>$co2</td> <td></td> <td $violet>$db</td></tr>";	
    // station extérieure
    $temp = degree2($dataExt["Temperature"]);
	$hum = $dataExt["Humidity"];
	$pres = intval(pressure2($dataInt["Pressure"]) + .5);
	$tabEXT = "<tr><td class='name'>$ext_name</td> <td></td><td $red>$temp</td> <td $green>$hum</td> <td></td> <td>$pres</td></tr>";	
    $cu = tu(); $pu = pru(); $vu = wu();
    // Infos
    $label[$i]  = "<table class='bulle' style='width:260px;'>";
    $label[$i] .=  "<caption > $p </caption>";
    $label[$i] .=  "<tr><th style='width:60px;''></th><th></th> <th>T</th> <th>H</th> <th>Co2</th> <th>P</th> <th>Db</th><th>R1h</th><th>R24h</th></tr>";
    $label[$i] .=   "$tabINT  $tabEXT";

    // mesures des modules de la sation $i
    $device = $mydevices[$i]; 
    for($j = 1; $j <= 10 ; $j++)
        {if(!isset($device["modules"][$j]))continue;
        $data = $device['modules'][$j]['dashboard_data'];            
        $name = $device["modules"][$j]["module_name"];
        if($j == 10)
            {$temp = $hum = $co2 = ' ';
            $rain1 = $data["sum_rain_1"];  $rain1 = intval($rain1*10+.5)/10;
            $rain24 = $data["sum_rain_24"];$rain24 = intval($rain24*10+.5)/10;
            }
        else
            {$temp = degree2($data["Temperature"]);
            $hum = $data["Humidity"];
            $co2 = $data["CO2"];
            $rain1 = $rain24 = ' ';
            }
        $label[$i] .= "<tr><td class='name'>$name</td><td>&nbsp;</td> <td $red>$temp</td> <td $green>$hum</td> <td $orange>$co2</td> <td></td> <td></td><td $green>$rain1</td><td $green>$rain24</td></tr>";        
        }
        
    if(isset($device["modules"][11])) 
        {$data = $device['modules'][11]['dashboard_data'];            
        $name = $device["modules"][11]["module_name"];
        $arr = explode(" ",$name);
		$name = substr($arr[0],0,15);
        $windspeed = speed2($data["GustStrength"]); 
        $windangle = angleDir($data["GustAngle"]);   
        $label[$i] .= "<tr><td class='name'>$name</td><td>&nbsp;</td><td colspan='2 '> $windspeed $vu</td><td> $windangle </td><td> </td></tr>";
        }
    $label[$i] .= '</table>';
    
    $temp = degree2($dataExt["Temperature"]);
    $slabel[$i] = $temp . ' '.$cu;	  // utilise pour les marker    	  
	}	

?>
<script
<?php   
//	if($use_google_key == 1)
//		echo("src='https://maps.googleapis.com/maps/api/js?libraries=weather,places?key=$google_key'>");
//		echo("src='https://maps.googleapis.com/maps/api/js?libraries=places?key=$google_key&sensor=false'>");
//	else
		echo("src='https://maps.googleapis.com/maps/api/js?libraries=places&sensor=false'>");
?>
</script>
<script>
    //var cloudLayer;
    var trafficlayer;
    var map;
    var show = 1;
    var showTraffic = 0;
    var showMarker = 1;
    var controlText;
    var zoomInit = 4;
    
	function createMarker(pos,label,slabel,map) 
	    {var marker = new StyledMarker({styleIcon:new StyledIcon(StyledIconTypes.BUBBLE,{color:'00ff00',text:slabel}),position:pos,map:map});
		var infowindow = new google.maps.InfoWindow(
		    {'content'  : label,
		    'disableAutoPan' : true
		    });
	   	google.maps.event.addListener(marker, 'rightclick', function() 
       		{marker.setVisible(false);
       		controlText.innerHTML = 'Show Markers';showMarker =0;
       		});  
       google.maps.event.addListener(marker, 'mouseover', function(){infowindow.open(map, marker);});
       google.maps.event.addListener(marker, 'mouseout', function(){infowindow.close(map, marker);}); 
       google.maps.event.addListener(marker, 'click', function()
       		{position = marker.getPosition();
       		pos= new google.maps.LatLng(position.lat() ,position.lng());//.03
       		map.setCenter(pos);
       		map.setZoom(map.getZoom()+4);
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
  		{echo("lat[$i] = {$mydevices[$i]['place']['location']['1']};\n");
  		echo("lng[$i] = {$mydevices[$i]['place']['location']['0']};\n");
  		echo("label[$i] = \"$label[$i]\";\n");
  		echo("slabel[$i] = \"$slabel[$i]\";\n");  			
  		}

?> 				
	for(i = 0;i < num;i++)
  		LatLng[i] = new google.maps.LatLng(lat[i],lng[i]);
  		
    var center = new google.maps.LatLngBounds(LatLng[0]);
  	for(i=1;i < num;i++)
    	center.extend(LatLng[i]);
  	       		
	var mapOptions = {
        zoom: zoomInit,
        center: center.getCenter(),
        disableDefaultUI: false,
        mapTypeId: google.maps.MapTypeId.HYBRID,
        mapTypeControl: true,
        mapTypeControlOptions: {
                mapTypeIds: [google.maps.MapTypeId.HYBRID,google.maps.MapTypeId.ROADMAP,google.maps.MapTypeId.SATELLITE]                                },
        zoomControl: true,
        zoomControlOptions: {
                            style: google.maps.ZoomControlStyle.LARGE,
                            position: google.maps.ControlPosition.LEFT_CENTER
                            },
        disableDoubleClickZoom: true,
        panControl: false,
        streetViewControl: false,
        };
        
    map = new google.maps.Map(document.getElementById('map_canvas'),mapOptions);		  		
    	 	
	for(i=0 ; i < num;i++)
		markers[i] = createMarker(LatLng[i],label[i],slabel[i],map);

	// add home control
	var homeControlDiv = document.createElement('div');
  	var homeControl = new HomeControl(homeControlDiv, map);
  	homeControlDiv.index = 1;
  	map.controls[google.maps.ControlPosition.TOP_LEFT].push(homeControlDiv);

	// add cloud layer
	//Layer = new google.maps.weather.CloudLayer();
	//cloudLayer.setMap(map);

	// add cloud control
    // add traffic layer
    trafficLayer = new google.maps.TrafficLayer();
    trafficLayer.setMap(null);

	// add traffic control
	var trafficControlDiv = document.createElement('div');
  	var trafficControl = new TrafficControl(trafficControlDiv, map);
  	trafficControlDiv.index = 1;
  	map.controls[google.maps.ControlPosition.TOP_LEFT].push(trafficControlDiv);
  	

	// add marker control
	markerControlDiv = document.createElement('div');
  	var markerControl = new MarkerControl(markerControlDiv, map);
  	markerControlDiv.index = 1;
  	map.controls[google.maps.ControlPosition.TOP_LEFT].push(markerControlDiv);

  	// add weather layer
<?php
/*
    if($Temperature_unit)
        echo("var weatherLayer = new google.maps.weather.WeatherLayer({
         temperatureUnits: google.maps.weather.TemperatureUnit.CELSIUS
        });");
	else
        echo("var weatherLayer = new google.maps.weather.WeatherLayer({
         temperatureUnits: google.maps.weather.TemperatureUnit.FAHRENHEIT
        });");
*/        
?>	
	//weatherLayer.setMap(map);	  			

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
	  controlText.style.fontFamily = 'Courier,sans-serif';
	  controlText.style.fontSize = '12px';
	  controlText.style.paddingLeft = '4px';
	  controlText.style.paddingRight = '4px';
	  controlText.innerHTML = 'Home';
	  controlUI.appendChild(controlText);
	  // Setup the click event listeners
  	  google.maps.event.addDomListener(controlUI, 'click', function() 
  		{map.setCenter(center.getCenter());
  		map.setZoom(zoomInit);
  		});
	  } 
	function TrafficControl(controlDiv, map) {
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
	  controlUI.title = 'Click hide/display the traffic';
	  controlDiv.appendChild(controlUI);

	  // Set CSS for the control interior.
	  var controlText = document.createElement('div');
	  controlText.style.fontFamily = 'Courier,sans-serif';
	  controlText.style.fontSize = '12px';
	  controlText.style.paddingLeft = '4px';
	  controlText.style.paddingRight = '4px';
	  controlText.innerHTML = 'Show Traffic';
	  controlUI.appendChild(controlText);	
  	  // Setup the click event listeners
  	  google.maps.event.addDomListener(controlUI, 'click', function() 
  	  	{if(showTraffic)
			{trafficLayer.setMap(null);showTraffic = 0;
			controlText.innerHTML = 'Show Traffic';		
			}
		else
			{trafficLayer.setMap(map);showTraffic = 1;
  			controlText.innerHTML = 'Hide Traffic';  			
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
	  controlText.style.fontFamily = 'Courier,sans-serif';
	  controlText.style.fontSize = '12px';
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
<!-- cannot be moved before -->
<script src='js/StyledMarker.js'></script>

</head>
  <!--<body  onload='initialize()' style='transform: scale(.8,.85);'>-->
  <body  onload='initialize()'>
  
  <!--<div style='transform: scale(.8;.9);  -moz-transform-origin: top left;'>-->
  <div>
<!-- Invisible table for calendar --> 

<table class="ds_box"  id="ds_conclass" style="display: none;" >
	<caption id="id_caption" class='ds_caption'>xxxx</caption>
	<tr><td id="ds_calclass">aaa</td></tr>
</table>
<?php require_once 'calendrier.php'; ?>

<!-- Tracé des icones -->	
<?php

$arrow = ($moonpercent >= 0 && $moonpercent < 50) ? '&#10138;':'&#10136;'; 
$txt = tr('Phase lunaire');
echo("<table id= 'icones' style='margin-left:auto; margin-right:auto;  margin-top:-2px; margin-bottom:0px; padding:0px '>
		<tr>\n");
echo "<td>\n";	

echo "<table class='icone'>\n";	
echo "<tr><td colspan='2' class='th'>$txt</td>\n";
echo("</tr><tr><td ><img src=$moonimg ALT='moon' style='height:100px;vertical-align:bottom;'/></td>\n");
echo "<td style='font-size:13px;'>phase:$moonpercent% &nbsp; <span style='font-size:18px;'> $arrow </span><br>lumen:$lumen%</td>\n";
echo "</tr>\n"; 

/* Moon info */
$tinfo = tr("Autres informations");
$moonphase = new MoonPhase();
$txt0 = tr('Nouvelle lune');
$txt1 = tr('Premier quartier');
$txt2 = tr('Pleine lune');
$txt3 = tr('Dernier quartier');
$txt0 = tr('Nouvelle lune');
$date0 = date("d/m/Y H:i",$moonphase->new_moon());
$date1 = date("d/m/Y H:i",$moonphase->first_quarter());
$date2 = date("d/m/Y H:i",$moonphase->full_moon());
$date3 = date("d/m/Y H:i",$moonphase->last_quarter());
$date4 = date("d/m/Y H:i",$moonphase->next_new_moon());

echo("
	<tr><td class='hl'> </td>
	    <td class='tooltip' >
		<a href='#' class='tooltip'>
  		$tinfo:		
        <div >
        <table class='info'>
        <tr><td style='width:90px;'>$txt0</td><td style='text-align:center;'>$date0</td></tr>
        <tr><td style='width:90px;'>$txt1</td><td style='text-align:center;'>$date1</td></tr>
        <tr><td style='width:90px;'>$txt2</td><td style='text-align:center;'>$date2</td></tr>
        <tr><td style='width:90px;'>$txt3</td><td style='text-align:center;'>$date3</td></tr>
        <tr><td style='width:90px;'>$txt0</td><td style='text-align:center;'>$date4</td></tr>
        </table>
        </div></a>
</td></tr></table>
"); 

echo "</td>\n";	
// Tracé des icones  
for($i = 0;$i < $numStations;$i++)
	{echo("<td>");
	fill($mydevices[$i]);
	echo("</td>");
	}
echo("</tr></table>");	
?>

<!-- trace des menus et de la Google map -->

<table class='container'>

<tr>
    <td class='container'>
        <?php
        drawMenuModules('292px');
        drawMenuStation('292px');
        ?>
    </td>
<!-- GOOGLE MAP -->

    <script>  
    var x = $(document).width();
    var y = $(document).height();
    var xx = $(window).width();
    if(xx <  $(window).height())y = Math.min(y,xx); // portrait
    var ico = document.getElementById("icones");
    var hico = ico.offsetHeight;
    //hico = Math.max(hico,144);
    var lico = ico.offsetWidth;  
    var gr = document.getElementById("modules");
    var larg = 2*gr.offsetWidth + 12;
     y -= (hico + 45);
    var larMin = lico - larg;
    var larMax = x - larg;
    var lar = Math.max(680,larMin);
    lar = Math.min(lar,larMax); 
    var t = "<td><div id='map_canvas'  class='map_canvas' style='margin-left:auto; margin-right:auto; margin-top:-2px; width:"+lar+"px; height:"
    t += y+"px; border:solid 2px gray;'> </div></td>";
    //t = "<td><div id='map_canvas'  style='width:400px; height:400px;' > </div></td>";
    document.write(t);
    //alert(t);
 </script>
    <!--<td><div id='map_canvas'  style='width:400px; height:400px;' > </div></td>-->
    <td class='container'>
        <?php
        drawMenuCompare('292px');//304
        drawMenuHist('292px');
        ?>	
    </td>
</tr>

</table>

<?php  drawLogoutBack(); ?>
</div>
</body>
</html>


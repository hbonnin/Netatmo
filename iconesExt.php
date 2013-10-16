<?php
require_once 'NAApiClient.php';
session_start(); 
?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<title>Stations Netatmo</title>
<meta charset='utf-8'>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<script src='js/size.js'></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="js/jcookies.js"></script>
<script src="js/login.js"></script>
<link rel='icon' href='favicon.ico'>
<link rel="apple-touch-icon" href="icone/meteo.png" >
<!-- iPhone 5 Retina -->
<link  href="image/startup-image-640x1096.png" rel="apple-touch-startup-image" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)">
<link href="image/startup-image-320x460.png" media="(device-width: 320px)" rel="apple-touch-startup-image">
<!-- iPad (non-Retina) (Portrait) -->
<link href="image/startup-image-768x1004.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)" rel="apple-touch-startup-image" />
<!-- iPad (non-Retina) (Landscape) -->
<link href="image/startup-image-1024x748.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)" rel="apple-touch-startup-image" />

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


-->
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

date_default_timezone_set($timezone);

initClient();
$client = $_SESSION['client'];
$mydevices = $_SESSION['mydevices']; 
$numStations = $mydevices["num"];
$devicelist = getDevicelist();
$Temperature_unit = $_SESSION['Temperature_unit'];
$last_mesures = getLastMeasures($devicelist);
$slabel = array($numStations);
$label = array($numStations);

// to speed reloading we compute only once the locations
if($mydevices['address'] == 0)
	{$mydevices['address'] = 1;
	for($i = 0;$i < $numStations;$i++)
		{$mydevices[$i]['latlng']['latitude'] = $devicelist["devices"][$i]["place"]["location"][1];
		$mydevices[$i]['latlng']['longitude'] = $devicelist["devices"][$i]["place"]["location"][0];
		$mydevices[$i]['latlng']['altitude'] = $devicelist["devices"][$i]["place"]["altitude"];
		$mydevices[$i]['address'] = geolocalize($mydevices[$i]['latlng']['latitude'],$mydevices[$i]['latlng']['longitude']);
		}
	$_SESSION['mydevices'] = $mydevices;	
	}
//Creation des InfoWindow
// moon phase
/*
$date = strtotime(date("Y-m-d"));
$moonPhase = ($date - 603240) / 2551392;
$moonPhase -= (int) $moonPhase;
$moonPhase = 100 - round($moonPhase * 100);
*/
$moonphase = new MoonPhase();
$phase = intval($moonphase->phase()*27 +.5);
$imgnum = sprintf('%1$02d',$phase);
$moonimg = "'".'icone/Moon/MoonDay'.$imgnum.'.png'."'";
$moonpercent = intval($moonphase->phase()*1000)/10;
$lumen = intval($moonphase->illumination()*1000)/10;
$day = idate('d');
$month = idate('m');
$year = idate('Y');

for($i = 0;$i < $numStations;$i++)
	{$altitude = $mydevices[$i]['latlng']['altitude'];
    $place = $mydevices[$i]['address'];
    $int_name = $mydevices[$i]["module_name"];
	$ext_name = $mydevices[$i]["modules"][0]["module_name"];
	// Lever/Coucher du soleil
	$Zenith = 90 + (50/60);
	$lat = $mydevices[$i]['latlng']['latitude'];
	$long = $mydevices[$i]['latlng']['longitude'];	
	$soleil = date_sunrise(time(),SUNFUNCS_RET_STRING,$lat,$long, $Zenith,2)."&nbsp;&nbsp;".date_sunset(time(),SUNFUNCS_RET_STRING,$lat,$long, $Zenith,2);
    // Lever/Coucher lune
    $moon = new moontime();
    $ret = $moon->calculateMoonTimes($month, $day, $year, $lat, $long); 
    $moon = date("H:i",$ret->moonrise) . '&nbsp;&nbsp;'. date("H:i",$ret->moonset);
/*	
	if(isset($devicelist["devices"][$i]['extra']))
        {$Q = $devicelist["devices"][$i]['extra']['air_quality']['data'][0]['value'][0];
        $QA[$i] = $Q[0] . " ".$Q[1] ;
        if(count($Q) >= 5 && isset($Q[3]))
            $QA[$i] .= " / ".$Q[3]." ".$Q[4];
        }
    else 
	    $QA[$i] ='';
*/	    
	if($place == "BAD")	
    	$p = '<b>' . $mydevices[$i]['station_name'] . ' <br>(' . $altitude . 'm)' . '</b></font>';
	else
    	$p = '<b>' . $place[1] . '</b><br><font size=2>' . $place[0] .  '<br> (' . $altitude . 'm)</font>'; 
// sun and moon
    $p .= "<br><div style='font-size:12px; font-weight:400; '>";
    $p .= "<table style='margin:auto;'><tr><td>";
    $p .= " <img src='icone/csun.png' ALT='sun' style='height:25px;vertical-align:middle;'/></td><td>&nbsp; $soleil </td>"; 
    $p .= "<td>&nbsp;&nbsp;&nbsp;";
    $p .= "<img src=$moonimg ALT='moon' style='height:25px;vertical-align:middle;'/></td><td>&nbsp; $moon</td>";
    $p .= '</tr></table></div>';
    $res = $last_mesures[$i]["modules"];
	$temp = degree2($res[0]['Temperature']);
	$hum = $res[0]['Humidity'];
	$co2 = $res[0]['CO2'];
	$db  = $res[0]['Noise'];
	$red = "style='color:#900'";
	$green = "style='color:#070'";
	$orange = "style='color: brown'";
	$violet = "style='color:#007'";
	
	$tabINT = "<td class='name'>$int_name</td> <td $red>$temp</td> <td $green>$hum</td>  <td $orange>$co2</td> <td></td> <td $violet>$db</td>";	
	$temp = degree2($res[1]['Temperature']);
	$hum = $res[1]['Humidity'];
	$pres = intval($res[0]['Pressure'] + .5);
	$tabEXT = "<td class='name'>$ext_name</td> <td $red>$temp</td> <td $green>$hum</td> <td></td> <td>$pres</td>";	
    $cu = $Temperature_unit ? '°':'F';
    $label[$i]  = "<table class='bulle'>"
        .'<caption >'. $p .'</caption>'
        ."<tr><th style='width:60px;''></th> <th>T$cu</th> <th>H%</th> <th>Co2</th> <th>Pmb</th> <th>Db</th></tr>"
        .'<tr>' . $tabINT .'</tr>'
        .'<tr>' . $tabEXT .'</tr>';
        
        $nModule = count($res);
        for($j = 2; $j < $nModule ; $j++)
            {$name = $res[$j]["module_name"];
            $temp = degree2($res[$j]["Temperature"]);
            $hum = $res[$j]["Humidity"];
            $co2 = $res[$j]["CO2"];		
            $tabMOD = "<tr><td class='name'>$name</td> <td $red>$temp</td> <td $green>$hum</td> <td $orange>$co2</td> <td></td> <td></td></tr>";
            $label[$i] = $label[$i] . '<tr>' . $tabMOD .'</tr>';        
            }
    $label[$i] = $label[$i] . '</table>';
    //if(!empty($QA[$i]))$label[$i] = $label[$i] . "<font size=1>Qualité de l'air: ".$QA[$i]."</font>";
    if($Temperature_unit)
        $slabel[$i] = degree2($res[1]['Temperature']) . '°';	  // usilise pour les marker    	  
    else
        $slabel[$i] = degree2($res[1]['Temperature']) . ' F';	  // usilise pour les marker    	  
	}	

?>
<script
<?php   
	if($use_google_key == 1)
		echo("src='https://maps.googleapis.com/maps/api/js?libraries=weather,places?key=$google_key&amp;sensor=false'>");
	else
		echo("src='https://maps.googleapis.com/maps/api/js?libraries=weather,places&amp;sensor=false'>");
?>
</script>

<script>
    var cloudLayer;
    var map;
    var show = 1;
    var showMarker = 1;
    var controlText;
    
	function createMarker(pos,label,slabel,map) 
	    {var marker = new StyledMarker({styleIcon:new StyledIcon(StyledIconTypes.BUBBLE,{color:'00ff00',text:slabel}),position:pos,map:map});
		//marker.setZIndex(1);
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
       		pos= new google.maps.LatLng(position.lat() + .3,position.lng());//.03
       		map.setCenter(pos);
  			map.setZoom(9);
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
  		{echo("lat[$i] = {$mydevices[$i]['latlng']['latitude']};\n");
  		echo("lng[$i] = {$mydevices[$i]['latlng']['longitude']};\n");
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
        zoom: 5,
        center: center.getCenter(),
        disableDefaultUI: true,
        disableDoubleClickZoom: true,
        scaleControl: false,
        scaleControlOptions: {position: google.maps.ControlPosition.TOP_RIGHT},
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
  	map.controls[google.maps.ControlPosition.TOP_LEFT].push(homeControlDiv);

	// add cloud layer
	cloudLayer = new google.maps.weather.CloudLayer();
	cloudLayer.setMap(map);

	// add cloud control
	var cloudControlDiv = document.createElement('div');
  	var cloudControl = new CloudControl(cloudControlDiv, map);
  	cloudControlDiv.index = 1;
  	map.controls[google.maps.ControlPosition.TOP_LEFT].push(cloudControlDiv);

	// add marker control
	markerControlDiv = document.createElement('div');
  	var markerControl = new MarkerControl(markerControlDiv, map);
  	markerControlDiv.index = 1;
  	map.controls[google.maps.ControlPosition.TOP_LEFT].push(markerControlDiv);

  	// add weather layer
<?php
    if($Temperature_unit)
        echo("var weatherLayer = new google.maps.weather.WeatherLayer({
         temperatureUnits: google.maps.weather.TemperatureUnit.CELSIUS
        });");
	else
        echo("var weatherLayer = new google.maps.weather.WeatherLayer({
         temperatureUnits: google.maps.weather.TemperatureUnit.FAHRENHEIT
        });");	
?>	
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
	  controlText.style.fontSize = '12px';
	  controlText.style.paddingLeft = '4px';
	  controlText.style.paddingRight = '4px';
	  controlText.innerHTML = 'Home';
	  controlUI.appendChild(controlText);
	  // Setup the click event listeners
  	  google.maps.event.addDomListener(controlUI, 'click', function() 
  		{map.setCenter(center.getCenter());
  		map.setZoom(4);
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
	  controlText.style.fontSize = '12px';
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
  <body  onload='initialize()'>

<!-- Invisible table for calendar --> 

<table class="ds_box"  id="ds_conclass" style="display: none;" >
	<caption id="id_caption" class='ds_caption'>xxxx</caption>
	<tr><td id="ds_calclass">aaa</td></tr>
</table>
<?php require_once 'calendrier.php'; ?>

<!-- Tracé des icones -->	
<?php
// calcul des minimax
$date_end = time();
$date_beg = $date_end - (24 * 60 * 60);
$tmins =  array($numStations);
$tmaxs =  array($numStations);
for($i = 0;$i < $numStations;$i++)
	{$device_id = $mydevices[$i]["_id"];
	$module_id = $mydevices[$i]["modules"][0]["_id"];
	$params = array("scale" => "1day"
    	, "type" => "min_temp,max_temp,date_min_temp,date_max_temp"
    	, "date_begin" => $date_beg
    	, "date_end" => $date_end
    	, "optimize" => true
    	, "device_id" => $device_id
    	, "module_id" => $module_id);
    $tmesure = $client->api("getmeasure", "POST", $params);	
    if(count($tmesure))
    	{$tmin[$i] = degree2($tmesure[0]['value'][0][0]);   
    	$tmax[$i] = degree2($tmesure[0]['value'][0][1]);
    	$dtmin[$i] = $tmesure[0]['value'][0][2];
    	$dtmax[$i] = $tmesure[0]['value'][0][3];
    	}
    else
       {$tmin[$i] = $tmax[$i] = '-'; 
       $dtmin[$i] = $dtmax[$i] = time(); 
       }
    }

$arrow = ($moonpercent >= 0 && $moonpercent < 50) ? '&#10138;':'&#10136;'; 
$txt = tr('Phase lunaire');
echo("<table id= 'icones' style='margin-left:auto; margin-right:auto;  margin-top:-2px; margin-bottom:0px; padding:0px '>
		<tr>\n");
echo "<td>\n";	
/*
echo "<table class='icone'>\n";	
echo "<tr><td colspan='2' class='th'>$txt</td>\n";
echo("</tr><tr><td rowspan='2' ><img src=$moonimg ALT='moon' style='height:100px;vertical-align:bottom;'/></td>\n");
echo "<td class='pl'>phase:$moonpercent% &nbsp; $arrow</td>\n";
echo "</tr><tr><td class='pl'>lum:$lumen%</td>\n";
echo "</tr></table>\n"; 
*/
echo "<table class='icone'>\n";	
echo "<tr><td colspan='2' class='th'>$txt</td>\n";
echo("</tr><tr><td ><img src=$moonimg ALT='moon' style='height:100px;vertical-align:bottom;'/></td>\n");
echo "<td style='font-size:13px;'>phase:$moonpercent% <br> $arrow <br>lumen:$lumen%</td>\n";
echo "</tr></table>\n"; 


echo "</td>\n";	
// Tracé des icones    
for($i = 0;$i < $numStations;$i++)
	{$res = $last_mesures[$i]["modules"];
	echo("<td>");
	$t0 = $tmin[$i];
	$t1 = $tmax[$i];
	$dt0 = $dtmin[$i];
	$dt1 = $dtmax[$i];
	fill($i,$devicelist["devices"][$i],$mydevices[$i],$res,$tmin[$i],$tmax[$i],$dtmin[$i],$dtmax[$i]);
	echo("</td>");
	}
echo("</tr></table>");	
?>

<!-- trace des menus et de la Google map -->

<table class='container'>

<tr>
    <td class='container'>
        <?php
        drawMenuModules('280px');
        drawMenuStation('280px');
        ?>
    </td>
<!-- GOOGLE MAP -->

    <script>    
    <?php echo ("var numStation = \"$numStations\";"); ?>
    var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
    y = w.innerHeight|| e.clientHeight|| g.clientHeight,
    x = w.innerWidth || e.clientWidth || g.clientWidth;
    ico = d.getElementById("icones");
    hico = ico.offsetHeight;
    hico = Math.max(hico,144);
    lico = ico.offsetWidth;
    y -= (hico + 45);
    // 3 + 2 + 183
    var larMin = lico - 2*190 -8;
    var larMax = x - 2*190;
    var lar = Math.max(680,larMin);
    lar = Math.min(lar,larMax); 
    //alert('lico'+lico+' lar:'+lar);
    var t = "<td><div id='map_canvas'  class='map_canvas' style='margin-left:auto; margin-left:auto; margin-top:-2px; width:"+lar+"px; height:"
    t += y+"px; border:solid 2px gray;'> </div>";
    document.write(t);
 </script>
    </td>
    <td class='container'>
        <?php
        drawMenuHist('280px');
        drawMenuCompare('280px');
        ?>	
    </td>
</tr>

</table>

<?php  drawLogoutBack(); ?>

</body>
</html>


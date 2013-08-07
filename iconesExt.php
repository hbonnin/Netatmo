<?php
require_once 'NAApiClient.php';
session_start(); 
?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<title>Stations Netatmo</title>
<meta charset='utf-8'>
<meta http-equiv="Refresh" content="1800">
<link rel='icon' href='favicon.ico'>
<link type='text/css' rel='stylesheet'  href='style.css'>
<link rel='stylesheet' media='screen' type='text/css' href='calendrierBleu.css'>
<script type='text/javascript' src='size.js'></script>

<?php
require_once 'Config.php';
require_once 'initClient.php';
require_once 'Geolocalize.php';
require_once 'fill.php';
require_once 'menus.php';
date_default_timezone_set("Europe/Paris");

initClient();
$client = $_SESSION['client'];
$mydevices = $_SESSION['mydevices']; 
$numStations = $mydevices["num"];
$devicelist = getDevicelist();
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
for($i = 0;$i < $numStations;$i++)
	{$altitude = $mydevices[$i]['latlng']['altitude'];
    $place = $mydevices[$i]['address'];
    $int_name = $mydevices[$i]["module_name"];
	$ext_name = $mydevices[$i]["modules"][0]["module_name"];
	if($place == "BAD")		
    	$p = '<b>' . $mydevices[$i]['station_name'] . ' (' . $altitude . 'm)' . '</b><br>';
	else
    	$p = '<b>' . $place[1] . '</b><br><font size=2>' . $place[0] .  '<br> (' . $altitude . 'm</font>)'; 

    $res = $last_mesures[$i]["modules"];
	$temp = $res[0]['Temperature'];
	$hum = $res[0]['Humidity'];
	$co2 = $res[0]['CO2'];
	$db  = $res[0]['Noise'];
	$red = "style='color:#900'";
	$green = "style='color:#070'";
	$orange = "style='color: brown'";
	$violet = "style='color:#007'";
	
	$tabINT = "<td class='name'>$int_name</td> <td $red>$temp</td> <td $green>$hum</td>  <td $orange>$co2</td> <td></td> <td $violet>$db</td>";	
	$temp = $res[1]['Temperature'];
	$hum = $res[1]['Humidity'];
	$pres = intval($res[0]['Pressure'] + .5);
	$tabEXT = "<td class='name'>$ext_name</td> <td $red>$temp</td> <td $green>$hum</td> <td></td> <td>$pres</td>";	

    $label[$i]  = "<table class='bulle'>"
        .'<caption >'. $p .'</caption>'
        ."<tr><th style='width:60px;''></th> <th>T°</th> <th>H%</th> <th>Co2</th> <th>P mb</th> <th>Db</th></tr>"
        .'<tr>' . $tabINT .'</tr>'
        .'<tr>' . $tabEXT .'</tr>';
        
        $nModule = count($res);
        for($j = 2; $j < $nModule ; $j++)
            {$name = $res[$j]["module_name"];
            $temp = $res[$j]["Temperature"];
            $hum = $res[$j]["Humidity"];
            $co2 = $res[$j]["CO2"];		
            $tabMOD = "<tr><td class='name'>$name</td> <td $red>$temp</td> <td $green>$hum</td> <td $orange>$co2</td> <td></td> <td></td></tr>";
            $label[$i] = $label[$i] . '<tr>' . $tabMOD .'</tr>';        
            }
    $label[$i] = $label[$i] . '</table>';       
    $slabel[$i] = $res[1]['Temperature'] . '°';	  // usilise pour les marker    	  
	}	

?>
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
        zoom: 4,
        center: center.getCenter(),
        disableDefaultUI: true,
        disableDoubleClickZoom: true,
        scaleControl: true,
            scaleControlOptions: {position: google.maps.ControlPosition.TOP_LEFT},
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
    	{$tmin[$i] = $tmesure[0]['value'][0][0];   
    	$tmax[$i] = $tmesure[0]['value'][0][1];
    	$dtmin[$i] = $tmesure[0]['value'][0][2];
    	$dtmax[$i] = $tmesure[0]['value'][0][3];
    	}
    else
       $tmins[$i] = $tmaxs[$i] = '-'; 
    }

echo("<table style='margin-left:auto; margin-right:auto;  margin-top:-2px; margin-bottom:0px; padding:0px '>
		<tr>");

// Tracé des icones    
for($i = 0;$i < $numStations;$i++)
	{$res = $last_mesures[$i]["modules"];
	echo("<td>");
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
        $num = count($devicelist["devices"]);
        drawMenuStation('255px');
        ?>
    </td>
<!-- GOOGLE MAP -->
<?php
if($_SESSION['Ipad'])
    $h = '490px'; 
else
    $h = '510px';
    echo("<td><div id='map_canvas'  class='map_canvas' style='margin-left:auto; margin-left:auto; margin-top:-2px; width:680px; height:$h; border:solid 2px gray;'> </div>");
?>
    </td>
    <td class='container'>
        <?php
        drawMenuCompare('255px');
        ?>	
    </td>
</tr>
</table>

<?php  drawLogoutBack(); ?>

</body>
</html>


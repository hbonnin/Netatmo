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
$numStations = count($devicelist["devices"]);
$latitude = array($numStations);
$longitude  = array($numStations);
$label = array($numStations);


for($i = 0;$i < $numStations;$i++)
	{$latitude[$i] = $devicelist["devices"][$i]["place"]["location"][1];
    $longitude[$i] = $devicelist["devices"][$i]["place"]["location"][0];
    $res = $mesures[$i]["modules"];
    $txt = sprintf("%3.1f° %d%% %dmb",$res[1]['Temperature'],$res[1]['Humidity'],$res[0]['Pressure']);
    $label[$i] = $txt;
	}	




echo("
<!DOCTYPE html>
<html>
  <head>
    <meta name='viewport' content='initial-scale=1.0, user-scalable=no' />
    <meta charset='utf-8'>

    <style type='text/css'>
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map_canvas { height: 100% }
    </style>
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
    
	function createMarker(pos,label,map) 
	    {var marker = new StyledMarker({styleIcon:new StyledIcon(StyledIconTypes.BUBBLE,{color:'00ff00',text:label}),position:pos,map:map});
		marker.setZIndex(100);
	   	google.maps.event.addListener(marker, 'click', function() 
       		{marker.setZIndex(marker.getZIndex()-1);});    	 
    	return marker;  
		}

      function initialize() {
  		var markers = [];
  		var lat = [];
  		var lng = [];
  		var LatLng = [];
  		var label = [];
");
  		echo("var num = $numStations;\n");
  		for($i = 0;$i < $numStations;$i++)
  			{echo("lat[$i] = $latitude[$i];\n");
  			echo("lng[$i] = $longitude[$i];\n");
  			echo("label[$i] = \"$label[$i]\";\n");
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
			markers[i] = createMarker(LatLng[i],label[i],map)

	}

    </script>
  </head>
  <body onload='initialize()'>
  <div id='map_canvas' style='width: 640px; height: 100%; '></div>
  </body>
</html>
");
?>

<?php
/*
Authentication to Netatmo Server with the user credentials grant
*/

require_once 'NAApiClient.php';
require_once 'Config.php';

if(isset($argc) && $argc >1)
	{$stationId=0; $nday=30;}
else {	 
$stationId = $_GET["station"];
$date0 = $_GET["date0"];
$txt = explode("/",$date0);
$date1 = $txt[1] . "/" . $txt[0] . "/" . $txt[2];
$timestamp = strtotime($date1);
$nday=(time() - strtotime($date1))/(24*60*60); 
$nday = intval($nday +.5);
if($nday <= 0)$nday = 1;
}


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
$mesures = $helper->GetLastMeasures($client,$devicelist);
$stat0 = $mesures[$stationId]['station_name'];


$device_id = $devicelist["devices"][$stationId]["_id"];
$module_id = $devicelist["devices"][$stationId]["modules"][0]["_id"];


date_default_timezone_set("Europe/Paris");
$date_end = time();
$date_beg = time() - ($nday * 24 * 60 * 60);


// exterieur
    $params = array("scale" => "1day"
    , "type" => "min_temp,max_temp,date_min_temp,date_max_temp"
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "limit"    => $nday
    , "device_id" => $device_id
    , "module_id" => $module_id);
    $meas = $client->api("getmeasure", "POST", $params);
// interieur    
    $params = array("scale" => "1day"
    , "type" => "min_temp,max_temp,date_min_temp,date_max_temp"
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "limit"    => $nday
    , "device_id" => $device_id);
    $meas1 = $client->api("getmeasure", "POST", $params);
 
    
function tip($temp,$tempDate)
	{return sprintf('%04.1f ...  %s',$temp,date("H:i",$tempDate)); 
	}    

// Temperatures extérieures
echo("
<html>
  <head>
  <meta HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf8\">
    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <script type='text/javascript'>
      google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
              var data = new google.visualization.DataTable();
	          data.addColumn('string', 'Date');
        	  data.addColumn('number', 'Tmax');
        	  data.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  data.addColumn('number', 'Tmin');        	  
        	  data.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });
        	  data.addColumn('number', 'Tmoy');
");
			$index0 = 	count($meas)-1;
			for($index = 0; $index <= $index0;++$index)
				{$num = count($meas[$index]["value"]);
				$date_beg = $meas[$index]["beg_time"];
				$date = date('d/m/Y',$date_beg);	
            	for($i=0; $i <$num;++$i)
            		{$itime = $date_beg + ($i * 24 * 60 * 60);
            		$idate = date("d/m",$itime);   
            		$tmin = $meas[$index]["value"][$i][0];
                	$tmax = $meas[$index]["value"][$i][1];  
                	$tminDate = $meas[$index]["value"][$i][2];  
                	$tmaxDate = $meas[$index]["value"][$i][3];
                	$minTip = tip($tmin,$tminDate);         	               
                	$maxTip = tip($tmax,$tmaxDate);         	               
                	echo("data.addRow([\"$idate\",$tmax,'$maxTip',$tmin,'$minTip',($tmin+$tmax)/2]);\n");                
                	}
                }
                $titleEXT = '"Extérieur: ' . $stat0 .'"';

// températures Intérieures                
echo("
              var data1 = new google.visualization.DataTable();
	          data1.addColumn('string', 'Date');
        	  data1.addColumn('number', 'Tmax'); 
        	  data1.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });        	       	  
        	  data1.addColumn('number', 'Tmin'); 
        	  data1.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });
  
");
 			$index0 = 	count($meas1)-1;
 			for($index = 0; $index <= $index0;++$index)
				{$num1 = count($meas1[$index]["value"]);
				$date_beg = $meas1[$index]["beg_time"];
				$date = date('d/m/Y',$date_beg);	
            	for($i=0; $i <$num1;++$i)
            		{$itime = $date_beg + ($i * 24 * 60 * 60);
            		$idate = date("d/m",$itime);   
            		$tmin = $meas1[$index]["value"][$i][0];
                	$tmax = $meas1[$index]["value"][$i][1];
                	$tminDate = $meas1[$index]["value"][$i][2];  
                	$tmaxDate = $meas1[$index]["value"][$i][3];
                	$minTip = tip($tmin,$tminDate);         	               
                	$maxTip = tip($tmax,$tmaxDate);         	               
                	echo("data1.addRow([\"$idate\",$tmax,'$maxTip',$tmin,'$minTip']);\n");                
                	}
                }	                     
			$titleINT = '"Intérieur: ' . $stat0 .'"';
             
echo("                   
             var chartExterieur = new google.visualization.LineChart(document.getElementById('chartExterieur'));
             chartExterieur.draw(data, { title: $titleEXT,focusTarget: 'category',colors: ['red', 'blue','gray']});
             var chartInterieur = new google.visualization.LineChart(document.getElementById('chartInterieur'));
             chartInterieur.draw(data1, { title: $titleINT ,focusTarget: 'category',colors: ['red', 'blue','yellow']});
            
             }  
          </script>
  </head>
  <body>
  	<center>
  	<!--<h2>Températures extrêmes de $stat0 depuis $date $num $num1</h2>-->
  	<!--
    <table>
    <tr><td id='chartInterieur_div' style='width: 600px; height: 600px; border:2px solid white;'>
    </td><td id='chartExterieur_div' style='width: 600px; height: 600px; border:2px solid white;'>
    </td></tr></table>-->
    
    <div id='chartInterieur' style='width:100%; height:50%;'></div>
    <div id='chartExterieur' style='width:100%; height:50%; '></div>

    </center>
  </body>
</html>
");
?>
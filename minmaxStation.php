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
$date = date('d/m/Y',$date_beg);


    $params = array("scale" => "1day"
    , "type" => "min_temp,max_temp,date_min_temp,date_max_temp"
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "limit"    => $nday
    , "device_id" => $device_id
    , "module_id" => $module_id);
    $meas = $client->api("getmeasure", "POST", $params);
    
    
    $params = array("scale" => "1day"
    , "type" => "min_temp,max_temp"
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "limit"    => $nday
    , "device_id" => $device_id);
    $meas1 = $client->api("getmeasure", "POST", $params);

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
");
			$index = 	count($meas)-1;
			$num = count($meas[$index]["value"]);
			$date_beg = $meas[$index]["beg_time"];
			$date = date('d/m/Y',$date_beg);	
            for($i=0; $i <$num;++$i)
            	{$itime = $date_beg + ($i * 24 * 60 * 60);
            	$idate = date("d/m/y",$itime);   
            	$tmin = $meas[$index]["value"][$i][0];
                $tmax = $meas[$index]["value"][$i][1];  
                $tminDate = $meas[$index]["value"][$i][2];  
                $tmaxDate = $meas[$index]["value"][$i][3];
                $tminTime = date("H:i",$tminDate);  
                $tmaxTime = date("H:i",$tmaxDate);  
                $minTip = sprintf('%04.1f ...  %s',$tmin,$tminTime);           	               
                $maxTip = sprintf('%04.1f ...  %s',$tmax,$tmaxTime);           	               
                echo("data.addRow([\"$idate\",$tmax,'$maxTip',$tmin,'$minTip']);\n");                
                }
// températures Intérieures                
echo("
              var data1 = new google.visualization.DataTable();
	          data1.addColumn('string', 'Date');
        	  data1.addColumn('number', 'Tmax'); 
        	  data1.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });        	       	  
        	  data1.addColumn('number', 'Tmin'); 
        	  data1.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });
  
");
 			$index = 	count($meas1)-1;
			$num = count($meas1[$index]["value"]);
			$date_beg = $meas1[$index]["beg_time"];
			$date = date('d/m/Y',$date_beg);	
            for($i=0; $i <$num;++$i)
            	{$itime = $date_beg + ($i * 24 * 60 * 60);
            	$idate = date("d/m/y",$itime);   
            	$tmin = $meas1[$index]["value"][$i][0];
                $tmax = $meas1[$index]["value"][$i][1];
                echo("data1.addRow([\"$idate\",$tmax,'$tmax',$tmin,'$tmin']);\n");               
                }                                  
echo("                   
             var chartExterieur = new google.visualization.LineChart(document.getElementById('chartExterieur_div'));
             chartExterieur.draw(data, { title: 'Extérieur',focusTarget: 'category',colors: ['red', 'blue']});
              var chartInterieur = new google.visualization.LineChart(document.getElementById('chartInterieur_div'));
             chartInterieur.draw(data1, { title: 'Intérieur' ,focusTarget: 'category',colors: ['red', 'blue']});
            
             }  
          </script>
  </head>
  <body>
  	<center>
  	<h2>Températures extrêmes de $stat0</h2>
    <table>
    <tr><td id='chartInterieur_div' style='width: 600px; height: 600px; border:2px solid white;'>
    </td><td id='chartExterieur_div' style='width: 600px; height: 600px; border:2px solid white;'>
    </td></tr></table>
    </center>
  </body>
</html>
");
?>
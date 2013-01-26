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
$nday = intval($nday + .5);
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
$device_id = $devicelist["devices"][$stationId]["_id"];
$module_id = $devicelist["devices"][$stationId]["modules"][0]["_id"];
$mesures = $helper->GetLastMeasures($client,$devicelist);
$stat0 = $mesures[$stationId]['station_name'];


date_default_timezone_set("Europe/Paris");
$date_end = time();
$date_beg = time() - ($nday * 24 * 60 * 60);
$date = date('d/m/Y',$date_beg);


    $params = array("scale" => "1day"
    , "type" => "min_temp,max_temp,Humidity"
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "limit"    => $nday
    , "device_id" => $device_id
    , "module_id" => $module_id);
    $meas = $client->api("getmeasure", "POST", $params);
    
    
    $params = array("scale" => "1day"
    , "type" => "min_temp,Humidity,CO2,Pressure,max_noise"
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "limit"    => $nday
    , "device_id" => $device_id);
    $meas1 = $client->api("getmeasure", "POST", $params);


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
        	  data.addColumn('number', 'Tmin °');
        	  data.addColumn('number', 'Tmax °');        	  
        	  data.addColumn('number', 'Humidity %');  
              data.addColumn({type: 'string', role: 'tooltip'});
      	  
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
                $hum = $meas[$index]["value"][$i][2]/4;
                $tooltip = sprintf('%s: \nHumidité %%:%d',$idate , ($hum)*4);            
                echo("data.addRow([\"$idate\",$tmin,$tmax,$hum,'$tooltip']);\n");                
                }
			$title = "'Extérieur'";  
echo("
              var data1 = new google.visualization.DataTable();
	          data1.addColumn('string', 'Date');
        	  data1.addColumn('number', 'Tmin °');
        	  data1.addColumn('number', 'Humidity %');
        	  data1.addColumn('number', 'CO2 ppm');
        	  data1.addColumn({type: 'string', role: 'tooltip'});
        	  data1.addColumn('number', 'Pres mb');
        	  data1.addColumn({type: 'string', role: 'tooltip'});
        	  data1.addColumn('number', 'Noise db');  
        	    
");
  			$index = 	count($meas1)-1;
			$num = count($meas1[$index]["value"]);
			$date_beg = $meas1[$index]["beg_time"];
			$date = date('d/m/Y',$date_beg);	
            for($i=0; $i <$num;++$i)
            	{$itime = $date_beg + ($i * 24 * 60 * 60);
            	$idate = date("d/m/y",$itime);   
            	$temp = $meas1[$index]["value"][$i][0];
                $hum = $meas1[$index]["value"][$i][1];
                $co = $meas1[$index]["value"][$i][2];
                $co = min($co,1000);$co /= 10;
                $tipCO2 = sprintf('%s: \nCO2 ppm:%d',$idate,$co *10);                
                $pres = $meas1[$index]["value"][$i][3]-950;
                $tipPRES = sprintf('%s: \nPression mb:%d',$idate,$pres +950);
                $noise = $meas1[$index]["value"][$i][4];
                echo("data1.addRow([\"$idate\",$temp,$hum,$co,'$tipCO2',$pres,'$tipPRES',$noise]);\n");                
                }
			$title1 = "'Intérieur'";
			
       	                    
                                   
echo("                   
             var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
             chart.draw(data, { title: $title,colors: ['blue', 'red', 'green', '#f3b49f', '#f6c7b6'] });
              var chart1 = new google.visualization.LineChart(document.getElementById('chart1_div'));
             chart1.draw(data1, { title: $title1 ,colors: ['red', 'green', 'orange', '#aaaaaa', '#aaaaff'] });
            
             }  
          </script>
  </head>
  <body>
    <center>
  	<h2>Graphiques de $stat0</h2>

    <table>
    <tr><td id='chart1_div' style='width: 600px; height: 600px; border:2px solid white;'>
    </td><td id='chart_div' style='width: 600px; height: 600px; border:2px solid white;'>
    </td></tr></table>
    </center>
  </body>
</html>
");
?>
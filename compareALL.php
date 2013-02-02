<?php

require_once 'NAApiClient.php';
require_once 'Config.php';

$date0 = $_POST['date0'];
$txt = explode("/",$date0);
$date1 = $txt[1] . "/" . $txt[0] . "/" . $txt[2];
$timestamp = strtotime($date1);
$nday=(time() - strtotime($date1))/(24*60*60); 
$nday = intval($nday + .5);
if($nday <= 0)$nday = 2;

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
$numStations = count($devicelist["devices"]);

$view = array($numStations);
for($i = 0 ;$i < $numStations; $i++)
	$view[$i] = 0;

foreach($_POST['stats'] as $chkbx)
		{$view[$chkbx] = 1;
		}
$numview = 0;
for($i = 0 ;$i < $numStations; $i++)
	if($view[$i])++$numview;
	
if($numview == 0){echo("Il faut au moins une station...");return;} 	

date_default_timezone_set("Europe/Paris");
$date_end = time();
$date_beg = time() - ($nday * 24 * 60 * 60); 
$date = date('d/m/Y',$date_beg);

$mesure = array($numStations);
$index = array($numStations);
$nums = array($numStations);
$dateBeg = array($numStations);
$nameStations = array($numStations);
$ii = array($numStations);


for($i = 0;$i < $numStations;$i++)
	{$device_id = $devicelist["devices"][$i]["_id"];
	$module_id = $devicelist["devices"][$i]["modules"][0]["_id"];
    $params = array("scale" => "1day"
    , "type" => "min_temp,max_temp"
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "limit"    => $nday
    , "device_id" => $device_id
    , "module_id" => $module_id);
    $mesure[$i] = $client->api("getmeasure", "POST", $params);
    $index[$i] = count($mesure[$i])-1;
    $nums[$i] = count($mesure[$i][$index[$i]]["value"]); 
    $dateBeg[$i] = $mesure[$i][$index[$i]]["beg_time"];
    $nameStations[$i] = $mesures[$i]['station_name'];
    }
    
$num = 0;
for($i = 0;$i < $numStations;$i++)
	$num = max($num,$nums[$i]);

$date_beg = $dateBeg[0];
for($i = 1;$i < $numStations;$i++)
	$date_beg = min($date_beg,$dateBeg[$i]);

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
");
	        for($i = 0;$i < $numStations;$i++)
	          	{if($view[$i] == 0)continue;
	          	$ii[$i] = 0;
	          	$name = explode(" ",$nameStations[$i]);
	          	echo("data.addColumn('number', \"$name[0]\");\n");
				echo("data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });\n");        	       	  
	          	}
	        echo("data.addColumn('number', '');\n"); 	   	
            for($i=0; $i <$num;++$i)
            	{$itime = $date_beg + ($i * 24*60*60);
            	$idate = date("d/m/y",$itime);
				echo("data.addRow([\"$idate\"");
            	for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		$tmin0 = '';
            		if($itime >= $dateBeg[$j]) {$tmin0 = $mesure[$j][$index[$j]]["value"][$ii[$j]++][0];}
            		echo(",$tmin0,'$tmin0'"); 
            		}
            	echo(",0]);\n"); 	
                }
				echo("data.removeColumn(1+2*$numview);\n");				 

echo("
              var data1 = new google.visualization.DataTable();
	          data1.addColumn('string', 'Date');
");
			
	        for($i = 0;$i < $numStations;$i++)
	          	{if($view[$i] == 0)continue;
	          	$ii[$i] = 0;
	          	$name = explode(" ",$nameStations[$i]);
	          	echo("data1.addColumn('number', \"$name[0]\");\n");
				echo("data1.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });\n");        	       	  
	          	}
	        echo("data1.addColumn('number', '');\n"); 	 
            for($i=0; $i <$num;++$i)
            	{$itime = $date_beg + ($i * 24*60*60);
            	$idate = date("d/m/y",$itime);
				echo("data1.addRow([\"$idate\"");
            	for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		$tmin0 = '';
            		if($itime >= $dateBeg[$j]) {$tmin0 = $mesure[$j][$index[$j]]["value"][$ii[$j]++][1];}
            		echo(",$tmin0,'$tmin0'"); 
            		}
            	echo(",0]);\n"); 	
                }
				echo("data1.removeColumn(1+2*$numview);\n");				 
 	
                                  
echo("                   
             var chart = new google.visualization.LineChart(document.getElementById('chartMin'));
             chart.draw(data, {title: 'Températures minimales extérieures' ,colors: ['blue','red', 'green', 'orange', '#aa00aa', '#f6c7b6'],focusTarget: 'category'} );
             var chart1 = new google.visualization.LineChart(document.getElementById('chartMax'));
             chart1.draw(data1, {title: 'Températures maximales extérieures' ,colors: ['blue','red', 'green', 'orange', '#aa00aa', '#f6c7b6'],focusTarget: 'category'} );
            
             }  
          </script>
  </head>
  <body>
  	<center>
  	<!--
  	<h2>Températures extérieures depuis le $date</h2>
    <table>
    <tr><td id='chart_div' style='width: 600px; height: 600px; border:1px solid white;'>
    </td>
    <td id='chart1_div' style='width: 600px; height: 600px; border:1px solid white;'>
    </td>
    </tr></table>-->
    
    <div id='chartMin' style='width:100%; height:50%;'></div>
    <div id='chartMax' style='width:100%; height:50%; '></div>

    </center>
  </body>
</html>
");
?>
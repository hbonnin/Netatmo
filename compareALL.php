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
if($nday <= 0)$nday = 2;
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
$numStations = count($devicelist["devices"]);
/*
$nameStations = array($numStations);
for($i = 0;$i < $numStations;$i++)
	$nameStations[$i] = $mesures[$i]['station_name'];
*/
date_default_timezone_set("Europe/Paris");
$date_end = time();
$date_beg = time() - ($nday * 24 * 60 * 60);
$date = date('d/m/Y',$date_beg);

$mesure = array($numStations);
$index = array($numStations);
$nums = array($numStations);
$dateBeg = array($numStations);
$nameStations = array($numStations);


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
    //$stat = $mesures[$i]['station_name'];
	//$arr = str_split($stat,12);
    //$nameStations[$i] = $arr[0];
    $nameStations[$i] = $mesures[$i]['station_name'];
    }
$num = 0;
for($i = 0;$i < $numStations;$i++)
	$num = max($num,$nums[$i]);
$date_beg = $dateBeg[0];
for($i = 1;$i < $numStations;$i++)
	$date_beg = min($date_beg,$dateBeg[$i]);
$date = date('d/m/Y',$date_beg);
 

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
	          	{$name = explode(" ",$nameStations[$i]);
	          	echo("data.addColumn('number', \"$name[0]\");\n");
				echo("data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });\n");        	       	  
	          	}

			for($i = $numStations;$i < 4;$i++) 	        	
	        	{echo("data.addColumn('number', '');\n"); 
				echo("data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });\n");        	       	  
	          	}
	        echo("data.addColumn('number', '');\n"); 	 
  	
			$i0 = $i1 = $i2 = $i3 = 0;
            for($i=0; $i <$num;++$i)
            	{$itime = $date_beg + ($i * 24*60*60);
            	$idate = date("d/m/y",$itime);
            	$tmin0 = $tmin1 = $tmin2 = $tmin3 = '';
            	if($itime >= $dateBeg[0]) {$tmin0 = $mesure[0][$index[0]]["value"][$i0++][0];} 
            	if($numStations > 1 && $itime >= $dateBeg[1]) {$tmin1 = $mesure[1][$index[1]]["value"][$i1++][0];} 
            	if($numStations > 2 && $itime >= $dateBeg[2]) {$tmin2 = $mesure[2][$index[2]]["value"][$i2++][0];} 
            	if($numStations > 3 && $itime >= $dateBeg[3]) {$tmin3 = $mesure[3][$index[3]]["value"][$i3++][0];} 
                echo("data.addRow([\"$idate\",$tmin0,'$tmin0',$tmin1,'$tmin1',$tmin2,'$tmin2',$tmin3,'$tmin3',0]);\n");                            
                }
            for($i = 9; $i > 2*$numStations;$i--)	
				echo("data.removeColumn($i);\n");				 

echo("
              var data1 = new google.visualization.DataTable();
	          data1.addColumn('string', 'Date');
");

	        for($i = 0;$i < $numStations;$i++)
	          	{$name = explode(" ",$nameStations[$i]);
	          	echo("data1.addColumn('number', \"$name[0]\");\n");
				echo("data1.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });\n");        	       	  
	          	}
			for($i = $numStations;$i < 4;$i++) 	        	
	        	{echo("data1.addColumn('number', '');\n");  
 				echo("data1.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });\n");        	       	  
	          	}
	        echo("data1.addColumn('number', '');\n"); 	 
 

 			$i0 = $i1 = $i2 = $i3 = 0;		
            for($i=0; $i <$num;++$i)
            	{$itime = $date_beg + ($i * 24 * 60 * 60);
            	$idate = date("d/m/y",$itime);
            	$tmin0 = $tmin1 = $tmin2 = $tmin3 = '';
            	if($itime >= $dateBeg[0]) {$tmin0 = $mesure[0][$index[0]]["value"][$i0++][1];} 
            	if($numStations > 1 && $itime >= $dateBeg[1]) {$tmin1 = $mesure[1][$index[1]]["value"][$i1++][1];} 
            	if($numStations > 2 && $itime >= $dateBeg[2]) {$tmin2 = $mesure[2][$index[2]]["value"][$i2++][1];} 
            	if($numStations > 3 && $itime >= $dateBeg[3]) {$tmin3 = $mesure[3][$index[3]]["value"][$i3++][1];} 
                echo("data1.addRow([\"$idate\",$tmin0,'$tmin0',$tmin1,'$tmin1',$tmin2,'$tmin2',$tmin3,'$tmin3',0]);\n");                
                }                
            for($i = 9; $i > 2*$numStations;$i--)
				echo("data1.removeColumn($i);\n");				 
                                  
echo("                   
             var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
             chart.draw(data, {title: 'Minimales' ,colors: ['blue','red', 'green', 'orange', '#eeeeee', '#f6c7b6'],focusTarget: 'category'} );
             var chart1 = new google.visualization.LineChart(document.getElementById('chart1_div'));
             chart1.draw(data1, {title: 'Maximales' ,colors: ['blue','red', 'green', 'orange', '#eeeeee', '#f6c7b6'],focusTarget: 'category'} );
            
             }  
          </script>
  </head>
  <body>
  	<center>
  	<h2>Températures extérieures depuis le $date</h2>
    <table>
    <tr><td id='chart_div' style='width: 600px; height: 600px; border:1px solid white;'>
    </td>
    <td id='chart1_div' style='width: 600px; height: 600px; border:1px solid white;'>
    </td>
    </tr></table>
    </center>
  </body>
</html>
");
?>
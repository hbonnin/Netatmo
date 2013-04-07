<?php

require_once 'NAApiClient.php';
require_once 'Config.php';

if(isset($argc) && $argc >1)
	{$stationId=0; $nday=140;
	$man = 1;
	}
else
	{$date0 = $_POST['date0'];
	$txt = explode("/",$date0);
	$date1 = $txt[1] . "/" . $txt[0] . "/" . $txt[2];
	$nday=(time() - strtotime($date1))/(24*60*60); 
	$nday = intval($nday + .5);
	$man = 0;	
	}
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
if($man == 1)
	$view[0] = $view[3] = 1;
else	
	{foreach($_POST['stats'] as $chkbx)
		{$view[$chkbx] = 1;
		}
	}
$numview = 0;  // Nombre de stations cochées
for($i = 0 ;$i < $numStations; $i++)
	if($view[$i])++$numview;
	
if($numview == 0){echo("Il faut au moins une station...");return;} 	

date_default_timezone_set("Europe/Paris");
$date_end = time();
$date_beg = $date_end - ($nday * 24 * 60 * 60); 
$date = date('d/m/Y',$date_beg);

$mesure = array($numStations);
$dateBeg = array($numStations);
$nameStations = array($numStations);
$ii = array($numStations);
$keys = array($numStations);
$nmesures = array($numStations);

$minDateBeg = $date_end;
$num = 0;
for($i = 0;$i < $numStations;$i++)
	{if($view[$i] == 0)continue;
	$device_id = $devicelist["devices"][$i]["_id"];
	$module_id = $devicelist["devices"][$i]["modules"][0]["_id"];
    $params = array("scale" => "1day"
    , "type" => "min_temp,max_temp"
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "limit"    => $nday
    , "optimize" => false
    , "device_id" => $device_id
    , "module_id" => $module_id);  
    $mesure[$i] = $client->api("getmeasure", "POST", $params);
    $nameStations[$i] = $mesures[$i]['station_name'];
    $keys[$i] = array_keys($mesure[$i]);
    $dateBeg[$i] = $keys[$i][0];
    $minDateBeg = min($minDateBeg,$dateBeg[$i]);    
    $nmesures[$i] = count($keys[$i]);
    $num = max($num,$nmesures[$i]);
    }
    
//print_r($keys[0]);
if($man)
{
for($i=0; $i < count($keys[0]);++$i)
	{$key = $keys[0][$i];  
	$idate = date("d/m/y H:i",$key);
	$tmin = $mesure[0][$key][0];
	echo("$i:$key date:$idate tmin:$tmin<br>\n");
	} 
for($i=0; $i < count($keys[3]);++$i)
	{$key = $keys[3][$i];  
	$idate = date("d/m/y H:i",$key);
	$tmin = $mesure[3][$key][0];
	echo("$i:$key date:$idate tmin:$tmin<br>\n");
	} 
$idate = date("d/m/y H:i",$minDateBeg);
echo("debut:$idate");
}

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
	        $itime = $minDateBeg;
	        //$itime = $timestamp;	   	
            for($i = 0; $i < $num;++$i)
            	{$idate = date("d/m/y",$itime);
				echo("data.addRow([\"$idate\"");
            	for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		$tmin0 = '';   
            		$key = $keys[$j][$ii[$j]];         		
            		if(abs($key - $itime) < 2*60*60) //changement d'horaire
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j];           			
            			$tmin0 = $mesure[$j][$key][0];
            			}
            		echo(",$tmin0,'$tmin0'"); 
            		}          		
            	echo(",0]);\n"); 	
            	$itime += 24*60*60;
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
	        $itime = $minDateBeg;	   	
            for($i=0; $i <$num;++$i)
            	{$idate = date("d/m/y",$itime);
				echo("data1.addRow([\"$idate\"");
            	for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		$tmin0 = '';        		
            		$key = $keys[$j][$ii[$j]]; 
            		if(abs($key - $itime) < 2*60*60)
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j];            			
            			$tmin0 = $mesure[$j][$key][1];
            			}

            		echo(",$tmin0,'$tmin0'"); 
            		}          		
            	echo(",0]);\n"); 	
            	$itime += 24*60*60;
                }
				echo("data1.removeColumn(1+2*$numview);\n");				 

                                  
echo("                   
             var chart = new google.visualization.LineChart(document.getElementById('chartMin'));
             chart.draw(data, {title: 'Températures minimales extérieures' ,colors: ['blue','red', 'green', 'orange', '#aa00aa', '#f6c7b6'],focusTarget: 'category'} );
             var chart1 = new google.visualization.LineChart(document.getElementById('chartMax'));
             chart1.draw(data1, {title: 'Températures maximales extérieures' ,colors: ['blue','red', 'green', 'orange', '#aa00aa', '#f6c7b6'],focusTarget: 'category'} );
            
             } // draw chart 
            
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
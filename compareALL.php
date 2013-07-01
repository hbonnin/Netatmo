<?php

require_once 'NAApiClient.php';
require_once 'Config.php';
require_once 'initClient.php';
require_once 'menus.php';

session_start();
$client = $_SESSION['client'];
$devicelist = $_SESSION['devicelist'];
$mesures = $_SESSION['mesures'];
date_default_timezone_set("UTC");

$date0 = $_POST["date0"];
$txt = explode("/",$date0);
$date_beg = mktime(0,0,0,$txt[1],$txt[0],$txt[2]);
$date1 = $_POST["date1"];
$txt = explode("/",$date1);
$date_end = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]);
$interval = $_POST["select"];
$numStations = count($devicelist["devices"]);

$view = array($numStations);
for($i = 0 ;$i < $numStations; $i++)
	$view[$i] = 0;

foreach($_POST['stats'] as $chkbx)
	$view[$chkbx] = 1;

$numview = 0;  // Nombre de stations cochées
for($i = 0 ;$i < $numStations; $i++)
	if($view[$i])++$numview;
	
if($numview == 0){echo("Il faut au moins une station...");return;} 	

$mesure = array($numStations);
$dateBeg = array($numStations);
$nameStations = array($numStations);
$ii = array($numStations);
$keys = array($numStations);
$nmesures = array($numStations);

$minDateBeg = $date_end;

for($i = 0;$i < $numStations;$i++)
	{if($view[$i] == 0)continue;
	$device_id = $devicelist["devices"][$i]["_id"];
	$module_id = $devicelist["devices"][$i]["modules"][0]["_id"];
    $params = array("scale" => $interval
    , "type" => "min_temp,max_temp,date_min_temp,date_max_temp"
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "optimize" => false
    , "device_id" => $device_id
    , "module_id" => $module_id);  
    $mesure[$i] = $client->api("getmeasure", "POST", $params);
    $nameStations[$i] = $mesures[$i]['station_name'];
    $keys[$i] = array_keys($mesure[$i]);
    $dateBeg[$i] = $keys[$i][0];
    $minDateBeg = min($minDateBeg,$dateBeg[$i]);    
    $nmesures[$i] = count($keys[$i]);
    }
    
if($interval == "1week")
	$inter = 7;
else
	$inter = 1;	

date_default_timezone_set("Europe/Paris");
function tip($temp,$tempDate)
	{return sprintf('%4.1f (%s)',$temp,date("H:i",$tempDate)); 
	}    


echo("
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
  <head>
  <title>Stations Netatmo</title>
  <meta charset='utf-8'>
  <link rel='icon' href='favicon.ico' />
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
	        $i = 0;	
            	do {
            	$idate = date("d/m/y",$itime);
				echo("data.addRow([\"$idate\"");
            	for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		$tmin0 = $tip = '';   
            		$key = $keys[$j][$ii[$j]];         		
            		if(abs($key - $itime) < 2*60*60) //changement d'horaire
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j];           			
            			$tmin0 = $mesure[$j][$key][0];
            			$tip = tip($tmin0,$mesure[$j][$key][2]);
            			}        		
            		echo(",$tmin0,'$tip'"); 
            		}          		
            	echo(",0]);\n"); 	
            	$itime += $inter*24*60*60;
            	++$i;
                }while($itime < $date_end);
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
	        $i = 0;   	
            do	{$idate = date("d/m/y",$itime);
				echo("data1.addRow([\"$idate\"");
            	for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		$tmin0 = $tip = '';        		
            		$key = $keys[$j][$ii[$j]]; 
            		if(abs($key - $itime) < 2*60*60)
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j];            			
            			$tmin0 = $mesure[$j][$key][1];
              			$tip = tip($tmin0,$mesure[$j][$key][3]);
          			}
            		echo(",$tmin0,'$tip'"); 
            		}          		
            	echo(",0]);\n"); 	
            	$itime += $inter*24*60*60;
            	++$i;
                }while($itime < $date_end);
				echo("data1.removeColumn(1+2*$numview);\n");				 

                                  
?>                  
             var chart = new google.visualization.LineChart(document.getElementById('chartMin'));
             var chart1 = new google.visualization.LineChart(document.getElementById('chartMax'));
             chart1.draw(data1, {title: 'Températures maximales extérieures' ,pointSize:3,colors: ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6'],focusTarget:'category',backgroundColor:'#f0f0f0',chartArea:{left:'5%',top:35,width:'83%',height:"70%"} });
             chart.draw(data,   {title: 'Températures minimales extérieures' ,pointSize:3,colors: ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6'],focusTarget:'category',backgroundColor:'#f0f0f0',chartArea:{left:'5%',top:35,width:'83%',height:"70%"} });
            
             } // draw chart 
            
          </script>
<script type='text/javascript' src='calendrier.js'></script> 
<link rel='stylesheet' media='screen' type='text/css' title='Design' href='calendrierBleu.css' />
<link type='text/css' rel='stylesheet'  href='style.css'/>
<script type='text/javascript' src='validate.js'></script>	
  </head>
 
  <body>  
<?php
$dateend = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d'),date('y')));
$datebeg = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d')-30,date('y')));
$num = count($devicelist["devices"]);
?>
<table style='padding:0px; width:100%;'>

<tr>
<td  style='padding:0px; vertical-align:bottom;'>
<?php
drawMenuCompare();
?>
</td>
    <td  style='padding:0px; vertical-align:bottom; width:100%;'>
    <div id='chartMin' class='chartMinMax' ></div></td>
 </tr>
 
 <tr>
 <td style='padding:0px; vertical-align:bottom;'>
<?php
drawMenuStation();
?>
 </td>
    <td style='padding:0px; vertical-align:bottom; width:100%;'>
    <div id='chartMax' class='chartMinMax' ></div></td>
</tr>
</table>


<!-- Invisible table for calendar --> 
<table class="ds_box"  id="ds_conclass" style="display: none;" >
	<caption id="id_caption" class='ds_caption'>xxxx</caption>
	<tr><td id="ds_calclass">aaa</td></tr>
</table>

  </body>
</html>


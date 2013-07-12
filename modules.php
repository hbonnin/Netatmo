<!DOCTYPE html SYSTEM 'about:legacy-compat'>
  <head>
	<title>Stations Netatmo</title>
	<meta charset='utf-8'>
	<link rel='icon' href='favicon.ico'>
    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
	<link type='text/css' rel='stylesheet'  href='style.css'>
	<script type='text/javascript' src='validate.js'></script>	
</head>
<?php
require_once 'NAApiClient.php';
require_once 'Config.php';
require_once 'initClient.php';
require_once 'menus.php';

session_start();
date_default_timezone_set("Europe/Paris");
initClient();
$client = $_SESSION['client'];
$devicelist = $_SESSION['devicelist'];
$mesures = $_SESSION['mesures'];

// $stationNum station utilise
$stationNum = $_GET['stationNum'];
if(isset($_POST['selectStation']))
    {$changedStation = ($stationNum != $_POST['selectStation']);
    $stationNum = $_POST['selectStation'];
    }
$_SESSION['stationId'] = $stationNum;

$res = $mesures[$stationNum]["modules"];
$numStations = count($res);
$device = $devicelist['devices'][$stationNum];
$device_id = $device['_id'];

for($i = 0;$i < $numStations;$i++)
    $nameStations[$i] = $res[$i]['module_name'];

// module principal + modules extra
// 0 station
// 1 module exterieur
// extra modules
$numModules = count($device['modules']);
$modules_id[0] = $device_id;
for($i = 1;$i < $numStations;$i++)    
    $modules_id[$i] = $device['modules'][$i -1]['_id'];
    
if(isset($_POST["date0"]))  
    $date0 = $_POST["date0"]; 
else
    $date0 = $_SESSION['datebeg'];  
if(isset($_POST["date1"]))  
    $date1 = $_POST["date1"];
else
    $date1 = $_SESSION['dateend']; 
    
$txt = explode("/",$date0);
$date_beg = mktime(0,0,0,$txt[1],$txt[0],$txt[2]);        
$txt = explode("/",$date1);
$date_end = mktime(0,0,0,$txt[1],$txt[0],$txt[2]);    
$_SESSION['datebeg'] = date("d/m/Y",$date_beg); 
$_SESSION['dateend'] = date("d/m/Y",$date_end); 

if(isset($_SESSION['selectMesureModule']))
    $selectMesure = $_SESSION['selectMesureModule'];
 else 	
	{$selectMesure = 'T';
	$_SESSION['selectMesureModule'] = $selectMesure;
	}
if(isset($_POST['selectMsesure']))
    $selectMesure = $_POST['selectMsesure'];

if(isset($_POST["select"]))
    {$interval = $_POST["select"];
    $_SESSION['selectedInter'] = $interval;    
    }
 else   
    {$interval = $_SESSION['selectedInter']; 
    $interval = checkSelect($interval,'M');
    }
if($interval == "1week")
	{$inter = 7*24*60*60;
	$tinter = '1 semaine';
	}
else if($interval == "1day")
	{$inter = 24*60*60;	
	$tinter = '1 journée';
	}
else //3hours	
	{$inter = 3*60*60;
    $tinter = '3 heures';
    }

$CO2 = 0;	
$HTime = 1;
if($selectMesure == 'T')
    {if($inter == 3*60*60)
        {$type = 'min_temp,max_temp';$HTime = 0;}
    else
        $type = 'min_temp,max_temp,date_min_temp,date_max_temp';
    $titre = 'Température ';
    }
else if($selectMesure == 'H')
    {if($inter == 3*60*60)
        {$type = 'min_hum,max_hum';$HTime = 0;}
    else
        $type = 'min_hum,max_hum,date_min_hum,date_max_hum';
    $titre = 'Humidité ';
    }    
else if($selectMesure == 'C') // ni max ni min CO2
    {//$type = 'CO2';
    if($inter == 3*60*60)
        {$type = 'min_co2,max_co2';$HTime = 0;}
    else
        $type = 'min_co2,max_co2,date_min_co2,date_max_co2';    
    $titre = 'CO2 ';
    $CO2 = 1;
    }    
$_SESSION['selectMesureModule'] = $selectMesure; 


if(isset($_SESSION['viewModule']))
    {$view = $_SESSION['viewModule'];
    if($view['station'] !=  $stationNum)
        {for($i = 0 ;$i < $numStations; $i++)
            $view[$i] = 1;    
        $view['station'] = $stationNum;
        }
    }
else
    for($i = 0 ;$i < $numStations; $i++)
        $view[$i] = 1;
       
if(isset($_POST['selectedModules']) && $changedStation == false)
    {for($i = 0 ;$i < $numStations; $i++)
	    $view[$i] = 0;
    foreach($_POST['selectedModules'] as $chkbx)
	    $view[$chkbx] = 1;
	}
	
$view['station'] = 	$stationNum;
$_SESSION['viewModule'] = $view;   

if($CO2)$view[1] = 0;

$numview = 0;  // Nombre de stations cochées
for($i = 0 ;$i < $numStations; $i++)
	if($view[$i])++$numview;
if($numview == 0)
    $view[$i] = $numview = 1;
		
$mesure = array($numStations);
$dateBeg = array($numStations);
$ii = array($numStations);
$keys = array($numStations);
$nmesures = array($numStations);

$minDateBeg = $date_end;
$numKeys = 0;
for($i = 1;$i < $numStations;$i++)
	{if($view[$i] == 0)continue;
	$moduleId = $modules_id[$i];
    $params = array("scale" => $interval
    , "type" =>  $type
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "optimize" => false
    , "device_id" => $device_id
    , "module_id" => $moduleId);  
    $mesure[$i] = $client->api("getmeasure", "POST", $params);
    $keys[$i] = array_keys($mesure[$i]);
    $numKeys = max($numKeys,count($keys[$i]));
    $dateBeg[$i] = $keys[$i][0];
    $minDateBeg = min($minDateBeg,$dateBeg[$i]);    
    $nmesures[$i] = count($keys[$i]);    
    }
if($view[0])
    {$params = array("scale" => $interval
    , "type" => $type
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "optimize" => false
    , "device_id" => $device_id); 
    $mesure[0] = $client->api("getmeasure", "POST", $params);
    $keys[0] = array_keys($mesure[0]);
    $numKeys = max($numKeys,count($keys[0]));
    $dateBeg[0] = $keys[0][0];
    $minDateBeg = min($minDateBeg,$dateBeg[0]);    
    $nmesures[0] = count($keys[0]);    
    }


/**************************************************************/
function tip($temp,$tempDate)
	{return sprintf('%4.1f (%s)',$temp,date("H:i",$tempDate)); 
	}    

echo("
    <script type='text/javascript'>
      google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart);

      function drawChart() {
              var data = new google.visualization.DataTable();              
	          data.addColumn('string', 'Date');
");
	        {for($i = 0;$i < $numStations;$i++)
	          	{if($view[$i] == 0)continue;
	          	$ii[$i] = 0; 
	          	$name = explode(" ",$nameStations[$i]);
	          	echo("data.addColumn('number', \"$name[0]\");\n");
				echo("data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });\n");        	       	  
	          	}	          	
	        echo("data.addColumn('number', '');\n"); 
	        $visupt = '';
            if($numKeys <= 73)$visupt = ",pointSize:3";	
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
            			    {$tmin0 = $mesure[$j][$key][0];
            			    if($HTime)
            			        $tip = tip($tmin0,$mesure[$j][$key][3]);
            			    else
            			        $tip = tip($tmin0,$itime);
            			    }
            			}        		
            		echo(",$tmin0,'$tip'"); 
            		}          		
            	echo(",0]);\n"); 	
            	$itime += $inter;
            	++$i;
                }while($itime < $date_end);
				echo("data.removeColumn(1+2*$numview);\n");				 
                }
            {                
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
            	do {
            	$idate = date("d/m/y",$itime);
				echo("data1.addRow([\"$idate\"");
            	for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		$tmin0 = $tip = '';   
            		$key = $keys[$j][$ii[$j]];         		
            		if(abs($key - $itime) < 2*60*60) //changement d'horaire
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j]; 
            			    {$tmin0 = $mesure[$j][$key][1];
              			    if($HTime)          			    
            			        $tip = tip($tmin0,$mesure[$j][$key][3]);
            			    else
            			        $tip = tip($tmin0,$itime);
            			    }            			    }
            			        		
            		echo(",$tmin0,'$tip'"); 
            		}          		
            	echo(",0]);\n"); 	
            	$itime += $inter;
            	++$i;
                }while($itime < $date_end);
				echo("data1.removeColumn(1+2*$numview);\n");				 
                }

$title = $titre . 'minimum'. ' (' . $tinter . ')';                
$title1 = $titre . 'maximal'. ' (' . $tinter . ')';
$param = "focusTarget:'category',backgroundColor:'#f0f0f0',chartArea:{left:\"5%\",top:25,width:\"85%\",height:\"75%\"}";
$param = $param . ",fontSize:10,titleTextStyle:{fontSize:12,color:'#303080',fontName:'Times'}";
			echo("                                   
             var chartMin = new google.visualization.LineChart(document.getElementById('chart0'));
             chartMin.draw(data ,{title: '$title' $visupt,colors: ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6'],$param });
             var chartMax = new google.visualization.LineChart(document.getElementById('chart1'));
             chartMax.draw(data1 ,{title: '$title1' $visupt,colors: ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6'],$param });
			");
/*
echo("
    google.visualization.events.addListener(chartMin, 'select', selectHandler);
    function selectHandler() {
        alert('A table row was selected');
        }
");
*/
/**************************************************************/
			
?>            
             } // draw chart 
            
          </script>
<script type='text/javascript' src='calendrier.js'></script> 
<link rel='stylesheet' media='screen' type='text/css' title='Design' href='calendrierBleu.css'>

<?php

echo("<body>");
		echo("
		<table class='ds_box'  id='ds_conclass' style='display: none;' >
		<caption id='id_caption' class='ds_caption'>xxxx</caption>
		<tr><td id='ds_calclass'>aaa</td></tr>
		</table>
		");
$hh = 310;
$h = $hh . 'px';
$h1 = $hh+2 .'px';
echo("<table style='padding:0px; width:100%; margin-bottom:-5px;'>
	<tr>
	<td  style='padding:0px; vertical-align:bottom;'>
	");
$num = count($devicelist["devices"]);  

drawMenuCompare($h1);   
echo("
	</td>
		<td  style='padding:0px; vertical-align:bottom; width:100%;'>
		<div id='chart0' class='chart' style='height:$h'></div></td>
	 </tr>
	 <tr>
	    <td style='padding:0px; vertical-align:bottom;'>
	 ");
drawMenuModules($h1);
echo("</td>    	 
		<td style='padding:0px; vertical-align:bottom; width:100%;'>
		<div id='chart1' class='chart' style='height:$h'></div></td>
	</tr>
	</table>
	");
	
drawLogoutBack(); 
?>
</body>
</html>

	


<!DOCTYPE html SYSTEM 'about:legacy-compat'>
  <head>
	<title>Stations Netatmo</title>
	<meta charset='utf-8'>
	<link rel='icon' href='favicon.ico'>
    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
	<script type='text/javascript' src='calendrier.js'></script> 
	<link rel='stylesheet' media='screen' type='text/css' title='Design' href='calendrierBleu.css'>
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
$stationNum = $_GET['stationNum'];
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
    {$date0 = $_POST["date0"];
    $txt = explode("/",$date0);
    $date_beg = mktime(0,0,0,$txt[1],$txt[0],$txt[2]);    
    }
else    
    $date_beg = mktime(0, 0, 0, date('m') , date('d')-30,date('y'));
    //$date_beg = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d')-30,date('y')));
    
if(isset($_POST["date1"]))  
    {$date1 = $_POST["date1"];
    $txt = explode("/",$date1);
    $date_end = mktime(0,0,0,$txt[1],$txt[0],$txt[2]);    
    }
else    
    $date_end = mktime(0, 0, 0, date('m') , date('d'),date('y'));
    //$date_end = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d'),date('y')));

if(isset($_POST['selectMsesure']))
    $selectMesure = $_POST['selectMsesure'];
 else 	
	$selectMesure = 'H';

$CO2 = 0;	
if($selectMesure == 'T')
    {$type = 'min_temp,max_temp,date_min_temp,date_max_temp';
    $titre = 'Température ';
    }
else if($selectMesure == 'H')
    {$type = 'min_hum,max_hum,date_min_hum,date_max_hum';
    $titre = 'Humidité ';
    }    
else if($selectMesure == 'C') // ni max ni min CO2
    {$type = 'CO2';
    $titre = 'CO2 ';
    $CO2 = 1;
    }    
    
$view = array($numStations);
for($i = 0 ;$i < $numStations; $i++)
	$view[$i] = 0;
if(isset($_POST['stats']))
    foreach($_POST['stats'] as $chkbx)
	    $view[$chkbx] = 1;
else
    for($i = 0 ;$i < $numStations; $i++)
	    $view[$i] = 1;
if($CO2)	
    $view[1] = 0;
$numview = 0;  // Nombre de stations cochées
for($i = 0 ;$i < $numStations; $i++)
	if($view[$i])++$numview;

if($numview == 0)
    {echo("Il faut au moins un module...");
    $view[0] = 1;
    } 	

if(isset($_POST["select"]))
    $interval = $_POST["select"];
 else   
    $interval = '1day';
    
if($interval == "1week")
	$inter = 7*24*60*60;
else if($interval == "1day")
	$inter = 24*60*60;	
else //3hours	
	$inter = 3*60*60;
		
$mesure = array($numStations);
$dateBeg = array($numStations);
$ii = array($numStations);
$keys = array($numStations);
$nmesures = array($numStations);

$minDateBeg = $date_end;

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
    $dateBeg[0] = $keys[0][0];
    $minDateBeg = min($minDateBeg,$dateBeg[0]);    
    $nmesures[0] = count($keys[0]);    
    }
echo("<pre>");
//print_r($view);
//print_r($nameStations);
//print_r($modules_id);
//print_r($mesure[2]);
//print_r($keys[2]);

echo("</pre>");

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
            			    if($CO2)
            			        {$tip =  sprintf('%4.1f',$tmin0); 
            			        $tmin0 = min($tmin0,1000);
            			        }
            			    else 
            			        $tip = tip($tmin0,$mesure[$j][$key][3]);
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
            			    {if($CO2)
            			        {$tmin0 = $mesure[$j][$key][0];
            			        $tip =  sprintf('%4.1f',$tmin0); 
            			        $tmin0 = min($tmin0,1000);
            			        }
            			    else
            			        {$tmin0 = $mesure[$j][$key][1];
            			        $tip = tip($tmin0,$mesure[$j][$key][3]);
            			        }
            			    }
            			}        		
            		echo(",$tmin0,'$tip'"); 
            		}          		
            	echo(",0]);\n"); 	
            	$itime += $inter;
            	++$i;
                }while($itime < $date_end);
				echo("data1.removeColumn(1+2*$numview);\n");				 
                }
if(!$CO2)
    {$title = $titre . 'minimale';                
    $title1 = $titre . 'maximale';
    }
else
    {$title = $titre;                
    $title1 = $titre;
    }

$param = "focusTarget:'category',backgroundColor:'#f0f0f0',chartArea:{left:\"5%\",top:25,width:\"85%\",height:\"75%\"}";
$param = $param . ",fontSize:10,titleTextStyle:{fontSize:12,color:'#303080',fontName:'Times'}";
			echo("                                   
             var chartMin = new google.visualization.LineChart(document.getElementById('chart0'));
             chartMin.draw(data ,{title: '$title' ,pointSize:3,colors: ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6'],$param });
             var chartMax = new google.visualization.LineChart(document.getElementById('chart1'));
             chartMax.draw(data1 ,{title: '$title1' ,pointSize:3,colors: ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6'],$param });
			");
/**************************************************************/
			
?>            
             } // draw chart 
            
          </script>

<?php

$datebeg = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d')-30,date('y')));
$dateend = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d'),date('y')));


echo("<body>");
$h = '315px';
echo("<table style='padding:0px; width:100%; margin-bottom:-5px;'>
	<tr>
	<td rowspan='2' style='padding:0px; vertical-align:bottom;'>
	");
drawMenuModules($stationNum,$h);

echo("
	</td>
		<td  style='padding:0px; vertical-align:bottom; width:100%;'>
		<div id='chart0' class='chart' style='height:$h'></div></td>
	 </tr>
	 <tr>
		<td style='padding:0px; vertical-align:bottom; width:100%;'>
		<div id='chart1' class='chart' style='height:$h'></div></td>
	</tr>
	</table>
	");
	
$draw = true;
drawLogoutBack($draw); 
?>
</body>
</html>

	


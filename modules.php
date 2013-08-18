<!DOCTYPE html SYSTEM 'about:legacy-compat'>
  <head>
	<title>Stations Netatmo</title>
	<meta charset='utf-8'>
	<link rel='icon' href='favicon.ico'>
    <script src='https://www.google.com/jsapi'></script>
    <script src='js/size.js'></script>
	<link type='text/css' rel='stylesheet'  href='style.css'>

<?php
require_once 'NAApiClient.php';
require_once 'Config.php';
require_once 'initClient.php';
require_once 'menus.php';

session_start();
date_default_timezone_set("Europe/Paris");
initClient();
$client = $_SESSION['client'];
// $stationNum station utilise
$stationNum = $_GET['stationNum']; // toujours défini
$changedSation = false;
if(isset($_POST['selectStation']))
    {$changedSation = ($_POST['selectStation'] != $stationNum); 
    $stationNum = $_POST['selectStation'];
    }
   
$_SESSION['stationId'] = $stationNum;

$mydevices = $_SESSION['mydevices']; 
$device = $mydevices[$stationNum];
$numStations = $device["modules"]["num"] + 1; 
$nameStation = $device['station_name'];

// device: tous les modules mais pas la station principale
// $numStations = $numModules + 1
// 0 station
// 1 module exterieur
// extra modules
$numModules = $device["modules"]["num"];
$device_id = $device['_id'];
$nameStations[0] = $device["module_name"]; 
$modules_id[0] = $device_id;
for($i = 1;$i < $numStations;$i++) // station et modules
    {$nameStations[$i] = $device["modules"][$i-1]["module_name"];
    $modules_id[$i] = $device['modules'][$i -1]['_id'];
    }

if(isset($_POST['selectMsesure']))
    $selectMesure = $_POST['selectMsesure'];
else
    $selectMesure = $_SESSION['selectMesureModule'];
    
if(isset($_POST["select"]))
    {$interval = $_POST["select"];
    $_SESSION['selectedInter'] = $interval;    
    }
 else   
    {$interval = $_SESSION['selectedInter']; 
    $interval = checkSelect($interval,'M');
    }
$opt = $_SESSION['MenuInterval']['opt']; 
$sel = selectIndex($opt,$interval);
$inter = $opt[$sel][2];
$tinter = $opt[$sel][1];

    
if(isset($_POST["date0"]))  
    $date0 = $_POST["date0"]; 
else
    $date0 = $_SESSION['datebeg'];  

if(isset($_POST["date1"]))  
    $date1 = $_POST["date1"];
else
    $date1 = $_SESSION['dateend'];   


if(isset($_GET['row']))// faire un zoom sur la date
    {$row = $_GET['row'];
    $date_beg = $_SESSION['date_beg'];
    $date_end = $_SESSION['date_end'];   
    $sel = selectIndex($opt,$interval);
    if($sel < maxIndexMenu('M'))
        {$beg = $_SESSION['begdata'];
        $dateRow = $beg + $row*$inter;
        $interval = $opt[$sel + 1][0]; 
        //$interval = checkSelect($interval,'M');
        $sel = selectIndex($opt,$interval);
        $inter = $opt[$sel][2];
        $tinter = $opt[$sel][1];	
        $date_beg = $dateRow - 50 * $inter;
        $date_end = $dateRow + 50 * $inter;  
        $date_end = min($date_end,time());
        $datebeg = date("d/m/Y",$date_beg); 
        $dateend = date("d/m/Y",$date_end);         
        $_SESSION['selectedInter'] = $interval; 
        $_SESSION['datebeg'] = date("d/m/Y",$date_beg); 
        $_SESSION['dateend'] = date("d/m/Y",$date_end); 
        $_SESSION['date_beg'] = $date_beg;
        $_SESSION['date_end'] = $date_end;  
        }
    else      
        {$numrows = ($date_end - $date_beg)/$inter;
        $rowBeg = max(0,$row - $numrows/10);
        $rowEnd = min($numrows - 1,$row + $numrows/10);
        $date_beg += $rowBeg * $inter;
        $date_end = $date_beg + $rowEnd * $inter;
        $date_end = min($date_end,time());
        $_SESSION['datebeg'] = date("d/m/Y",$date_beg); 
        $_SESSION['dateend'] = date("d/m/Y",$date_end); 
        $_SESSION['date_beg'] = $date_beg;
        $_SESSION['date_end'] = $date_end;  
        }
    }
else
    {chkDates($date0,$date1,$interval,$inter);	
    $date_beg = $_SESSION['date_beg'];
    $date_end = $_SESSION['date_end'];
    }


$CO2 = 0;	
$HTime = 1;
if($selectMesure == 'T')
    {$titre = 'Température ';
    $titre1 = 'Température ';
    if($inter >= 24*60*60)
        $type = 'min_temp,max_temp,date_min_temp,date_max_temp';
    else if($inter == 3*60*60)
        {$type = 'min_temp,max_temp';$HTime = 0;}
    else
        {$type = 'Temperature,Humidity';$HTime = 0;
        $titre1 = 'Humidité '; 
        }
    }
else if($selectMesure == 'H')
    {$titre = 'Humidité ';
    $titre1 = 'Humidité ';    
    if($inter >= 24*60*60)
         $type = 'min_hum,max_hum,date_min_hum,date_max_hum';
    else if($inter == 3*60*60)
        {$type = 'min_hum,max_hum';$HTime = 0;}
    else 
        {$type = 'Humidity,CO2';$HTime = 0;
        $titre1 = 'CO2';$CO2 = 1;
        }
    }    
else if($selectMesure == 'C') 
    {$titre = 'CO2 ';
    $titre1 = 'CO2 ';    
    if($inter >= 24*60*60)
        $type = 'min_co2,max_co2,date_min_co2,date_max_co2';  
    else if($inter == 3*60*60)
        {$type = 'min_co2,max_co2';$HTime = 0;}
    else 
        {$type = 'CO2,Temperature';$HTime = 0;
        $titre1 = 'Température ';
        }
    $CO2 = 1;
    }    
$_SESSION['selectMesureModule'] = $selectMesure; 
$viewModules = $_SESSION['viewModules'];

if(isset($_POST['selectedModules']) && $changedSation == false)
    {for($i = 0 ;$i < $numStations; $i++)
	    $viewModules[$stationNum][$i] = 0;
    foreach($_POST['selectedModules'] as $chkbx)
	    $viewModules[$stationNum][$chkbx] = 1;
    $numview = 0;  // Nombre de stations cochées
    for($i = 0 ;$i < $numStations; $i++)
        if($viewModules[$stationNum][$i])++$numview;
    if($numview == 0)
        $viewModules[$stationNum][0] = $numview = 1; 
    $viewModules[$stationNum]["numView"] = $numview;    
    $_SESSION['viewModules'] = $viewModules;	
	}  
$view = $viewModules[$stationNum];    
$numview = $view["numView"];
    
if($CO2)
    {if($view[1])--$numview;
    $view[1] = 0;
    if($numview == 0)
        $view[0] = $numview = 1;     
    }
	
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
    
    try
        {$mesure[$i] = $client->api("getmeasure", "POST", $params);
        }
    catch(NAClientException $ex)
    	{echo "An error happend while trying to retrieve your last measures\n";
    	 logMsg ("An error happend while trying to retrieve your last measures <br>
    	    type:$type scale:$interval device_id:$device_id module_id:$moduleId");
    	$_SESSION['ex'] = $ex;
        echo $ex->getMessage()."\n";
    	}
    if(count($mesure[$i]) == 0){$view[$i] = 0;--$numview;continue;}    
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
    if(count($mesure[0]) == 0)
        {drawCharts('M');
        echo("<script>document.getElementById('chart0').innerHTML = 'NO MEASURES';</script>");
        return;
        } 	  
    $keys[0] = array_keys($mesure[0]);
    $numKeys = max($numKeys,count($keys[0]));
    $dateBeg[0] = $keys[0][0];
    $minDateBeg = min($minDateBeg,$dateBeg[0]);    
    $nmesures[0] = count($keys[0]);   
    }
 
if($numKeys == 0)
    {drawCharts('M');
    echo("<script>document.getElementById('chart0').innerHTML = 'NO MEASURES';</script>");
    return;
    } 	
  
/**************************************************************/
$jour = array("Dim","Lun","Mar","Mer","Jeu","Ven","Sam"); 
function tip($temp,$tempDate)
	{return sprintf('%4.1f (%s)',$temp,date("H:i",$tempDate)); 
	}    

echo("
    <script>
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
	        $visupt = '';
            if($numKeys <= 48)$visupt = ",pointSize:3";	
	        $itime = $minDateBeg; 
			$beg = date("d/m/y",$minDateBeg); 
			$end = date("d/m/y",$date_end); 
	        $i = 0;	
            	do {
            	if($inter > 3*60*60)
            	    $idate = date("d/m/y",$itime);
            	else
            	    $idate = date("d/m H:i",$itime);
				echo("data.addRow([\"$idate\"");
            	for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		$tmin0 = $tip = '';   
            		$key = $keys[$j][$ii[$j]]; 
            		if(abs($key - $itime) < 2*$inter) //changement d'horaire
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j];           			
            			    {$tmin0 = $mesure[$j][$key][0];
            			    if($HTime)
            			        $tip = tip($tmin0,$mesure[$j][$key][3]);
            			    else
            			        $tip = $tmin0;//tip($tmin0,$itime);
            			    }
            			}        		
            		echo(",$tmin0,'$tip'"); 
            		}          		
            	echo(",0]);\n"); 	
            	$itime += $inter;
            	++$i;
                }while($itime <= $date_end);
				echo("data.removeColumn(1+2*$numview);\n");				 
/***********************************************************************************/                         
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
			$beg = date("d/m/y",$minDateBeg); 
			$end = date("d/m/y",$date_end); 
			$_SESSION['begdata'] = $minDateBeg;
	        $i = 0;	
            	do {
            	if($inter > 3*60*60)
            	    $idate = date("d/m/y",$itime);
            	else
            	    $idate = date("d/m H:i",$itime);
				echo("data1.addRow([\"$idate\"");
            	for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		$tmin0 = $tip = '';   
            		$key = $keys[$j][$ii[$j]];         		
            		if(abs($key - $itime) < 2*$inter) //changement d'horaire
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j]; 
            			    {$tmin0 = $mesure[$j][$key][1];
              			    if($HTime)          			    
            			        $tip = tip($tmin0,$mesure[$j][$key][3]);
            			    else
            			        $tip = $tmin0;//tip($tmin0,$itime);
            			    }            			    
            			}
            			        		
            		echo(",$tmin0,'$tip'"); 
            		}          		
            	echo(",0]);\n"); 	
            	$itime += $inter;
            	++$i;
                }while($itime <= $date_end);
				echo("data1.removeColumn(1+2*$numview);\n");				               
/**********************************************************************************************/
             if($inter > 30*60)    
                {$title = $nameStation.': '.$titre . 'minimale'. ' ('.$beg. ' - ' .$end.' @'. $tinter . ' '.$numKeys.' mesures)'; 
                $title1 = $nameStation.': '.$titre1 . 'maximale'. ' ('.$beg.' - '.$end. ' @' . $tinter . ' '.$numKeys.' mesures)'; 
                }
            else
                {$title = $nameStation.': '.$titre .  ' ('.$beg. ' - ' .$end.' @'. $tinter . ' '.$numKeys.' mesures)'; 
                $title1 = $nameStation.': '.$titre1.' ('.$beg.' -'.$end. ' @' . $tinter . ' '.$numKeys.' mesures)'; 
                }
            $param = "focusTarget:'category',backgroundColor:'#f0f0f0',chartArea:{left:\"5%\",top:25,width:\"85%\",height:\"75%\"}";
            $param .= ",fontSize:10,titleTextStyle:{fontSize:12,color:'#303080',fontName:'Times'}";
            $param .= ',tooltip: {isHtml: true},curveType:"function"';
            
?>
            colorMin = ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6'];
            colorMax = ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6'];
<?php   
			echo("                                   
             var chartMin = new google.visualization.LineChart(document.getElementById('chart0'));
             chartMin.draw(data ,{title: '$title' $visupt,colors:colorMin ,$param });
             var chartMax = new google.visualization.LineChart(document.getElementById('chart1'));
             chartMax.draw(data1 ,{title: '$title1' $visupt,colors: colorMax,$param });
			");
			

$menuModules = 'modules.php?stationNum=' .$_SESSION['stationId'];
echo("var menuModules = \"$menuModules\";\n");  
  
?>


<?php
echo("
    google.visualization.events.addListener(chartMin, 'select', MinClickHandler);        
     function MinClickHandler()
          {var selection = chartMin.getSelection();
          var num = colorMin.length;
          for (var i = 0; i < selection.length; i++) 
            {var item = selection[i];
            if(item.row != null && data.getNumberOfRows() > 20   && !isMobile())
                top.location.href=menuModules+'&row='+item.row;                        
            if(item.column != null && data.getNumberOfColumns() > 3) 
                {data.removeColumn(item.column); 
                var col0 = (item.column -1)/2;
                for(var col = col0;col < num-1;col++)
                    colorMin[col] = colorMin[col+1];                 
                data.removeColumn(item.column);
                chartMin.draw(data ,{title: '$title' $visupt,colors:colorMin,$param });               
                return;
                }
            }
        }
    google.visualization.events.addListener(chartMax, 'select', MaxClickHandler);        
     function MaxClickHandler()
          {var selection = chartMax.getSelection();
          var num = colorMax.length;
          for (var i = 0; i < selection.length; i++) 
            {var item = selection[i];
            if(item.row != null  && data1.getNumberOfRows() > 20   && !isMobile())
                top.location.href=menuModules+'&row='+item.row;                        
            if(item.column != null && data1.getNumberOfColumns() > 3)
                {data1.removeColumn(item.column);
                var col0 = (item.column -1)/2;
                for(var col = col0;col < num-1;col++)
                    colorMax[col] = colorMax[col+1];                 
                data1.removeColumn(item.column); 
                chartMax.draw(data1 ,{title: '$title1' $visupt,colors: colorMax,$param });
                return;
                }
            }
         }
");
			
?>            
             } // draw chart 
            
          </script>
<link rel='stylesheet' media='screen' type='text/css'  href='calendrierBleu.css'>
</head>
<body>
<?php
drawCharts('M');
?>
</body>
</html>

	


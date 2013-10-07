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
require_once 'translate.php';
session_start();
date_default_timezone_set($timezone);

initClient();
$client = $_SESSION['client'];
$mydevices = $_SESSION['mydevices'];
$Temperature_unit = $_SESSION['Temperature_unit'];
$cu = $Temperature_unit ? '°':' F';
if(isset($_POST['station'])) 
    $stationId = $_POST['station'];
else if(isset($_SESSION['stationId']))
    $stationId = $_SESSION['stationId'];
$_SESSION['stationId'] = $stationId;

date_default_timezone_set("UTC");

if(isset($_POST["date0"]))  
    $date0 = $_POST["date0"]; 
else
    $date0 = $_SESSION['datebeg'];  

if(isset($_POST["date1"]))  
    $date1 = $_POST["date1"];
else
    $date1 = $_SESSION['dateend']; 

if(isset($_POST["select"]))
    {$interval = $_POST["select"];
    $_SESSION['selectedInter'] = $interval;    
    }
 else   /* en fait inutil pour le moment */
    {$interval = $_SESSION['selectedInter']; 
    $interval = checkSelect($interval,'M');
    }
$opt = $_SESSION['MenuInterval']['opt']; 
$sel = selectIndex($opt,$interval);

if(isset($_POST["hist"]))
    {$hist = $_POST["hist"];
    $_SESSION['hist'] = $hist;
    }
else
    $hist = $_SESSION['hist'];

$inter = $opt[$sel][2];
$tinter = $opt[$sel][1];    

if(isset($_GET['row']))// faire un zoom sur la date
    {$row = $_GET['row'];
    $date_beg = $_SESSION['date_beg'];
    $date_end = $_SESSION['date_end'];   
    $sel = selectIndex($opt,$interval);
    if($sel < maxIndexMenu('C'))
        {$beg = $_SESSION['begdata'];
        $dateRow = $beg + $row*$inter;
        $interval = $opt[$sel + 1][0]; 
        //$interval = checkSelect($interval,'G');
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


$mydevices = $_SESSION['mydevices']; 
$device_id = $mydevices[$stationId]["_id"];
$module_id = $mydevices[$stationId]["modules"][0]["_id"];
$ext_name  = $mydevices[$stationId]["modules"][0]["module_name"];
$stat_name = $mydevices[$stationId]["station_name"];
$type = 'min_temp,max_temp';
$titre = 'Température ';
$mesure = array(2);
$dateBeg = array(2);
$ii = array(2);
$keys = array(2);
$nmesures = array(2);

$params = array("scale" => $interval
                , "type" => $type
                , "date_begin" => $date_beg
                , "date_end" => $date_end
                , "optimize" => false
                , "device_id" => $device_id
                , "module_id" => $module_id);  
                
$mesure[0] = $client->api("getmeasure", "POST", $params);

if($hist == 6)
    $delta = 184*24*60*60;
else
    $delta = 365*24*60*60;
$date_beg1 = $date_beg -$delta;
$date_end1 = $date_end -$delta;
$params = array("scale" => $interval
                , "type" => $type
                , "date_begin" => $date_beg1
                , "date_end" => $date_end1
                , "optimize" => false
                , "device_id" => $device_id
                , "module_id" => $module_id);  
$mesure[1] = $client->api("getmeasure", "POST", $params);
if(count($mesure[1]) == 0)
    {drawCharts('H');
    echo("<script>document.getElementById('chart0').innerHTML = 'NO MEASURES';</script>");
    return;
    } 
	    

for($i = 0; $i < 2; $i++)
    {$keys[$i] = array_keys($mesure[$i]);
    $dateBeg[$i] = $keys[$i][0];
    $nmesures[$i] = count($keys[$i]);
    }

$visupt = "";
if($nmesures[0] <= 48)$visupt = ",pointSize:3";	
date_default_timezone_set($timezone);


echo("
    <script>
      google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart);
      
      function drawChart() {
              var data = new google.visualization.DataTable();              
	          data.addColumn('string', 'Date');
");
	        for($i = 0;$i < 2;$i++)
	          	{$ii[$i] = 0; 
	          	//if($i == 0)
	          	    echo("data.addColumn('number', \"$stat_name\");\n");
/*	          	    
	          	else
	          	    {$txt = '-'.$hist.' '.tr('mois');
	          	    echo("data.addColumn('number', \"$txt\");\n");
	          	    }
*/	          	    
				echo("data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });\n");        	       	  
	          	}
	          	
	        echo("data.addColumn('number', '');\n"); 
	        $itime = $keys[0][0];
	        $_SESSION['begdata'] = $date_beg;
			$beg = date("d/m/y", $date_beg); 
			$end = date("d/m/y",$date_end); 	        	        
	        $i = 0;	
            	do {
            	$idate = date("d/m/y",$itime);
				echo("data.addRow([\"$idate\"");          
            	for($j = 0; $j < 2;$j++)
            		{$tmin0 = $tip = '';   
            		$key = $keys[$j][$ii[$j]]; 
            		$key1 = $key;
            		if($j == 1)$key1 += $delta;
            		if(abs($key1 - $itime) < 2*$inter) //changement d'horaire
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j]; 
            			$tmin0 = degree2($mesure[$j][$key][0]);
            			if($j == 0)
            			    {$t0 = $tmin0;
            			    $tip = sprintf('%4.1f',$tmin0).date(' d/m/y',$key);
            			    }
            			else
            			   $tip = sprintf('%4.1f %4.1f',$tmin0,$tmin0-$t0).date(' d/m/y',$key);
            			}        	
            		echo(",$tmin0,'$tip'"); 
            		}          		
            	echo(",0]);\n"); 	
            	$itime += $inter;
            	++$i;
                }while($itime < $date_end);
				echo("data.removeColumn(5);\n");	
				$tmesure = tr("mesure").'s';
				$title = tr($titre . 'minimale extérieure') . '  ('.$beg.' - '.$end.' @'. tr($tinter).")";                

echo("
              var data1 = new google.visualization.DataTable();
	          data1.addColumn('string', 'Date');
");
	        for($i = 0;$i < 2;$i++)
	          	{$ii[$i] = 0; 
	          	//if($i == 0)
	          	    echo("data1.addColumn('number', \"$stat_name\");\n");
/*	          	    
	          	else
	          	    {$txt = '-'.$hist.' '.tr('mois');
	          	    echo("data1.addColumn('number', \"$txt\");\n");
	          	    }
*/	          	    
				echo("data1.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });\n");        	       	  
	          	}
	          	
	        echo("data1.addColumn('number', '');\n"); 
	        $itime = $keys[0][0];	        
	        //$itime = $date_beg; 
	        $i = 0;	
            	do {
            	$idate = date("d/m/y",$itime);
				echo("data1.addRow([\"$idate\"");          
            	for($j = 0; $j < 2;$j++)
            		{$tmin0 = $tip = '';   
            		$key = $keys[$j][$ii[$j]]; 
            		$key1 = $key;
            		if($j == 1)$key1 += $delta;
            		if(abs($key1 - $itime) < 2*$inter) //changement d'horaire
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j]; 
            			$tmin0 = degree2($mesure[$j][$key][1]);
            			if($j == 0)
            			    {$t0 = $tmin0;
            			    $tip = sprintf('%4.1f',$tmin0).date(' d/m/y',$key);
            			    }
            			else
            			   $tip = sprintf('%4.1f %4.1f',$tmin0,$tmin0-$t0).date(' d/m/y',$key);
            			}        		
            		echo(",$tmin0,'$tip'"); 
            		}          		
            	echo(",0]);\n"); 	
            	$itime += $inter;
            	++$i;
                }while($itime < $date_end);
				echo("data1.removeColumn(5);\n");	
				$tmesure = tr("mesure").'s';
				$title1 = tr($titre . 'maximale extérieure') . '  ('.$beg.' - '.$end.' @'. tr($tinter).")";                



$param = "focusTarget:'category',backgroundColor:'#f0f0f0',chartArea:{left:\"5%\",top:25,width:\"85%\",height:\"75%\"}";
$param .= ",fontSize:10,titleTextStyle:{fontSize:12,color:'#303080',fontName:'Times'}";
$param .= ',tooltip: {isHtml: true},curveType:"function"';
?>
colorMin =  ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6','#aaaaaa'];
colorMax =  ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6','#aaaaaa'];
<?php
			echo("                                   
             var chartMin = new google.visualization.LineChart(document.getElementById('chart0'));
             chartMin.draw(data ,{title:'$title'$visupt,colors:colorMin,$param });
             var chartMax = new google.visualization.LineChart(document.getElementById('chart1'));
             chartMax.draw(data1,{title: '$title1'$visupt,colors: colorMax,$param });             
			");
?> 
} // draw chart 


            
</script>
<link rel='stylesheet' media='screen' type='text/css'  href='calendrierBleu.css'>   
</head>
 <body> 
 <?php
	drawCharts('H');
?>
</body>
</html>


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
else if(isset($_POST['selectStation']))
    $stationId = $_POST['selectStation'];        
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
    
if(isset($_POST['selectMsesure']))
    {$selectMesure = $_POST['selectMsesure'];
    $_SESSION['selectMesureCompare'] = $selectMesure; 
    }
else 
    $selectMesure = $_SESSION['selectMesureCompare'];
   
if($selectMesure == 'P')$selectMesure == 'T';   
if($selectMesure == 'T')
    {$type = 'min_temp,max_temp';
    $titre = 'Température ';
    }
else if($selectMesure == 'H' || $selectMesure == 'h')
    {$type = 'min_hum,max_hum';
    $titre = 'Humidité ';
    }   
    

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

$mesure = array(2);
$dateBeg = array(2);
$ii = array(2);
$keys = array(2);
$nmesures = array(2);

if($selectMesure == 'h')
    $params = array("scale" => $interval
                    , "type" => $type
                    , "date_begin" => $date_beg
                    , "date_end" => $date_end
                    , "optimize" => false
                    , "device_id" => $device_id);  
else
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
    $delta = 366*24*60*60;
$date_beg1 = $date_beg -$delta;
$date_end1 = $date_end -$delta;
if($selectMesure == 'h')
    $params = array("scale" => $interval
                    , "type" => $type
                    , "date_begin" => $date_beg1
                    , "date_end" => $date_end1
                    , "optimize" => false
                    , "device_id" => $device_id);
else
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

function tipHTML($stat_name,$t0,$t1,$date0,$date1)
	{global $cu,$type;
	if($type != 'min_temp,max_temp')$cu = '%';
	$tt0 = $tt1 = $tt = '';
	if(!empty($t0)) $tt0 = sprintf('  %4.1f%s',$t0,$cu);
	if(!empty($t1)) $tt1 = sprintf('  %4.1f%s',$t1,$cu);
	if(!empty($t0) && !empty($t1)) $tt = sprintf('  %4.1f%s',$t1 - $t0,$cu);
	return '<table style="width:130px; height:85px; padding:5px; margin:2px"><caption><b>' . $stat_name . '</b></caption><tr><td></td><td></td></tr>'
	. '<tr><td><i>'.$date0.'</i></td><td style="color: red; text-align:right;"><b>'.$tt0. "</b></td></tr>"
	. '<tr><td><i>'.$date1.'</i></td><td style="color: blue; text-align:right;"><b>'.$tt1. "</b></td></tr>"
	. '<tr><td><i>Diff</i></td><td style="color: green; text-align:right;"><b>' .$tt. "</b></td></tr>"
	. '</table>';
	}

echo("
    <script>
      google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart);
      
      function drawChart() {
              var data = new google.visualization.DataTable();              
	          data.addColumn('string', 'Date');
			  data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });  
");
            echo("data.addColumn('number', \"$stat_name\");\n");
            $txt = $stat_name.' -'.$hist.' '.tr('mois');
            echo("data.addColumn('number', \"$txt\");\n");
	        echo("data.addColumn('number', '');\n"); 
	        
	        $ii[0] = $ii[1] = 0; 	          	
	        $itime = $keys[0][0];
	        $_SESSION['begdata'] = $date_beg;
			$beg = date("d/m/y", $date_beg); 
			$end = date("d/m/y",$date_end); 	        	        
	        $i = 0;	
            	do {
            	$idate = date("d/m/y",$itime);
				echo("data.addRow([\"$idate\""); 
				$t0 = $t1 = $tip = '';
            	for($j = 0; $j < 2;$j++)
            		{$tmin0 = '';   
            		$key = $keys[$j][$ii[$j]]; 
            		$key1 = $key;
            		if($j == 1)$key1 += $delta;
            		if(abs($key1 - $itime) < 2*$inter) //changement d'horaire
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j]; 
            			$tmin0 = degree2($mesure[$j][$key][0]);
            			if($j == 0)
            			    {$t0 = $tmin0;
            			    $date0 = date('d/m/y',$key);
            			    }
            			else
            			    {$t1 = $tmin0;
            			    $date1 = date('d/m/y',$key);
            			    }
            			}        	
            		}          		
            	$tip = tipHTML($stat_name,$t0,$t1,$date0,$date1);
            	echo(",'$tip',$t0,$t1,0]);\n"); 	
            	$itime += $inter;
            	++$i;
                }while($itime < $date_end);
				echo("data.removeColumn(4);\n");	
				$tmesure = tr("mesure").'s';
				if($selectMesure == 'h')
				    $title = tr($titre . 'minimale intérieure') . '  ('.$beg.' - '.$end.' @'. tr($tinter).")";                
                else
				    $title = tr($titre . 'minimale extérieure') . '  ('.$beg.' - '.$end.' @'. tr($tinter).")";                

echo("
              var data1 = new google.visualization.DataTable();
	          data1.addColumn('string', 'Date');
			  data1.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });  
");
            echo("data1.addColumn('number', \"$stat_name\");\n");
            $txt = $stat_name.' -'.$hist.' '.tr('mois');
            echo("data1.addColumn('number', \"$txt\");\n");
	        echo("data1.addColumn('number', '');\n"); 
	        
	        $ii[0] = $ii[1] = 0; 	          	
	        $itime = $keys[0][0];	        
	        $i = 0;	
            	do {
            	$idate = date("d/m/y",$itime);
				echo("data1.addRow([\"$idate\"");  
				$t0 = $t1 = $tip = '';
            	for($j = 0; $j < 2;$j++)
            		{$tmin0 = '';   
            		$key = $keys[$j][$ii[$j]]; 
            		$key1 = $key;
            		if($j == 1)$key1 += $delta;
            		if(abs($key1 - $itime) < 2*$inter) //changement d'horaire
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j]; 
            			$tmin0 = degree2($mesure[$j][$key][1]);
            			if($j == 0)
            			    {$t0 = $tmin0;
            			    $date0 = date('d/m/y',$key);
            			    }
            			else
            			    {$t1 = $tmin0;
            			    $date1 = date('d/m/y',$key);
            			    }
            			}
            		}          		
             	$tip = tipHTML($stat_name,$t0,$t1,$date0,$date1);
            	echo(",'$tip',$t0,$t1,0]);\n"); 	
                $itime += $inter;
            	++$i;
                }while($itime < $date_end);
				echo("data1.removeColumn(4);\n");	
				$tmesure = tr("mesure").'s';
				if($selectMesure == 'h')				
				    $title1 = tr($titre . 'maximale intérieure') . '  ('.$beg.' - '.$end.' @'. tr($tinter).")";  
				else
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


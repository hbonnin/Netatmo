<!DOCTYPE html SYSTEM 'about:legacy-compat'>
  <head>
	<title>Stations Netatmo</title>
	<meta charset='utf-8'>
	<link rel='icon' href='favicon.ico'>
    <script src='https://www.google.com/jsapi'></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>        
    <script src='js/size.js'></script>
	<link type='text/css' rel='stylesheet'  href='style.css'>

<?php
define('__ROOT__', dirname(__FILE__));
require_once (__ROOT__.'/src/Netatmo/autoload.php');

require_once 'Config.php';
require_once 'initClient.php';
require_once 'menus.php';
require_once 'translate.php';

session_start();
checkToken();
$timezone = $_SESSION['timezone'];
date_default_timezone_set($timezone);
$client = $_SESSION['client'];
$mydevices = $_SESSION['mydevices'];
$cu = tu(); $pu = pru();
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

$numStations = $mydevices["num"];

if(isset($_POST['stats']))
    {for($i = 0 ;$i < $numStations; $i++)
        $view[$i] = 0;
    $numview = 0;  // Nombre de stations cochées      
    foreach($_POST['stats'] as $chkbx)
	    {$view[$chkbx] = 1;++$numview;}
	if($numview == 0)
	    {$numview = 1; $view[0] = 1;}
	$view['numview'] = $numview;
	}   
else
    {$view = $_SESSION['viewCompare'];
    $numview = $view['numview'];
    }
$_SESSION['viewCompare'] = $view;   	
	
if(isset($_POST['selectMsesure']))
    {$selectMesure = $_POST['selectMsesure'];
    $_SESSION['selectMesureCompare'] = $selectMesure; 
    }
else 
    $selectMesure = $_SESSION['selectMesureCompare'];
    
if($selectMesure == 'T')
    {$type = 'min_temp,max_temp,date_min_temp,date_max_temp';
    $titre = 'Température ';
    }
else if($selectMesure == 'H')
    {$type = 'min_hum,max_hum,date_min_hum,date_max_hum';
    $titre = 'Humidité ';
    $cu = '%';
    }   
else if($selectMesure == 'P')
    {$type = 'min_pressure,max_pressure,date_min_pressure,date_max_pressure';
    $titre = 'Pression ';
    $cu = $pu;
    }   

 
$mesure = array($numStations);
$dateBeg = array($numStations);
$nameStations = array($numStations);
$ii = array($numStations);
$keys = array($numStations);
$nmesures = array($numStations);

$minDateBeg = $date_end;
$maxMesures = 0;
for($i = 0;$i < $numStations;$i++)
	{if($view[$i] == 0)continue;
	$device_id = $mydevices[$i]["_id"];
	$module_id = $mydevices[$i]["modules"][0]["_id"];
	if($selectMesure == 'P')
	    {$params = array("scale" => $interval
        , "type" => $type
        , "date_begin" => $date_beg
        , "date_end" => $date_end
        , "optimize" => false
        , "device_id" => $device_id);
        $mesure[$i] = $client->api("getmeasure", "POST", $params);
	    }
	else
        {$params = array("scale" => $interval
        , "type" => $type
        , "date_begin" => $date_beg
        , "date_end" => $date_end
        , "optimize" => false
        , "device_id" => $device_id
        , "module_id" => $module_id);  
        $mesure[$i] = $client->api("getmeasure", "POST", $params);
        }
    if(count($mesure[$i]) == 0)
        {$view[$i] = 0; --$numview;continue;}
    $nameStations[$i] = $mydevices[$i]["station_name"];
    $keys[$i] = array_keys($mesure[$i]);
    $dateBeg[$i] = $keys[$i][0];
    $minDateBeg = min($minDateBeg,$dateBeg[$i]);    
    $nmesures[$i] = count($keys[$i]);
    $maxMesures = max($maxMesures,$nmesures[$i]);
    }

if($maxMesures == 0)
    {echo("</script>
        <link rel='stylesheet' media='screen' type='text/css'  href='calendrierBleu.css'>   
        </head>
        <body> 
        ");
    drawCharts('C');
    echo("<script>document.getElementById('chart0').innerHTML = 'NO MEASURES';</script>");
    echo("</body></html>");
    return;
    }     
$visupt = "";
if($maxMesures <= 48)$visupt = ",pointSize:3";	   
date_default_timezone_set($timezone);
function tip($temp,$tempDate)
	{global $cu;
	return sprintf('%4.1f %s (%s)',$temp,$cu,date("H:i",$tempDate)); 
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
	          	$tmin[$i] = 10000;
	          	$name = explode(" ",$nameStations[$i]);
	          	echo("data.addColumn('number', \"$name[0]\");\n");
				echo("data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });\n");        	       	  
                echo ("data.addColumn({type:'string', role:'annotation'});\n"); 
	          	}
	          	
	        echo("data.addColumn('number', '');\n"); 
	        $itime = $minDateBeg; 
	        $_SESSION['begdata'] = $minDateBeg;
			$beg = date("d/m/y", $minDateBeg); 
			$end = date("d/m/y",$date_end); 	        	        
	        $i = 0;	
	        $break = 0;
            	do {
            	$idate = date("d/m/y",$itime);
				echo("data.addRow([\"$idate\"");
            	for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		$tmin0 = $tip = '';   
            		$key = $keys[$j][$ii[$j]];  
            		if(abs($key - $itime) < 2*$inter) //changement d'horaire
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j]; 
            			if($selectMesure == 'T')
            			    $tmin0 = degree2($mesure[$j][$key][0]);
            			else if($selectMesure == 'P')
            			    $tmin0 = pressure2($mesure[$j][$key][0]);           			    
                        else
            			    $tmin0 = $mesure[$j][$key][0];
            			if($tmin[$j] > $tmin0)
            			    {$tmin[$j] = intval($tmin0*10 +.5)/10;
            			    $itmin[$j] = $i;
            			    }
            			$tip = tip($tmin0,$mesure[$j][$key][2]);
            			}        		
            		echo(",$tmin0,'$tip',''"); 
            		}          		
            	echo(",0]);\n"); 
            	$itime += $inter;
            	if($itime >= $date_end)$break = 1;
            	++$i;
                }while(!$break);
                $im = -1;
                for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		++$im;
            		$t = $tmin[$j];
            		echo("data.setValue($itmin[$j],3*($im+1),'$t'+ ' $cu');\n");
            		}
				echo("data.removeColumn(1+3*$numview);\n");	
				$tmesure = tr("mesure").'s';
				$title = tr($titre . 'minimale extérieure') . '  ('.$beg.' - '.$end.' @'. tr($tinter).' '.$maxMesures." $tmesure)";                

echo("
              var data1 = new google.visualization.DataTable();
	          data1.addColumn('string', 'Date');
");
			
	        for($i = 0;$i < $numStations;$i++)
	          	{if($view[$i] == 0)continue;
	          	$ii[$i] = 0; 
	          	$tmin[$i] = -1000;
	          	$name = explode(" ",$nameStations[$i]);
	          	echo("data1.addColumn('number', \"$name[0]\");\n");
				echo("data1.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true} });\n");  
				echo ("data1.addColumn({type:'string', role:'annotation'});\n"); 
	          	}
	          	
	        echo("data1.addColumn('number', '');\n"); 
	        $itime = $minDateBeg;
			$beg = date("d/m/y", $minDateBeg); 
			$end = date("d/m/y",$date_end); 	        
	        
	        $i = 0; 
	        $break = 0;
            do	{$idate = date("d/m/y",$itime);
				echo("data1.addRow([\"$idate\"");
            	for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		$tmin0 = $tip = '';        		
            		$key = $keys[$j][$ii[$j]]; 
            		if(abs($key - $itime) < 2*$inter)
            			{if( $ii[$j] < $nmesures[$j] -1)++$ii[$j]; 
            			if($selectMesure == 'T')
            			    $tmin0 = degree2($mesure[$j][$key][1]);
            			else if($selectMesure == 'P')
            			    $tmin0 = pressure2($mesure[$j][$key][1]);           			                			    
                        else
            			    $tmin0 = $mesure[$j][$key][1];
            			if($tmin[$j] < $tmin0)
            			    {$tmin[$j] = intval($tmin0*10 +.5)/10;
            			    $itmin[$j] = $i;
            			    }
            			    
              			$tip = tip($tmin0,$mesure[$j][$key][3]);
          			}
            		echo(",$tmin0,'$tip',''"); 
            		}          		
            	echo(",0]);\n"); 
            	$itime += $inter;
            	if($itime >= $date_end)$break = 1;
            	++$i;
                }while(!$break); 
                $im = -1;
                for($j = 0; $j < $numStations;$j++)
            		{if($view[$j] == 0)continue;
            		++$im;
            		$t = $tmin[$j];
            		echo("data1.setValue($itmin[$j],3*($im+1),'$t'+ ' $cu');\n");
            		}
              
				echo("data1.removeColumn(1+3*$numview);\n");				 
				$tmesure = tr("mesure").'s';
				$title1 = tr($titre . 'maximale extérieure') . '  ('.$beg.' - '.$end.' @'. tr($tinter).' '.$maxMesures." $tmesure)";                

$param = "focusTarget:'category',backgroundColor:'#f0f0f0',chartArea:{left:\"5%\",top:25,width:\"85%\",height:\"75%\"}";
$param .= ",fontSize:10,titleTextStyle:{fontSize:14,color:'#303080',fontName:'Times'}";
$param .= ',tooltip: {isHtml: true},curveType:"function"';
?>
colorMin =  ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6','#aaaaaa'];
colorMax =  ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6','#aaaaaa'];
<?php
			echo("                                   
             var chartMin = new google.visualization.LineChart(document.getElementById('chart0'));
             var chartmin = new google.visualization.LineChart(document.getElementById('chart1'));
             chartmin.draw(data1,{title: '$title1'$visupt,colors: colorMax,$param });
             chartMin.draw(data ,{title:'$title'$visupt,colors:colorMin,$param });
			");

?> 
    google.visualization.events.addListener(chartMin, 'select', MinClickHandler);        
     function MinClickHandler()
          {var selection = chartMin.getSelection();
          var num = colorMin.length;
          for (var i = 0; i < selection.length; i++) 
            {var item = selection[i];
            if(item.row != null  && data.getNumberOfRows() > 20   && !isMobile())
                top.location.href='compareALL.php?row='+item.row;
            if(item.column != null && data.getNumberOfColumns() > 3) 
                {data.removeColumn(item.column); 
                var col0 = (item.column -1)/2;
                for(var col = col0;col < num-1;col++)
                    colorMin[col] = colorMin[col+1];                 
                data.removeColumn(item.column);
                <?php echo("chartMin.draw(data ,{title:'$title' ,pointSize:3,colors:colorMin,$param });"); ?>             
                break;
                }
            }
        }
    google.visualization.events.addListener(chartmin, 'select', MaxClickHandler);        
     function MaxClickHandler()
          {var selection = chartmin.getSelection();
          var num = colorMax.length;
          for (var i = 0; i < selection.length; i++) 
            {var item = selection[i];
            if(item.row != null  && data1.getNumberOfRows() > 20  && !isMobile())
                top.location.href='compareALL.php?row='+item.row;
            if(item.column != null && data1.getNumberOfColumns() > 3)
                {data1.removeColumn(item.column);
                var col0 = (item.column -1)/2;
                for(var col = col0;col < num-1;col++)
                    colorMax[col] = colorMax[col+1]; 
                data1.removeColumn(item.column); 
                <?php echo("chartmin.draw(data1,{title: '$title1' ,pointSize:3,colors: colorMax,$param });"); ?>
                }
            }
         }


} // draw chart 
            
</script>
<link rel='stylesheet' media='screen' type='text/css'  href='calendrierBleu.css'>   
</head>
 <body> 
 <?php
	drawCharts('C');
?>
</body>
</html>


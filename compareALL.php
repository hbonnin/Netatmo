<?php
require_once 'NAApiClient.php';
require_once 'Config.php';
require_once 'initClient.php';
require_once 'menus.php';
session_start();
?>
<!DOCTYPE html SYSTEM 'about:legacy-compat'>
  <head>
	<title>Stations Netatmo</title>
	<meta charset='utf-8'>
	<link rel='icon' href='favicon.ico'>
    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
	<link type='text/css' rel='stylesheet'  href='style.css'>
	<script type='text/javascript' src='validate.js'></script>	

<?php
/* Ne peux actuellement etre appeler directement */
date_default_timezone_set("Europe/Paris");
initClient();
$client = $_SESSION['client'];
$devicelist = $_SESSION['devicelist'];
$mesures = $_SESSION['mesures'];
date_default_timezone_set("UTC");

if(isset($_POST["date0"]))  
    $date0 = $_POST["date0"]; 
else
    $date0 = $_SESSION['datebeg'];  

if(isset($_POST["date1"]))  
    $date1 = $_POST["date1"];
else
    $date1 = $_SESSION['dateend']; 
 

$txt = explode("/",$date1);
$date_end = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]);
$date_end = min($date_end,time());
$txt = explode("/",$date0);
$date_beg = mktime(0,0,0,$txt[1],$txt[0],$txt[2]);
$date_beg =	min($date_beg,$date_end);


if(isset($_POST["select"]))
    {$interval = $_POST["select"];
    $_SESSION['selectedInter'] = $interval;    
    }
 else   /* en fait inutil pour le moment */
    {$interval = $_SESSION['selectedInter']; 
    $interval = checkSelect($interval,'M');
    }
    
if($interval == '1week')
    {$date_beg = min($date_beg,$date_end - 18*24*60*60);
    $inter  = 7*24*60;
    }
else
    {$inter = 24*60;
    $date_beg -= 24*60*60;
    }

$n_mesure = min(1024,($date_end-$date_beg)/($inter*60));
$date_beg = max($date_beg,($date_end - $n_mesure*$inter*60));

// pour tracer le calendrier	
$datebeg = date("d/m/Y",$date_beg); 
$dateend = date("d/m/Y",$date_end); 
$_SESSION['datebeg'] = $datebeg;
$_SESSION['dateend'] = $dateend;


$numStations = count($devicelist["devices"]);


if(isset($_SESSION['viewCompare']))
    $view = $_SESSION['viewCompare'];
else
    {for($i = 1 ;$i < $numStations; $i++)
        $view[$i] = 0;
    $view[0] = 1;
       }
if(isset($_POST['stats']))
    {for($i = 0 ;$i < $numStations; $i++)
        $vien[$i] = 0;
    foreach($_POST['stats'] as $chkbx)
	    $view[$chkbx] = 1;
	}    
$_SESSION['viewCompare'] = $view;   

$numview = 0;  // Nombre de stations cochées
for($i = 0 ;$i < $numStations; $i++)
	if($view[$i])++$numview;
	
$selectMesure = $_POST['selectMsesure'];
if($selectMesure == 'T')
    {$type = 'min_temp,max_temp,date_min_temp,date_max_temp';
    $titre = 'Température ';
    }
else if($selectMesure == 'H')
    {$type = 'min_hum,max_hum,date_min_hum,date_max_hum';
    $titre = 'Humidité ';
    }   
 $_SESSION['selectMesureCompare'] = $selectMesure; 
 
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
    , "type" => $type
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
			$beg = date("d/m/y", $minDateBeg); 
			$end = date("d/m/y",$date_end); 	        	        
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
				$title = $titre . 'minimale extérieure' . '  ('.$beg.' - '.$end.')';                

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
			$beg = date("d/m/y", $minDateBeg); 
			$end = date("d/m/y",$date_end); 	        
	        
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
				$title1 = $titre . 'maximale extérieure' . '  ('.$beg.' - '.$end.')';                

$param = "focusTarget:'category',backgroundColor:'#f0f0f0',chartArea:{left:\"5%\",top:25,width:\"85%\",height:\"75%\"}";
$param .= ",fontSize:10,titleTextStyle:{fontSize:12,color:'#303080',fontName:'Times'}";
?>
colorMin =  ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6'];
colorMax =  ['red','blue', 'green', 'orange', '#aa00aa', '#f6c7b6'];
<?php
			echo("                                   
             var chartMin = new google.visualization.LineChart(document.getElementById('chart0'));
             var chartMax = new google.visualization.LineChart(document.getElementById('chart1'));
             chartMax.draw(data1,{title: '$title1' ,pointSize:3,colors: colorMax,$param });
             chartMin.draw(data ,{title:'$title' ,pointSize:3,colors:colorMin,$param });
			");
?> 
    google.visualization.events.addListener(chartMin, 'select', MinClickHandler);        
     function MinClickHandler()
          {if(data.getNumberOfColumns() <= 3)return;
          var selection = chartMin.getSelection();
          var num = colorMin.length;
          for (var i = 0; i < selection.length; i++) 
            {var item = selection[i];
            if(item.column != null ) 
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
    google.visualization.events.addListener(chartMax, 'select', MaxClickHandler);        
     function MaxClickHandler()
          {if(data1.getNumberOfColumns() <= 3)return;
          var selection = chartMax.getSelection();
          var num = colorMax.length;
          for (var i = 0; i < selection.length; i++) 
            {var item = selection[i];
            if(item.column != null)
                {data1.removeColumn(item.column);
                var col0 = (item.column -1)/2;
                for(var col = col0;col < num-1;col++)
                    colorMax[col] = colorMax[col+1]; 
                //for(var col = 0;col < num-1;col++)alert('eff:'+item.column+'col:'+col+' '+colorMax[col]);
                data1.removeColumn(item.column); 
                <?php echo("chartMax.draw(data1,{title: '$title1' ,pointSize:3,colors: colorMax,$param });"); ?>
                }
            }
         }


} // draw chart 
            
</script>
<script type='text/javascript' src='calendrier.js'></script> 
<link rel='stylesheet' media='screen' type='text/css'  href='calendrierBleu.css'>   
</head>
 <body> 
 <?php
	$num = count($devicelist["devices"]);
	drawCharts('C');
?>
</body>
</html>


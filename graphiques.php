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
    <link rel='icon' href='favicon.ico' >
    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
	<link type='text/css' rel='stylesheet'  href='style.css'>
	<script type='text/javascript' src='validate.js'></script>	

<?php
date_default_timezone_set("Europe/Paris");

if(!isset($_POST) && !isset($_GET)){echo " No POST or GET";return;}
initClient();
$client = $_SESSION['client'];
$devicelist = $_SESSION['devicelist'];
$mesures = $_SESSION['mesures'];

if(isset($_POST["select"]))
    {$interval = $_POST["select"];
    $_SESSION['selectedInter'] = $interval;    
    }
 else   /* en fait inutil pour le moment */
    {$interval = $_SESSION['selectedInter']; 
    $interval = checkSelect($interval,'M');
    }

if(isset($_POST['date0']))
    $date0 = $_POST['date0'];
else
    $date0 =$_SESSION['datebeg'];  
if(isset($_POST['date1']))
    $date1 = $_POST['date1'];
else
    $date1 = $_SESSION['dateend']; 
    
    
if(isset($_POST['station'])) 
    $stationId = $_POST['station'];
else if(isset($_SESSION['stationId']))
    $stationId = $_SESSION['stationId'];
else
    $stationId = 0;

$_SESSION['stationId'] = $stationId;


if($interval=="1week")
	{$inter = 7*24*60*60;
	$tinter = '1 semaine';
	$req =  "min_temp,max_temp,min_hum,max_hum,date_min_temp,date_max_temp,date_min_hum,date_max_hum";	
	$req1 = "min_temp,max_temp,min_hum,max_co2,min_pressure,max_noise";	
	}
else if($interval=="1day")
	{$inter = 24*60*60;
	$tinter = '1 jour';
	$req =  "min_temp,max_temp,min_hum,max_hum,date_min_temp,date_max_temp,date_min_hum,date_max_hum";
	$req1 = "min_temp,max_temp,min_hum,max_co2,min_pressure,max_noise";		
	}
else if($interval=="3hours")
	{$inter = 3*60*60;
	$tinter = '3 heures';	
	$req =  "Temperature,Humidity";	
	$req1 = "Temperature,Humidity,max_co2,min_pressure,max_noise";
	}	
else if($interval=="30min")
	{$inter = 30*60;
	$tinter = '30 minutes';
	$req = "Temperature,Humidity";
	$req1 = "Temperature,Humidity,max_co2,min_pressure,max_noise";
	}	
else if($interval=="max")
	{$inter = 5*60;
	$tinter = '5 minutes';
	$req = "Temperature,Humidity";
	$req1 = "Temperature,Humidity,CO2,Pressure,Noise";
	}
$date_beg = $date_end = 0;
chkDates($date0,$date1,$interval,$inter,&$date_beg,&$date_end);	

/*	
$txt = explode("/",$date1);
$date_end = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]);
$date_end = min($date_end,time());
$txt = explode("/",$date0);
$date_beg = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]); 
$date_beg =	min($date_beg,$date_end);

if($interval == '1week')
    $date_beg = min($date_beg,$date_end - 18*24*60*60);
else if($interval == '1day')
    $date_beg -= 24*60*60;
else if($interval == '3hours')
    $date_beg = min($date_beg,$date_end - 24*60*60);
else 
    $date_beg = min($date_beg,$date_end - 12*60*60);
    
$n_mesure = min(1024,($date_end-$date_beg)/($inter));
$date_beg = max($date_beg,($date_end - $n_mesure*$inter));

$datebeg = date("d/m/Y",$date_beg); 
$dateend = date("d/m/Y",$date_end); 
$_SESSION['datebeg'] = $datebeg;
$_SESSION['dateend'] = $dateend;
*/

$device_id = $devicelist["devices"][$stationId]["_id"];
$module_id = $devicelist["devices"][$stationId]["modules"][0]["_id"];

$int_name = $devicelist["devices"][$stationId]["module_name"];
$ext_name = $devicelist["devices"][$stationId]["modules"][0]["module_name"];
$stat_name = $mesures[$stationId]['station_name'];

date_default_timezone_set("UTC");


	// exterieur
    $params = array("scale" => $interval
    , "type" => $req
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "optimize" => false
    , "device_id" => $device_id
    , "module_id" => $module_id);  
    try
    	{$meas = $client->api("getmeasure", "POST", $params);
    	}
    catch(NAClientException $ex)
    	{echo "An error happend while trying to retrieve your last measures\n";
        echo $ex->getMessage()."\n";
    	}
    	
 	// interieur    
    $params = array("scale" => $interval
    , "type" => $req1
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "optimize" => false
    , "device_id" => $device_id); 
    try
    	{$meas1 = $client->api("getmeasure", "POST", $params); 
		}
    catch(NAClientException $ex)
    	{echo "An error happend while trying to retrieve your last measures\n";
        echo $ex->getMessage()."\n";
    	}

date_default_timezone_set("Europe/Paris");
$jour = array("Dim","Lun","Mar","Mer","Jeu","Ven","Sam"); 
$visupt = '';

function tipHTMLext2($idate,$tmax,$hum)
	{return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>Température</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . '°</b></td></tr>'
	. '<tr><td><i>Humidité</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$hum) . '%</b></td></tr>'
	. '</table>';
	}

function tipHTMLext($idate,$datemin,$datemax,$tmax,$tmin,$min_hum,$max_hum,$dateminh,$datemaxh)
	{return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>T max</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . '°</b></td>'
	. '<td style=\"font-size: 12px;\">' . date('d/m/y H:i',$datemax) .'</tr>'
	. '<tr><td><i>T min</i></td><td style=\" color: blue;\"><b>' . sprintf('%4.1f',$tmin) . '°</b></td>'
	. '<td style=\"font-size: 12px; \">' . date('d/m/y H:i',$datemin) .'</tr>'
	. '<tr><td><i>H_max</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$max_hum) . '%</b></td>'
	. '<td style=\"font-size: 12px;\">' . date('d/m/y H:i',$datemaxh) .'</tr>'	
	. '<tr><td><i>H min</i></td><td style=\" color: #040;\"><b>' . sprintf('%d',$min_hum) . '%</b></td>'
	. '<td style=\"font-size: 12px;\">' . date('d/m/y H:i',$dateminh) .'</tr>'
	. '</table>';
	}
function tipHTMLint6($idate,$tmax,$tmin,$hum,$co,$pres,$noise)
	{return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>T max</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . '°</b></td></tr>'
	. '<tr><td><i>T min</i></td><td style=\" color: blue;\"><b>' . sprintf('%4.1f',$tmin) . '°</b></td></tr>'
	. '<tr><td><i>Humidité</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$hum) . '%</b></td></tr>'
	. '<tr><td><i>CO2</i></td><td style=\" color: orange;\"><b>' . sprintf('%d',$co) . ' ppm</b></td></tr>'
	. '<tr><td><i>Pression</i></td><td style=\" color: black;\"><b>' . sprintf('%d',$pres) . ' mb</b></td></tr>'
	. '<tr><td><i>Noise max</i></td><td style=\" color: magenta;\"><b>' . sprintf('%d',$noise) . ' db</b></td></tr>'
	. '</table>';
	}
function tipHTMLint5($idate,$tmax,$hum,$co,$pres,$noise)
	{return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>Température</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . '°</b></td></tr>'
	. '<tr><td><i>Humidité</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$hum) . '%</b></td></tr>'
	. '<tr><td><i>CO2</i></td><td style=\" color: orange;\"><b>' . sprintf('%d',$co) . ' ppm</b></td></tr>'
	. '<tr><td><i>Pression</i></td><td style=\" color: black;\"><b>' . sprintf('%d',$pres) . ' mb</b></td></tr>'
	. '<tr><td><i>Noise</i></td><td style=\" color: magenta;\"><b>' . sprintf('%d',$noise) . ' db</b></td></tr>'
	. '</table>';
	}
/*********************************************************************************************************/
/*********************************************************************************************************/

echo("
	<script type='text/javascript'>
      google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
              var dataExt = new google.visualization.DataTable();
              var dataInt = new google.visualization.DataTable();
	");              

 			$keys= array_keys($meas);
			$num = count($keys);
			$itime = $keys[0];  
			$beg = date("d/m/y", $keys[0]); 
			$end = date("d/m/y",$keys[$num-1]); 
			if($num <= 73)$visupt = ",pointSize:3";	

if($inter > 3*60*60) //1week, 1day
	{           
echo("	 
	 		dataExt.addColumn('string', 'Date');
        	dataExt.addColumn({type: \"string\", role: \"tooltip\",p: {html: true} });        	        	      	  
        	dataExt.addColumn('number', 'Tmax'); 
        	dataExt.addColumn('number', 'Tmin');     	  
        	dataExt.addColumn('number', 'Humidity min');  
        	dataExt.addColumn('number', 'Humidity max');  
         	dataExt.addColumn('number', '');   	  
");
			
	        $ii = $break = 0;	
            do
            	{$day = idate('w',$itime);
           		$idate = date("d/m/y",$itime); 
            	$tmin = $tmax = $hum = $tip = $d = '';
            	$key = $keys[$ii];         		
            	if(abs($key - $itime) < 2*60*60) //changement d'horaire
            		{if($ii < $num -1)++$ii;
            		else $break = 1;      
	//$req =  "min_temp,max_temp,min_hum,max_hum,date_min_temp,date_max_temp,date_min_hum,date_max_hum";
            		$tmin = $meas[$key][0];
            		$tmax = $meas[$key][1];
             		$min_hum = $meas[$key][2]; 
             		$max_hum = $meas[$key][3]; 
           			$iidate = $jour[$day] . date(" d/m/y ",$itime);
					$tip = tipHTMLext($iidate,$meas[$key][4],$meas[$key][5],$tmax,$tmin,$min_hum,$max_hum,$meas[$key][6],$meas[$key][7]);          		
            		}
            	if($hum)$hum = $hum/4;	
                //$dTmin = idate('H',$meas[$key][4]);
                echo("dataExt.addRow([\"$idate\",'$tip',$tmax,$tmin,$min_hum,$max_hum,1]);\n"); 
                if($itime >= $date_end)$break = 1;
                $itime += $inter;
                }while($break != 1);
           	echo("dataExt.removeColumn(6);\n");				      
	}
else   //5 ou 30 minutes ou 3 heures
	{
	echo("              
	          dataExt.addColumn('string', 'Date');
        	  dataExt.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	        	      	  	          
        	  dataExt.addColumn('number', 'Temp.'); 
        	  dataExt.addColumn('number', 'Humidity');  
         	  dataExt.addColumn('number', '');   	  
	");

	        $ii = $break = 0;	
            do
            	{$day = idate('w',$itime);
            	if($inter == 30*60)
            		$idate = date("d/m/y",$itime); 
            	else
            		$idate = $jour[$day] . date(" H:i",$itime);           		 
            	$tmin =  $hum = $tip = '';
            	$key = $keys[$ii];         		
            	if(abs($key - $itime) < $inter*2) // mesures décalées
            		{if($ii < $num -1)++$ii;
            		else $break = 1;           			
            		$tmin = $meas[$key][0];
            		$hum = $meas[$key][1];  
            		$itime = $keys[$ii]; 
	            	if($inter == 30*60)        		
            			$iidate = $jour[$day] . date(" d/m/y H:i",$itime);
            		else	         		           		
            			$iidate = $jour[$day] . date(" d/m/y H:i",$itime);         		           		
					$tip = tipHTMLext2($iidate,$tmin,$hum);
            		}
            	if($hum)$hum = $hum/4;	
                echo("dataExt.addRow([\"$idate\",'$tip',$tmin,$hum,1]);\n"); 
                if($itime >= $date_end)$break = 1;
                $itime += $inter;
                }while($break != 1);
           	echo("dataExt.removeColumn(4);\n");				      
	}
	$titleExt = '"' .$stat_name. '-' .$ext_name. '   (' .$beg. ' - '.$end.' @'. $tinter .')"';       	                    	
	
/*********************************************************************************************************/

 			$keys= array_keys($meas1);
			$num = count($keys);
			$itime = $keys[0];  
			$beg = date("d/m/y", $keys[0]); 
			$end = date("d/m/y",$keys[$num-1]); 
			if($num <= 73)$visupt = ",pointSize:3";	

if($inter > 3*60*60)	//1week,1day	
	{echo("
	          dataInt.addColumn('string', 'Date');
        	  dataInt.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  dataInt.addColumn('number', 'Tmax');
        	  dataInt.addColumn('number', 'Tmin');
        	  dataInt.addColumn('number', 'Humidity min');
        	  dataInt.addColumn('number', 'CO2 max');
        	  dataInt.addColumn('number', 'Pressure min');
        	  dataInt.addColumn('number', 'Noise max');  
          	  dataInt.addColumn('number', '');   	         	    
	");
			// Compute Max et Min pression	
			$MaxPression = 0;
			$MinPression = 2000;
			for($i=0; $i < $num;++$i)
				{$pres = $meas1[$keys[$i]][4];
				$MaxPression = max($MaxPression,$pres);
				$MinPression = min($MinPression,$pres);
				}	
			$xp = 100/($MaxPression - $MinPression);		
		
	        $ii = $break = 0;	
            do
            	{$day = idate('w',$itime);
           		$idate = date("d/m/y",$itime);  
            	$temp = $hum = $co = $pres = $noise = $tip = '';
            	$key = $keys[$ii];         		
            	if(abs($key - $itime) < 2*60*60) //changement d'horaire
            		{if($ii < $num -1)++$ii; 
            		else $break = 1;           			          			
            		$tmin = $meas1[$key][0];
            		$tmax = $meas1[$key][1];
                	$hum = $meas1[$key][2];
                	$co = $meas1[$key][3];
                	$pres = $meas1[$key][4];
                	$noise = $meas1[$key][5];                	
 //$req1 = "min_temp,max_temp,Humidity,CO2,min_pressure,max_noise";		
                    if($inter >= 3*60*60)
             			$iidate = $jour[$day] . date(" d/m/y",$itime) . '&nbsp &nbsp &nbsp &nbsp' . date("H:i",$itime);            		
					else
            			$iidate = $jour[$day] . date(" d/m/y ",$itime);
                	$tip = tipHTMLint6($iidate,$tmax,$tmin,$hum,$co,$pres,$noise);
                	if($co){$co = min($co,1000);$co /= 10;}           
                	$pres = ($pres-$MinPression)*$xp;
                	}
                echo("dataInt.addRow([\"$idate\",'$tip',$tmax,$tmin,$hum,$co,$pres,$noise,1]);\n");                
                if($itime >= $date_end)$break = 1;
                $itime += $inter;
                }while($break != 1);
            echo("dataInt.removeColumn(8);\n");				      
     	                    	
	}
else  // 5 minutes, 30 minutes, 3 heures
	{if($inter >= 30*60)
	    echo("
	          dataInt.addColumn('string', 'Date');
        	  dataInt.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  dataInt.addColumn('number', 'Temp.');
        	  dataInt.addColumn('number', 'Humidity');
        	  dataInt.addColumn('number', 'CO2 max');
        	  dataInt.addColumn('number', 'Pressure min');
        	  dataInt.addColumn('number', 'Noise max');  
          	  dataInt.addColumn('number', '');   	         	    
	    ");	
	else
	    echo("
	          dataInt.addColumn('string', 'Date');
        	  dataInt.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  dataInt.addColumn('number', 'Temp.');
        	  dataInt.addColumn('number', 'Humidity');
        	  dataInt.addColumn('number', 'CO2');
        	  dataInt.addColumn('number', 'Pressure');
        	  dataInt.addColumn('number', 'Noise');  
          	  dataInt.addColumn('number', '');   	         	    
	    ");

 			// Compute Max et Min pression	
			$MaxPression = 0;
			$MinPression = 2000;
			for($i=0; $i < $num;++$i)
				{$pres = $meas1[$keys[$i]][3];
				$MaxPression = max($MaxPression,$pres);
				$MinPression = min($MinPression,$pres);
				}
			if($MaxPression - $MinPression)	
			    $xp = 100/($MaxPression - $MinPression);
			else
			    $xp = 0;
	        $ii = $break = 0;	
            do
            	{$day = idate('w',$itime);
            	if($inter == 30*60)
            		$idate = date("d/m/y",$itime); 
            	else
            		$idate = $jour[$day] . date(" H:i",$itime);           		 
            	$temp = $hum = $co = $pres = $noise = $tooltip = '';
            	$key = $keys[$ii];         		
            	if(abs($key - $itime) < $inter*2) 
            		{if($ii < $num -1)++$ii; 
            		else $break = 1;           			          			
            		$tmin = $meas1[$key][0];
                	$hum = $meas1[$key][1];
                	$co = $meas1[$key][2];
                	$pres = $meas1[$key][3];
                	$noise = $meas1[$key][4];  
            		$itime = $keys[$ii];          		                	
           			$iidate = $jour[$day] . date(" d/m/y",$itime) . '&nbsp &nbsp &nbsp &nbsp' . date("H:i",$itime);
                	$tip = tipHTMLint5($iidate,$tmin,$hum,$co,$pres,$noise);
                	if($co){$co = min($co,1000);$co /= 10;}             
                	if($xp)$pres = ($pres-$MinPression)*$xp;
                	}
                echo("dataInt.addRow([\"$idate\",'$tip',$tmin,$hum,$co,$pres,$noise,1]);\n");                
                if($itime >= $date_end)$break = 1;
                $itime += $inter;
                }while($break != 1);
            echo("dataInt.removeColumn(7);\n");				      
 	} 
	$titleInt = '"' .$stat_name. '-' .$int_name. '   (' .$beg. ' - '.$end.' @'. $tinter .')"';       	                    	

echo("
	var chartExt = new google.visualization.LineChart(document.getElementById('chart1'));
    var chartInt = new google.visualization.LineChart(document.getElementById('chart0'));
    ");
	$param = "focusTarget:'category',tooltip: {isHtml: true}";
	$param .= "$visupt,backgroundColor:'#f0f0f0',chartArea:{left:\"5%\",top:25,width:\"85%\",height:\"75%\"}";
	$param .= "$visupt,fontSize:10,titleTextStyle:{fontSize:12,color:'#303080',fontName:'Times'}";


    echo("inter = $inter;");  //pour le script
?>
    if(inter > 3*60*60) 	    
        {colorInt =  ['red','blue','green','orange','brown','#ff69b4'];
        colorExt =  ['red','blue','green','#00dd00'];
        }
    else
        {colorInt = ['red','green','orange','brown','#ff69b4'];
        colorExt = ['red','green'];
        }
<?php

echo("
    chartInt.draw(dataInt, {title: $titleInt,colors:colorInt ,$param});
    chartExt.draw(dataExt, {title: $titleExt,colors:colorExt,$param});
    ");
?>

    google.visualization.events.addListener(chartInt, 'select', IntClickHandler);        
     function IntClickHandler()
        {if(dataInt.getNumberOfColumns() <= 3)return;
        var selection = chartInt.getSelection();
        var num = colorInt.length;
        for (var i = 0; i < selection.length; i++) 
            {var item = selection[i];
            if(item.column != null) 
                {dataInt.removeColumn(item.column); 
                for(var col = item.column-2;col < num-1;col++)
                    colorInt[col] = colorInt[col+1]; 
                <?php echo("chartInt.draw(dataInt, {title: $titleInt,colors:colorInt,$param });");?>
                break;
                }
            }
        }
    google.visualization.events.addListener(chartExt, 'select', ExtClickHandler);        
    function ExtClickHandler()
        {if(dataExt.getNumberOfColumns() <= 3)return;
        var selection = chartExt.getSelection();
        var num = colorExt.length;  
        for (var i = 0; i < selection.length; i++) 
            {var item = selection[i];
            if(item.column != null)
                {dataExt.removeColumn(item.column); 
                for(var col = item.column-2;col < num-1;col++)
                    colorExt[col] = colorExt[col+1];                 
                <?php echo("chartExt.draw(dataExt, {title: $titleExt,colors:colorExt,$param});");?>
                }
            }
         }
         
} // endDraw 
           
	</script>
<script type='text/javascript' src='calendrier.js'></script> 
<link rel='stylesheet' media='screen' type='text/css' title='Design' href='calendrierBleu.css' >
	
</head>
  <body>
 <?php
	$num = count($devicelist["devices"]);
	drawCharts('G');	
 ?>
  </body>
</html>

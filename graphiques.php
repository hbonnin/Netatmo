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
//$from = $_SESSION['calledfrom']; 
$stationId = $_POST["station"];
$interval = $_POST["select"];
       
//$user = $client->api("getuser", "POST");

if($interval =="max")//5 minutes
	{$date_end = time();
	$date_beg = $date_end - (48 * 60 * 60);
	}
else
	{$date1 = $_POST["date1"];
	$txt = explode("/",$date1);
	$date_end = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]);
	if($interval =="30min")
		$date_beg = $date_end - (14 * 24 * 60 * 60) - (30 * 60);
	else //3hours 1day 1week
		{$date0 = $_POST["date0"];
		$txt = explode("/",$date0);
		$date_beg = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]);
		$date_beg -= 24*60*60;
		}
	}
	
if($interval=="1week")
	{$inter = 7*24*60;
	$tinter = '1 semaine';
	$req =  "min_temp,max_temp,Humidity,date_min_temp,date_max_temp";
	$req1 = "min_temp,max_temp,Humidity,CO2,min_pressure,max_noise";	
	}
else if($interval=="1day")
	{$inter = 24*60;
	$tinter = '1 jour';
	$req =  "min_temp,max_temp,Humidity,date_min_temp,date_max_temp";
	$req1 = "min_temp,max_temp,Humidity,CO2,min_pressure,max_noise";		
	}
else if($interval=="30min")
	{$inter = 30;
	$tinter = '30 minutes';
	$req = "Temperature,Humidity";
	$req1 = "Temperature,Humidity,CO2,Pressure,Noise";
	}	
else if($interval=="max")
	{$inter = 5;
	$tinter = '5 minutes';
	$req = "Temperature,Humidity";
	$req1 = "Temperature,Humidity,CO2,Pressure,Noise";
	}
else // 3hours
	{$inter = 3*60;
	$tinter = '3 heures';
	$req =  "min_temp,max_temp,Humidity";	
	$req1 = "min_temp,max_temp,Humidity,CO2,Pressure,max_noise";
	}

$device_id = $devicelist["devices"][$stationId]["_id"];
$module_id = $devicelist["devices"][$stationId]["modules"][0]["_id"];

$int_name = $devicelist["devices"][$stationId]["module_name"];
$ext_name = $devicelist["devices"][$stationId]["modules"][0]["module_name"];

date_default_timezone_set("UTC");

$stat0 = $mesures[$stationId]['station_name'];
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

function tipHTML2($idate,$tmax,$hum)
	{return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>Température</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . '°</b></td></tr>'
	. '<tr><td><i>Humidité</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$hum) . '%</b></td></tr>'
	. '</table>';
	}
function tipHTML3($idate,$tmax,$tmin,$hum)
	{return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>T max</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . '°</b></td></tr>'
	. '<tr><td><i>T min</i></td><td style=\" color: blue;\"><b>' . sprintf('%4.1f',$tmin) . '°</b></td></tr>'
	. '<tr><td><i>Humidité</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$hum) . '%</b></td></tr>'
	. '</table>';
	}
function tipHTML5($idate,$datemax,$datemin,$tmax,$tmin,$hum)
	{return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>T max</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . '°</b></td>'
	. '<td style=\"font-size: 12px;\">' . date('H:i',$datemax) .'</tr>'
	. '<tr><td><i>T min</i></td><td style=\" color: blue;\"><b>' . sprintf('%4.1f',$tmin) . '°</b></td>'
	. '<td style=\"font-size: 12px; \">' . date('H:i',$datemin) .'</tr>'	
	. '<tr><td><i>Humidité</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$hum) . '%</b></td></tr>'
	. '</table>';
	}

?>
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
              var data1 = new google.visualization.DataTable();
<?php

if($inter > 30) //1week, 1day, 3hours
	{           
echo("	 
	 		data.addColumn('string', 'Date');
        	data.addColumn({type: \"string\", role: \"tooltip\",p: {html: true} });        	        	      	  
        	data.addColumn('number', 'Tmax'); 
        	data.addColumn('number', 'Tmin');     	  
        	data.addColumn('number', 'Humidity');  
         	data.addColumn('number', '');   	  
");
 			$keys= array_keys($meas);
			$num = count($keys);
			if($num <= 73)$visupt = ",pointSize:3";	
			$itime = $keys[0];  
			$nDays = ($keys[$num-1] - $itime);
	        $ii = $break = 0;	
            do
            	{$day = idate('w',$itime);
           		$idate = date("d/m/y",$itime); 
            	$tmin = $tmax = $hum = $tip = '';
            	$key = $keys[$ii];         		
            	if(abs($key - $itime) < 2*60*60) //changement d'horaire
            		{if($ii < $num -1)++$ii;
            		else $break = 1;           			
            		$tmin = $meas[$key][0];
            		$tmax = $meas[$key][1];
             		$hum = $meas[$key][2]; 
             		if($inter == 3*60)
            			$iidate = $jour[$day] . date(" d/m/y H:i",$itime);             		
 					else	
             			$iidate = $jour[$day] . date(" d/m/y ",$itime);
            		if($inter == 24*60)
						$tip = tipHTML5($iidate,$meas[$key][4],$meas[$key][3],$tmax,$tmin,$hum);          		
					else
						$tip = tipHTML3($iidate,$tmax,$tmin,$hum);
            		}
            	if($hum)$hum = $hum/4;	
                echo("data.addRow([\"$idate\",'$tip',$tmax,$tmin,$hum,1]);\n"); 
                if($itime >= $date_end)$break = 1;
                $itime += $inter*60;
                }while($break != 1);
           	echo("data.removeColumn(5);\n");				      
	}
else   //5 ou 30 minutes
	{
	echo("              
	          data.addColumn('string', 'Date');
        	  data.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	        	      	  	          
        	  data.addColumn('number', 'Temp.'); 
        	  data.addColumn('number', 'Humidity');  
         	  data.addColumn('number', '');   	  
	");

 			$keys= array_keys($meas);
			$num = count($keys);
			if($num <= 73)$visupt = ",pointSize:3";	
			$itime = $keys[0];  
			$nDays = ($keys[$num-1] - $itime);
	        $ii = $break = 0;	
            do
            	{$day = idate('w',$itime);
            	if($inter == 30)
            		$idate = date("d/m/y",$itime); 
            	else
            		$idate = $jour[$day] . date(" H:i",$itime);           		 
            	$tmin =  $hum = $tip = '';
            	$key = $keys[$ii];         		
            	if(abs($key - $itime) < $inter*2*60) // mesures décalées
            		{if($ii < $num -1)++$ii;
            		else $break = 1;           			
            		$tmin = $meas[$key][0];
            		$hum = $meas[$key][1];  
            		$itime = $keys[$ii]; 
	            	if($inter == 30)        		
            			$iidate = $jour[$day] . date(" d/m/y H:i",$itime);
            		else	         		           		
            			$iidate = $jour[$day] . date(" H:i",$itime);         		           		
					$tip = tipHTML2($iidate,$tmin,$hum);
            		}
            	if($hum)$hum = $hum/4;	
                echo("data.addRow([\"$idate\",'$tip',$tmin,$hum,1]);\n"); 
                if($itime >= $date_end)$break = 1;
                $itime += $inter*60;
                }while($break != 1);
           	echo("data.removeColumn(4);\n");				      
	}
	$title = '"' .$stat0. '-' .$ext_name. '   (' .intval(.5 + $nDays/3600/24).' jours: ' . $num . ' mesures / '. $tinter .')"';       	                    	
	
/************************************/			     	                    
function tip1HTML6($idate,$tmax,$tmin,$hum,$co,$pres,$noise)
	{return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>T max</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . '°</b></td></tr>'
	. '<tr><td><i>T min</i></td><td style=\" color: blue;\"><b>' . sprintf('%4.1f',$tmin) . '°</b></td></tr>'
	. '<tr><td><i>Humidité</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$hum) . '%</b></td></tr>'
	. '<tr><td><i>CO2</i></td><td style=\" color: orange;\"><b>' . sprintf('%d',$co) . ' ppm</b></td></tr>'
	. '<tr><td><i>Pression</i></td><td style=\" color: black;\"><b>' . sprintf('%d',$pres) . ' mb</b></td></tr>'
	. '<tr><td><i>Noise max</i></td><td style=\" color: magenta;\"><b>' . sprintf('%d',$noise) . ' db</b></td></tr>'
	. '</table>';
	}
function tip1HTML5($idate,$tmax,$hum,$co,$pres,$noise)
	{return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>Température</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . '°</b></td></tr>'
	. '<tr><td><i>Humidité</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$hum) . '%</b></td></tr>'
	. '<tr><td><i>CO2</i></td><td style=\" color: orange;\"><b>' . sprintf('%d',$co) . ' ppm</b></td></tr>'
	. '<tr><td><i>Pression</i></td><td style=\" color: black;\"><b>' . sprintf('%d',$pres) . ' mb</b></td></tr>'
	. '<tr><td><i>Noise</i></td><td style=\" color: magenta;\"><b>' . sprintf('%d',$noise) . ' db</b></td></tr>'
	. '</table>';
	}
	

if($inter > 30)			
	{echo("
	          data1.addColumn('string', 'Date');
        	  data1.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  data1.addColumn('number', 'Tmax');
        	  data1.addColumn('number', 'Tmin');
        	  data1.addColumn('number', 'Humidity');
        	  data1.addColumn('number', 'CO2');
        	  data1.addColumn('number', 'Pressure min');
        	  data1.addColumn('number', 'Noise');  
          	  data1.addColumn('number', '');   	         	    
	");
 			$keys= array_keys($meas1);
			$num = count($keys);
			// Compute Max et Min pression	
			$MaxPression = 0;
			$MinPression = 2000;
			for($i=0; $i < $num;++$i)
				{$pres = $meas1[$keys[$i]][4];
				$MaxPression = max($MaxPression,$pres);
				$MinPression = min($MinPression,$pres);
				}	
			$xp = 100/($MaxPression - $MinPression);		
		
			$itime = $keys[0];
			$nDays = ($keys[$num-1] - $itime); 
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
             		if($inter == 3*60)
             			$iidate = $jour[$day] . date(" d/m/y",$itime) . '&nbsp &nbsp &nbsp &nbsp' . date("H:i",$itime);            		
					else
            			$iidate = $jour[$day] . date(" d/m/y ",$itime);
                	$tip = tip1HTML6($iidate,$tmax,$tmin,$hum,$co,$pres,$noise);
                	if($co){$co = min($co,1000);$co /= 10;}           
                	$pres = ($pres-$MinPression)*$xp;
                	}
                echo("data1.addRow([\"$idate\",'$tip',$tmax,$tmin,$hum,$co,$pres,$noise,1]);\n");                
                if($itime >= $date_end)$break = 1;
                $itime += $inter*60;
                }while($break != 1);
            echo("data1.removeColumn(8);\n");				      
     	                    	
	}
else  // 5 minutes ou 30 minutes
	{echo("
	          data1.addColumn('string', 'Date');
        	  data1.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  data1.addColumn('number', 'Temp.');
        	  data1.addColumn('number', 'Humidity');
        	  data1.addColumn('number', 'CO2');
        	  data1.addColumn('number', 'Pressure');
        	  data1.addColumn('number', 'Noise');  
          	  data1.addColumn('number', '');   	         	    
	");

 			$keys= array_keys($meas1);
			$num = count($keys);	
			// Compute Max et Min pression	
			$MaxPression = 0;
			$MinPression = 2000;
			for($i=0; $i < $num;++$i)
				{$pres = $meas1[$keys[$i]][3];
				$MaxPression = max($MaxPression,$pres);
				$MinPression = min($MinPression,$pres);
				}
			$xp = 100/($MaxPression - $MinPression);		
			$itime = $keys[0]; 
			$nDays = ($keys[$num-1] - $itime);
	        $ii = $break = 0;	
            do
            	{$day = idate('w',$itime);
            	if($inter == 30)
            		$idate = date("d/m/y",$itime); 
            	else
            		$idate = $jour[$day] . date(" H:i",$itime);           		 
            	$temp = $hum = $co = $pres = $noise = $tooltip = '';
            	$key = $keys[$ii];         		
            	if(abs($key - $itime) < $inter*2*60) 
            		{if($ii < $num -1)++$ii; 
            		else $break = 1;           			          			
            		$tmin = $meas1[$key][0];
                	$hum = $meas1[$key][1];
                	$co = $meas1[$key][2];
                	$pres = $meas1[$key][3];
                	$noise = $meas1[$key][4];  
            		$itime = $keys[$ii];          		                	
	            	if($inter == 30)        		
            			$iidate = $jour[$day] . date(" d/m/y",$itime) . '&nbsp &nbsp &nbsp &nbsp' . date("H:i",$itime);
            		else	         		           		
            			$iidate = $jour[$day] . date(" H:i",$itime);         		           		
                	$tip = tip1HTML5($iidate,$tmin,$hum,$co,$pres,$noise);
                	if($co){$co = min($co,1000);$co /= 10;}             
                	$pres = ($pres-$MinPression)*$xp;
                	}
                echo("data1.addRow([\"$idate\",'$tip',$tmin,$hum,$co,$pres,$noise,1]);\n");                
                if($itime >= $date_end)$break = 1;
                $itime += $inter*60;
                }while($break != 1);
            echo("data1.removeColumn(7);\n");				      
 	} 
	$title1 = '"' .$stat0. '-' .$int_name. '   (' .intval(.5 + $nDays/3600/24).' jours: ' . $num . ' mesures / '. $tinter .')"';       	                    	

$common = "focusTarget:'category',tooltip: {isHtml: true}";
$extra = "backgroundColor:'#f0f0f0',chartArea:{left:\"5%\",top:35,width:\"80%\",height:\"70%\"}";
$param = $common . ',' .$extra;

if($inter > 30) 	                                
echo("                   
             var chartExt = new google.visualization.LineChart(document.getElementById('chartExt'));
             chartExt.draw(data, {title: $title $visupt,colors: ['red','blue','green'],$param});
             var chartInt = new google.visualization.LineChart(document.getElementById('chartInt'));
             chartInt.draw(data1, {title: $title1 $visupt,colors: ['red','blue','green','orange','brown','#e0b0e0'] ,$param});
");
else
echo("                 
             var chartExt = new google.visualization.LineChart(document.getElementById('chartExt'));
             chartExt.draw(data, {title: $title $visupt,colors: ['red','green'],$param});
              var chartInt = new google.visualization.LineChart(document.getElementById('chartInt'));
             chartInt.draw(data1, {title: $title1 $visupt,colors: ['red','green','orange','brown','#f0b0f0'] ,$param});
");

?>           
             }  
          </script>

<script type='text/javascript' src='calendrier.js'></script> 
<link type='text/css' rel='stylesheet'  href='style.css'/>
<script type='text/javascript' src='validate.js'></script>	
<link rel='stylesheet' media='screen' type='text/css' title='Design' href='calendrierBleu.css' />
</head>
  <body>
<?php
//echo $extra;
//echo $common;
$dateend = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d'),date('y')));
$datebeg = date("d/m/Y",mktime(0, 0, 0, date('m') , date('d')-30,date('y')));
$num = count($devicelist["devices"]);
?>
<table style='border:solid 2px white; padding:0px; width:100%;'>
<tr>
<td>
<?php
drawMenuCompare();
?>
</td>
    <td  style='vertical-align:bottom; width:100%;'>
    <div id='chartInt' class='chartInt' ></div></td>
 </tr>
 
 <tr>
 <td>
<?php
drawMenuStation();
?>
 </td>
    <td style='vertical-align:bottom; width:100%;'>
    <div id='chartExt' class='chartExt' ></div></td>
</tr>
</table>
	
<!-- Invisible table for calendar --> 
<table class="ds_box"  id="ds_conclass" style="display: none;" >
	<caption id="id_caption" class='ds_caption'>xxxx</caption>
	<tr><td id="ds_calclass">aaa</td></tr>
</table>

  </body>
</html>

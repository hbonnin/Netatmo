<?php
require_once 'NAApiClient.php';
require_once 'Config.php';

session_set_cookie_params(1200); 
session_start();
date_default_timezone_set("UTC");


if(!isset($_POST["select"]))
	{$stationId=0; 
	$date_end = time();
	$date_beg = time() - (10 * 24 * 60 * 60);
	$interval = "1day";
	}
else 
	{$interval = $_POST["select"];
	$stationId = $_POST["station"];
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

if(isset($_SESSION['client']))
    {$client = $_SESSION['client'];
    //echo("<pre>");print_r($client);echo("</pre");
    }
else
	{$client = new NAApiClient(array("client_id" => $client_id, "client_secret" => $client_secret, "username" => $test_username, "password" => $test_password));
	try {
    	$tokens = $client->getAccessToken();       
		} catch(NAClientException $ex) {
    		echo ("Identifiant ou mot de passe incorrect");
		exit(-1);	
		}
	$_SESSION['client'] = $client;	
	}  


$helper = new NAApiHelper();
if(isset($_SESSION['devicelist']))
    $devicelist = $_SESSION['devicelist'];
else
	{try {
		$devicelist = $client->api("devicelist", "POST");
		}
	catch(NAClientException $ex) {
		$ex = stristr(stristr($ex,"Stack trace:",true),"message");
		echo("$ex");
		exit(-1);
		}	
	$devicelist = $helper->SimplifyDeviceList($devicelist);
    $_SESSION['devicelist'] = $devicelist;
    }
  
if(isset($_SESSION['mesures']))
    $mesures = $_SESSION['mesures'];
else
	{$mesures = $helper->GetLastMeasures($client,$devicelist);
	$_SESSION['mesures'] = $mesures;
	}


$device_id = $devicelist["devices"][$stationId]["_id"];
$module_id = $devicelist["devices"][$stationId]["modules"][0]["_id"];
//echo("<pre>");print_r($devicelist["devices"][4]);echo("</pre>");

$int_name = $devicelist["devices"][$stationId]["module_name"];
$ext_name = $devicelist["devices"][$stationId]["modules"][0]["module_name"];

$stat0 = $mesures[$stationId]['station_name'];
	// exterieur
    $params = array("scale" => $interval
    , "type" => $req
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "optimize" => false
    , "device_id" => $device_id
    , "module_id" => $module_id);
    $meas = $client->api("getmeasure", "POST", $params);
    
 	// interieur    
    $params = array("scale" => $interval
    , "type" => $req1
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "optimize" => false
    , "device_id" => $device_id);
    $meas1 = $client->api("getmeasure", "POST", $params); 

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
              var data1 = new google.visualization.DataTable();
");

if($inter > 30) //1week, 1day, 3hours
	{echo("              
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
		
if($inter > 30) 	                                
echo("                   
             var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
             chart.draw(data, {title: $title $visupt,focusTarget: 'category',tooltip: {isHtml: true},colors: ['red','blue','green'] });
             var chart1 = new google.visualization.LineChart(document.getElementById('chart1_div'));
             chart1.draw(data1, {title: $title1 $visupt,focusTarget: 'category',tooltip: {isHtml: true},colors: ['red','blue','green','orange','brown','#f0b0f0'] });
");
else
echo("                   
             var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
             chart.draw(data, {title: $title $visupt,focusTarget: 'category',tooltip: {isHtml: true},colors: ['red','green'] });
              var chart1 = new google.visualization.LineChart(document.getElementById('chart1_div'));
             chart1.draw(data1, {title: $title1 $visupt,focusTarget: 'category',tooltip: {isHtml: true},colors: ['red','green','orange','brown','#f0b0f0'] });
");


echo("            
             }  
          </script>
  </head>
  <body style='margin:0; padding:0;'>
<!--      
	<table style='width:100%; height:100%px; margin-left:auto; margin-right:auto;  border:1px solid black;'>
    <tr><td id='chart1_div' style='height: 390px;'>
    </tr><tr>
    </td><td id='chart_div' style='height: 270px;'>
    </td></tr></table>
-->
    <div id='chart1_div' style='width:100%; height:390px; margin-left:auto; margin-right:auto;'></div>
    <div id='chart_div' style='width:100%; height:270px; margin-left:auto; margin-right:auto;'></div>
   
  </body>
</html>
");
?>
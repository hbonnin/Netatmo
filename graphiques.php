<?php
require_once 'NAApiClient.php';
require_once 'Config.php';

date_default_timezone_set("UTC");

if(isset($argc) && $argc >1)
	{$stationId=0; 
	$date_end = time();
	$date_beg = time() - (48 * 60 * 60);
	$interval = "max";
	$man = 1;
	}
else 
	{$man = 0;	 
	$interval = $_POST["select"];
	$stationId = $_POST["station"];
	if($interval =="max")
		{$date_end = time();
		$date_beg = $date_end - (48 * 60 * 60);
		}
	else
		{$date1 = $_POST["date1"];
		$txt = explode("/",$date1);
		$date_end = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]);
		if($interval =="30min")
			$date_beg = $date_end - (14 * 24 * 60 * 60);
		else
			{$date0 = $_POST["date0"];
			$txt = explode("/",$date0);
			$date_beg = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]);
			}
		}	
	}
	
if($interval=="1week")
	{$inter = 7*24*60;
	$req =  "min_temp,max_temp,Humidity,date_min_temp,date_max_temp";
	$req1 = "min_temp,max_temp,Humidity,CO2,Pressure,max_noise";	
	}
else if($interval=="1day")
	{$inter = 24*60;
	$req =  "min_temp,max_temp,Humidity,date_min_temp,date_max_temp";
	$req1 = "min_temp,max_temp,Humidity,CO2,Pressure,max_noise";		
	}
else if($interval=="30min")
	{$inter = 30;
	$req = "Temperature,Humidity";
	$req1 = "Temperature,Humidity,CO2,Pressure,Noise";
	}	
else if($interval=="max")
	{$inter = 5;
	$req = "Temperature,Humidity";
	$req1 = "Temperature,Humidity,CO2,Pressure,Noise";
	}
else // 3hours
	{$inter = 3*60;
	$req =  "min_temp,max_temp,Humidity";	
	$req1 = "min_temp,max_temp,Humidity,CO2,Pressure,max_noise";
	}
	
$client = new NAApiClient(array("client_id" => $client_id, "client_secret" => $client_secret, "username" => $test_username, "password" => $test_password));
$helper = new NAApiHelper();

try {
    $tokens = $client->getAccessToken();        
    
} catch(NAClientException $ex) {
    echo "An error happend while trying to retrieve your tokens\n";
    exit(-1);
}

$devicelist = $client->api("devicelist", "POST");
$devicelist = $helper->SimplifyDeviceList($devicelist);
$device_id = $devicelist["devices"][$stationId]["_id"];
$module_id = $devicelist["devices"][$stationId]["modules"][0]["_id"];
$mesures = $helper->GetLastMeasures($client,$devicelist);
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

if($man)
{
echo("inter=$inter");		
$idate = date("d/m/y H:i",$date_beg);	
echo("date_beg:$idate\n");
$idate = date("d/m/y H:i",$date_end);	
echo("date_end:$idate\n");
$keys= array_keys($meas);
$num = count($keys);
echo("num:$num\n");
for($i=0; $i < $num;++$i)
	{$key = $keys[$i];  
	$idate = date("d/m/y H:i s",$key);
	$tmin = $meas[$key][0];
	echo("$i date:$idate tmin:$tmin<br>\n");
	} 
}

date_default_timezone_set("Europe/Paris");
function tip($temp,$tempDate)
	{return sprintf('%4.1f (%s)',$temp,date("H:i",$tempDate)); 
	}    
$jour = array("Dim","Lun","Mar","Mer","Jeu","Ven","Sam"); 
$visupt = '';

echo("
<html>
  <head>
  <meta HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf8\">
    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <script type='text/javascript'>
      google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
              var data = new google.visualization.DataTable();
              var data1 = new google.visualization.DataTable();
");
/********************************************/
if($inter > 30)
	{echo("              
	          data.addColumn('string', 'Date');
        	  data.addColumn('number', 'Tmax °'); 
        	  data.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	        	      	  
        	  data.addColumn('number', 'Tmin °');     	  
        	  data.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  data.addColumn('number', 'Humidity %');  
              data.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });
         	  data.addColumn('number', '');   	  
	");
 			$keys= array_keys($meas);
			$num = count($keys);
			if($num <= 73)$visupt = ",pointSize:3";	
			$itime = $keys[0];  
	        $ii = $break = 0;	
            do
            	{if($inter == 3*60)
            		$idate = date("d/m/y:H",$itime); 
            	else
            		$idate = date("d/m/y",$itime); 
            		 
            	$tmin = $tmax = $hum = $humtip = $mintip = $maxtip = '';
            	$key = $keys[$ii];         		
            	if(abs($key - $itime) < 2*60*60) //changement d'horaire
            		{if($ii < $num -1)++$ii;
            		else $break = 1;           			
            		$tmin = $meas[$key][0];
            		$tmax = $meas[$key][1];
            		if($inter == 24*60)
            			{$mintip = tip($tmin,$meas[$key][3]);        		
            			$maxtip = tip($tmax,$meas[$key][4]); 
            			}           		
            		else
            			{$mintip = sprintf('%4.1f',$tmin);          		
            			$maxtip = sprintf('%4.1f',$tmax);
            			}
            		$hum = $meas[$key][2]/4;  
            		$humtip = sprintf('%d', ($hum)*4);           		
            		}
                echo("data.addRow([\"$idate\",$tmax,'$maxtip',$tmin,'$mintip',$hum,'$humtip',1]);\n"); 
                $itime += $inter*60;
                }while($break != 1);
           	echo("data.removeColumn(7);\n");				      
			$title = '"Exterieur: ' . $stat0 . ' (' . $num . ' mesures)' .'"';       	                    	
	}
else
	{
	echo("              
	          data.addColumn('string', 'Date');
        	  data.addColumn('number', 'T°'); 
        	  data.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	        	      	  
        	  data.addColumn('number', 'Humidity %');  
              data.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });
         	  data.addColumn('number', '');   	  
	");

 			$keys= array_keys($meas);
			$num = count($keys);
			if($num <= 73)$visupt = ",pointSize:3";	
			$itime = $keys[0];  
	        $ii = $break = 0;	
            do
            	{$day = idate('w',$itime);
            	$idate = $jour[$day] . date(" H:i",$itime);           		 
            	$tmin =  $hum = $humtip = $mintip =  '';
            	$key = $keys[$ii];         		
            	if(abs($key - $itime) < $inter*2*60) // mesures décalées
            		{if($ii < $num -1)++$ii;
            		else $break = 1;           			
            		$tmin = $meas[$key][0];
            		$mintip = sprintf('%4.1f',$tmin);          		
            		$hum = $meas[$key][1]/4;  
            		$humtip = sprintf('%d', ($hum)*4); 
            		$itime = $keys[$ii]; 
            		$idate = $jour[$day] . date(" H:i",$itime);         		
            		}
                echo("data.addRow([\"$idate\",$tmin,'$mintip',$hum,'$humtip',1]);\n"); 
                $itime += $inter*60;
                }while($break != 1);
           	echo("data.removeColumn(5);\n");				      
			$title = '"Exterieur: ' . $stat0 . ' (' . $num . ' mesures)' .'"';  
	}
/************************************/			     	                    
if($inter > 30)			
	{echo("
	          data1.addColumn('string', 'Date');
        	  data1.addColumn('number', 'Tmax °');
        	  data1.addColumn('number', 'Tmin °');
        	  data1.addColumn('number', 'Humidity %');
        	  data1.addColumn('number', 'CO2 ppm');
        	  data1.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  data1.addColumn('number', 'Pres mb');
        	  data1.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  data1.addColumn('number', 'Noise Max db');  
          	  data1.addColumn('number', '');   	         	    
	");
 			$keys= array_keys($meas1);
			$num = count($keys);	
			$itime = $keys[0];  
	        $ii = $break = 0;	
            do
            	{if($inter == 3*60)
            		$idate = date("d/m/y:H",$itime); 
            	else
            		$idate = date("d/m/y",$itime);  
            	$temp = $hum = $co = $pres = $noise = $tooltip = '';
            	$key = $keys[$ii];         		
            	if(abs($key - $itime) < 2*60*60) //changement d'horaire
            		{if($ii < $num -1)++$ii; 
            		else $break = 1;           			          			
            		$tmin = $meas1[$key][0];
            		$tmax = $meas1[$key][1];
                	$hum = $meas1[$key][2];
                	$co = $meas1[$key][3];
                	$co = min($co,1000);$co /= 10;
                	$tipCO2 = sprintf('%d',$co *10);                
                	$pres = $meas1[$key][4]-970;
                	$tipPRES = sprintf('%d',$pres +970);
                	$noise = $meas1[$key][5];
                	}
                echo("data1.addRow([\"$idate\",$tmax,$tmin,$hum,$co,'$tipCO2',$pres,'$tipPRES',$noise,1]);\n");                
                $itime += $inter*60;
                }while($break != 1);
            echo("data1.removeColumn(9);\n");				      
			$title1 = '"Intérieur: ' . $stat0 . ' (' . $num . ' mesures)' .'"';       	                    	
	}
else
	{echo("
	          data1.addColumn('string', 'Date');
        	  data1.addColumn('number', 'T°');
        	  data1.addColumn('number', 'Humidity %');
        	  data1.addColumn('number', 'CO2 ppm');
        	  data1.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  data1.addColumn('number', 'Pres mb');
        	  data1.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  data1.addColumn('number', 'Noise db');  
          	  data1.addColumn('number', '');   	         	    
	");

 			$keys= array_keys($meas1);
			$num = count($keys);	
			$itime = $keys[0];  
	        $ii = $break = 0;	
            do
            	{$day = idate('w',$itime);
            	$idate = $jour[$day] . date(" H:i",$itime);           		 
            	$temp = $hum = $co = $pres = $noise = $tooltip = '';
            	$key = $keys[$ii];         		
            	if(abs($key - $itime) < $inter*2*60) 
            		{if($ii < $num -1)++$ii; 
            		else $break = 1;           			          			
            		$tmin = $meas1[$key][0];
                	$hum = $meas1[$key][1];
                	$co = $meas1[$key][2];
                	$co = min($co,1000);$co /= 10;
                	$tipCO2 = sprintf('%d',$co *10);                
                	$pres = $meas1[$key][3]-970;
                	$tipPRES = sprintf('%d',$pres +970);
                	$noise = $meas1[$key][4];
            		$itime = $keys[$ii];          		
                	}
                echo("data1.addRow([\"$idate\",$tmin,$hum,$co,'$tipCO2',$pres,'$tipPRES',$noise,1]);\n");                
                $itime += $inter*60;
                }while($break != 1);
            echo("data1.removeColumn(8);\n");				      
			$title1 = '"Intérieur: ' . $stat0 . ' (' . $num . ' mesures)' .'"';       	                    
 	} 
if($inter > 30) 	                                
echo("                   
             var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
             chart.draw(data, {title: $title $visupt,focusTarget: 'category',colors: ['red','blue','green'] });
              var chart1 = new google.visualization.LineChart(document.getElementById('chart1_div'));
             chart1.draw(data1, {title: $title1 $visupt,focusTarget: 'category',colors: ['red','blue','green','orange','brown','#f0b0f0'] });
");
else
echo("                   
             var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
             chart.draw(data, {title: $title $visupt,focusTarget: 'category',colors: ['red','green'] });
              var chart1 = new google.visualization.LineChart(document.getElementById('chart1_div'));
             chart1.draw(data1, {title: $title1 $visupt,focusTarget: 'category',colors: ['red','green','orange','brown','#f0b0f0'] });
");


echo("            
             }  
          </script>
  </head>
  <body>
    <center>
  	<!--<h3>Graphiques de $stat0</h3>
	<table>
    <tr><td id='chart1_div' style='width: 600px; height: §00px; border:2px solid white;'>
    </td><td id='chart_div' style='width: 600px; height: §00px; border:2px solid white;'>
    </td></tr></table>-->

    <div id='chart1_div' style='width:100%; height:55%;'></div>
    <div id='chart_div' style='width:100%; height:45%; '></div>
    </center>
  </body>
</html>
");
?>
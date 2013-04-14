<?php
require_once 'NAApiClient.php';
require_once 'Config.php';

date_default_timezone_set("UTC");

if(isset($argc) && $argc >1)
	{$stationId=0; 
	$date_end = time();
	$date_beg = time() - (7 * 24 * 60 * 60);
	$interval = "1day";
	$man = 1;
	}
else {	 
	$stationId = $_POST["station"];	
	$date0 = $_POST["date0"];
	$txt = explode("/",$date0);
	$date_beg = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]);
	$date1 = $_POST["date1"];
	$txt = explode("/",$date1);
	$date_end = mktime(date('H'),date('i'),0,$txt[1],$txt[0],$txt[2]);
	$interval = $_POST["interval"];
	$man = 0;	
	}
	
if($interval=="1day")
	$inter = 24;
else
	$inter = 3;
	

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
	$extra = '';
	if($inter == 24)
		$extra = ",date_min_temp,date_max_temp";

    $params = array("scale" => $interval
    , "type" => "min_temp,max_temp,Humidity" . $extra
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "optimize" => false
    , "device_id" => $device_id
    , "module_id" => $module_id);
    $meas = $client->api("getmeasure", "POST", $params);
 	    
    $params = array("scale" => $interval
    , "type" => "min_temp,max_temp,Humidity,CO2,Pressure,max_noise"
    , "date_begin" => $date_beg
    , "date_end" => $date_end
    , "optimize" => false
    , "device_id" => $device_id);
    $meas1 = $client->api("getmeasure", "POST", $params);

if($man)
{
$keys= array_keys($meas);
$num = count($keys);
for($i=0; $i < $num;++$i)
	{$key = $keys[$i];  
	$idate = date("d/m/y H:i",$key);
	$tmin = $meas[$key][0];
	$tmax = $meas[$key][1];
	echo("$i date:$idate tmin:$tmin,tmax:$tmax<br>\n");
	} 	
$idate = date("d/m/y H:i",$date_beg);	
echo("date_beg:$idate\n");
$idate = date("d/m/y H:i",$date_end);	
echo("date_end:$idate\n");
}

date_default_timezone_set("Europe/Paris");
function tip($temp,$tempDate)
	{return sprintf('%04.1f :: %s',$temp,date("H:i",$tempDate)); 
	}    


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
	          data.addColumn('string', 'Date');
        	  data.addColumn('number', 'Tmin °'); 
        	  data.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	        	      	  
        	  data.addColumn('number', 'Tmax °');     	  
        	  data.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });        	  
        	  data.addColumn('number', 'Humidity %');  
              data.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });
         	  data.addColumn('number', '');   	  
");
 			$keys= array_keys($meas);
			$num = count($keys);	
			$itime = $keys[0];  
	        $ii = $break = 0;	
            do
            	{if($inter == 3)
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
            		if($inter == 24)
            			{$mintip = tip($tmin,$meas[$key][3]);        		
            			$maxtip = tip($tmax,$meas[$key][4]); 
            			}           		
            		else
            			{$mintip = sprintf('%04.1f',$tmin);          		
            			$maxtip = sprintf('%04.1f',$tmax);
            			}
            		$hum = $meas[$key][2]/4;  
            		$humtip = sprintf('%d', ($hum)*4);           		
            		}
                echo("data.addRow([\"$idate\",$tmin,'$mintip',$tmax,'$maxtip',$hum,'$humtip',1]);\n"); 
                $itime += $inter*60*60;
                }while($break != 1);
           	echo("data.removeColumn(7);\n");				      
			$title = '"Extérieur: ' . $stat0 .'"';
			
echo("
              var data1 = new google.visualization.DataTable();
	          data1.addColumn('string', 'Date');
        	  data1.addColumn('number', 'Tmin °');
        	  data1.addColumn('number', 'Tmax °');
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
            	{if($inter == 3)
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
                echo("data1.addRow([\"$idate\",$tmin,$tmax,$hum,$co,'$tipCO2',$pres,'$tipPRES',$noise,1]);\n");                
                $itime += $inter*60*60;
                }while($break != 1);
            echo("data1.removeColumn(9);\n");				      
			$title1 = '"Intérieur: ' . $stat0 .'"';       	                    
                                  
echo("                   
             var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
             chart.draw(data, { title: $title,focusTarget: 'category',colors: ['blue','red', 'green', 'blue', '#f3b49f', '#f6c7b6'] });
              var chart1 = new google.visualization.LineChart(document.getElementById('chart1_div'));
             chart1.draw(data1, { title: $title1 ,focusTarget: 'category',colors: ['blue','red', 'green', 'orange', '#aaaaaa', '#aaaaff'] });
            
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
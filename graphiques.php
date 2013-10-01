<!DOCTYPE html SYSTEM 'about:legacy-compat'>
  <head>
  	<title>Stations Netatmo</title>
  	<meta charset='utf-8'>
    <link rel='icon' href='favicon.ico' >
    <script src='https://www.google.com/jsapi'></script>
    <script src='js/size.js'></script>
	<link type='text/css' rel='stylesheet'  href='style.css'>
    <link rel='stylesheet' type='text/css'  href='calendrierBleu.css' >

<?php
require_once 'NAApiClient.php';
require_once 'Config.php';
require_once 'initClient.php';
require_once 'menus.php';
session_start(); date_default_timezone_set($timezone);

if(!isset($_POST) && !isset($_GET)){echo " No POST or GET";return;}
initClient();
$client = $_SESSION['client'];
$Temperature_unit = $_SESSION['Temperature_unit'];
$cu = $Temperature_unit ? '°':' F';
if(isset($_POST['station'])) 
    $stationId = $_POST['station'];
else if(isset($_SESSION['stationId']))
    $stationId = $_SESSION['stationId'];
$_SESSION['stationId'] = $stationId;

if(isset($_POST["select"]))
    {$interval = $_POST["select"];
    $_SESSION['selectedInter'] = $interval;    
    }
 else   /* en fait inutil pour le moment */
    {$interval = $_SESSION['selectedInter']; 
    $interval = checkSelect($interval,'G');
    }
$opt = $_SESSION['MenuInterval']['opt']; 
$sel = selectIndex($opt,$interval);
$inter = $opt[$sel][2];
$tinter = $opt[$sel][1];	

if(isset($_POST['date0']))
    $date0 = $_POST['date0'];
else
    $date0 =$_SESSION['datebeg'];  
if(isset($_POST['date1']))
    $date1 = $_POST['date1'];
else
    $date1 = $_SESSION['dateend']; 

if(isset($_GET['row']))// faire un zoom sur la date
    {$row = $_GET['row'];
    $date_beg = $_SESSION['date_beg'];
    $date_end = $_SESSION['date_end'];   
    $sel = selectIndex($opt,$interval);
    if($sel < maxIndexMenu('G'))
        {$beg = $_SESSION['begdata'];
        $dateRow = $beg + $row*$inter;
        $interval = $opt[$sel + 1][0]; 
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
  
if(abs($date_beg - $_SESSION['date_begP']) < $inter  &&  abs($date_end - $_SESSION['date_endP']) < $inter
    && $interval == $_SESSION['selectedInterP']  &&  $stationId == $_SESSION['stationIdP'])
    $reloadData = 0; 
else
    {$reloadData = 1; 
    $_SESSION['date_begP'] = $date_beg;
    $_SESSION['date_endP'] = $date_end;
    $_SESSION['selectedInterP'] = $interval;
    $_SESSION['stationIdP'] = $stationId;
    }

$selectMesures = $_SESSION['selectMesures'];   
if(isset($_POST['smesure']))
    {for($i = 0 ;$i < 5;$i++)
        $selectMesures[$i] = 0;
    $selected = 0;        
    foreach($_POST['smesure'] as $chkbx)
        {$selectMesures[$chkbx] = 1;++$selected;}
    if(!$selected){$selectMesures[0] = 1;++$selected;}    
    }
// calcul des colonnes à effacer **********************************
 function compactArray($array)
    {for($ii = $i = 0; $i < count($array);$i++)
        if($array[$i] != '0')
            $narray[$ii++] = $array[$i];
    return $narray;    
    }

for($i = 0 ;$i < 9;$i++)
    $eraseInt[$i] = $eraseExt[$i] = 0;
if($inter > 3*60*60) 
    {$colorInt =  array('red','blue','green','orange','brown','#ff69b4');
    $colorExt =  array('red','blue','green','#00dd00');  
    if(!$selectMesures[0])
         {$eraseInt[2] = $eraseInt[3] = 1;
         $colorInt[0] = $colorInt[1] = '0';
         }
    for($i = 1 ;$i < 5;$i++)
        if(!$selectMesures[$i])
            {$eraseInt[$i+ 3] = 1;
            $colorInt[$i+1] = '0';
            }
    if(!$selectMesures[1]) 
        {$eraseExt[4] = $eraseExt[5] = 1;
         $colorExt[2] = $colorExt[3] = '0';
         }      
     if($selectMesures[1] && !$selectMesures[0])  
        {$eraseExt[2] = $eraseExt[3] = 1; 
         $colorExt[0] = $colorExt[1] = '0';
         }   
    } 
else
    {$colorInt = array('red','green','orange','brown','#ff69b4');
    $colorExt = array('red','green');
    for($i = 0 ;$i < 5;$i++)
        if(!$selectMesures[$i])
            {$eraseInt[$i+2]  = 1;
            $colorInt[$i] = '0';
            }
        if(!$selectMesures[1])
            {$eraseExt[1+2]  = 1;
            $colorExt[1] = '0';
            }   
        if($selectMesures[1] && !$selectMesures[0])  
            {$eraseExt[2]  = 1;
            $colorExt[0] = '0';
            }   
    }
$colorInt = compactArray($colorInt);
$colorExt = compactArray($colorExt);     
$_SESSION['selectMesures'] = $selectMesures; 
// *********************************************************************
if($interval=="1week")
	{$req =  "min_temp,max_temp,min_hum,max_hum,date_min_temp,date_max_temp,date_min_hum,date_max_hum";	
	$req1 = "min_temp,max_temp,min_hum,max_co2,min_pressure,max_noise";	
	}
else if($interval=="1day")
	{$req =  "min_temp,max_temp,min_hum,max_hum,date_min_temp,date_max_temp,date_min_hum,date_max_hum";
	$req1 = "min_temp,max_temp,min_hum,max_co2,min_pressure,max_noise";		
	}
else if($interval=="3hours")
	{$req =  "Temperature,Humidity";	
	$req1 = "Temperature,Humidity,max_co2,min_pressure,max_noise";
	}	
else if($interval=="30min")
	{$req = "Temperature,Humidity";
	$req1 = "Temperature,Humidity,max_co2,min_pressure,max_noise";
	}	
else if($interval=="max")
	{$req = "Temperature,Humidity";
	$req1 = "Temperature,Humidity,CO2,Pressure,Noise";
	}

$mydevices = $_SESSION['mydevices']; 
$device_id = $mydevices[$stationId]["_id"];
$module_id = $mydevices[$stationId]["modules"][0]["_id"];
$int_name  = $mydevices[$stationId]["module_name"];
$ext_name  = $mydevices[$stationId]["modules"][0]["module_name"];
$stat_name = $mydevices[$stationId]["station_name"];
if($reloadData)
    {date_default_timezone_set("UTC");
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
        echo "<pre>";print_r($ex);echo "</pre>";
        echo "<script>alert('Quitter');</script>";
        echo $ex->getMessage()."\n";
        }
    if(count($meas) == 0)
        {drawCharts('G');
        echo("<script>document.getElementById('chart0').innerHTML = 'NO MEASURES';</script>");
        return;
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
        echo "<pre>";print_r($ex);echo "</pre>";
        echo "<script>alert('Quitter');</script>";
        echo $ex->getMessage()."\n";
        }
    date_default_timezone_set($timezone);
    $_SESSION['GraphiqueMesureInt'] = gzcompress(json_encode($meas1),2);
    $_SESSION['GraphiqueMesureExt'] = gzcompress(json_encode($meas),2);
    $_SESSION['timeLoad'] = time();
    }
else
    {$meas1 = json_decode(gzuncompress($_SESSION['GraphiqueMesureInt']),true);
    $meas =  json_decode(gzuncompress($_SESSION['GraphiqueMesureExt']),true);
    date_default_timezone_set($timezone);  
    }
$timeLoadData = $_SESSION['timeLoad'];
$dateLoadData = date("H:i:s ",$timeLoadData);
$jour = array("Dim","Lun","Mar","Mer","Jeu","Ven","Sam"); 
$visupt = 0;

function tipHTMLext2($idate,$tmax,$hum)
	{global $cu;
	return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>Température</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . "$cu</b></td></tr>"
	. '<tr><td><i>Humidité</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$hum) . '%</b></td></tr>'
	. '</table>';
	}

function tipHTMLext($idate,$datemin,$datemax,$tmax,$tmin,$min_hum,$max_hum,$dateminh,$datemaxh)
	{global $cu;
	return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>T max</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . "$cu</b></td>"
	. '<td style=\"font-size: 12px;\">' . date('d/m/y H:i',$datemax) .'</tr>'
	. '<tr><td><i>T min</i></td><td style=\" color: blue;\"><b>' . sprintf('%4.1f',$tmin) . "$cu</b></td>"
	. '<td style=\"font-size: 12px; \">' . date('d/m/y H:i',$datemin) .'</tr>'
	. '<tr><td><i>H_max</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$max_hum) . '%</b></td>'
	. '<td style=\"font-size: 12px;\">' . date('d/m/y H:i',$datemaxh) .'</tr>'	
	. '<tr><td><i>H min</i></td><td style=\" color: #040;\"><b>' . sprintf('%d',$min_hum) . '%</b></td>'
	. '<td style=\"font-size: 12px;\">' . date('d/m/y H:i',$dateminh) .'</tr>'
	. '</table>';
	}
function tipHTMLint6($idate,$tmax,$tmin,$hum,$co,$pres,$noise)
	{global $cu;
	return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>T max</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . "$cu</b></td></tr>"
	. '<tr><td><i>T min</i></td><td style=\" color: blue;\"><b>' . sprintf('%4.1f',$tmin) . "$cu</b></td></tr>"
	. '<tr><td><i>Humidité</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$hum) . '%</b></td></tr>'
	. '<tr><td><i>CO2</i></td><td style=\" color: orange;\"><b>' . sprintf('%d',$co) . ' ppm</b></td></tr>'
	. '<tr><td><i>Pression</i></td><td style=\" color: black;\"><b>' . sprintf('%d',$pres) . ' mb</b></td></tr>'
	. '<tr><td><i>Noise max</i></td><td style=\" color: magenta;\"><b>' . sprintf('%d',$noise) . ' db</b></td></tr>'
	. '</table>';
	}
function tipHTMLint5($idate,$tmax,$hum,$co,$pres,$noise)
	{global $cu;
	return '<table><caption><b>' . $idate . '</b></caption>'
	. '<tr><td><i>Température</i></td><td style=\" color: red;\"><b>' . sprintf('%4.1f',$tmax) . "$cu</b></td></tr>"
	. '<tr><td><i>Humidité</i></td><td style=\" color: green;\"><b>' . sprintf('%d',$hum) . '%</b></td></tr>'
	. '<tr><td><i>CO2</i></td><td style=\" color: orange;\"><b>' . sprintf('%d',$co) . ' ppm</b></td></tr>'
	. '<tr><td><i>Pression</i></td><td style=\" color: black;\"><b>' . sprintf('%d',$pres) . ' mb</b></td></tr>'
	. '<tr><td><i>Noise</i></td><td style=\" color: magenta;\"><b>' . sprintf('%d',$noise) . ' db</b></td></tr>'
	. '</table>';
	}
/*********************************************************************************************************/
/*********************************************************************************************************/

echo("
	<script>
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
			$_SESSION['begdata'] = $keys[0];
			if($num <= 48)$visupt = 3;	

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
            	$tmin = $tmax = $min_hum = $max_hum = $tip = $d = '';
            	$key = $keys[$ii];         	
            	if(abs($key - $itime) < 2*$inter) //changement d'horaire
            		{if($ii < $num -1)++$ii; 
                	else $break = 1;
            		//$req =  "min_temp,max_temp,min_hum,max_hum,date_min_temp,date_max_temp,date_min_hum,date_max_hum";
            		$tmin = degree2($meas[$key][0]);
            		$tmax = degree2($meas[$key][1]);
             		$min_hum = $meas[$key][2]; 
             		$max_hum = $meas[$key][3]; 
           			$iidate = $jour[$day] . date(" d/m/y ",$key);
					$tip = tipHTMLext($iidate,$meas[$key][4],$meas[$key][5],$tmax,$tmin,$min_hum,$max_hum,$meas[$key][6],$meas[$key][7]);          		
            		}
                echo("dataExt.addRow([\"$idate\",'$tip',$tmax,$tmin,$min_hum,$max_hum,1]);\n"); 
                if($itime >= $date_end)$break = 1;
                $itime += $inter;
                }while($break != 1);
           	echo("dataExt.removeColumn(6);\n");	
            for($i = 8 ;$i >= 0;--$i)
                if($eraseExt[$i])echo("dataExt.removeColumn($i);\n");	
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
            	$idate = date("d/m H:i",$itime); 
            	$tmin =  $hum = $tip = '';
            	$key = $keys[$ii];            	
            	if(abs($key - $itime) < $inter*3) // mesures décalées
            		{if($ii < $num -1)++$ii;
                	else $break = 1;           			
            		$tmin = degree2($meas[$key][0]);
            		$hum = $meas[$key][1];  
            		$iidate = $jour[$day] . date(" d/m/y H:i",$key);         		           		
					$tip = tipHTMLext2($iidate,$tmin,$hum);
            		}
                else if(($key - $itime) < 0)
                    {while($ii < $num -1 &&  ($keys[++$ii] - $itime) < 0)
                        $key = $keys[$ii]; 
                    }
            		
                echo("dataExt.addRow([\"$idate\",'$tip',$tmin,$hum,1]);\n"); 
                if($itime >= $date_end)$break = 1;
                $itime += $inter;
                }while($break != 1);
           	echo("dataExt.removeColumn(4);\n");		
            for($i = 8 ;$i >= 0;--$i)
                if($eraseExt[$i])echo("dataExt.removeColumn($i);\n");	
           	
	}
	$titleExt = '"' .$stat_name. '-' .$ext_name. '   (' .$beg. ' - '.$end.' @'. $tinter .' '.$num.' mesures)"';       	                    	
	
/*********************************************************************************************************/

 			$keys= array_keys($meas1);
			$num = count($keys);
			$itime = $keys[0];  
			$beg = date("d/m/y", $keys[0]); 
			$end = date("d/m/y",$keys[$num-1]); 
			if($num <= 48)$visupt = 3;	

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
			if($MaxPression == $MinPression) $xp = 0;
			else $xp = 100/($MaxPression - $MinPression);		
		
	        $ii = $break = 0;	
            do
            	{$day = idate('w',$itime);
           		$idate = date("d/m/y",$itime);  
            	$tmin = $tmax = $hum = $co = $pres = $noise = $tip = '';
            	$key = $keys[$ii];            	    			          			
            	if(abs($key - $itime) < 2*$inter) //changement d'horaire
            		{if($ii < $num -1)++$ii; 
                	else $break = 1;       
            		$tmin = degree2($meas1[$key][0]);
            		$tmax = degree2($meas1[$key][1]);
                	$hum = $meas1[$key][2];
                	$co = $meas1[$key][3];
                	$pres = intval($meas1[$key][4]+.5);
                	$noise = $meas1[$key][5];                	
 //$req1 = "min_temp,max_temp,Humidity,CO2,min_pressure,max_noise";		
             		$iidate = $jour[$day] . date(" d/m/y",$key) . '&nbsp &nbsp &nbsp &nbsp' . date("H:i",$key);            		
                	$tip = tipHTMLint6($iidate,$tmax,$tmin,$hum,$co,$pres,$noise);
                	if($co){$co = min($co,1000);$co /= 10;}           
                	if($xp)$pres = intval(($pres-$MinPression)*$xp + .5);
                	}
                echo("dataInt.addRow([\"$idate\",'$tip',$tmax,$tmin,$hum,$co,$pres,$noise,1]);\n");                
                if($itime >= $date_end)$break = 1;
                $itime += $inter;
                }while($break != 1);
            echo("dataInt.removeColumn(8);\n");				      
     	    for($i = 8 ;$i >= 0;--$i)
                if($eraseInt[$i])echo("dataInt.removeColumn($i);\n");	
              	
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
			if($MaxPression == $MinPression) $xp = 0;
			else $xp = 100/($MaxPression - $MinPression);		
			    
	        $ii = $break = 0;
            do
            	{$day = idate('w',$itime);
            	$idate = date("d/m H:i",$itime); 
            	$tmin = $hum = $co = $pres = $noise = $tip = '';
            	$key = $keys[$ii]; 
            	if(abs($key - $itime) < 2*$inter) 
            		{if($ii < $num -1)++$ii; 
            	    else $break = 1;
            		$tmin = degree2($meas1[$key][0]);
                	$hum = $meas1[$key][1];
                	$co = $meas1[$key][2];
                	$pres = intval($meas1[$key][3] + .5);
                	$noise = $meas1[$key][4];  
           			$iidate = $jour[$day] . date(" d/m/y",$key) . '&nbsp &nbsp &nbsp &nbsp' . date("H:i",$key);
                	$tip = tipHTMLint5($iidate,$tmin,$hum,$co,$pres,$noise);
                	if($co){$co = min($co,1000);$co /= 10;}             
                	if($xp)$pres = intval(($pres-$MinPression)*$xp + .5);
                	}              	
                else if(($key - $itime) < 0)
                    {while($ii < $num -1 &&  ($keys[++$ii] - $itime) < 0)
                        {$key = $keys[$ii]; 
                        //$a = date("H:i",$key);$b = date("H:i",$itime);
                        //$txt = 'ii:'.$ii.' key:'.$a.' itime:'.$b.' dt:'.($key - $itime)/60;
                        //logMsg($txt);
                        }
                    }
                    
                echo("dataInt.addRow([\"$idate\",'$tip',$tmin,$hum,$co,$pres,$noise,1]);\n");                
                if($itime >= $date_end)$break = 1;
                $itime += $inter;
                }while($break != 1);
            echo("dataInt.removeColumn(7);\n");				      
      	    for($i = 8 ;$i >= 0;--$i)
                if($eraseInt[$i])echo("dataInt.removeColumn($i);\n");	
            } 
	$titleInt =  '"' .$stat_name. '-' .$int_name. '   (' .$beg. ' - '.$end.' @'. $tinter.' '.$num.' mesures @ '.$dateLoadData.')"';       	                    	

    echo("inter = $inter;\n");  
    echo("visupt = $visupt;\n");      
    echo("titleInt = $titleInt;\n");  
    echo("titleExt = $titleExt;\n");  
    echo 'var colorInt ='.json_encode($colorInt,true).";\n";
    echo 'var colorExt ='.json_encode($colorExt,true).";\n";
?>
	var chartExt = new google.visualization.LineChart(document.getElementById('chart1'));
    var chartInt = new google.visualization.LineChart(document.getElementById('chart0'));
/*    
    options = {focusTarget:"category",tooltip: {isHtml: true},curveType:"function"
               ,chartArea:{left:"5%",top:25,width:"85%",height:"75%"}
               ,pointSize:visupt,fontSize:10,titleTextStyle:{fontSize:12,color:"#303080",fontName:"Times"}
               };
                             
    optionsInt = options;
    optionsInt.title = titleInt;
    optionsInt.colors = colorInt;  
    chartInt.draw(dataInt,optionsInt);
    
    optionsExt = options;
    optionsExt.title = titleExt;
    optionsExt.colors = colorExt;
    chartExt.draw(dataExt,optionsExt);  
*/    
    param = 'opt={focusTarget:"category",tooltip: {isHtml: true},curveType:"function"'
            +',chartArea:{left:"5%",top:25,width:"85%",height:"75%"}'
            +',pointSize:visupt,fontSize:10,titleTextStyle:{fontSize:12,color:"#303080",fontName:"Times"}';

    paramInt = param+',title:titleInt,colors:colorInt}';
    chartInt.draw(dataInt,eval(paramInt));
    paramExt = param+',title:titleExt,colors:colorExt}';
    chartExt.draw(dataExt,eval(paramExt));

/* 
    row: time 
    plus petite colonne 1 
    si une seule courbe -> 3 colonnes (t, tooltip, courbe)
*/
    google.visualization.events.addListener(chartInt, 'select', IntClickHandler);        
     function IntClickHandler()
        {var selection = chartInt.getSelection();
        var num = colorInt.length;
        for (var i = 0; i < selection.length; i++) 
            {var item = selection[i];
            if(item.row != null  && (dataInt.getNumberOfRows() > 20) && !isMobile()) 
                top.location.href='graphiques.php?row='+item.row;
            if(item.column != null && dataInt.getNumberOfColumns() > 3) 
                {dataInt.removeColumn(item.column); 
                for(var col = item.column-2;col < num-1;col++)
                    colorInt[col] = colorInt[col+1]; 
                chartInt.draw(dataInt,eval(paramInt));
                return;
                }
            }
        }
    google.visualization.events.addListener(chartExt, 'select', ExtClickHandler);        
    function ExtClickHandler()
        {var selection = chartExt.getSelection();
        var num = colorExt.length;  
        for (var i = 0; i < selection.length; i++) 
            {var item = selection[i];
            if(item.row != null  && dataExt.getNumberOfRows() > 20  && !isMobile())
                top.location.href='graphiques.php?row='+item.row;
            if(item.column != null && dataExt.getNumberOfColumns() > 3) // date,tooltip
                {dataExt.removeColumn(item.column); 
                for(var col = item.column-2;col < num-1;col++)
                    colorExt[col] = colorExt[col+1];  
                chartExt.draw(dataExt,eval(paramExt));  
                return;
                }
            }
         }
         
} // endDraw 
           
</script>
</head>
<body>
 <?php
	drawCharts('G');	
 ?>
</body>
</html>
